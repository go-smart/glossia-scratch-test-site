<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdForeignKeyToSimulationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Simulation', function(Blueprint $table)
		{
      $table->foreign('Parent_Id', 'FK_Simulation_Simulation')->references('Id')->on('Simulation');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('Simulation', function(Blueprint $table)
		{
      $table->dropForeign('FK_Simulation_Simulation');
		});
	}

}
