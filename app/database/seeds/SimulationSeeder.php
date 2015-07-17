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
use Illuminate\Database\Eloquent\Collection;

function crossProduct($a, $b)
{
  $c = [];
  $c[0] = $a[1] * $b[2] - $a[2] * $b[1];
  $c[1] = $a[2] * $b[0] - $a[0] * $b[2];
  $c[2] = $a[0] * $b[1] - $a[1] * $b[0];

  $sqNorm = $c[0] * $c[0] + $c[1] * $c[1] + $c[2] * $c[2];

  if ($sqNorm > 1E-10)
    return array_map(function ($l) use ($sqNorm) { return $l / sqrt($sqNorm); }, $c);

  return $c;
}


class SimulationSeeder extends Seeder {

  protected $r = 0;

  public function clean()
  {
    Simulation::where('Caption', 'LIKE', "Sample %")->delete();
  }

  public function deepClean()
  {
    Simulation::where('Caption', 'LIKE', "N: %")->delete();
  }

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Eloquent::unguard();

    $this->clean();
    //$this->deepClean();

    $organs = ['liver', 'kidney'];
    foreach ($organs as $organ)
    {
      $referenceSimulation[$organ] = [];
      $sim =
        Simulation::join('ItemSet_Patient AS IS_P', 'IS_P.Id', '=', 'Simulation.Patient_Id')
        ->join('ItemSet AS IS', 'IS.Id', '=', 'IS_P.Id')
        ->join('Simulation_Needle AS SN', 'SN.Simulation_Id', '=', 'Simulation.Id')
        ->where('IS_P.OrganType', '=', ContextEnum::value($organ))
        ->where('IS.IsDeleted', '=', 'FALSE')
        ->select('Simulation.*')
        ->first();

      if ($sim)
      {
        $referenceSimulation[$organ]["patient"] = DB::table('ItemSet_Patient')->whereId($sim->Patient_Id)->first();

        $referenceNeedle = $sim->SimulationNeedles->first();
        $referenceSimulation[$organ]["target"] = $referenceNeedle->Target;
        $referenceSimulation[$organ]["entry"] = $referenceNeedle->Entry;
      }
      else
      {
        $referenceSimulation[$organ]["patient"] = NULL;
        $referenceSimulation[$organ]["target"] = PointSet::fromArray([0, 0, 0]);
        $referenceSimulation[$organ]["entry"] = PointSet::fromArray([1, 1, 1]);
      }


    }

