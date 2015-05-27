<?php

class ProtocolController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
    $modalities = Modality::with('protocols')->get();

		return View::make('protocols.index', compact('modalities'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
    $modalities = Modality::all()->lists('name', 'id');

    return View::make('protocols.create', compact('modalities'));
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
    $protocol = Protocol::find($id);

    if (empty($protocol))
    {
      return Redirect::route('protocol.index')->withError("Protocol not found");
    }

    $protocol->combinations->load('powerGenerator', 'numericalModel', 'context');

		return View::make('protocols.show', compact('protocol'));
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
