<?php

use Illuminate\Database\Eloquent\Collection;

class SimulationController extends \BaseController {

  protected $httpTransferrerBase = 'https://smart-mict.de/api/downloadFile';

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
    $backups = array_filter(scandir(public_path() . '/backups'), function ($d) { return $d[0] != '.'; });
    $simulations = Simulation::with([
      'Combination.NumericalModel',
      'Combination.Protocol',
      'Combination.PowerGenerator',
      'Combination.PowerGenerator.Modality'
    ])
    ->leftJoin('ItemSet as SimulationItem', 'SimulationItem.Id', '=', 'Simulation.Id')
    ->leftJoin('ItemSet as PatientItem', 'PatientItem.Id', '=', 'Simulation.Patient_Id')
    ->leftJoin('ItemSet_Patient', 'ItemSet_Patient.Id', '=', 'Simulation.Patient_Id')
    ->leftJoin('ItemSet_Segmentation', function ($leftJoin) {
      $leftJoin->on('ItemSet_Segmentation.Patient_Id', '=', 'Simulation.Patient_Id');
      $leftJoin->on('ItemSet_Segmentation.State', '=', DB::raw('3'));
      $leftJoin->on('ItemSet_Segmentation.SegmentationType', '=', DB::raw(SegmentationTypeEnum::Lesion));
    })
    ->leftJoin('ItemSet_VtkFile as LesionFile', 'LesionFile.Segmentation_Id', '=', 'ItemSet_Segmentation.Id')
    ->leftJoin('AspNetUsers as Clinician', 'Clinician.Id', '=', 'ItemSet_Patient.AspNetUsersId')
    ->select(
      'Simulation.*',
      'SimulationItem.CreationDate as creationDate',
      'LesionFile.Id as SegmentedLesionId',
      'Clinician.Id as ClinicianId',
      'Clinician.UserName as ClinicianUserName'
    )
    ->get();

