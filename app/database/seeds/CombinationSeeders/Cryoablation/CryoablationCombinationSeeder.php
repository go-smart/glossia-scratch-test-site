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
    $modality['cryo'] = Modality::create(array("name" => "Cryoablation"));

    /* Add model */
    $model['numa sif'] = new NumericalModel;
    $model['numa sif']->fill(array('name' => 'NUMA Cryoablation Basic SIF', 'family' => 'elmer-libnuma', 'definition' => 'lorem ipsum'));
    $model['numa sif']->attribute(['name' => 'SETTING_TIMESTEP_SIZE', 'type' => 'float', 'value' => '4', 'widget' => 'textbox']);
    $modality['cryo']->numericalModels()->save($model['numa sif']);
    $model['numa sif']->arguments()->save(new Argument(['name' => 'Temperature']));
    $model['numa sif']->arguments()->save(new Argument(['name' => 'Time']));
    $model['numa sif']->attribute(['name' => 'BODY_TEMPERATURE', 'type' => 'float', 'value' => null, 'widget' => 'textbox']);

    $organ = Region::whereName('organ')->first();
    $vessels = Region::whereName('vessels')->first();
    $veins = Region::whereName('veins')->first();
    $arteries = Region::whereName('arteries')->first();
    $tumour = Region::whereName('tumour')->first();
    $model['numa sif']->regions()->attach($organ, ['minimum' => 1, 'maximum' => 1]);
    $model['numa sif']->regions()->attach($vessels);
    $model['numa sif']->regions()->attach($veins);
    $model['numa sif']->regions()->attach($arteries);
    $model['numa sif']->regions()->attach($tumour);

    $model['galilfoam'] = new NumericalModel;
    $model['galilfoam']->fill(array('name' => 'Galil OpenFOAM', 'family' => 'elmer-libnuma', 'definition' => 'lorem ipsum'));
    $modality['cryo']->numericalModels()->save($model['galilfoam']);
    $model['galilfoam']->arguments()->save(new Argument(['name' => 'Temperature']));
    $model['galilfoam']->arguments()->save(new Argument(['name' => 'Time']));
    $model['galilfoam']->attribute(['name' => 'BODY_TEMPERATURE', 'type' => 'float', 'value' => null, 'widget' => 'textbox']);

    $model['galilfoam']->regions()->attach($organ, ['minimum' => 1, 'maximum' => 1]);
    $model['galilfoam']->regions()->attach($vessels);
    $model['galilfoam']->regions()->attach($tumour);

    $this->call('\CombinationSeeders\Cryoablation\GalilCombinationSeeder');
  }

}
