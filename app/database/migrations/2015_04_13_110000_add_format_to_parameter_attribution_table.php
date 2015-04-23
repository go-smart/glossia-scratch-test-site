<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFormatToParameterAttributionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Parameter_Attribution', function(Blueprint $table)
		{
			$table->string('Format')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('Parameter_Attribution', function(Blueprint $table)
		{
			$table->dropColumn('Format');
		});
	}

}
