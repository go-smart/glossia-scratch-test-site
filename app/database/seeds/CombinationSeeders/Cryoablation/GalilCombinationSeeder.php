<?php namespace CombinationSeeders\Cryoablation;
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

class GalilCombinationSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
    $modality['cryo'] = Modality::whereName('Cryoablation')->first();
    $model['numa sif'] = NumericalModel::whereName('NUMA Cryoablation Basic SIF')->first();
    $model['galilfoam'] = NumericalModel::whereName('Galil OpenFOAM')->first();

    /* Add generators */
    $generator['visual-ice'] = new PowerGenerator;
    $generator['visual-ice']->fill(array(
      'name' => 'Visual-ICE',
      'manufacturer' => 'Galil Medical'
    ));
    $modality['cryo']->powerGenerators()->save($generator['visual-ice']);

    $generator['presice'] = new PowerGenerator;
    $generator['presice']->fill(array(
      'name' => 'Presice',
      'manufacturer' => 'Galil Medical'
    ));
    $modality['cryo']->powerGenerators()->save($generator['presice']);

    $generator['seednet'] = new PowerGenerator;
    $generator['seednet']->fill(array(
      'name' => 'SeedNet',
      'manufacturer' => 'Galil Medical'
    ));
    $modality['cryo']->powerGenerators()->save($generator['seednet']);

    $generator['mri-seednet'] = new PowerGenerator;
    $generator['mri-seednet']->fill(array(
      'name' => 'MRI SeedNet',
      'manufacturer' => 'Galil Medical'
    ));
    $modality['cryo']->powerGenerators()->save($generator['mri-seednet']);

    $probes = [
      'IceSEED' => ['x', 'y', 'z', '0', 'b'],
      'IceEDGE' => ['x', 'y', 'z', '0', '123'],
      'IceROD' => ['x', 'y', 'z', '0', '143'],
      'IceSPHERE' => ['x', 'y', 'z', '0', 'b'],
    ];

    /* Add needles */
    foreach ($probes as $name => $probeA)
    {
      $probe = new Needle(['name' => $name, 'manufacturer' => 'Galil Medical', 'file' => 'library:cryo-two-part-cylinder-1', 'class' => 'solid-boundary']);
      $modality['cryo']->needles()->save($probe);

      foreach ($generator as $g)
        $probe->powerGenerators()->attach($g);

      $probe->attribute(['name' => 'NEEDLE_GAUGE', 'type' => 'float', 'value' => "$probeA[0]", 'widget' => 'textbox']);
      $probe->attribute(['name' => 'NEEDLE_SHAFT_LENGTH', 'type' => 'float', 'value' => "$probeA[1]", 'widget' => 'textbox']);
      $probe->attribute(['name' => 'NEEDLE_ACTIVE_THAWING_TEMPERATURE', 'type' => 'float', 'value' => "$probeA[2]", 'widget' => 'textbox']);
      $probe->attribute(['name' => 'NEEDLE_PASSING_THAWING_HEAT_FLUX', 'type' => 'float', 'value' => "$probeA[3]", 'widget' => 'textbox']);
      $probe->attribute(['name' => 'NEEDLE_FREEZING_TEMPERATURE', 'type' => 'float', 'value' => "$probeA[4]", 'widget' => 'textbox']);
      $needle[$name] = $probe;
      $probe->save();
    }
    /* Add protocols */
    $protocol['empty'] = new Protocol;
    $protocol['empty']->fill(array(
      'name' => 'Empty'
    ));
    $modality['cryo']->protocols()->save($protocol['empty']);

    /* Add combinations */
     foreach ($model as $m)
      foreach ($generator as $g) {
        $c = new Combination;
        $c->protocol()->associate($protocol['empty']);
        $c->powerGenerator()->associate($g);
        $c->numericalModel()->associate($m);
        $c->context()->associate(Context::whereName('kidney')->first());
        $c->save();
        foreach ($needle as $n)
          $c->needles()->attach($n);
        $combination['galil-' . $g->name . '-' . $m->name] = $c;
      }
	}

}
