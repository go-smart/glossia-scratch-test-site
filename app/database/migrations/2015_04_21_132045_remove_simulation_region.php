<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSimulationRegion extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
    $forwardMigration = new CreateTempSimulationRegion();
    $forwardMigration->down();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
    $forwardMigration = new CreateTempSimulationRegion();
    $forwardMigration->up();
	}

}
