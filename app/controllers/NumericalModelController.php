<?php

class NumericalModelController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
    if (Input::has('Modality'))
    {
      $models = NumericalModel::where('Modality_Id', '=', Input::get('Modality'))->get();
    }
    else
    {
      $models = NumericalModel::all();
    }

    if (Request::ajax())
    {
      return Response::json($models->lists('Name', 'Id'));
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
    $numerical_model = NumericalModel::find(Input::get('Id'));

    if (empty($numerical_model))
    {
      return Response::json(["error" => "Numerical model not found"]);
    }

    $arguments = $numerical_model->Arguments;

    return Response::json($arguments);
  }

  /**
   * Return Parameters
   *
   * @return Response
   */
  public function parameters()
  {
    $numerical_model = NumericalModel::find(Input::get('Id'));

    if (empty($numerical_model))
    {
      return Response::json(["error" => "Generator not found"]);
    }

    $parameters = $numerical_model->Parameters()->get();

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
    $regions = Region::all();

		return View::make('numerical_models.create', ['regions' => $regions]);
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
    $numerical_model = NumericalModel::find($id);

    return View::make('numerical_models.show', compact('numerical_model'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
    $numerical_model = NumericalModel::find($id);

    return View::make('numerical_models.edit', compact('numerical_model'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
    $numerical_model = NumericalModel::find($id);
    if (Input::has('definition'))
    {
      $numerical_model->Definition = Input::get('definition');
      $dfile = fopen("/tmp/model-" . $id . ".txt", "w");
      fwrite($dfile, $numerical_model->Definition);
      fclose($dfile);
    }
    $numerical_model->save();
    return Redirect::route('numerical_model.edit', $id);
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

