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


use \Seeder;

use \Algorithm;
use \Argument;
use \Combination;
use \Modality;
use \Needle;
use \NumericalModel;
use \Parameter;
use \PowerGenerator;
use \Protocol;

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
      $protocol = Protocol::whereName($protocolName)->whereModalityId($modality->id)->first();

      if (empty($protocol))
        throw new Exception("Could not find protocol! ($algorithmsXml)");

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
            $arguments[] = ['name' => $argument->getAttribute('name')];
          }
          break;
        case 'parameters':
          foreach ($node->childNodes as $parameter) {
            if (get_class($parameter) == 'DOMText')
              continue;
            $parameters[] = [
                'name' => $parameter->getAttribute('name'),
                'type' => $parameter->getAttribute('type'),
                'value' => $parameter->hasAttribute('value') ? $parameter->getAttribute('value') : null
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
      $result = Parameter::whereName($resultName)->first();
      if (empty($result))
      {
        $result = Parameter::create(['name' => $resultName]);
      }
      $algorithm->result()->associate($result);
      $algorithm->protocol()->associate($protocol);
      $algorithm->save();

      foreach ($arguments as $argument)
        $algorithm->arguments()->save(new Argument($argument));

      foreach ($parameters as $parameter)
        $algorithm->attribute($parameter);
    }
  }

}
