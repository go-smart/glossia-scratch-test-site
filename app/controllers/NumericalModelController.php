<?php

class NumericalModelController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
    if (Input::has('modality'))
    {
      $models = NumericalModel::where('modality_id', '=', Input::get('modality'))->get();
    }
    else
    {
      $models = NumericalModel::all();
    }

    if (Request::ajax())
    {
      return Response::json($models->lists('name', 'id'));
    }

    return View::make('numerical_models.index', compact('models'));
	}

  /**
   * Return Algorithms
   *
   * @return Response
   */
  public function arguments()
  {
    $numerical_model = NumericalModel::find(Input::get('id'));

    if (empty($numerical_model))
    {
      return Response::json(["error" => "Numerical model not found"]);
    }

    $arguments = $numerical_model->arguments;

    return Response::json($arguments);
  }

  /**
   * Return Parameters
   *
   * @return Response
   */
  public function parameters()
  {
    $numerical_model = NumericalModel::find(Input::get('id'));

    if (empty($numerical_model))
    {
      return Response::json(["error" => "Generator not found"]);
    }

    $parameters = $numerical_model->parameters()->get();

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

