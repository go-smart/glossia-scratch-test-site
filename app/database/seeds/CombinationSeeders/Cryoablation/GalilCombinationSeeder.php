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
    \Eloquent::unguard();
    $modality[''] = Modality::whereName('Cryoablation')->first();
    $modality[' [G]'] = Modality::whereName('Cryoablation [GS-only]')->first();

    $model[''] = NumericalModel::whereName('NUMA Cryoablation Basic SIF')->first();
    $model[' [G]'] = NumericalModel::whereName('GOpenFOAM')->first();
    $needleFile = Needle::where('FileName', '=', 'needle_mw')->first();

    foreach ($modality as $mlabel => $m)
    {
      /* Add generators */
      $generator['visual-ice'] = new PowerGenerator;
      $generator['visual-ice']->fill(array(
        'Name' => 'Visual-ICE' . $mlabel,
        'Manufacturer' => 'Galil Medical'
      ));
      $m->powerGenerators()->save($generator['visual-ice']);
      $generator['visual-ice']->attribute(['Name' => 'NEEDLE_MIN_AMOUNT', 'Type' => 'int', 'Value' => "1", 'Widget' => 'textbox']);
      $generator['visual-ice']->attribute(['Name' => 'NEEDLE_MAX_AMOUNT', 'Type' => 'int', 'Value' => "10", 'Widget' => 'textbox']);

      $generator['Presice'] = new PowerGenerator;
      $generator['Presice']->fill(array(
        'Name' => 'Presice' . $mlabel,
        'Manufacturer' => 'Galil Medical'
      ));
      $m->powerGenerators()->save($generator['Presice']);
      $generator['Presice']->attribute(['Name' => 'NEEDLE_MIN_AMOUNT', 'Type' => 'int', 'Value' => "1", 'Widget' => 'textbox']);
      $generator['Presice']->attribute(['Name' => 'NEEDLE_MAX_AMOUNT', 'Type' => 'int', 'Value' => "10", 'Widget' => 'textbox']);

      $generator['Seednet'] = new PowerGenerator;
      $generator['Seednet']->fill(array(
        'Name' => 'SeedNet' . $mlabel,
        'Manufacturer' => 'Galil Medical'
      ));
      $m->powerGenerators()->save($generator['Seednet']);
      $generator['Seednet']->attribute(['Name' => 'NEEDLE_MIN_AMOUNT', 'Type' => 'int', 'Value' => "1", 'Widget' => 'textbox']);
      $generator['Seednet']->attribute(['Name' => 'NEEDLE_MAX_AMOUNT', 'Type' => 'int', 'Value' => "10", 'Widget' => 'textbox']);

      $generator['mri-seednet'] = new PowerGenerator;
      $generator['mri-seednet']->fill(array(
        'Name' => 'MRI SeedNet' . $mlabel,
        'Manufacturer' => 'Galil Medical'
      ));
      $m->powerGenerators()->save($generator['mri-seednet']);
      $generator['mri-seednet']->attribute(['Name' => 'NEEDLE_MIN_AMOUNT', 'Type' => 'int', 'Value' => "1", 'Widget' => 'textbox']);
      $generator['mri-seednet']->attribute(['Name' => 'NEEDLE_MAX_AMOUNT', 'Type' => 'int', 'Value' => "10", 'Widget' => 'textbox']);

      $probes = [
        'IceSEED' => ['X', '20', '288', '0', 'B'],
        'IceEDGE' => ['X', '45', '288', '0', '123'],
        'IceROD' => ['X', '45', '288', '0', '143'],
        'IceSPHERE' => ['X', '25', '288', '0', 'B'],
      ];

      /* Add needles */
      foreach ($probes as $name => $probeA)
      {
        $name .= $mlabel;
        print $name;
        $probe = new Needle(['Name' => $name, 'Manufacturer' => 'Galil Medical', 'Geometry' => 'library:rfa-cylinder-1', 'Class' => 'solid-boundary',
        'FileId' => $needleFile->FileId, 'Extension' => $needleFile->Extension, 'FileName' => $needleFile->FileName]);
        $m->needles()->save($probe);

        foreach ($generator as $g)
          $probe->powerGenerators()->attach($g);

        $probe->attribute(['Name' => 'NEEDLE_GAUGE', 'Type' => 'float', 'Value' => "$probeA[0]", 'Widget' => 'textbox']);
        $probe->attribute(['Name' => 'NEEDLE_ACTIVE_LENGTH', 'Type' => 'float', 'Value' => "$probeA[1]", 'Widget' => 'textbox']);
        $probe->attribute(['Name' => 'NEEDLE_ACTIVE_THAWING_TEMPERATURE', 'Type' => 'float', 'Value' => "$probeA[2]", 'Widget' => 'textbox']);
        $probe->attribute(['Name' => 'NEEDLE_PASSING_THAWING_HEAT_FLUX', 'Type' => 'float', 'Value' => "$probeA[3]", 'Widget' => 'textbox']);
        $probe->attribute(['Name' => 'NEEDLE_FREEZING_TEMPERATURE', 'Type' => 'float', 'Value' => "$probeA[4]", 'Widget' => 'textbox']);
        $needle[$name] = $probe;
        $probe->save();
      }
      /* Add protocols */
      $protocol['Empty'] = new Protocol;
      $protocol['Empty']->fill(array(
        'Name' => 'Empty' . $mlabel
      ));
      $m->protocols()->save($protocol['Empty']);

      /* Add combinations */
      $m = $model[$mlabel];
        foreach ($generator as $g) {
          $c = new Combination;
          $c->isPublic = true; //($m->Name == 'NUMA Cryoablation Basic SIF');
          $c->protocol()->associate($protocol['Empty']);
          $c->powerGenerator()->associate($g);
          $c->numericalModel()->associate($m);
          $c->context()->associate(Context::byNameFamily('kidney', 'organ'));
          $c->save();
          foreach ($needle as $n)
            $c->needles()->attach($n);
          $combination['galil-' . $g->name . '-' . $m->name] = $c;
        }
    }
	}

}
