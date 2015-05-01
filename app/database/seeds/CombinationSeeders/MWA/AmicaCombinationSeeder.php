<?php namespace CombinationSeeders\MWA;
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
use \Combination;
use \Modality;
use \Needle;
use \NumericalModel;
use \Parameter;
use \PowerGenerator;
use \Protocol;
use \Context;

class AmicaCombinationSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
    $modality['mwa'] = Modality::whereName('MWA')->first();
    $model['mwa linear sif'] = NumericalModel::whereName('NUMA MWA Linear SIF')->first();
    $model['mwa nonlinear sif'] = NumericalModel::whereName('NUMA MWA Nonlinear SIF')->first();

    /* Add generators */
    $generator['amica-gen'] = new PowerGenerator;
    $generator['amica-gen']->fill(array(
      'Name' => 'AMICA-GEN AGN-H-1.0',
      'Manufacturer' => 'HS'
    ));
    $modality['mwa']->powerGenerators()->save($generator['amica-gen']);
    $generator['amica-gen']->attribute(['Name' => 'GENERATOR_FREQUENCY', 'Type' => 'float', 'Value' => 2450.0, 'Units' => 'MHz', 'Widget' => 'textbox']);
    $generator['amica-gen']->attribute(['Name' => 'GENERATOR_MAX_WATT_CW', 'Type' => 'float', 'Value' => 140.0, 'Units' => 'W', 'Widget' => 'textbox']);
    $generator['amica-gen']->attribute(['Name' => 'NEEDLE_MAX_AMOUNT', 'Type' => 'int', 'Value' => "1", 'Widget' => 'textbox']);
    $generator['amica-gen']->attribute(['Name' => 'NEEDLE_MIN_AMOUNT', 'Type' => 'int', 'Value' => "1", 'Widget' => 'textbox']);
    $result = $generator['amica-gen']->attribute(['Name' => 'CONSTANT_DUMMY', 'Type' => 'float', 'Widget' => 'points-over-time', 'Value' => '120', 'Units' => 'W']);

    /* Add needles */
    $coaxial_cables = [[1.5, 105, 'S1.5'], [2.5, 90, '']];
    $amica_probe_models = [[11, 150], [14, 150], [14, 200], [14, 270], [16, 150], [16, 200], [16, 270]];
    foreach ($coaxial_cables as $coax)
      foreach ($amica_probe_models as $probeA)
      {
        if ($probeA[0] > 14 && $coax[1] > 90)
          continue;

        $name = "APK$probeA[0]$probeA[1]T19V5" . $coax[2];

        $probe = new Needle;
        $probe->fill(array(
          'Name' => $name,
          'Manufacturer' => 'HS',
          'File' => '',
          'Geometry' => 'library:default',
          'Class' => 'axisymm-2d'
        ));
        $modality['mwa']->needles()->save($probe);
        $probe->powerGenerators()->attach($generator['amica-gen']);
        $probe->attribute(['Name' => 'NEEDLE_GAUGE', 'Type' => 'float', 'Value' => "$probeA[0]", 'Widget' => 'textbox']);
        $probe->attribute(['Name' => 'NEEDLE_LENGTH', 'Type' => 'float', 'Value' => "$probeA[1]", 'Widget' => 'textbox']);
        $probe->attribute(['Name' => 'NEEDLE_COAXIAL_CABLE_LENGTH', 'Type' => 'float', 'Value' => $coax[0], 'Widget' => 'textbox']);
        $probe->attribute(['Name' => 'NEEDLE_MAX_POWER_OUTPUT', 'Type' => 'float', 'Value' => $coax[1], 'Widget' => 'textbox']);
        //FIXME: check these true for all
        $probe->attribute(['Name' => 'NEEDLE_CHOKE', 'Type' => 'boolean', 'Value' => 'true', 'Widget' => 'select']);
        $probe->attribute(['Name' => 'NEEDLE_RING_WIDTH', 'Type' => 'float', 'Value' => '5', 'Units' => 'mm', 'Widget' => 'select']);
        $probe->attribute(['Name' => 'NEEDLE_ACTIVE_REGION', 'Type' => 'float', 'Value' => '20', 'Units' => 'mm', 'Widget' => 'textbox']);
        $probe->attribute(['Name' => 'NEEDLE_COOLING', 'Type' => 'boolean', 'Value' => 'true', 'Widget' => 'textbox']);
        $needle[$name] = $probe;
      }

    /* Add protocols */
    $protocol['user-modified'] = new Protocol;
    $protocol['user-modified']->fill(array(
      'Name' => 'Generic modifiable power',
    ));
    $modality['mwa']->protocols()->save($protocol['user-modified']);
/*
    $algorithm["user-modified power"] = new Algorithm;
    $algorithm["user-modified power"]->content = <<<ENDLIPSUM2
ENDLIPSUM2;
    $algorithm["user-modified power"]->protocol()->associate($protocol['user-modified']);
    $algorithm["user-modified power"]->result()->associate($result);
    $algorithm["user-modified power"]->save();
    $algorithm["user-modified power"]->attribute(['Name' => 'CONSTANT_INPUT_POWER', 'Type' => 'array(Time,float)', 'Value' => '{"0.0": 150, "2.0": 300}']);

    $algorithm["user-modified power"]->attribute(['Name' => 'NEEDLE_MAX_POWER_OUTPUT', 'Type' => 'float', 'Description' => 'Maximum power that can be output from this needle']);

    $argument = new Argument;
    $argument->name = "Time";
    $argument->argumentable()->associate($algorithm["user-modified power"]);
    $argument->save();
*/

    /* Add combinations */
    foreach (['liver', 'kidney', 'lung'] as $organ)
    {
      $o = Context::byNameFamily($organ, 'organ');
        foreach ($model as $m) {
          $c = new Combination;
          $c->isPublic = ($m->Name == 'NUMA MWA Nonlinear SIF');
          $c->protocol()->associate($protocol['user-modified']);
          $c->powerGenerator()->associate($generator['amica-gen']);
          $c->numericalModel()->associate($m);
          $c->context()->associate($o);
          $c->save();
          foreach ($needle as $n)
            $c->needles()->attach($n);
          $combination['amica-' . $m->name] = $c;
        }
    }
	}

}
