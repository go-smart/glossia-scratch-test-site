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
    $modality['rfa'] = Modality::create(array("name" => "RFA"));

    /* Add model */
    $model['rfa basic sif'] = new NumericalModel;
    $model['rfa basic sif']->fill(array('name' => 'NUMA RFA Basic SIF', 'family' => 'elmer-libnuma'));
    $modality['rfa']->numericalModels()->save($model['rfa basic sif']);
    $model['rfa basic sif']->importSif(public_path() . '/templates/go-smart-template_ps.sif');
    $model['rfa basic sif']->arguments()->save(new Argument(['name' => 'ObservedTemperature']));
    $model['rfa basic sif']->arguments()->save(new Argument(['name' => 'Impedance']));
    $model['rfa basic sif']->arguments()->save(new Argument(['name' => 'Phase']));
    $model['rfa basic sif']->arguments()->save(new Argument(['name' => 'Time']));

    $organ = Region::whereName('organ')->first();
    $vessels = Region::whereName('vessels')->first();
    $arteries = Region::whereName('veins')->first();
    $veins = Region::whereName('arteries')->first();
    $bronchi = Region::whereName('bronchi')->first();
    $tumour = Region::whereName('tumour')->first();
    $model['rfa basic sif']->regions()->attach($organ, ['minimum' => 1, 'maximum' => 1]);
    $model['rfa basic sif']->regions()->attach($vessels);
    $model['rfa basic sif']->regions()->attach($arteries);
    $model['rfa basic sif']->regions()->attach($veins);
    $model['rfa basic sif']->regions()->attach($tumour);
    $model['rfa basic sif']->regions()->attach($bronchi);

    /* Defaults */
    $model['rfa basic sif']->attribute(['name' => 'SETTING_TIMESTEP_SIZE', 'type' => 'float', 'value' => '4', 'widget' => 'textbox']);
    // Allows an upper limit if protocol mucks up:
    $model['rfa basic sif']->attribute(['name' => 'SETTING_FINAL_TIMESTEP', 'type' => 'int', 'value' => '10000', 'widget' => 'textbox']);
    $model['rfa basic sif']->attribute(['name' => 'CENTRE_LOCATION', 'type' => 'string', 'value' => 'first-needle', 'widget' => 'textbox']);
    $model['rfa basic sif']->attribute(['name' => 'SIMULATION_SCALING', 'type' => 'float', 'value' => '0.001', 'widget' => 'textbox']);
    $model['rfa basic sif']->attribute(['name' => 'SIMULATION_DOMAIN_RADIUS', 'type' => 'float', 'value' => '40.0', 'widget' => 'textbox']);

    /* Requirements */
    $model['rfa basic sif']->attribute(['name' => 'BODY_TEMPERATURE', 'type' => 'float', 'value' => null, 'widget' => 'textbox']);
    $model['rfa basic sif']->attribute(['name' => 'NEEDLE_TIP_LOCATION', 'type' => 'array(float)', 'value' => null, 'widget' => 'coordinate']);
    $model['rfa basic sif']->attribute(['name' => 'NEEDLE_ENTRY_LOCATION', 'type' => 'array(float)', 'value' => null, 'widget' => 'coordinate']);
    $model['rfa basic sif']->updatePlaceholdersFromDefinition();

    $this->call('\CombinationSeeders\RFA\BostonScientificCombinationSeeder');
    $this->call('\CombinationSeeders\RFA\RitaCombinationSeeder');
  }

}
