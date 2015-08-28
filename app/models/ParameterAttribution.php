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



class ParameterAttribution extends UuidModel {

  /**
   * Look after created_at and modified_at properties automatically
   *
   * @var boolean
   */
  public $timestamps = false;

  protected $priorityList = [];

  protected $specifyingFields = ['needle', 'algorithm', 'power_generator', 'numerical_model', 'context'];

  protected static $updateByDefault = false;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Parameter_Attribution';

  public function __construct(array $attributes = []) {
    parent::__construct($attributes);

    $this->priorityList = [];

    foreach ($this->specifyingFields as $idx => $field)
      $this->priorityList[$field] = $idx;
  }

  public function Parameter() {
    return $this->belongsTo('Parameter', 'Parameter_Id');
  }

  public function PowerGenerator() {
    return $this->belongsTo('PowerGenerator', 'Power_Generator_Id');
  }

  public function Needle() {
    return $this->belongsTo('Needle', 'Needle_Id');
  }

  public function Protocol() {
    return $this->belongsTo('Protocol', 'Protocol_Id');
  }

  public function Algorithm() {
    return $this->belongsTo('Algorithm', 'Algorithm_Id');
  }

  public function NumericalModel() {
    return $this->belongsTo('NumericalModel', 'Numerical_Model_Id');
  }

  public function Specificity() {
    return count(array_filter(array_map([$this, 'getAttribute'], $this->specifyingFields)));
  }

  public function getContextAttribute() {
    return Context::find($this->{Context::$idField});
  }

  public function getUnitsAttribute($units) {
    return ($units !== null ? json_decode($units) : null);
  }

  public function setUnitsAttribute($units) {
    if ($units !== null) {
      $units = json_encode($units);
    }

    $this->attributes['Units'] = $units;
  }

  public function getWidgetAttribute($widget) {
    return ($widget !== null ? json_decode($widget) : null);
  }

  public function setWidgetAttribute($widget) {
    if ($widget !== null) {
      if (!is_array($widget))
        $widget = [$widget];

      $widget = json_encode($widget);
    }

    $this->attributes['Widget'] = $widget;
  }

  public function Context() {
    return $this->belongsTo('Context', Context::$idField);
  }

  public function activeFields() {
    return array_filter($this->specifyingFields, function ($field) {
      $fieldName = train_case($field) . "_Id";
      return $this->{$fieldName};
    });
  }

  public function Priority() {
    $fields = $this->activeFields();

    if (!count($fields))
      return null;

    return min(array_map(function ($field) {
      return $this->priorityList[$field];
    }, $fields));
  }

  public function findUnique() {
    return false;
  }

  public function getAsHtmlAttribute()
  {
    $combined = "<span class='parameter' title='Type: $this->Format";

    if ($this->Units)
    {
      $combined .= "; Units: " . (is_array($this->Units) ? implode('|', $this->Units) : $this->Units);
    }

    if ($this->Widget)
    {
      $combined .= "; Can be given by user using " . $this->Widget[0];
      if (count($this->Widget) > 1)
        $combined .= "(" . implode(', ', $this->Widget[1]) . ")";
    }

    if ($this->Parameter->Description)
      $combined .= "; ($this->Description)";

    $combined .= "'>{$this->Parameter->Name}</span>";

    return $combined;
  }
}
