<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFormatAndEditableToSimulationParameters extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
    foreach (['Simulation_Parameter', 'Simulation_Needle_Parameter'] as $table)
      Schema::table($table, function(Blueprint $table)
      {
        $table->string('Format')->nullable();
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
    $column = 'Editable';
    foreach (['Simulation_Parameter', 'Simulation_Needle_Parameter'] as $table)
    {
      $constraint = DB::select("
        SELECT D.name FROM sys.default_constraints AS D
        WHERE D.parent_object_id=OBJECT_ID(:table)
          AND D.parent_column_id=(
            SELECT column_id
            FROM sys.columns
            WHERE object_id=OBJECT_ID(:table)
              AND name=:column
          )
      ", ["table" => $table, "column" => $column]);

      if (!empty($constraint))
        DB::statement("ALTER TABLE " . $table . " DROP CONSTRAINT " . $constraint[0]->name);

      Schema::table($table, function(Blueprint $table)
      {
        $table->dropColumn('Format');
        $table->dropColumn('Editable');
      });
    }
	}

}
