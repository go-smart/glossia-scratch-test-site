<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSimulationIdToDeleteCascadeOnSimulationNeedle extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Simulation_Needle', function(Blueprint $table)
		{
      $table->dropForeign('FK_Simulation_Needle_Simulation');
      $table->foreign('Simulation_Id', 'FK_Simulation_Needle_Simulation')->references('Id')->on('Simulation')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('Simulation_Needle', function(Blueprint $table)
		{
      $table->dropForeign('FK_Simulation_Needle_Simulation');
      $table->foreign('Simulation_Id', 'FK_Simulation_Needle_Simulation')->references('Id')->on('Simulation');
		});
	}

}
