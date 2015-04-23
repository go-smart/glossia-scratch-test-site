<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSimulationNeedleParameter extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Simulation_Needle_Parameter', function(Blueprint $table)
		{
			$table->increments('Id');
      $table->integer('SimulationNeedleId')->unsigned();
      $table->foreign('SimulationNeedleId')->references('Id')->on('Simulation_Needle');
      $table->char('ParameterId', 36);
      $table->foreign('ParameterId')->references('Id')->on('Parameter');
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
		Schema::drop('Simulation_Needle_Parameter');
	}

}
