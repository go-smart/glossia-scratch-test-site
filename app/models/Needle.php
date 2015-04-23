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



class Needle extends Paramable {

  /**
   * Look after created_at and modified_at properties automatically
   *
   * @var boolean
   */
  public $timestamps = false;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Needle';

  public static $idField = 'Needle_Id';

  /**
   * The generators allowed for this needle.
   *
   * @var string
   */
  public function PowerGenerators() {
    return $this->belongsToMany('PowerGenerator', 'Needle_Power_Generator', 'Needle_Id', 'Power_Generator_Id');
  }

  /**
   * The modality that this applies to.
   *
   * @var string
   */
  public function Modality() {
    return $this->belongsTo('Modality', 'Modality_Id');
  }

  public function Combinations() {
    return $this->belongsToMany('Combination', 'Combination_Needle', 'Needle_Id', 'Combination_Id');
  }

  public function SimulationNeedles() {
    return $this->hasMany('SimulationNeedle', 'Needle_Id');
  }

  public function findUnique()
  {
    return self::whereName($this->Name)
      ->whereManufacturer($this->Manufacturer)
      ->whereClass($this->Class)
      ->first();
  }
}
