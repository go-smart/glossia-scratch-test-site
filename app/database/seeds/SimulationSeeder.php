<?php
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


use \DB;
use \Seeder;

class SimulationSeeder extends Seeder {

  protected $r = 0;

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $this->makeSimulation('NUMA RFA Basic SIF', 'RITA Starburst 5cm Protocol', [],
    [
      'organ' => ["organ.vtp"],
      'vessels' => ["vessels1.vtp"],
      'tumour' => ["tumour.vtp"]
    ],
    [
      [
        "manufacturer" => "RITA",
        "name" => "Starburst MRI",
        "parameters" => [
          'NEEDLE_TIP_LOCATION' => '[0.8, 240.0, -177.6]',
          'NEEDLE_ENTRY_LOCATION' => '[0.0, 240.0, -177.6]'
        ]
      ]
    ]
    );

    $this->makeSimulation('NUMA MWA Nonlinear SIF', 'Generic modifiable power', [],
    [
      'organ' => ["organ.vtp"],
      'vessels' => ["vessels1.vtp"],
      'tumour' => ["tumour.vtp"]
    ],
    [
      [
        "manufacturer" => "HS",
        "name" => "APK11150T19V5",
        "parameters" => [
          'NEEDLE_TIP_LOCATION' => '[0.8, 240.0, -177.6]',
          'NEEDLE_ENTRY_LOCATION' => '[0.0, 240.0, -177.6]'
        ]
      ]
    ]
    );
  }

  public function makeSimulation($model, $protocol, $parameterData, $regionData, $needles)
  {
    $numerical_model = NumericalModel::whereName($model)->first();
    $protocol = Protocol::whereName($protocol)->first();

    $combinations = $numerical_model->combinations()->whereProtocolId($protocol->id);

    $needleData = [];
    $n = 0;
    foreach ($needles as $needleConfig)
    {
      $n++;
      $needle = Needle::whereManufacturer($needleConfig["manufacturer"])->whereName($needleConfig["name"])->first();
      $needleData = [
        "needle$n" => [
          'id' => $needle->id,
          'parameters' => $needleConfig["parameters"]
        ]
      ];
    }

    $simulation = Simulation::create([
        'combination_id' => $combinations->first()->id,
        'parameter_data' => json_encode($parameterData),
        'region_data' => json_encode($regionData),
        'needle_data' => json_encode($needleData)
    ]);

    $this->r++;
    print "Simulation #$this->r: " . $simulation->id . "\n";
  }

}
