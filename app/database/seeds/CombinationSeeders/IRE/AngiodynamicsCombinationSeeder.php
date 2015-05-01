<?php namespace CombinationSeeders\IRE;
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

// We are assuming all probes in simulation same!!!
//
class AngiodynamicsCombinationSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
    $modality['ire'] = Modality::whereName('IRE')->first();
    $m = NumericalModel::whereName('NUMA IRE 3D SIF')->first();

    /* Add generators */
    $generator = new PowerGenerator([
      'Name' => 'Angiodynamics',
      'Manufacturer' => 'Angiodynamics'
    ]);
    $generator->attribute(['Name' => 'NEEDLE_MAX_AMOUNT', 'Type' => 'int', 'Value' => "2", 'Widget' => 'textbox']);
    $generator->attribute(['Name' => 'NEEDLE_MIN_AMOUNT', 'Type' => 'int', 'Value' => "6", 'Widget' => 'textbox']);
    $modality['ire']->powerGenerators()->save($generator);

    /* Add needles */
    $probe = new Needle(['Name' => 'Basic', 'Manufacturer' => 'Angiodynamics', 'File' => '', 'Geometry' => 'library:rfa-cylinder-1', 'Class' => 'solid-boundary']);
    $modality['ire']->needles()->save($probe);

    $probe->attribute(['Name' => 'NEEDLE_GAUGE', 'Type' => 'float', 'Value' => "A", 'Widget' => 'textbox']);
    $probe->attribute(['Name' => 'NEEDLE_SHAFT_LENGTH', 'Type' => 'float', 'Value' => "B", 'Widget' => 'textbox']);
    $probe->powerGenerators()->attach($generator);

    /* Add protocols */
    $protocol['6-node'] = new Protocol;
    $protocol['6-node']->fill(array(
      'Name' => 'Empty'
    ));
    $modality['ire']->protocols()->save($protocol['6-node']);

    /* Add combinations */
    $c = new Combination;
    $c->isPublic = true;
    $c->protocol()->associate($protocol['6-node']);
    $c->powerGenerator()->associate($generator);
    $c->numericalModel()->associate($m);
    $c->context()->associate(Context::byNameFamily('liver', 'organ'));
    $c->save();
    $c->needles()->attach($probe);
  }
}
