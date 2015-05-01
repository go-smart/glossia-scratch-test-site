<?php namespace CombinationSeeders\RFA;
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
use \Context;

class BostonScientificCombinationSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
    /* Add modalities */
    $modality['rfa'] = Modality::whereName('RFA')->first();
    $model['rfa basic sif'] = NumericalModel::whereName('NUMA RFA Basic SIF')->first();

    /* Add generators */
    $generator['rf3000'] = new PowerGenerator;
    $generator['rf3000']->fill(array(
      'Name' => 'RF3000',
      'Manufacturer' => 'Boston Scientific'
    ));
    $modality['rfa']->PowerGenerators()->save($generator['rf3000']);
    $generator['rf3000']->attribute(['Name' => 'GENERATOR_WATTAGE', 'Type' => 'float', 'Value' => 200.0, 'Widget' => 'textbox']);
    $generator['rf3000']->attribute(['Name' => 'INITIAL_POWER', 'Type' => 'float', 'Value' => 200.0, 'Widget' => 'textbox']);
    $generator['rf3000']->attribute(['Name' => 'MAX_POWER', 'Type' => 'float', 'Value' => 200.0, 'Widget' => 'textbox']);
    $generator['rf3000']->attribute(['Name' => 'NEEDLE_MAX_AMOUNT', 'Type' => 'int', 'Value' => "1", 'Widget' => 'textbox']);
    $generator['rf3000']->attribute(['Name' => 'NEEDLE_MIN_AMOUNT', 'Type' => 'int', 'Value' => "1", 'Widget' => 'textbox']);

    /* Add needles */
    $needle['leveen std 3cm'] = new Needle;
    $needle['leveen std 3cm']->fill(array(
      'Name' => 'LeVeen Standard 3cm',
      'Manufacturer' => 'Boston Scientific',
      'File' => '',
      'Geometry' => 'library:umbrella tines',
      'Class' => 'point-sources'
    ));
    $modality['rfa']->Needles()->save($needle['leveen std 3cm']);
    $needle['leveen std 3cm']->powerGenerators()->attach($generator['rf3000']);
    $needle['leveen std 3cm']->attribute(['Name' => 'NEEDLE_EXTENSION', 'Type' => 'float', 'Value' => 3.0, 'Widget' => 'textbox']);

    $needle['leveen std 3.5cm'] = new Needle;
    $needle['leveen std 3.5cm']->fill(array(
      'Name' => 'LeVeen Standard 3.5cm',
      'Manufacturer' => 'Boston Scientific',
      'File' => '',
      'Geometry' => 'library:umbrella tines',
      'Class' => 'point-sources'
    ));
    $modality['rfa']->Needles()->save($needle['leveen std 3.5cm']);
    $needle['leveen std 3.5cm']->powerGenerators()->attach($generator['rf3000']);
    $needle['leveen std 3.5cm']->attribute(['Name' => 'NEEDLE_EXTENSION', 'Type' => 'float', 'Value' => 3.5, 'Widget' => 'textbox']);

    $needle['leveen std 4cm'] = new Needle;
    $needle['leveen std 4cm']->fill(array(
      'Name' => 'LeVeen Standard 4cm',
      'Manufacturer' => 'Boston Scientific',
      'File' => '',
      'Geometry' => 'library:umbrella tines',
      'Class' => 'point-sources'
    ));
    $modality['rfa']->Needles()->save($needle['leveen std 4cm']);
    $needle['leveen std 4cm']->powerGenerators()->attach($generator['rf3000']);
    $needle['leveen std 4cm']->attribute(['Name' => 'NEEDLE_EXTENSION', 'Type' => 'float', 'Value' => 4.0, 'Widget' => 'textbox']);

    $needle['leveen std 5cm'] = new Needle;
    $needle['leveen std 5cm']->fill(array(
      'Name' => 'LeVeen Standard 5cm',
      'Manufacturer' => 'Boston Scientific',
      'File' => '',
      'Geometry' => 'library:umbrella tines',
      'Class' => 'point-sources'
    ));
    $modality['rfa']->Needles()->save($needle['leveen std 5cm']);
    $needle['leveen std 5cm']->powerGenerators()->attach($generator['rf3000']);
    $needle['leveen std 5cm']->attribute(['Name' => 'NEEDLE_EXTENSION', 'Type' => 'float', 'Value' => 5.0, 'Widget' => 'textbox']);

    $needle['leveen super slim 2cm'] = new Needle;
    $needle['leveen super slim 2cm']->fill(array(
      'Name' => 'LeVeen Super Slim 2cm',
      'Manufacturer' => 'Boston Scientific',
      'File' => '',
      'Geometry' => 'library:umbrella tines',
      'Class' => 'point-sources'
    ));
    $modality['rfa']->Needles()->save($needle['leveen super slim 2cm']);
    $needle['leveen super slim 2cm']->powerGenerators()->attach($generator['rf3000']);
    $needle['leveen super slim 2cm']->attribute(['Name' => 'NEEDLE_EXTENSION', 'Type' => 'float', 'Value' => 2.0, 'Widget' => 'textbox']);

    $needle['leveen super slim 3cm'] = new Needle;
    $needle['leveen super slim 3cm']->fill(array(
      'Name' => 'LeVeen Super Slim 3cm',
      'Manufacturer' => 'Boston Scientific',
      'File' => '',
      'Geometry' => 'library:umbrella tines',
      'Class' => 'point-sources'
    ));
    $modality['rfa']->Needles()->save($needle['leveen super slim 3cm']);
    $needle['leveen super slim 3cm']->powerGenerators()->attach($generator['rf3000']);
    $needle['leveen super slim 3cm']->attribute(['Name' => 'NEEDLE_EXTENSION', 'Type' => 'float', 'Value' => 3.0, 'Widget' => 'textbox']);

    /* Add protocols */
    foreach (array('3cm', '3.5cm', '4cm', '5cm') as $n) {
      $protocol["leveen std $n"] = new Protocol;
      $protocol["leveen std $n"]->fill(array(
        'Name' => "LeVeen Standard $n Ablation Algorithm",
      ));
      $modality['rfa']->protocols()->save($protocol["leveen std $n"]);
    }

    foreach (array('2cm', '3cm') as $n) {
      $protocol["leveen super slim $n"] = new Protocol;
      $protocol["leveen super slim $n"]->fill(array(
        'Name' => "LeVeen Super Slim $n Ablation Algorithm",
      ));
      $modality['rfa']->protocols()->save($protocol["leveen super slim $n"]);
    }

    /* This is now done via XML files
    foreach ($protocol as $pn => $p) {
      $algorithm["$pn power"] = new Algorithm;
      $algorithm["$pn power"]->content = <<<ENDLIPSUM2
Fusce non ex tellus. Vestibulum aliquet lacinia augue, et euismod purus 
congue eget. Nullam sit amet aliquet risus. Mauris suscipit lorem erat, 
a lacinia eros commodo ac. Praesent eu varius magna. Ut aliquet mauris 
nulla, at commodo mi blandit in. Aliquam vel quam rhoncus, mattis lorem 
vitae, tempus tortor. Class aptent taciti sociosqu ad litora torquent 
per conubia nostra, per inceptos himenaeos. Nullam id facilisis felis. 
Nulla facilisi. In et sapien leo. Pellentesque in ornare elit. In sed 
finibus lectus, eget sodales risus.

Vestibulum vitae nibh eget arcu ullamcorper fringilla. Phasellus rhoncus 
libero nulla, vel sollicitudin odio laoreet eu. Lorem ipsum dolor sit 
amet, consectetur adipiscing elit. Sed quis facilisis libero, at ornare 
augue. Integer dignissim, mauris in malesuada tincidunt, turpis nunc 
tempor diam, at pretium mi quam id lectus. In nunc metus, ultrices id 
consectetur sit amet, luctus vitae nunc. Duis in tristique erat, sit 
amet ultrices lectus. Nunc mollis tellus non urna mollis pretium. In 
efficitur ante vel ante pharetra, eget elementum ipsum fermentum. 
ENDLIPSUM2;
      $algorithm["$pn power"]->protocol()->associate($protocol[$pn]);

      $result = new Parameter;
      $result->fill(['Name' => 'INPUT_POWER', 'Type' => 'float']);
      $result->save();
      $algorithm["$pn power"]->result()->associate($result);
      $algorithm["$pn power"]->save();

      $algorithm["$pn power"]->attribute(['Name' => 'NEEDLE_EXTENSION', 'Type' => 'float', 'Restriction' => 'needle']);
      $algorithm["$pn power"]->attribute(['Name' => 'INITIAL_POWER', 'Type' => 'float', 'Restriction' => 'generator']);

      $argument = new Argument;
      $argument->name = "Temperature";
      $argument->argumentable()->associate($algorithm["$pn power"]);
      $argument->save();

      $argument = new Argument;
      $argument->name = "Time";
      $argument->argumentable()->associate($algorithm["$pn power"]);
      $argument->save();
    }
     */

    foreach (array('kidney', 'liver', 'lung') as $organ) {
      $o = Context::byNameFamily($organ, 'organ');

      foreach (array('3cm', '3.5cm', '4cm', '5cm') as $n) {
        $c = new Combination;
        $c->isPublic = true;
        $c->protocol()->associate($protocol['leveen std ' . $n]);
        $c->powerGenerator()->associate($generator['rf3000']);
        $c->numericalModel()->associate($model['rfa basic sif']);
        $c->context()->associate($o);
        $c->save();
        $c->Needles()->attach($needle['leveen std ' . $n]);
        $combination['bs-' . $n] = $c;
      }

      foreach (array('2cm', '3cm') as $n) {
        $c = new Combination;
        $c->isPublic = true;
        $c->Protocol()->associate($protocol['leveen super slim ' . $n]);
        $c->PowerGenerator()->associate($generator['rf3000']);
        $c->NumericalModel()->associate($model['rfa basic sif']);
        $c->Context()->associate($o);
        $c->save();
        $c->Needles()->attach($needle['leveen super slim ' . $n]);
        $combination['bs-' . $n] = $c;
      }
    }
	}

}
