<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEditableToParameterAttribution extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Parameter_Attribution', function(Blueprint $table)
		{
			$table->integer('Editable')->default(2);
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
			$table->dropColumn('Editable');
		});
	}

}
