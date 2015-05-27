<?php

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
		return View::make('simulations.create', compact('contexts'));
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

    $userParameters = $simulation->parameters();
    $regions = $simulation->regions();
    $combination = $simulation->combination;
    $needles = $simulation->needles();
    $needleParameters = $simulation->needleParameters();

    $xml = $combination->xml($userParameters, $regions, $incompatibilities, $needles, $needleParameters);

    if (!empty($incompatibilities))
      return Response::make(array_map('trim', $incompatibilities), 400);

    if ($xml === null)
      return Response::make("Simulation could not be built from input (reasons unreported)", 400);

    if (Input::get('html'))
    {
      $xml->preserveWhiteSpace = false;
      $xml->formatOutput = true;

      return View::make('simulations.show', ['simulationXml' => $xml]);
    }

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
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
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
