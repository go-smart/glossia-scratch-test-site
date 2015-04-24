<?php

use Illuminate\Database\Eloquent\Collection;

class SimulationController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
    $simulations = Simulation::all();

		return View::make('simulations.index', compact('simulations'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
    $contexts = Context::all()->lists('name', 'id');
		return View::make('simulations.create', compact('Contexts'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
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
    $transferrerUrl->nodeValue = 'http://gosmartfiles.blob.core.windows.net/gosmart';
    $transferrer->appendChild($transferrerUrl);
    $root->appendChild($transferrer);

    $combination->xml($root, $userParameters, $regions, $incompatibilities, $needles, $needleParameters);

    if (!empty($incompatibilities))
      return Response::make(array_map('trim', $incompatibilities), 400);

    if ($xml === null)
      return Response::make("Simulation could not be built from input (reasons unreported)", 400);

    $xml->preserveWhiteSpace = false;
    $xml->formatOutput = true;

    if (Input::get('html'))
      return View::make('simulations.show', ['simulationXml' => $xml]);

    return $xml->saveXML();
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
		return View::make('simulations.edit', compact('simulation', 'needles', 'regions'));
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


}
