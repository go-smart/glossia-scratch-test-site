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


use Illuminate\Database\Eloquent\Collection;

class Simulation extends UuidModel {

  protected $cachedParameters = null;

  public $timestamps = false;

  protected $table = "Simulation";

  protected static $updateByDefault = false;

  public function Combination() {
    return $this->belongsTo('Combination', 'Combination_Id');
  }

  /* This actually hydrates and then stringifies the parameter value again, but if the Parameter
   * object starts to store values as non-strings this is where it should change */
  public function Parameters() {
    return $this->belongsToMany('Parameter', 'Simulation_Parameter', 'SimulationId', 'ParameterId')->withPivot(['ValueSet']);
  }

  public function SimulationNeedles() {
    return $this->hasMany('SimulationNeedle', 'Simulation_Id');
  }

  public function getSegmentationsAttribute() {
    $segmentations = new Collection(DB::select('
      SELECT IS_S.SegmentationType AS SegmentationType, IS_F.FileName AS FileName, IS_F.Extension AS Extension
      FROM ItemSet_Segmentation IS_S
       JOIN ItemSet_Patient IS_P ON IS_S.Patient_Id=IS_P.Id
       JOIN ItemSet_VtkFile IS_V ON IS_S.Id=IS_V.Segmentation_Id
       JOIN ItemSet_File IS_F ON IS_F.Id=IS_V.Id
      WHERE IS_P.Id=:PatientId
    ', ['PatientId' => $this->Patient_Id]));
    $segmentations->each(function ($s) { $s->Name = SegmentationTypeEnum::get($s->SegmentationType); });
    return $segmentations;
  }

  public function findUnique()
  {
    return false;
  }

  public function getAsStringAttribute()
  {
    return $this->Combination->asString . ' (' . $this->Id . ')';
  }

  public function getAsHtmlAttribute()
  {
    return "<span class='parameter' title='" . htmlentities($this->asString) . "'>" . $this->Caption . "</span>";
  }

}
