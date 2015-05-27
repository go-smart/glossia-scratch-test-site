<?php

class NeedleController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
    if (Input::has('power_generator'))
    {
      $needles = new \Illuminate\Database\Eloquent\Collection;
      $generator = PowerGenerator::find(Input::get('power_generator'));
      if (!empty($generator))
      {
        $needles = $generator->needles;
      }
    }
    else
    {
      $needles = Needle::all();
    }

    if (Request::ajax())
    {
      return Response::json($needles->lists('name', 'id'));
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
    $needle = Needle::find(Input::get('id'));

    if (empty($needle))
    {
      return Response::json(["error" => "Needle not found"]);
    }

    $parameters = $needle->parameters()->get();

    $parameters = $parameters->map(function ($parameter) {
      return array(
        'id' => $parameter->id,
        'name' => $parameter->name,
        'type' => $parameter->type,
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
