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

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'parameter_attributions';

  public function __construct(array $attributes = []) {
    parent::__construct($attributes);

    $this->priorityList = [];

    foreach ($this->specifyingFields as $idx => $field)
      $this->priorityList[$field] = $idx;
  }

  public function parameter() {
    return $this->belongsTo('Parameter');
  }

  public function powerGenerator() {
    return $this->belongsTo('PowerGenerator');
  }

  public function needle() {
    return $this->belongsTo('Needle');
  }

  public function protocol() {
    return $this->belongsTo('Protocol');
  }

  public function algorithm() {
    return $this->belongsTo('Algorithm');
  }

  public function numericalModel() {
    return $this->belongsTo('NumericalModel');
  }

  public function specificity() {
    return count(array_filter(array_map([$this, 'getAttribute'], $this->specifyingFields)));
  }

  public function activeFields() {
    return array_filter($this->specifyingFields, function ($field) {
      return !empty($this->$field);
    });
  }

  public function priority() {
    $fields = $this->activeFields();

    if (!count($fields))
      return null;

    return min(array_map(function ($field) {
      return $this->priorityList[$field];
    }, $fields));
  }

}
