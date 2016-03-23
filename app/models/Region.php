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



abstract class SegmentationTypeEnum
{
  const Liver = 0;
  const Lung = 1;
  const Kidney = 2;
  const Prostate = 3;
  const Vessels = 4;
  const Tumor = 5;
  const Bronchi = 6;
  const Lesion = 7;
  const Simulation = 8;
  const TACE = 9;

  static $all = [
    0 => 'Liver',
    1 => 'Lung',
    2 => 'Kidney',
    3 => 'Prostate',
    4 => 'Vessels',
    5 => 'Tumor',
    6 => 'Bronchi',
    7 => 'Lesion',
    8 => 'Simulation',
    9 => 'TACE'
  ];

  public static function get($id)
  {
    if (is_numeric($id))
      return isset(self::$all[$id]) ? self::$all[$id] : null;
    $id = train_case($id);
    try {
      constant('SegmentationTypeEnum::' . $id); // Check constant exists
    }
    catch (ErrorException $e) {
      return null;
    }
    return $id;
  }
}


class Region extends UuidModel {

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
	protected $table = 'Region';

  public function NumericalModels() {
    return $this->belongsToMany('NumericalModel', 'Numerical_Model_Region', 'Region_Id', 'Numerical_Model_Id')->withPivot('Maximum', 'Minimum');
  }

  public function findUnique() {
    return self::whereName($this->Name)
      ->whereFormat($this->Format)
      ->first();
  }

  public function getSegmentationTypesAttribute() {
    return DB::table('Region_SegmentationType')->whereRegionId($this->Id)->lists('SegmentationType');
  }

  public function addSegmentationType($typ) {
    if (!in_array($typ, $this->SegmentationTypes))
      DB::insert('insert into Region_SegmentationType (Region_Id, SegmentationType) values (:reg, :seg)',
        ['reg' => $this->Id, 'seg' => $typ]
      );
    return $this;
  }

  public function getDescriptionAttribute() {
    return ucwords($this->Name) . ' (' . implode(', ', array_map(function ($s) { return SegmentationTypeEnum::get($s); }, $this->SegmentationTypes)) . ')';
  }
}