		return View::make('simulations.index', compact('simulations', 'backups'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
    $contexts = Context::all()->lists('Name', 'Id');
		return View::make('simulations.create', compact('contexts'));
	}


  public function patient()
  {
    $context = Context::find(Input::get('Context_Id'));

    $patients = DB::table('ItemSet_Patient')->whereExists(function ($q) {
      $q->select(DB::raw(1))
        ->from('ItemSet_Segmentation')
        ->whereRaw('ItemSet_Patient.Id = ItemSet_Segmentation.Patient_Id')
        ->where('ItemSet_Segmentation.State', '=', 3);
      })
        /*->where('IsDeleted', '=', 'false') SOFT-DELETING SEEMS TO HAVE GONE UPSTREAM */
      ->where('OrganType', '=', $context->Id)->get();

    $output = [];
    foreach ($patients as $patient)
      $output[$patient->Id] = $patient->Alias . ' (' . $patient->Description . ')';

    return $output;
  }

	public function duplicate($id)
	{
		$oldSimulation = Simulation::find($id);

    $simulation = new Simulation;
    $simulation->Combination_Id = $oldSimulation->Combination_Id;
    $simulation->Patient_Id = $oldSimulation->Patient_Id;
    $simulation->Caption = $oldSimulation->Caption . '+';
    if (substr($simulation->Caption, 0, 2) != "N:")
      $simulation->Caption = "N: " . $simulation->Caption;
    $simulation->SegmentationType = $oldSimulation->SegmentationType;
    $simulation->Progress = '0';
    $simulation->State = 0;
    $simulation->Color = 0;
    $simulation->Active = 0;
    $simulation->save();

    $oldSimulation->SimulationNeedles->each(function ($needle) use ($simulation) {
      $simulationNeedle = new SimulationNeedle;
      $simulationNeedle->Needle_Id = $needle->Needle_Id;
      $simulationNeedle->Target_Id = PointSet::create(['X' => $needle->Target->X, 'Y' => $needle->Target->Y, 'Z' => $needle->Target->Z])->Id;
      $simulationNeedle->Entry_Id = PointSet::create(['X' => $needle->Entry->X, 'Y' => $needle->Entry->Y, 'Z' => $needle->Entry->Z])->Id;
      $simulationNeedle->Simulation_Id = $simulation->Id;
      $simulationNeedle->save();

      $needle->Parameters->each(function ($parameter) use ($simulationNeedle) {
        $simulationNeedle->Parameters()->attach($parameter, ['ValueSet' => $parameter->ValueSet]);
      });
    });

    $oldSimulation->Parameters->each(function ($parameter) use ($simulation) {
      $simulation->Parameters()->attach($parameter, ['ValueSet' => $parameter->pivot->ValueSet]);
    });

    DB::table('ItemSet')->insert(['CreationDate' => date('Y-m-d H:i:s'), 'IsDeleted' => false, 'Id' => $simulation->Id]);

    if (Response::json())
      return $simulation;

    return Redirect::route('simulation.edit', $simulation->Id);
	}

	public function rebuild($id)
	{
    $simulation = Simulation::find($id);
    if (!$simulation)
      return Response::json(["msg" => "No such simulation found"], 400);

    $incompatibilities = [];
    $userRequiredParameters = [];

    $needles = $simulation->SimulationNeedles->lists('Needle', 'Id');
    list($parameters, $needleParameters) = $simulation->Combination->compileParameters(new Collection,
      $needles, new Collection, $incompatibilities, $userRequiredParameters);

    foreach ($parameters as $parameter) {
      $simulation->parameters()->detach($parameter);
      $simulation->parameters()->attach($parameter, ['ValueSet' => $parameter->Value]);
    };

    $simulation->SimulationNeedles->each(function ($simulationNeedle) use ($needleParameters) {
      $simulationNeedle->Parameters()->detach();
      $needleIx = substr($simulationNeedle->Id, 0, 36);
      if (array_key_exists($needleIx, $needleParameters))
      {
        foreach ($needleParameters[$needleIx] as $needleParameter)
        {
          \Log::error($needleParameter->ValueSet);
          $simulationNeedle->Parameters()->attach($needleParameter, ['ValueSet' => $needleParameter->ValueSet]);
        }
      }
    });

    DB::table('ItemSet')->where('Id', '=', $simulation->Id)->update(['CreationDate' => date('Y-m-d H:i:s')]);

    return Response::json(["msg" => "Simulation rebuilt"], 200);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$combination = Combination::find(Input::get('Combination_Id'));
    $patient = DB::table('ItemSet_Patient')->whereId(Input::get('Patient_Id'))->first();
    $caption = Input::get('caption');

    $incompatibilities = [];
    $userRequiredParameters = [];

    list($parameters, $needleParameters) = $combination->compileParameters(
      new Collection,
      [],
      new Collection,
      $incompatibilities,
      $userRequiredParameters
    );

    $simulation = new Simulation;
    $simulation->Combination_Id = $combination->Combination_Id;
    $simulation->Patient_Id = $patient->Id;
    $simulation->Caption = 'N: ' . $caption;
    $simulation->SegmentationType = 0;
    $simulation->Progress = '0';
    $simulation->State = 0;
    $simulation->Color = 0;
    $simulation->Active = 0;
    $simulation->save();

    foreach ($parameters as $parameter) {
      $simulation->parameters()->attach($parameter, ['ValueSet' => $parameter->Value]);
    };

    return Redirect::route('simulation.edit', $simulation->Id);
	}

	public function dashboard()
	{
		return View::make('simulations.dashboard');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
    $simulation = Simulation::find($id);

    if (empty($simulation))
    {
      return ["error" => "Simulation not found"];
    }

    if (Response::json() && !in_array(Request::format(), ['xml', 'html']))
      return $simulation;

    $incompatibilities = [];

    $userParameters = $simulation->Parameters;
    //$regions = $simulation->Regions;
    $regions = $simulation->Segmentations;
    $combination = $simulation->Combination;
    $needles = [];
    $needleParameters = [];

    foreach ($simulation->SimulationNeedles as $sn)
    {
      $t = (string)$sn->Id;
      $needles[$t] = $sn->Needle;
      $needleParameters[$t] = new Collection;

      /* Check in MySQL
      var_dump($sn->Id === '236548FB-F08A-4420-A922-E1806C61A19B');
      var_dump(mb_detect_encoding($sn->Id));
      //dd(gettype($sn->Id));
      $t = (string)($sn->Id);
      var_dump(mb_detect_encoding($t));
      var_dump($t);
      var_dump($sn->Id);
      //var_dump($t[0]);
      //$t[0] = 'E';
      //var_dump($t);
      var_dump($sn->Id);
      $needleParameters[$t] = $needleParameters[$sn->Id];
      dd($needleParameters);
      */

      foreach ($sn->Parameters as $snp)
      {
        $needleParameters[$t][$snp->Name] = $snp;
        $needleParameters[$t][$snp->Name]->Value = $snp->pivot->ValueSet;
      }

      foreach (["NEEDLE_TIP_LOCATION" => $sn->Target, "NEEDLE_ENTRY_LOCATION" => $sn->Entry] as $name => $pointSet)
      {
        $location = new Parameter;
        $location->Name = $name;
        $location->Type = "array(float)";
        $location->Value = json_encode([
          (float)$pointSet->X,
          (float)$pointSet->Y,
          (float)$pointSet->Z
        ]);
        $needleParameters[$t][$name] = $location;
      }
    }

    $xml = new DOMDocument('1.0');
    $root = $xml->createElement('simulationDefinition');
    $xml->appendChild($root);

    $transferrer = $xml->createElement('transferrer');
    $transferrer->setAttribute('class', 'http');
    $transferrerUrl = $xml->createElement('url');
    $transferrerUrl->nodeValue = $this->httpTransferrerBase;
    $transferrer->appendChild($transferrerUrl);
    $root->appendChild($transferrer);

    $simulation->xml($root);

    if (!empty($incompatibilities))
      return Response::make(array_map('trim', $incompatibilities), 400);

    if ($xml === null)
      return Response::make("Simulation could not be built from input (reasons unreported)", 400);

    $xml->preserveWhiteSpace = false;
    $xml->formatOutput = true;

    if (Input::get('html'))
      return View::make('simulations.show', ['simulationXml' => $xml]);

    return Response::make($xml->saveXML(), 200)->header('Content-Type', 'application/xml');
	}

  public function getSegmentedLesion($id)
  {
    $simulation = Simulation::select(
      'LesionFile.Id as SegmentedLesionId',
      'LesionFile.FileName as SegmentedLesionFileName',
      'LesionFile.Extension as SegmentedLesionExtension'
    )
    ->leftJoin('ItemSet_Segmentation', function ($leftJoin) {
      $leftJoin->on('ItemSet_Segmentation.Patient_Id', '=', 'Simulation.Patient_Id');
      $leftJoin->on('ItemSet_Segmentation.State', '=', DB::raw('3'));
      $leftJoin->on('ItemSet_Segmentation.SegmentationType', '=', DB::raw(SegmentationTypeEnum::Lesion));
    })
    ->leftJoin('ItemSet_VtkFile as VtkFile', 'VtkFile.Segmentation_Id', '=', 'ItemSet_Segmentation.Id')
    ->leftJoin('ItemSet_File as LesionFile', 'LesionFile.Id', '=', 'VtkFile.Id')
    ->find($id);

    if (!$simulation)
      return Response::make('Simulation not found', 400);

    if ($simulation->SegmentedLesionId)
    {
      return Redirect::to($this->httpTransferrerBase . '/' . strtolower($simulation->SegmentedLesionId) . '/' . $simulation->SegmentedLesionFileName . '.' . $simulation->SegmentedLesionExtension);
    }

    return Response::make('Segmented lesion not found', 404);
  }


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
    $simulation = Simulation::find($id);
    $needles = $simulation->Combination->Needles;
    $regions = $simulation->Combination->NumericalModel->Regions;

    $otherSimulationTargets = PointSet::join('Simulation_Needle as SN', 'SN.Target_Id', '=', 'PointSet.Id')
      ->join('Simulation as S', 'S.Id', '=', 'SN.Simulation_Id')
      ->where('S.Patient_Id', '=', $simulation->Patient_Id)
      ->where('S.Id', '!=', $simulation->Id)
      ->get()
      ->lists('asString');

		return View::make('simulations.edit', compact('simulation', 'needles', 'regions', 'otherSimulationTargets'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
    $simulation = Simulation::find($id);
    if (Input::has('caption'))
      $simulation->Caption = Input::get('caption');
    $simulation->save();

    foreach ($simulation->Parameters as $parameter)
    {
      if (Input::has('parameters-' . $parameter->Id))
      {
        $parameter->pivot->ValueSet = Input::get('parameters-' . $parameter->Id);
        $parameter->pivot->save();
      }
    }

    if (Input::get('new-parameter-name') && Input::get('new-parameter-value') != '')
    {
      $parameter = Parameter::whereName(Input::get('new-parameter-name'))->first();
      if ($parameter)
      {
        $simulation->Parameters()->attach($parameter, ["ValueSet" => Input::get('new-parameter-value')]);
        $simulation->save();
      }
    }

    if (Input::get('removing'))
    {
      if (Input::get('simulation-needle-id'))
      {
        $simulationNeedle = SimulationNeedle::find(Input::get('simulation-needle-id'));
        $simulationNeedle->delete();
      }

      if (Input::get('region-remove-id'))
      {
        $region = Region::find(Input::get('region-remove-id'));
        if (Input::get('region-remove-location'))
          $simulation->Regions()->newPivotStatementForId($region->Id)->where('Location', '=', Input::get('region-remove-location'))->delete();
        else
          $simulation->Regions()->newPivotStatementForId($region->Id)->whereNull('Location')->delete();
      }
    }
    else {
      if (Input::get('needle-id'))
      {
        $needle = Needle::find(Input::get('needle-id'));
        $simulationNeedle = new SimulationNeedle;
        $simulationNeedle->Simulation_Id = $simulation->Id;
        $simulationNeedle->Needle_Id = $needle->Id;
        $target = json_decode(Input::get('needle-target'));
        $simulationNeedle->Target_Id = PointSet::create(['X' => $target[0], 'Y' => $target[1], 'Z' => $target[2]])->Id;
        $entry = json_decode(Input::get('needle-entry'));
        $simulationNeedle->Entry_Id = PointSet::create(['X' => $entry[0], 'Y' => $entry[1], 'Z' => $entry[2]])->Id;
        $simulationNeedle->save();
      }

      if (Input::get('region-id'))
      {
        $region = Region::find(Input::get('region-id'));
        $simulation->Regions()->attach($region, ['Location' => Input::get('region-location')]);
        $simulation->save();
      }
    }

    return Redirect::route('simulation.edit', $id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


  public function restore()
  {
    if (!Input::has('batch'))
      return Response::json(['message' => 'Need guid and batch'], 400);

    $batch = preg_replace('/[^0-9-_]+/', '', Input::get('batch'));

    if (Input::has('guid'))
    {
      $guids = [preg_replace('/[^A-Z0-9-]+/', '', Input::get('guid'))];
    }
    else
    {
      $guids = array_map(function ($x) {
        return preg_replace('/[^A-Z0-9-]+/', '', $x);
      }, array_filter(scandir(public_path() . '/backups/' . $batch), function ($x) {
        return $x[0] != '.';
      }));
    }

    $simulations = new Collection;

    foreach ($guids as $id)
    {
      $path = public_path() . '/backups/' . $batch . '/' . $id . '.xml';
      if (!file_exists($path))
        return Response::json(['message' => 'Cannot find backup', 'guid' => $id, 'batch' => $batch, 'path' => $path], 400);

      $xml = new DOMDocument;
      $xml->load($path);

      try {
        $simulations[] = Simulation::fromXml($xml);
      }
      catch (Exception $e)
      {
        return Response::json($e, 400);
      }
    }

    return Response::json($simulations->lists('Id'), 200);
  }

  public function backup()
  {
    $store = !Input::get('html');
    $path = public_path() . '/backups/' . date("Y-m-d_H-i-s");
    if ($store)
    {
      mkdir($path, 0777, true);
    }

    if (Input::has('prefix'))
      $simulations = Simulation::where("Caption", "LIKE", Input::get('prefix') . "%")->get();
    else
      $simulations = Simulation::all();

    $couldNotStore = [];
    $simulations->each(function ($simulation) use (&$couldNotStore, $store, $path) {
      $xml = new DOMDocument('1.0');
      $root = $xml->createElement('simulationDefinition');
      $xml->appendChild($root);

      try {
        $simulation->xml($root, true);
      }
      catch (Exception $e)
      {
        $couldNotStore[] = $simulation;
        return;
      }

      $xml->preserveWhiteSpace = false;
      $xml->formatOutput = true;

      if ($store)
        $xml->save($path . '/' . $simulation->Id . '.xml');
    });

    if (Request::format() == 'json')
    {
      if (count($store))
      {
        $responseArray = array_map(function ($s) {
          return ['id' => $s->Id, 'description' => $s->asString];
        });
        return json_encode($responseArray);
      }
      return true;
    }
    else
    {
      $err = "Backup<br/>\n";
      foreach ($couldNotStore as $simulation)
        $err .= "Error for " . $simulation->Id . ':' . $simulation->asString . "<br\>\n";
      if ($store)
        $err .= 'Complete.';
      else
        $err .= 'Tested but not stored.';
      return $err;
    }
  }
}
