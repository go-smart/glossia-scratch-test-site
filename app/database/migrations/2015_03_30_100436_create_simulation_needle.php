<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSimulationNeedle extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Simulation_Needle', function(Blueprint $table)
		{
			$table->increments('Id');
      $table->char('Simulation_Id', 36);
      $table->foreign('Simulation_Id')->references('Id')->on('Simulation');
      $table->char('Needle_Id', 36);
      $table->foreign('Needle_Id')->references('Id')->on('Needle');
      $table->char('Target_Id', 36);
      $table->foreign('Target_Id')->references('Id')->on('PointSet');
      $table->char('Entry_Id', 36);
      $table->foreign('Entry_Id')->references('Id')->on('PointSet');
      $table->integer('Color')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('Simulation_Needle');
	}

}
