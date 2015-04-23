<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNumericalModelArgument extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Numerical_Model_Argument', function(Blueprint $table)
		{
      $table->char('Numerical_Model_Id', 36);
      $table->foreign('Numerical_Model_Id')->references('Id')->on('Numerical_Model');
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
		Schema::drop('Numerical_Model_Argument');
	}

}
