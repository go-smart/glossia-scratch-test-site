<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOriginalIdColumnToSimulationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Simulation', function(Blueprint $table)
		{
      $table->uniqueidentifier('Original_Id')->nullable()->onDelete('set null');
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
			$table->dropColumn('Original_Id');
		});
	}

}