    foreach (['5cm', '4cm', '3cm', '2cm'] as $length)
      $this->makeSimulation("$length RITA RFA", $referenceSimulation["liver"]["patient"], 'liver', 'NUMA RFA Basic SIF', "RITA Starburst $length Protocol", [],
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
            'NEEDLE_TIP_LOCATION' => $referenceSimulation["liver"]["target"]->asString,
            'NEEDLE_ENTRY_LOCATION' => $referenceSimulation["liver"]["entry"]->asString
          ]
        ]
      ]
      );

    $this->makeSimulation('Cryoablation', $referenceSimulation["kidney"]["patient"], 'kidney', 'NUMA Cryoablation Basic SIF', 'Empty',
    [
      'SETTING_FINAL_TIMESTEP' => '300'
    ],
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
          'NEEDLE_TIP_LOCATION' => $referenceSimulation["kidney"]["target"]->asString,
          'NEEDLE_ENTRY_LOCATION' => $referenceSimulation["kidney"]["entry"]->asString
        ]
      ]
    ]
    );

    $needleDeltas = [
      [10, 8, -5],
      [10, 8, 5],
      [10, -8, -5],
      [10, -8, 5],
      [10, 5, 0],
      [10, -5, 0]
    ];
    $ireTipCentre = $referenceSimulation["liver"]["target"]->asArray;
    $ireEntryCentre = $referenceSimulation["liver"]["entry"]->asArray;

    $parallel = array_map(function ($c) use ($ireTipCentre, $ireEntryCentre) {
      return $ireTipCentre[$c] - $ireEntryCentre[$c];
    }, [0, 1, 2]);
    $norm = sqrt($parallel[0] * $parallel[0] + $parallel[1] * $parallel[1] + $parallel[2] * $parallel[2]);
    $parallel = array_map(function ($c) use ($norm) { return $c / $norm; }, $parallel);

    $randVec = [0, 1.2384717624, 0.00000342878];
    $perp1 = crossProduct($parallel, $randVec);
    $perp2 = crossProduct($parallel, $perp1);
    //$parallel = [1, 0, 0];
    //$perp1 = [0, 1, 0];
    //$perp2 = [0, 0, 1];

    $ireNeedles = [];
    foreach ($needleDeltas as $needleDelta)
    {
      $needleOffset = array_map(function ($c) use ($needleDelta, $parallel, $perp1, $perp2) {
        return $needleDelta[0] * $parallel[$c] + $needleDelta[1] * $perp1[$c] + $needleDelta[2] * $perp2[$c];
      }, [0, 1, 2]);

      $needleTip = array_map(function ($p) { return $p[0] + $p[1]; }, array_map(null, $ireTipCentre, $needleOffset));
      $ireNeedle = [
        "Manufacturer" => "Angiodynamics",
        "Name" => "Basic",
        "Parameters" => [
          'NEEDLE_TIP_LOCATION' => json_encode($needleTip),
          'NEEDLE_ENTRY_LOCATION' => json_encode(array_map(function ($p) { return $p[0] + $p[1]; }, array_map(null, $ireEntryCentre, $needleOffset))),
        ]
      ];
      $ireNeedles[] = $ireNeedle;
    }
    $this->makeSimulation('IRE', $referenceSimulation["liver"]["patient"], 'liver', 'NUMA IRE 3D SIF', 'Empty',
      [
        'CONSTANT_IRE_POTENTIAL_DIFFERENCES' => "[1300, 1500, 1300, 1900, 1300, 1300, 1300, 1900, 1300]"
      ],
      [
        'organ' => ["organ.vtp"],
        'vessels' => ["vessels1.vtp"],
        'tumour' => ["tumour.vtp"]
      ],
      $ireNeedles
    );

    $this->makeSimulation('Amica MWA', $referenceSimulation["kidney"]["patient"], 'kidney', 'NUMA MWA Nonlinear SIF', 'Generic modifiable power', [],
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
          'NEEDLE_TIP_LOCATION' => $referenceSimulation["kidney"]["target"]->asString,
          'NEEDLE_ENTRY_LOCATION' => $referenceSimulation["kidney"]["entry"]->asString
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

  public function makeSimulation($caption, $patient, $organ, $model, $protocol, $parameterData, $regionData, $needles)
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
        'Patient_Id' => $patient->Id ?: '00000000-0000-0000-0000-000000000000',
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

    $simulation->save();

    $simulationNeedles = [];
    $needleData = [];
    $needleUserParameters = new Collection;
    $n = 0;
    foreach ($needles as $needleConfig)
    {
      $n++;

      $needle = Needle::whereManufacturer($needleConfig["Manufacturer"])
        ->whereName($needleConfig["Name"])
        ->first();

      $needleUserParameters[$needle->Id] = new Collection;

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
        $parameter->Value = $paramValue;
        $needleUserParameters[$needle->Id][$paramName] = $parameter;
      }

      $simulationNeedles[] = $needle;
    }

    $parameters = new Collection;
    foreach ($parameterData as $parameterName => $value)
    {
      $parameter = Parameter::whereName($parameterName)->first();
      $parameter->Value = $value;
      $parameters[$parameter->Name] = $parameter;
    }

    $incompatibilities = [];
    $userRequiredParameters = [];
    list($parameters, $needleParameters) = $combination->compileParameters($parameters, $simulationNeedles, $needleUserParameters, $incompatibilities, $userRequiredParameters);
    if (count($incompatibilities))
    {
      var_dump($incompatibilities);
      var_dump($userRequiredParameters);
    }

    foreach ($parameters as $parameterName => $parameter)
    {
      $simulation->Parameters()->attach($parameter, ['ValueSet' => $parameter->Value]);
    }

    $simulation->SimulationNeedles->each(function ($simulationNeedle) use ($needleParameters) {
      if (array_key_exists($simulationNeedle->Needle_Id, $needleParameters)) {
        $needleParameters[$simulationNeedle->Needle_Id]->each(function ($p) use ($simulationNeedle) {
          $simulationNeedle->Parameters()->attach($p);
        });
      }
    });

    $this->r++;
    print "Simulation #$this->r: " . $simulation->Combination->asString . " [ " . strtoupper($simulation->Id) . " ]\n";
  }

}
