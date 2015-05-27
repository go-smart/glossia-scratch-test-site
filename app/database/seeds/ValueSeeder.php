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

class ValueSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Eloquent::unguard();

    $parameterFields = [
      'name', 'type', 'widget', 'description', 'units',
      'priority', 'restriction'
    ];
    $constantsXmls = File::allFiles(public_path() . '/constants');

    foreach ($constantsXmls as $constantsXml) {
      $dom = new DomDocument;
      $dom->load($constantsXml);
      $root = $dom->documentElement;
      $class = $root->getAttribute('class');

      /* Not very safe! */
      $object = new $class;
      $name = $root->getAttribute('name');
      $objectQuery = $object->whereName($name);

      if ($root->hasAttribute('family'))
        $objectQuery = $objectQuery->whereFamily($root->getAttribute('family'));

      $target = $objectQuery->first();

      if (empty($target))
        throw new Exception("Did not find object $name ($class) for $constantsXml");

      foreach ($root->childNodes as $constant) {
        if (get_class($constant) == 'DOMText')
          continue;

        if (!$constant->hasAttribute('name'))
          throw new Exception("Constant missing name! ($constantsXml)");

        $present = array_filter($parameterFields, [$constant, 'hasAttribute']);
        $parameterData = [];

        array_map(function ($a) use (&$parameterData, $constant) {
          $parameterData[$a] = $constant->getAttribute($a);
        }, $present);

        if (!$constant->hasAttribute('description'))
          $parameterData['description'] = $parameterData['name'];

        $parameterData['name'] = preg_replace('/[ -]/', '_', $parameterData['name']);
        $parameterData['name'] = 'CONSTANT_' . strtoupper(preg_replace('/[[:^word:]]/', '', $parameterData['name']));

        $parameter = Parameter::whereName($parameterData['name'])->first();
        if (empty($parameter))
          $parameter = Parameter::create($parameterData);
        $id_name = snake_case($class) . '_id';
        $attributionData = [$id_name => $target->id, 'parameter_id' => $parameter->id];

        if ($constant->hasAttribute('context'))
          $attributionData['context_id'] = Context::whereName($constant->getAttribute('context'))->first()->id;

        if ($constant->hasAttribute('value'))
          $attributionData['value'] = $constant->getAttribute('value');
        else
          $attributionData['value'] = null;

        $attribution = ParameterAttribution::create($attributionData);
      }
    }
  }

}
