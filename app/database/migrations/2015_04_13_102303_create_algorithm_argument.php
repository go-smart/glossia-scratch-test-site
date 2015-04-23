<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlgorithmArgument extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Algorithm_Argument', function(Blueprint $table)
		{
      $table->char('Algorithm_Id', 36);
      $table->foreign('Algorithm_Id')->references('Id')->on('Algorithm');
      $table->char('Argument_Id', 36);
      $table->foreign('Argument_Id')->references('Id')->on('Argument');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('Algorithm_Argument');
	}

}
