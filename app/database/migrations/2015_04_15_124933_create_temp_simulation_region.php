<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempSimulationRegion extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Simulation_Region', function(Blueprint $table)
		{
      $table->char('Id', 36);
      $table->char('Simulation_Id', 36);
      $table->char('Region_Id', 36);
      $table->string('Location')->nullable();
      $table->unique(['Simulation_Id', 'Region_Id', 'Location']);
		});
     /* FIXME: may be required for MSSQL
    DB::statement('ALTER TABLE Simulation_Region ALTER COLUMN Simulation_Id uniqueidentifier');
    DB::statement('ALTER TABLE Simulation_Region ALTER COLUMN Region_Id uniqueidentifier');
    DB::statement('
      CREATE TABLE [dbo].[Simulation_Region]
      (Simulation_Id uniqueidentifier, Region_Id uniqueidentifier, Location varchar(160),
       CONSTRAINT [PK_SimulationRegion_1] PRIMARY KEY CLUSTERED (Simulation_Id, Region_Id, Location))
    ');
     */
		Schema::table('Simulation_Region', function(Blueprint $table)
    {
      $table->foreign('Simulation_Id')->references('Id')->on('Simulation');
      $table->foreign('Region_Id')->references('Id')->on('Region');
    });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('Simulation_Region');
	}

}
