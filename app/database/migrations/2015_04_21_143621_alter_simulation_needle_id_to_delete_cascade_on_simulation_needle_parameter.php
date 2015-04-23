<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSimulationNeedleIdToDeleteCascadeOnSimulationNeedleParameter extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Simulation_Needle_Parameter', function(Blueprint $table)
		{
      $table->dropForeign('FK_Simulation_Needle_Parameter_Simulation_Needle');
      $table->foreign('SimulationNeedleId', 'FK_Simulation_Needle_Parameter_Simulation_Needle')->references('Id')->on('Simulation_Needle')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('Simulation_Needle_Parameter', function(Blueprint $table)
		{
      $table->dropForeign('FK_Simulation_Needle_Parameter_Simulation_Needle');
      $table->foreign('SimulationNeedleId', 'FK_Simulation_Needle_Parameter_Simulation_Needle')->references('Id')->on('Simulation_Needle');
		});
	}

}
