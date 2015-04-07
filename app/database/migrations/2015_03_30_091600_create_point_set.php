<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSet extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('PointSet', function(Blueprint $table)
		{
			$table->char('Id', 36)->primary();
      $table->float('X');
      $table->float('Y');
      $table->float('Z');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('PointSet');
	}

}
