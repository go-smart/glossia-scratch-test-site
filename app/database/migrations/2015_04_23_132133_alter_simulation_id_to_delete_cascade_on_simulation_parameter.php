<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSimulationIdToDeleteCascadeOnSimulationParameter extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Simulation_Parameter', function(Blueprint $table)
		{
      $table->dropForeign('FK_Simulation_Parameter_Simulation');
      $table->foreign('SimulationId', 'FK_Simulation_Parameter_Simulation')->references('Id')->on('Simulation')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('Simulation_Parameter', function(Blueprint $table)
		{
      $table->dropForeign('FK_Simulation_Parameter_Simulation');
      $table->foreign('SimulationId', 'FK_Simulation_Parameter_Simulation')->references('Id')->on('Simulation');
		});
	}

}
