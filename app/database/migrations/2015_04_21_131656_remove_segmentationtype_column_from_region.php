<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSegmentationtypeColumnFromRegion extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Region', function(Blueprint $table)
		{
			$table->dropColumn('SegmentationType');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('Region', function(Blueprint $table)
		{
			$table->integer('SegmentationType')->default(0);
		});
	}

}
