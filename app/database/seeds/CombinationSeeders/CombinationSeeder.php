<?php namespace CombinationSeeders;
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


use \DB;
use \Seeder;

class CombinationSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $lockedCombinations = \Combination::whereExists(function ($q) {
      $q->select(DB::raw(1))
        ->from('Simulation')
        ->whereRaw('Simulation.Combination_Id = Combination.Id');
    })->get();

    if (!$lockedCombinations->isEmpty())
      $this->command->info("The following combinations are locked for removal by simulations\n  * " .
        $lockedCombinations->implode('asString', "\n  * ")
      );

    \Combination::whereNotExists(function ($q) {
      $q->select(DB::raw(1))
        ->from('Simulation')
        ->whereRaw('Simulation.Combination_Id = Combination.Id');
    })->delete();

    $this->call('\CombinationSeeders\RFA\RFACombinationSeeder');
    $this->call('\CombinationSeeders\MWA\MWACombinationSeeder');
    $this->call('\CombinationSeeders\Cryoablation\CryoablationCombinationSeeder');
    $this->call('\CombinationSeeders\IRE\IRECombinationSeeder');
  }

}
