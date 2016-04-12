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
		\Eloquent::unguard();
    $modality['Cryo'] = Modality::create(array("Name" => "Cryoablation"));
    $modality['GCryo'] = Modality::whereName("Development")->first();

    $oldNM = $modality['GCryo']->numericalModels()->first();
    $definition = fopen('/tmp/.tmp.most-recent-gfoam', 'w');
    if ($oldNM)
      fwrite($definition, $oldNM->Definition);
    fclose($definition);

    /* Add model */
    $model['numa sif'] = new NumericalModel;
    $model['numa sif']->fill(array('Name' => 'NUMA Cryoablation Basic SIF', 'Family' => 'elmer-libnuma', 'Definition' => 'lorem ipsum'));
    $modality['Cryo']->numericalModels()->save($model['numa sif']);
    $model['numa sif']->attribute(['Name' => 'SETTING_FINAL_TIMESTEP', 'Type' => 'int', 'Value' => '390', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_TIMESTEP_SIZE', 'Type' => 'float', 'Value' => '4', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_LESION_FIELD', 'Type' => 'string', 'Value' => 'lesion', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'RESOLUTION_FIELD_FAR', 'Type' => 'float', 'Value' => '7', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'RESOLUTION_FIELD_NEEDLE_ZONE', 'Type' => 'float', 'Value' => '1.5', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_LESION_THRESHOLD_UPPER', 'Type' => 'float', 'Value' => '233.0', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_LESION_THRESHOLD_LOWER', 'Type' => 'float', 'Value' => 'null', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SIMULATION_SCALING', 'Type' => 'float', 'Value' => '0.001', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'SETTING_ORGAN_AS_SUBDOMAIN', 'Type' => 'boolean', 'Value' => 'false', 'Widget' => 'checkbox']);
    $model['numa sif']->attribute(['Name' => 'SIMULATION_DOMAIN_RADIUS', 'Type' => 'float', 'Value' => '60.0', 'Widget' => 'textbox']);
    $model['numa sif']->attribute(['Name' => 'CENTRE_LOCATION', 'Type' => 'string', 'Value' => 'centroid-of-tips', 'Widget' => 'textbox']);
    $model['numa sif']->placeholder('CONSTANT_FLOW_RATE', null, 'array(tuple(Time,float))', true, ['linegraph', ['Time', 'Flow']], ['s', '%']);
    $model['numa sif']->importSif(public_path() . '/templates/go-smart-template_cryo.sif');
    $model['numa sif']->arguments()->attach(Argument::create(['Name' => 'Temperature']));
    $model['numa sif']->arguments()->attach(Argument::create(['Name' => 'Time']));

    $organ = Region::whereName('organ')->first();
    $vessels = Region::whereName('vessels')->first();
    $veins = Region::whereName('veins')->first();
    $arteries = Region::whereName('arteries')->first();
    $tumour = Region::whereName('tumour')->first();
    $simulatedLesion = Region::whereName('existing-lesion')->first();
    $segmentedLesion = Region::whereName('segmented-lesion')->first();
    $tace = Region::whereName('tace')->first();
    $model['numa sif']->regions()->attach($organ, ['Minimum' => 1, 'Maximum' => 1]);
    $model['numa sif']->regions()->attach($vessels);
    $model['numa sif']->regions()->attach($veins);
    $model['numa sif']->regions()->attach($arteries);
    $model['numa sif']->regions()->attach($tumour);
    $model['numa sif']->regions()->attach($simulatedLesion);
    $model['numa sif']->regions()->attach($segmentedLesion);
    $model['numa sif']->regions()->attach($tace);

    $model['Gfoam'] = new NumericalModel;
    $model['Gfoam']->fill(array('Name' => 'GOpenFOAM', 'Family' => 'gFoam', 'Definition' => 'lorem ipsum'));
    $modality['GCryo']->numericalModels()->save($model['Gfoam']);
    $model['Gfoam']->importSif(public_path() . '/templates/go-smart-template_cryo.sif'); // THIS PROVIDES THE KEY PARAM DEPS
    //$model['Gfoam']->Definition = file_get_contents('/home/administrator/parameters.yml') . "\n==========ENDPARAMETERS========\n" . file_get_contents('/home/administrator/g.py');
    $model['Gfoam']->Definition = file_get_contents('/tmp/.tmp.most-recent-gfoam');
    $model['Gfoam']->save();
    $model['Gfoam']->arguments()->attach(Argument::create(['Name' => 'Temperature']));
    $model['Gfoam']->arguments()->attach(Argument::create(['Name' => 'Time']));
    $model['Gfoam']->attribute(['Name' => 'CONSTANT_BODY_TEMPERATURE', 'Type' => 'Float', 'Value' => null, 'Widget' => 'textbox']);
    $model['Gfoam']->attribute(['Name' => 'SETTING_TIMESTEP_SIZE', 'Type' => 'int', 'Value' => '390', 'Widget' => 'textbox']);
    $model['Gfoam']->attribute(['Name' => 'CONSTANT_MESH_PADDING_DISTANCE', 'Type' => 'float', 'Value' => '1.0', 'Widget' => 'textbox']);
    $model['Gfoam']->attribute(['Name' => 'CONSTANT_SIMULTANEOUS_PROCESSES', 'Type' => 'int', 'Value' => '2', 'Widget' => 'textbox']);
    $model['Gfoam']->attribute(['Name' => 'SETTING_LESION_THRESHOLD_UPPER', 'Type' => 'float', 'Value' => '233.0', 'Widget' => 'textbox']);
    $model['Gfoam']->attribute(['Name' => 'SETTING_LESION_THRESHOLD_LOWER', 'Type' => 'float', 'Value' => 'null', 'Widget' => 'textbox']);
    $model['Gfoam']->attribute(['Name' => 'CONSTANT_NUMBER_OF_CYCLES', 'Type' => 'int', 'Value' => '1', 'Widget' => 'textbox']);


    $model['Gfoam']->regions()->attach($organ, ['Minimum' => 1, 'Maximum' => 1]);
    $model['Gfoam']->regions()->attach($vessels);
    $model['Gfoam']->regions()->attach($tumour);

    $this->call('\CombinationSeeders\Cryoablation\GalilCombinationSeeder');
  }

}
