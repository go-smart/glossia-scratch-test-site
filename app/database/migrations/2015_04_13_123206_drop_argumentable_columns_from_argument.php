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
      $table->dropColumn('Argumentable_Id');
		});
		Schema::table('Argument', function(Blueprint $table)
		{
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
      $table->char('Argumentable_Id', 36)->default('00000000-0000-0000-0000-000000000000');
      $table->string('Argumentable_Type')->default('');
		});
	}

}
