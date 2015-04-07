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

    if (Input::has('Needle_Id'))
      $combinations->whereNeedleId(Input::get('Needle_Id'));

    if (Input::has('Power_Generator_Id'))
      $combinations->wherePowerGeneratorId(Input::get('Power_Generator_Id'));

    if (Input::has('Context_Id'))
      $combinations->whereContextId(Input::get('Context_Id'));

    if (Input::has('Modality_Id')) {
      $modality_id = Input::get('Modality_Id');
      $combinations->whereHas('PowerGenerator', function($query) use ($modality_id) {
        $query->whereModalityId($modality_id);
      });
    }

    if (Input::has('output')) {
      switch (Input::get('output')) {
      case 'Needle':
        $output_ids = array_unique($combinations->get()->lists('Needle_Id'));
        return Needle::find($output_ids)->lists('Name', 'Id');
      case 'PowerGenerator':
        $output_ids = array_unique($combinations->get()->lists('Power_Generator_Id'));
        return PowerGenerator::find($output_ids)->lists('Name', 'Id');
      case 'NumericalModel':
        $output_ids = array_unique($combinations->get()->lists('Numerical_Model_Id'));
        return NumericalModel::find($output_ids)->lists('Name', 'Id');
      case 'Context':
        $output_ids = array_unique($combinations->get()->lists('Context_Id'));
        return Context::find($output_ids)->lists('Name', 'Id');
      case 'Modality':
        $combinations= $combinations
          ->join('Power_Generator', 'Power_Generator.Id', '=', 'Combination.Power_Generator_Id')
          ->select('Power_Generator.Modality_Id AS Modality_Id');
        $output_ids = array_unique($combinations->get()->lists('Modality_Id'));
        return Modality::find($output_ids)->lists('Name', 'Id');
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
      return $parameter->Value;

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
