<?php
/**
 * This file is part of the Go-Smart Simulation Architecture (GSSA).
 * Go-Smart is an EU-FP7 project, funded by the European Commission.
 *
 * Copyright (C) 2013-  NUMA Engineering Ltd. (see AUTHORS file)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParameterAttributionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Parameter_Attribution', function(Blueprint $table)
		{
			$table->char('Id', 36)->primary();
      $table->char('Parameter_Id', 36)->nullable();
      $table->foreign('Parameter_Id')->references('Id')->on('Parameter')->onDelete('cascade');
      $table->char('Needle_Id', 36)->nullable();
      $table->foreign('Needle_Id')->references('Id')->on('Needle')->onDelete('cascade');
      $table->char('Power_Generator_Id', 36)->nullable();
      $table->foreign('Power_Generator_Id')->references('Id')->on('Power_Generator')->onDelete('cascade');
      $table->char('Numerical_Model_Id', 36)->nullable();
      $table->foreign('Numerical_Model_Id')->references('Id')->on('Numerical_Model')->onDelete('cascade');
      if (Config::get('gosmart.context_as_enum'))
      {
        $table->int('OrganType')->nullable();
      }
      else
      {
        $table->char('Context_Id', 36)->nullable();
        $table->foreign('Context_Id')->references('Id')->on('Context')->onDelete('cascade');
      }
      $table->char('Algorithm_Id', 36)->nullable();
      $table->foreign('Algorithm_Id')->references('Id')->on('Algorithm')->onDelete('cascade');
      $table->string('Value')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('Parameter_Attribution');
	}

}
