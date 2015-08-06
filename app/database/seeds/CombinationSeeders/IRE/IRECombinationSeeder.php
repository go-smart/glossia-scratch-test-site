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
use \Argument;
use \Combination;
use \Modality;
use \Needle;
use \NumericalModel;
use \Parameter;
use \PowerGenerator;
use \Protocol;
use \Region;

class IRECombinationSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $modality['ire'] = Modality::create(array("Name" => "IRE"));

    /* Add model */
    $model['numa sif'] = new NumericalModel;
    $model['numa sif']->fill(array('Name' => 'NUMA IRE 3D SIF', 'Family' => 'elmer-libnuma', 'Definition' => 'lorem ipsum'));
    $modality['ire']->numericalModels()->save($model['numa sif']);
    $model['numa sif']->attribute(['Name' => 'SIMULATION_SCALING', 'Type' => 'float', 'Value' => '0.001', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SIMULATION_DOMAIN_RADIUS', 'Type' => 'float', 'Value' => '40.0', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_SOLID_NEEDLES', 'Type' => 'boolean', 'Value' => 'true', 'Widget' => 'checkbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_LESION_FIELD', 'Type' => 'string', 'Value' => 'max_e', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_LESION_THRESHOLD_UPPER', 'Type' => 'float', 'Value' => 'null', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_LESION_THRESHOLD_LOWER', 'Type' => 'float', 'Value' => '80000', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'RESOLUTION_FIELD_NEEDLE_ZONE', 'Type' => 'float', 'Value' => '0.3', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'CENTRE_LOCATION', 'Type' => 'string', 'Value' => 'centroid-of-tips', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_ORGAN_AS_SUBDOMAIN', 'Type' => 'boolean', 'Value' => 'true', 'Widget' => 'checkbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_ORGAN_AS_SURFACE', 'Type' => 'boolean', 'Value' => 'true', 'Widget' => 'checkbox']);
    $model['numa sif']->placeholder('CONSTANT_IRE_SEQUENCE', null, 'array(tuple(int,int,float))', true, 'Table(Anode, Cathode, Potential Differences)');
    $model['numa sif']->importSif(public_path() . '/templates/go-smart-template_ire.sif');
    $model['numa sif']->arguments()->attach(Argument::create(['Name' => 'Temperature']));
    $model['numa sif']->arguments()->attach(Argument::create(['Name' => 'Time']));

    $organ = Region::whereName('organ')->first();
    $vessels = Region::whereName('vessels')->first();
    $veins = Region::whereName('veins')->first();
    $arteries = Region::whereName('arteries')->first();
    $bronchi = Region::whereName('bronchi')->first();
    $tumour = Region::whereName('tumour')->first();
    $simulatedLesion = Region::whereName('existing-lesion')->first();
    $tace = Region::whereName('tace')->first();
    $model['numa sif']->regions()->attach($organ, ['Minimum' => 1, 'Maximum' => 1]);
    $model['numa sif']->regions()->attach($vessels);
    $model['numa sif']->regions()->attach($veins);
    $model['numa sif']->regions()->attach($arteries);
    $model['numa sif']->regions()->attach($tumour);
    $model['numa sif']->regions()->attach($simulatedLesion);
    $model['numa sif']->regions()->attach($tace);

    $this->call('\CombinationSeeders\IRE\AngiodynamicsCombinationSeeder');
  }

}
