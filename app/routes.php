<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', ['as' => 'home', function()
{
	return View::make('hello');
}]);

/* Needles */
Route::get('needle/parameters', 'NeedleController@parameters');
Route::resource('needle', 'NeedleController');

/* Power generators */
Route::get('power_generator/parameters', 'PowerGeneratorController@parameters');
Route::resource('power_generator', 'PowerGeneratorController');

/* Protocols */
Route::resource('protocol', 'ProtocolController');

/* Algorithms */
Route::resource('algorithm', 'AlgorithmController');

/* Combinations */
Route::resource('combination', 'CombinationController');
Route::get('combination/{id}/parameter/{name}', 'CombinationController@retrieveParameter');

/* Simulations */
Route::resource('simulation', 'SimulationController');

/* Numerical models */
Route::get('numerical_model/parameters', 'NumericalModelController@parameters');
Route::get('numerical_model/arguments', 'NumericalModelController@arguments');
Route::resource('numerical_model', 'NumericalModelController');
