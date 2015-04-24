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
use \Region;

class RFACombinationSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $modality['rfa'] = Modality::create(array("Name" => "RFA"));

    /* Add model */
    $model['rfa basic sif'] = new NumericalModel;
    $model['rfa basic sif']->fill(array('Name' => 'NUMA RFA Basic SIF', 'Family' => 'elmer-libnuma'));
    $modality['rfa']->numericalModels()->save($model['rfa basic sif']);
    $model['rfa basic sif']->importSif(public_path() . '/templates/go-smart-template_ps.sif');
    $model['rfa basic sif']->arguments()->attach(Argument::create(['Name' => 'ObservedTemperature']));
    $model['rfa basic sif']->arguments()->attach(Argument::create(['Name' => 'Impedance']));
    $model['rfa basic sif']->arguments()->attach(Argument::create(['Name' => 'Phase']));
    $model['rfa basic sif']->arguments()->attach(Argument::create(['Name' => 'Time']));

    $organ = Region::whereName('organ')->first();
    $vessels = Region::whereName('vessels')->first();
    $arteries = Region::whereName('veins')->first();
    $veins = Region::whereName('arteries')->first();
    $bronchi = Region::whereName('bronchi')->first();
    $tumour = Region::whereName('tumour')->first();
    $model['rfa basic sif']->regions()->attach($organ, ['Minimum' => 1, 'Maximum' => 1]);
    $model['rfa basic sif']->regions()->attach($vessels);
    $model['rfa basic sif']->regions()->attach($arteries);
    $model['rfa basic sif']->regions()->attach($veins);
    $model['rfa basic sif']->regions()->attach($tumour);
    $model['rfa basic sif']->regions()->attach($bronchi);

    /* Defaults */
    $model['rfa basic sif']->attribute(['Name' => 'SETTING_TIMESTEP_SIZE', 'Type' => 'float', 'Value' => '4', 'Widget' => 'textbox']);
    // Allows an upper limit if protocol mucks up:
    $model['rfa basic sif']->attribute(['Name' => 'SETTING_FINAL_TIMESTEP', 'Type' => 'int', 'Value' => '10000', 'Widget' => 'textbox']);
    $model['rfa basic sif']->attribute(['Name' => 'CENTRE_LOCATION', 'Type' => 'string', 'Value' => 'first-needle', 'Widget' => 'textbox']);
    $model['rfa basic sif']->attribute(['Name' => 'SETTING_LESION_FIELD', 'Type' => 'string', 'Value' => 'dead', 'Widget' => 'textbox']);
    $model['rfa basic sif']->attribute(['Name' => 'SETTING_LESION_THRESHOLD_UPPER', 'Type' => 'float', 'Value' => 'null', 'Widget' => 'textbox']);
    $model['rfa basic sif']->attribute(['Name' => 'SETTING_LESION_THRESHOLD_LOWER', 'Type' => 'float', 'Value' => '0.8', 'Widget' => 'textbox']);
    $model['rfa basic sif']->attribute(['Name' => 'SIMULATION_SCALING', 'Type' => 'float', 'Value' => '0.001', 'Widget' => 'textbox']);
    $model['rfa basic sif']->attribute(['Name' => 'SIMULATION_DOMAIN_RADIUS', 'Type' => 'float', 'Value' => '40.0', 'Widget' => 'textbox']);

    /* Requirements */
    $model['rfa basic sif']->attribute(['Name' => 'CONSTANT_BODY_TEMPERATURE', 'Type' => 'float', 'Value' => null, 'Widget' => 'textbox']);

    $this->call('\CombinationSeeders\RFA\BostonScientificCombinationSeeder');
    $this->call('\CombinationSeeders\RFA\RitaCombinationSeeder');
  }

}
