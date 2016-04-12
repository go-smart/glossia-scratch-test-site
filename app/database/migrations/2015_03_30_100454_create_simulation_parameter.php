<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSimulationParameter extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Simulation_Parameter', function(Blueprint $table)
		{
			$table->increments('Id');
      $table->char('Simulation_Id', 36);
      $table->foreign('Simulation_Id')->references('Id')->on('Simulation');
      $parameter_column_name = (Config::get('gosmart.legacy_support') ? 'ParameterId' : 'Parameter_Id');
      $table->char($parameter_column_name, 36);
      $table->foreign($parameter_column_name)->references('Id')->on('Parameter');
      $table->string('ValueSet');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('Simulation_Parameter');
	}

}
