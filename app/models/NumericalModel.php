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
	protected $table = 'Numerical_Model';

  /**
   * The modality that this applies to.
   *
   * @var string
   */
  public function Modality() {
    return $this->belongsTo('Modality', 'Modality_Id');
  }

  public function Regions() {
    return $this->belongsToMany('Region', 'Numerical_Model_Region', 'Numerical_Model_Id', 'Region_Id')->withPivot('Maximum', 'Minimum');
  }

  public function Combinations() {
    return $this->hasMany('Combination', 'Numerical_Model_Id');
  }

  public function Arguments() {
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

  public function Results() {
    return $this->hasMany('Result', 'Numerical_Model_Id');
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
    foreach ($this->Regions as $region) {
      $suppliedCount = isset($suppliedRegions[$region->Name]) ? count($suppliedRegions[$region->Name]) : 0;
      $pivot = $region->pivot;

      if (Config::get('gosmart.check_regions') !== false)
      {
        if ($pivot->Maximum !== null && $suppliedCount > $pivot->Maximum)
          $incompatibilities[] = "Too many region entries for $region->Name (max $pivot->Maximum, provided $suppliedCount)";

        if ($pivot->Minimum !== null && $suppliedCount < $pivot->Minimum)
          $incompatibilities[] = "Too few region entries for $region->Name (min $pivot->Minimum, provided $suppliedCount)";
      }

      if ($suppliedCount) {
        $k = 0;
        foreach ($suppliedRegions[$region->Name] as $entry) {
          $regionNode = new DOMElement("region");
          $regionsNode->appendChild($regionNode);
          $regionNode->setAttribute('id', $region->Name . '-' . $k);
          $regionNode->setAttribute('name', $region->Name);
          $regionNode->setAttribute('format', $region->Format);
          $regionNode->setAttribute('input', $entry);
          $regionNode->setAttribute('groups', $region->Groups); /* groups should be a JSON array */
          $k += 1;
        }
      }
      unset($suppliedRegions[$region->Name]);
    }

    if (count($suppliedRegions))
      $incompatibilities[] = "Unknown regions for model $this->Name : " . implode(', ', array_keys($suppliedRegions));

    $needlesNode = new DOMElement("needles");
    $parent->appendChild($needlesNode);
    foreach ($needles as $needleIx => $needle) {
      $needleNode = new DOMElement("needle");
      $needlesNode->appendChild($needleNode);
      $needleNode->setAttribute("index", $needleIx);

      /* isset checks value whether NULL */
      if (isset($needleUserData[$needleIx]) && isset($needleUserData[$needleIx]['class']))
        $needleNode->setAttribute("class", $needleUserData[$needleIx]['class']);
      else if (!empty($needle->Class))
        $needleNode->setAttribute("class", $needle->Class);
      else
        $incompatibilities[] = "Needle class is not given for " . $needleIx;

      if (isset($needleUserData[$needleIx]) && isset($needleUserData[$needleIx]['file']))
        $needleNode->setAttribute("file", $needleUserData[$needleIx]['file']);
      else if (!empty($needle->File))
        $needleNode->setAttribute("file", $needle->File);
      else
        $incompatibilities[] = "Needle file is not given for " . $needleIx;

      $parametersNode = new DOMElement("parameters");
      $needleNode->appendChild($parametersNode);
      if (isset($needleParameters[$needleIx]))
        foreach ($needleParameters[$needleIx] as $parameter)
          $parameter->xml($parametersNode);
    }

    $definition = new DOMElement("definition");
    $definitionText = new DOMText($this->Definition);
    $parent->appendChild($definition);
    $definition->setAttribute('family', $this->Family);
    $definition->appendChild($definitionText);
  }

  public function importSif($filename)
  {
    if (empty($this->Id))
      throw RuntimeException("Numerical model must have ID (i.e. be saved) before calling importSif");

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

  public function findUnique()
  {
    return self::whereName($this->Name)
      ->whereFamily($this->Family)
      ->first();
  }
}
