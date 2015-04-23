<?php

class NeedleController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
    if (Input::has('Power_Generator'))
    {
      $needles = new \Illuminate\Database\Eloquent\Collection;
      $generator = PowerGenerator::find(Input::get('Power_Generator'));
      if (!empty($generator))
      {
        $needles = $generator->Needles;
      }
    }
    else
    {
      $needles = Needle::all();
    }

    if (Request::ajax())
    {
      return Response::json($needles->lists('Name', 'Id'));
    }

    return View::make('needles.index', compact('needles'));
	}

  /**
   * Return Parameters
   *
   * @return Response
   */
  public function parameters()
  {
    $needle = Needle::find(Input::get('Id'));

    if (empty($needle))
    {
      return Response::json(["error" => "Needle not found"]);
    }

    $parameters = $needle->parameters()->get();

    $parameters = $parameters->map(function ($parameter) {
      return array(
        'Id' => $parameter->Id,
        'Name' => $parameter->Name,
        'Type' => $parameter->Type,
        'Html' => $parameter->as_html()
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
		return View::make('needles.create');
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
