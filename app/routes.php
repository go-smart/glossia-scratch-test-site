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
Route::get('simulation/dashboard', 'SimulationController@dashboard');
Route::get('simulation/{id}/duplicate', ['uses' => 'SimulationController@duplicate', 'as' => 'simulation.duplicate']);
Route::get('simulation/{id}/rebuild', ['uses' => 'SimulationController@rebuild', 'as' => 'simulation.rebuild']);
Route::get('simulation/{id}/segmentedLesion', ['uses' => 'SimulationController@getSegmentedLesion', 'as' => 'simulation.getSegmentedLesion']);
Route::get('simulation/patient', 'SimulationController@patient');
Route::get('simulation/backup', ['uses' => 'SimulationController@backup', 'as' => 'simulation.backup']);
Route::get('simulation/restore', ['uses' => 'SimulationController@restore', 'as' => 'simulation.restore']);
Route::resource('simulation', 'SimulationController');

/* Numerical models */
Route::get('numerical_model/parameters', 'NumericalModelController@parameters');
Route::get('numerical_model/arguments', 'NumericalModelController@arguments');
Route::resource('numerical_model', 'NumericalModelController');
