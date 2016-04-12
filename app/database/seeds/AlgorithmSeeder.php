<?php
/**
 * This file is part of the Go-Smart Simulation Architecture (GSSA).
 * Go-Smart is an EU-FP7 project, funded by the European Commission.
 *
 * Copyright (C) 2013-  NUMA Engineering Ltd. (see AUTHORS file)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


class AlgorithmSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Eloquent::unguard();

    $algorithmsXmls = File::allFiles(public_path() . '/algorithms');

    foreach ($algorithmsXmls as $algorithmsXml) {
      $dom = new DomDocument;
      $dom->load($algorithmsXml);
      $root = $dom->documentElement;

      $modality = Modality::whereName($root->getAttribute('modality'))->first();

      if (empty($modality))
        throw new Exception("Could not find modality! ($algorithmsXml)");

      $protocolName = $root->getAttribute('protocol');
      $protocol = Protocol::whereName($protocolName)->whereModalityId($modality->Id)->first();

      if (empty($protocol)) {
        \Log::warning("Could not find protocol! ($algorithmsXml)");
        continue;
      }

      $arguments = [];
      $parameters = [];
      $description = "";

      foreach ($root->childNodes as $node) {
        if (get_class($node) == 'DOMText')
          continue;

        switch ($node->nodeName) {
        case 'arguments':
          foreach ($node->childNodes as $argument) {
            if (get_class($argument) == 'DOMText')
              continue;
            $arguments[] = ['Name' => $argument->getAttribute('name')];
          }
          break;
        case 'parameters':
          foreach ($node->childNodes as $parameter) {
            if (get_class($parameter) == 'DOMText')
              continue;
            $parameters[] = [
                'Name' => $parameter->getAttribute('name'),
                'Type' => $parameter->getAttribute('type'),
                'Value' => $parameter->hasAttribute('value') ? $parameter->getAttribute('value') : null
            ];
          }
          break;
        case 'description':
          $description = $node->textContent;
          break;
        default:
          throw new Exception("Unrecognized entry in algorithm XML - $node->nodeName! ($algorithmsXml)");
        }
      }

      $algorithm = new Algorithm;
      $algorithm->content = $description;

      $resultName = $root->getAttribute('result');
      $resultType = $root->getAttribute('type');
      $result = Parameter::whereName($resultName)->first();
      if (empty($result))
      {
        $result = Parameter::create(['Name' => $resultName, 'Type' => $resultType]);
      }
      $algorithm->result()->associate($result);
      $algorithm->protocol()->associate($protocol);
      $algorithm->save();

      foreach ($arguments as $argument)
        $algorithm->arguments()->attach(Argument::create($argument));

      foreach ($parameters as $parameter)
        $algorithm->attribute($parameter);
    }
  }

}
