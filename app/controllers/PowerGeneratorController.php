<?php

class PowerGeneratorController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
    if (Input::has('Modality'))
    {
      $generators = PowerGenerator::where('Modality_Id', '=', Input::get('Modality'))->get();
    }
    else
    {
      $generators = PowerGenerator::all();
    }

    if (Request::ajax())
    {
      return Response::json($generators->lists('Name', 'Id'));
    }

    return View::make('power_generators.index', compact('generators'));
	}

  /**
   * Return Parameters
   *
   * @return Response
   */
  public function parameters()
  {
    $power_generator = PowerGenerator::find(Input::get('Id'));

    if (empty($power_generator))
    {
      return "Not found";
    }

    $parameters = $power_generator->parameters()->get();

    $parameters = $parameters->map(function ($parameter) {
      return array(
        'Id' => $parameter->Id,
        'Name' => $parameter->Name,
        'Type' => $parameter->Type,
        'html' => $parameter->as_html()
      );
    });

    return Response::json($parameters);
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
		//
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
