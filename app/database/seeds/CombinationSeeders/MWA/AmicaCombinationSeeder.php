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
use \Argument;
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
      'name' => 'AMICA-GEN AGN-H-1.0',
      'manufacturer' => 'HS'
    ));
    $modality['mwa']->powerGenerators()->save($generator['amica-gen']);
    $generator['amica-gen']->attribute(['name' => 'GENERATOR_FREQUENCY', 'type' => 'float', 'value' => 2450.0, 'units' => 'MHz', 'widget' => 'textbox']);
    $generator['amica-gen']->attribute(['name' => 'GENERATOR_MAX_WATT_CW', 'type' => 'float', 'value' => 140.0, 'units' => 'W', 'widget' => 'textbox']);
    $result = $generator['amica-gen']->attribute(['name' => 'CONSTANT_INPUT_POWER', 'type' => 'float', 'widget' => 'points-over-time', 'value' => '120', 'units' => 'W']);

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
          'name' => $name,
          'manufacturer' => 'HS',
          'file' => 'library:default',
          'class' => 'axisymm-2d'
        ));
        $modality['mwa']->needles()->save($probe);
        $probe->powerGenerators()->attach($generator['amica-gen']);
        $probe->attribute(['name' => 'NEEDLE_GAUGE', 'type' => 'float', 'value' => "$probeA[0]", 'widget' => 'textbox']);
        $probe->attribute(['name' => 'NEEDLE_LENGTH', 'type' => 'float', 'value' => "$probeA[1]", 'widget' => 'textbox']);
        $probe->attribute(['name' => 'NEEDLE_COAXIAL_CABLE_LENGTH', 'type' => 'float', 'value' => $coax[0], 'widget' => 'textbox']);
        $probe->attribute(['name' => 'NEEDLE_MAX_POWER_OUTPUT', 'type' => 'float', 'value' => $coax[1], 'widget' => 'textbox']);
        //FIXME: check these true for all
        $probe->attribute(['name' => 'NEEDLE_CHOKE', 'type' => 'boolean', 'value' => 'true', 'widget' => 'select']);
        $probe->attribute(['name' => 'NEEDLE_RING_WIDTH', 'type' => 'float', 'value' => '5', 'units' => 'mm', 'widget' => 'select']);
        $probe->attribute(['name' => 'NEEDLE_ACTIVE_REGION', 'type' => 'float', 'value' => '20', 'units' => 'mm', 'widget' => 'textbox']);
        $probe->attribute(['name' => 'NEEDLE_COOLING', 'type' => 'boolean', 'value' => 'true', 'widget' => 'textbox']);
        $needle[$name] = $probe;
      }

    /* Add protocols */
    $protocol['user-modified'] = new Protocol;
    $protocol['user-modified']->fill(array(
      'name' => 'Generic modifiable power',
    ));
    $modality['mwa']->protocols()->save($protocol['user-modified']);

    $algorithm["user-modified power"] = new Algorithm;
    $algorithm["user-modified power"]->content = <<<ENDLIPSUM2
! Because the type of the result parameter has a widget points-over-time,
! this can be overridden by doctors via their graph.
! If they don't, clearly we need a default...
return \$NEEDLE_MAX_POWER_OUTPUT;
ENDLIPSUM2;
    $algorithm["user-modified power"]->protocol()->associate($protocol['user-modified']);
    $algorithm["user-modified power"]->result()->associate($result);
    $algorithm["user-modified power"]->save();

    $algorithm["user-modified power"]->attribute(['name' => 'NEEDLE_MAX_POWER_OUTPUT', 'type' => 'float', 'description' => 'Maximum power that can be output from this needle']);

    $argument = new Argument;
    $argument->name = "Time";
    $argument->argumentable()->associate($algorithm["user-modified power"]);
    $argument->save();

    /* Add combinations */
    foreach (['liver', 'kidney', 'lung'] as $organ)
    {
      $o = Context::whereName($organ)->first();
        foreach ($model as $m) {
          $c = new Combination;
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
