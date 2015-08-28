<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdColumnToSimulationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Simulation', function(Blueprint $table)
		{
      $table->uniqueidentifier('Parent_Id')->nullable()->onDelete('set null');
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
			$table->dropColumn('Parent_Id');
		});
	}

}
