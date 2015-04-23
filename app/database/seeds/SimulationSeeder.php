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

  public function clean()
  {
    Simulation::where('Caption', 'LIKE', "Sample %")->delete();
  }

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Eloquent::unguard();

    foreach (['5cm', '4cm', '3cm', '2cm'] as $length)
      $this->makeSimulation("$length RITA RFA", 'liver', 'NUMA RFA Basic SIF', "RITA Starburst $length Protocol", [],
      [
        'organ' => ["organ.vtp"],
        'vessels' => ["vessels1.vtp"],
        'tumour' => ["tumour.vtp"]
      ],
      [
        [
          "Manufacturer" => "RITA",
          "Name" => "Starburst MRI",
          "Parameters" => [
            'NEEDLE_TIP_LOCATION' => '[0.8, 240.0, -177.6]',
            'NEEDLE_ENTRY_LOCATION' => '[0.0, 240.0, -177.6]'
          ]
        ]
      ]
      );

    $this->makeSimulation('Cryoablation', 'kidney', 'NUMA Cryoablation Basic SIF', 'Empty', [],
    [
      'organ' => ["organ.vtp"],
      'vessels' => ["vessels1.vtp"],
      'tumour' => ["tumour.vtp"]
    ],
    [
      [
        "Manufacturer" => "Galil Medical",
        "Name" => "IceROD",
        "Parameters" => [
          'NEEDLE_TIP_LOCATION' => '[0.8, 240.0, -177.6]',
          'NEEDLE_ENTRY_LOCATION' => '[0.0, 240.0, -177.6]'
        ]
      ]
    ]
    );

    $needleDeltas = [
      [0, 8, -5],
      [0, 8, 5],
      [0, -8, -5],
      [0, -8, 5],
      [0, 5, 0],
      [0, -5, 0]
    ];
    $ireNeedles = [];
    $ireTipCentre = [0.8, 240.0, -177.6];
    $ireEntryCentre = [0.0, 240.0, -177.6];
    foreach ($needleDeltas as $needleDelta)
    {
      $ireNeedle = [
        "Manufacturer" => "Angiodynamics",
        "Name" => "Basic",
        "Parameters" => [
          'NEEDLE_TIP_LOCATION' => json_encode(array_map(function ($p) { return $p[0] + $p[1]; }, array_map(null, $ireTipCentre, $needleDelta))),
          'NEEDLE_ENTRY_LOCATION' => json_encode(array_map(function ($p) { return $p[0] + $p[1]; }, array_map(null, $ireEntryCentre, $needleDelta))),
        ]
      ];
      $ireNeedles[] = $ireNeedle;
    }
    $this->makeSimulation('IRE', 'liver', 'NUMA IRE 3D SIF', 'Empty', [],
      [
        'organ' => ["organ.vtp"],
        'vessels' => ["vessels1.vtp"],
        'tumour' => ["tumour.vtp"]
      ],
      $ireNeedles
    );

    $this->makeSimulation('Amica MWA', 'kidney', 'NUMA MWA Nonlinear SIF', 'Generic modifiable power', [],
    [
      'organ' => ["organ.vtp"],
      'vessels' => ["vessels1.vtp"],
      'tumour' => ["tumour.vtp"]
    ],
    [
      [
        "Manufacturer" => "HS",
        "Name" => "APK11150T19V5",
        "Parameters" => [
          'NEEDLE_TIP_LOCATION' => '[0.8, 240.0, -177.6]',
          'NEEDLE_ENTRY_LOCATION' => '[0.0, 240.0, -177.6]'
        ]
      ]
    ]
    );
  }

  public function makePointSet($jsonArray)
  {
    $arr = json_decode($jsonArray);
    $pointSet = PointSet::create(['X' => $arr[0], 'Y' => $arr[1], 'Z' => $arr[2]]);
    return $pointSet;
  }

  public function makeSimulation($caption, $organ, $model, $protocol, $parameterData, $regionData, $needles)
  {
    $numerical_model = NumericalModel::whereName($model)->first();
    $protocol = Protocol::whereName($protocol)->whereModalityId($numerical_model->Modality_Id)->first();
    $context = Context::byNameFamily($organ, 'organ');

    $combinations = $numerical_model
      ->Combinations()
      ->whereProtocolId($protocol->Id)
      ->where(Context::$idField, "=", $context->Id);

    $combination = $combinations->first();
    $simulation = Simulation::create([
        'Combination_Id' => $combination->Combination_Id,
        'Patient_Id' => DB::table('ItemSet_Patient')->where('OrganType', '=', $combination->OrganType)->first()->Id ?: '00000000-0000-0000-0000-000000000000',
        'Caption' => 'Sample Simulation for ' . $caption,
        'SegmentationType' => 0,
        'Progress' => '0',
        'State' => 0,
        'Color' => 0,
        'Active' => 0
    ]);

    /*
    foreach ($regionData as $name => $locations)
    {
      $region = Region::whereName($name)->first();
      foreach ($locations as $location)
        $simulation->regions()->attach($region, ['Location' => $location]);
    }
     */

    $needleData = [];
    $n = 0;
    foreach ($needles as $needleConfig)
    {
      $n++;

      $needle = Needle::whereManufacturer($needleConfig["Manufacturer"])
        ->whereName($needleConfig["Name"])
        ->first();

      $simulationNeedle = SimulationNeedle::create([
        'Needle_Id' => $needle->Id,
        'Simulation_Id' => $simulation->Id,
        'Target_Id' => $this->makePointSet($needleConfig["Parameters"]["NEEDLE_TIP_LOCATION"])->Id,
        'Entry_Id' => $this->makePointSet($needleConfig["Parameters"]["NEEDLE_ENTRY_LOCATION"])->Id
      ]);
      $simulationNeedleId = $simulationNeedle->Id;

      foreach ($needleConfig["Parameters"] as $paramName => $paramValue)
      {
        $parameter = Parameter::whereName($paramName)->first();
        $simulationNeedleParameter = DB::table('Simulation_Needle_Parameter')
          ->insert([
            'SimulationNeedleId' => $simulationNeedleId,
            'ParameterId' => $parameter->Id,
            'ValueSet' => $paramValue
          ]);
      }
    }

    $this->r++;
    print "Simulation #$this->r: " . $simulation->asString . "\n";
  }

}
