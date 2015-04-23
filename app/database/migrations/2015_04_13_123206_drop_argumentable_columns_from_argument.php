<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropArgumentableColumnsFromArgument extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Argument', function(Blueprint $table)
		{
			//
      $table->dropColumn('Argumentable_Id');
      $table->dropColumn('Argumentable_Type');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('Argument', function(Blueprint $table)
		{
      $table->char('Argumentable_Id', 36);
      $table->string('Argumentable_Type');
		});
	}

}
