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
use \Region;

class MWACombinationSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $modality['mwa'] = Modality::create(array("Name" => "MWA"));

    /* Add model */
    $model['mwa linear sif'] = new NumericalModel;
    $model['mwa linear sif']->fill(array('Name' => 'NUMA MWA Linear SIF', 'Family' => 'elmer-libnuma'));
    $modality['mwa']->numericalModels()->save($model['mwa linear sif']);
    $model['mwa linear sif']->importSif(public_path() . '/templates/go-smart-template_mwa-l.sif');
    $model['mwa linear sif']->arguments()->save(new Argument(['Name' => 'Temperature']));
    $model['mwa linear sif']->arguments()->save(new Argument(['Name' => 'Time']));
    $model['mwa linear sif']->attribute(['Name' => 'SETTING_TIMESTEP_SIZE', 'Type' => 'float', 'Value' => '4', 'Widget' => 'textbox']);
    $model['mwa linear sif']->attribute(['Name' => 'CONSTANT_BODY_TEMPERATURE', 'Type' => 'float', 'Value' => null, 'Widget' => 'textbox']);

    /* Defaults */
    $model['mwa linear sif']->attribute(['Name' => 'SETTING_TIMESTEP_SIZE', 'Type' => 'float', 'Value' => '4', 'Widget' => 'textbox']);
    // Allows an upper limit if protocol mucks up:
    $model['mwa linear sif']->attribute(['Name' => 'SETTING_FINAL_TIMESTEP', 'Type' => 'int', 'Value' => '10000', 'Widget' => 'textbox']);
    $model['mwa linear sif']->attribute(['Name' => 'CENTRE_LOCATION', 'Type' => 'string', 'Value' => 'first-needle', 'Widget' => 'textbox']);
    $model['mwa linear sif']->attribute(['Name' => 'SIMULATION_SCALING', 'Type' => 'float', 'Value' => '0.001', 'Widget' => 'textbox']);
    $model['mwa linear sif']->attribute(['Name' => 'SIMULATION_DOMAIN_RADIUS', 'Type' => 'float', 'Value' => '40.0', 'Widget' => 'textbox']);
    $model['mwa linear sif']->attribute(['Name' => 'SETTING_AXISYMMETRIC_INNER', 'Type' => 'string', 'Value' => 'basic-mwa', 'Widget' => 'textbox']);
    $model['mwa linear sif']->attribute(['Name' => 'SETTING_AXISYMMETRIC_INNER_COARSE', 'Type' => 'string', 'Value' => 'basic-mwa-coarse', 'Widget' => 'textbox']);

    $model['mwa nonlinear sif'] = new NumericalModel;
    $model['mwa nonlinear sif']->fill(array('Name' => 'NUMA MWA Nonlinear SIF', 'Family' => 'elmer-libnuma'));
    $modality['mwa']->numericalModels()->save($model['mwa nonlinear sif']);
    $model['mwa nonlinear sif']->importSif(public_path() . '/templates/go-smart-template_mwa-nl.sif');
    $model['mwa nonlinear sif']->arguments()->save(new Argument(['Name' => 'Temperature']));
    $model['mwa nonlinear sif']->arguments()->save(new Argument(['Name' => 'Time']));
    $model['mwa nonlinear sif']->attribute(['Name' => 'SETTING_TIMESTEP_SIZE', 'Type' => 'float', 'Value' => '4', 'Widget' => 'textbox']);
    $model['mwa nonlinear sif']->attribute(['Name' => 'CONSTANT_BODY_TEMPERATURE', 'Type' => 'float', 'Value' => null, 'Widget' => 'textbox']);

    /* Defaults */
    $model['mwa nonlinear sif']->attribute(['Name' => 'SETTING_TIMESTEP_SIZE', 'Type' => 'float', 'Value' => '4', 'Widget' => 'textbox']);
    // Allows an upper limit if protocol mucks up:
    $model['mwa nonlinear sif']->attribute(['Name' => 'SETTING_FINAL_TIMESTEP', 'Type' => 'int', 'Value' => '10000', 'Widget' => 'textbox']);
    $model['mwa nonlinear sif']->attribute(['Name' => 'CENTRE_LOCATION', 'Type' => 'string', 'Value' => 'first-needle', 'Widget' => 'textbox']);
    $model['mwa nonlinear sif']->attribute(['Name' => 'SIMULATION_SCALING', 'Type' => 'float', 'Value' => '0.001', 'Widget' => 'textbox']);
    $model['mwa nonlinear sif']->attribute(['Name' => 'SIMULATION_DOMAIN_RADIUS', 'Type' => 'float', 'Value' => '40.0', 'Widget' => 'textbox']);
    $model['mwa nonlinear sif']->attribute(['Name' => 'SETTING_AXISYMMETRIC_INNER', 'Type' => 'string', 'Value' => 'basic-mwa', 'Widget' => 'textbox']);
    $model['mwa nonlinear sif']->attribute(['Name' => 'SETTING_AXISYMMETRIC_INNER_COARSE', 'Type' => 'string', 'Value' => 'basic-mwa-coarse', 'Widget' => 'textbox']);
    $model['mwa nonlinear sif']->attribute(['Name' => 'ELMER_NUMA_MODULES', 'Type' => 'array(string)', 'Value' => '[ "mwa_RelPerm", "mwa_ElecCond" ]', 'Widget' => 'textbox']);

    $organ = Region::whereName('organ')->first();
    $vessels = Region::whereName('vessels')->first();
    $veins = Region::whereName('veins')->first();
    $arteries = Region::whereName('arteries')->first();
    $bronchi = Region::whereName('bronchi')->first();
    $tumour = Region::whereName('tumour')->first();
    $model['mwa nonlinear sif']->regions()->attach($organ, ['Minimum' => 1, 'Maximum' => 1]);
    $model['mwa nonlinear sif']->regions()->attach($vessels);
    $model['mwa nonlinear sif']->regions()->attach($veins);
    $model['mwa nonlinear sif']->regions()->attach($arteries);
    $model['mwa nonlinear sif']->regions()->attach($tumour);
    $model['mwa nonlinear sif']->regions()->attach($bronchi);

    $this->call('\CombinationSeeders\MWA\AmicaCombinationSeeder');
  }

}
