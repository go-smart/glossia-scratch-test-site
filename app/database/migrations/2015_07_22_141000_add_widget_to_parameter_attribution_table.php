<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWidgetToParameterAttributionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Parameter_Attribution', function(Blueprint $table)
		{
			$table->string('Widget')->nullable();
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
			$table->dropColumn('Widget');
		});
	}

}
