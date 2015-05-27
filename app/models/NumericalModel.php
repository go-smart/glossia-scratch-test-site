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


class NumericalModel extends Paramable {

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
	protected $table = 'numerical_models';

  /**
   * The modality that this applies to.
   *
   * @var string
   */
  public function modality() {
    return $this->belongsTo('Modality');
  }

  public function regions() {
    return $this->belongsToMany('Region')->withPivot('maximum', 'minimum');
  }

  public function combinations() {
    return $this->hasMany('Combination');
  }

  public function arguments() {
    return $this->morphMany('Argument', 'argumentable');
  }

  public function updatePlaceholdersFromDefinition() {
    $parameterRegex = Config::get('gosmart.parameterRegex');

    if (empty($parameterRegex))
      $parameterRegex = '/\$((CONSTANT|SETTING)_[A-Z_]+)/';

    $matches = [];
    preg_match_all($parameterRegex, $this->definition, $matches, PREG_PATTERN_ORDER);

    foreach ($matches[1] as $match) {
      $this->placeholder($match);
    }
  }

  public function results() {
    return $this->hasMany('Result');
  }

  /* XML */
  public function xml($parent, $suppliedRegions, &$incompatibilities, $needles = [], $needleParameters = [], $needleUserData = []) {
    foreach ($this->results as $result) {
      $resultNode = new DOMNode("result");
      $resultNode->setAttribute('name', $this->result->name);
      $parent->appendChild($resultNode);
    }

    $regions = [];
    $regionsNode = new DOMElement("regions");
    $parent->appendChild($regionsNode);
    foreach ($this->regions as $region) {
      $suppliedCount = isset($suppliedRegions[$region->name]) ? count($suppliedRegions[$region->name]) : 0;
      $pivot = $region->pivot;

      if ($pivot->maximum !== null && $suppliedCount > $pivot->maximum)
        $incompatibilities[] = "Too many region entries for $region->name (max $pivot->maximum, provided $suppliedCount)";

      if ($pivot->minimum !== null && $suppliedCount < $pivot->minimum)
        $incompatibilities[] = "Too few region entries for $region->name (min $pivot->minimum, provided $suppliedCount)";

      if ($suppliedCount) {
        $k = 0;
        foreach ($suppliedRegions[$region->name] as $entry) {
          $regionNode = new DOMElement("region");
          $regionsNode->appendChild($regionNode);
          $regionNode->setAttribute('id', $region->name . '-' . $k);
          $regionNode->setAttribute('name', $region->name);
          $regionNode->setAttribute('format', $region->format);
          $regionNode->setAttribute('input', $entry);
          $regionNode->setAttribute('groups', $region->groups); /* groups should be a JSON array */
          $k += 1;
        }
      }
      unset($suppliedRegions[$region->name]);
    }

    if (count($suppliedRegions))
      $incompatibilities[] = "Unknown regions for model $this->name : " . implode(', ', array_keys($suppliedRegions));

    $needlesNode = new DOMElement("needles");
    $parent->appendChild($needlesNode);
    foreach ($needles as $needleIx => $needle) {
      $needleNode = new DOMElement("needle");
      $needlesNode->appendChild($needleNode);
      $needleNode->setAttribute("index", $needleIx);

      /* isset checks value whether NULL */
      if (isset($needleUserData[$needleIx]) && isset($needleUserData[$needleIx]['class']))
        $needleNode->setAttribute("class", $needleUserData[$needleIx]['class']);
      else if (!empty($needle->class))
        $needleNode->setAttribute("class", $needle->class);
      else
        $incompatibilities[] = "Needle class is not given for " . $needleIx;

      if (isset($needleUserData[$needleIx]) && isset($needleUserData[$needleIx]['file']))
        $needleNode->setAttribute("file", $needleUserData[$needleIx]['file']);
      else if (!empty($needle->file))
        $needleNode->setAttribute("file", $needle->file);
      else
        $incompatibilities[] = "Needle file is not given for " . $needleIx;

      $parametersNode = new DOMElement("parameters");
      $needleNode->appendChild($parametersNode);
      if (isset($needleParameters[$needleIx]))
        foreach ($needleParameters[$needleIx] as $parameter)
          $parameter->xml($parametersNode);
    }

    $definition = new DOMElement("definition");
    $definitionText = new DOMText($this->definition);
    $parent->appendChild($definition);
    $definition->setAttribute('family', $this->family);
    $definition->appendChild($definitionText);
  }

  public function importSif($filename)
  {
    if (empty($this->id))
      throw Exception("Numerical model must have ID (i.e. be saved) before calling importSif");

    $sif = file_get_contents($filename);

    $matches = [];
    preg_match_all("/\\$([_a-zA-Z][_a-zA-Z0-9]*)/", $sif, $matches);

    $this->definition = $sif;

    foreach ($matches[1] as $key)
    {
      if (strpos($key, 'CONSTANT_') === 0 || strpos($key, 'SETTING_') === 0)
        $this->placeholder($key);
    }

    $this->save();
  }
}
