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
use \Region;

class CryoablationCombinationSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $modality['Cryo'] = Modality::create(array("Name" => "Cryoablation"));

    /* Add model */
    $model['numa sif'] = new NumericalModel;
    $model['numa sif']->fill(array('Name' => 'NUMA Cryoablation Basic SIF', 'Family' => 'elmer-libnuma', 'Definition' => 'lorem ipsum'));
    $model['numa sif']->attribute(['Name' => 'SETTING_TIMESTEP_SIZE', 'Type' => 'Float', 'Value' => '4', 'Widget' => 'Textbox']);
    $modality['Cryo']->numericalModels()->save($model['numa sif']);
    $model['numa sif']->arguments()->save(new Argument(['Name' => 'Temperature']));
    $model['numa sif']->arguments()->save(new Argument(['Name' => 'Time']));
    $model['numa sif']->attribute(['Name' => 'CONSTANT_BODY_TEMPERATURE', 'Type' => 'Float', 'Value' => null, 'Widget' => 'Textbox']);

    $organ = Region::whereName('Organ')->first();
    $vessels = Region::whereName('Vessels')->first();
    $veins = Region::whereName('Veins')->first();
    $arteries = Region::whereName('Arteries')->first();
    $tumour = Region::whereName('Tumour')->first();
    $model['numa sif']->regions()->attach($organ, ['Minimum' => 1, 'Maximum' => 1]);
    $model['numa sif']->regions()->attach($vessels);
    $model['numa sif']->regions()->attach($veins);
    $model['numa sif']->regions()->attach($arteries);
    $model['numa sif']->regions()->attach($tumour);

    $model['Galilfoam'] = new NumericalModel;
    $model['Galilfoam']->fill(array('Name' => 'Galil OpenFOAM', 'Family' => 'elmer-libnuma', 'Definition' => 'lorem ipsum'));
    $modality['Cryo']->numericalModels()->save($model['Galilfoam']);
    $model['Galilfoam']->arguments()->save(new Argument(['Name' => 'Temperature']));
    $model['Galilfoam']->arguments()->save(new Argument(['Name' => 'Time']));
    $model['Galilfoam']->attribute(['Name' => 'CONSTANT_BODY_TEMPERATURE', 'Type' => 'Float', 'Value' => null, 'Widget' => 'Textbox']);

    $model['Galilfoam']->regions()->attach($organ, ['Minimum' => 1, 'Maximum' => 1]);
    $model['Galilfoam']->regions()->attach($vessels);
    $model['Galilfoam']->regions()->attach($tumour);

    $this->call('\CombinationSeeders\Cryoablation\GalilCombinationSeeder');
  }

}
