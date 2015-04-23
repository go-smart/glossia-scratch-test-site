<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionSegmentationType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
    DB::statement('
      CREATE TABLE [dbo].[Region_SegmentationType]
      (Region_Id uniqueidentifier, SegmentationType INT,
       CONSTRAINT [PK_RegionSegmentationType_1] PRIMARY KEY CLUSTERED (Region_Id, SegmentationType))
    ');
		Schema::table('Region_SegmentationType', function(Blueprint $table)
    {
      $table->foreign('Region_Id')->references('Id')->on('Region')->onDelete('cascade');
    });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('Region_SegmentationType');
	}

}
