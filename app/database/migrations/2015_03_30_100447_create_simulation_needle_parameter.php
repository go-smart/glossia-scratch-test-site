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
      $table->integer('Simulation_Needle_Id')->unsigned();
      $table->foreign('Simulation_Needle_Id')->references('Id')->on('Simulation_Needle');
      $table->char('Parameter_Id', 36);
      $table->foreign('Parameter_Id')->references('Id')->on('Parameter');
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
