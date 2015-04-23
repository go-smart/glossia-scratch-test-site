<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCombinationNeedleToCascadeOnCombinationDelete extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Combination_Needle', function(Blueprint $table)
		{
			$table->dropForeign('FK_Combination_Needle_Combination');
      $table->foreign('Combination_Id')->references('Combination_Id')->on('Combination')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('Combination_Needle', function(Blueprint $table)
		{
			$table->dropForeign('FK_Combination_Needle_Combination');
      $table->foreign('Combination_Id')->references('Combination_Id')->on('Combination');
		});
	}

}
