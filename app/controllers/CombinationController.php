<?php

class CombinationController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
    $combinations = Combination::with('PowerGenerator', 'Needle', 'Protocol');

    if (Input::has('needle_id'))
      $combinations->whereNeedleId(Input::get('needle_id'));

    if (Input::has('power_generator_id'))
      $combinations->wherePowerGeneratorId(Input::get('power_generator_id'));

    if (Input::has('context_id'))
      $combinations->whereContextId(Input::get('context_id'));

    if (Input::has('modality_id')) {
      $modality_id = Input::get('modality_id');
      $combinations->whereHas('powerGenerator', function($query) use ($modality_id) {
        $query->whereModalityId($modality_id);
      });
    }

    if (Input::has('output')) {
      switch (Input::get('output')) {
      case 'Needle':
        $output_ids = array_unique($combinations->get()->lists('needle_id'));
        return Needle::find($output_ids)->lists('name', 'id');
      case 'PowerGenerator':
        $output_ids = array_unique($combinations->get()->lists('power_generator_id'));
        return PowerGenerator::find($output_ids)->lists('name', 'id');
      case 'NumericalModel':
        $output_ids = array_unique($combinations->get()->lists('numerical_model_id'));
        return NumericalModel::find($output_ids)->lists('name', 'id');
      case 'Context':
        $output_ids = array_unique($combinations->get()->lists('context_id'));
        return Context::find($output_ids)->lists('name', 'id');
      case 'Modality':
        $combinations= $combinations
          ->join('power_generators', 'power_generators.id', '=', 'combinations.power_generator_id')
          ->select('power_generators.modality_id AS modality_id');
        $output_ids = array_unique($combinations->get()->lists('modality_id'));
        return Modality::find($output_ids)->lists('name', 'id');
      }

      return $combinations;
    }

		return View::make('combinations.index', compact('combinations'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
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
    $combination = Combination::find($id);

    if (empty($combination))
    {
      return ["error" => "Combination not found"];
    }

    $incompatibilities = [];

    $xml = $combination->xml([], [], $incompatibilities, []);

    if (!empty($incompatibilities))
      return Response::make(array_map('trim', $incompatibilities), 400);

    if ($xml === null)
      return Response::make("Combination could not be built from input (reasons unknown)", 400);

    if (Input::get('html'))
    {
      $xml->preserveWhiteSpace = false;
      $xml->formatOutput = true;

      return View::make('combinations.show', ['combinationXml' => $xml]);
    }

    return $xml->saveXML();
	}

  /**
   * Get the value for a core parameter, i.e. one that is not associated
   * with a specific Simulation (including those associated with the
   * choice and type of needles, as this is also specific to a Simulation)
   *
   * @param string $id Combination for which we wish to find a core parameter
   * @param string $name Parameter name to be retrieved
   * @return string Textual representation of value
   */
  public function retrieveParameter($id, $name)
  {
    $combination = Combination::find($id);

    if (empty($combination))
    {
      return ["error" => "Combination not found"];
    }

    $parameter = $combination->retrieveParameter($name);

    if ($parameter)
      return $parameter->value;

    return null;
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
