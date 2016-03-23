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

  public static $idField = 'Numerical_Model_Id';

  /**
   * The modality that this applies to.
   *
   * @var string
   */
  public function Modality() {
    return $this->belongsTo('Modality', 'Modality_Id');
  }

  public function ParameterAttributions() {
    return $this->hasMany('ParameterAttribution', 'Numerical_Model_Id');
  }

  public function Regions() {
    return $this->belongsToMany('Region', 'Numerical_Model_Region', 'Numerical_Model_Id', 'Region_Id')->withPivot('Maximum', 'Minimum');
  }

  public function Combinations() {
    return $this->hasMany('Combination', 'Numerical_Model_Id');
  }

  public function Arguments() {
    return $this->belongsToMany('Argument', 'Numerical_Model_Argument', 'Numerical_Model_Id', 'Argument_Id');
  }

  public function loadDefinitionFromString($definition) {
    $parameterRegex = Config::get('gosmart.parameterRegex');

    if (empty($parameterRegex))
      $parameterRegex = '/({{ p.((CONSTANT|SETTING|PARAMETER)_[A-Z_]+)\|?[a-zA-Z0-9_]* }}):?([a-z0-9\(\)A-Z_,]*)/';

    $this->updatePlaceholdersFromString($parameterRegex, $definition);
    $definition = preg_replace($parameterRegex, '${1}', $definition);

    $this->Definition = $definition;
  }

  public function updatePlaceholdersFromString($parameterRegex, $definition) {
    $matches = [];
    preg_match_all($parameterRegex, $definition, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
      $this->placeholder($match[2], null, $match[4], false);
    }
  }

  public function Results() {
    return $this->hasMany('Result', 'Numerical_Model_Id');
  }

  /* XML */
  public function xml($parent, $suppliedRegions, &$incompatibilities, $needles = [], $needleUserData = [], $backup=false) {
    foreach ($this->results as $result) {
      $resultNode = new DOMNode("result");
      $resultNode->setAttribute('name', $this->result->name);
      $parent->appendChild($resultNode);
    }

    $regions = [];
    $regionsNode = new DOMElement("regions");
    $parent->appendChild($regionsNode);
    foreach ($this->Regions as $region) {
      $entries = $suppliedRegions->filter(function ($r) use ($region) { return in_array($r->SegmentationType, $region->SegmentationTypes); });
      $entries->each(function($e) { $e->Location = strtolower($e->FileId) . '/' . $e->FileName . '.' . $e->Extension; });
      $suppliedCount = $entries->count();
      $pivot = $region->pivot;

      if (Config::get('gosmart.check_regions') !== false)
      {
        if ($pivot->Maximum !== null && $suppliedCount > $pivot->Maximum)
          $incompatibilities[] = "Too many region entries for $region->Name (max $pivot->Maximum, provided $suppliedCount)";

        if ($pivot->Minimum !== null && $suppliedCount < $pivot->Minimum)
          $incompatibilities[] = "Too few region entries for $region->Name (min $pivot->Minimum, provided $suppliedCount)";
      }
      else if ($pivot->Minimum)
      {
        for ($k = 0 ; $k < $pivot->Minimum - $entries->count() ; $k++)
        {
          $entry = clone $region;
          $entries[] = $entry;
          $entry->Location = $region->Name . '.vtp';
        }
      }
      $k = 0;
      foreach ($entries as $entry) {
        $regionNode = new DOMElement("region");
        $regionsNode->appendChild($regionNode);
        $regionNode->setAttribute('id', $region->Name . '-' . $k);
        $regionNode->setAttribute('name', $region->Name);
        $regionNode->setAttribute('format', $region->Format);
        $regionNode->setAttribute('input', $entry->Location);
        $regionNode->setAttribute('groups', $region->Groups); /* groups should be a JSON array */
        $k += 1;
      }
      $suppliedRegions = $suppliedRegions->reject(function ($r) use ($region) {
        return $r->SegmentationType == $region->SegmentationType;
      });
    }

    //if (count($suppliedRegions))
    //  $incompatibilities[] = "Unknown regions for model $this->Name : " . implode(', ', $suppliedRegions->lists('Name'));

    $needlesNode = new DOMElement("needles");
    $parent->appendChild($needlesNode);
    $i = 1;
    foreach ($needles as $simulationNeedle) {
      $needle = $simulationNeedle->Needle;
      if ($simulationNeedle->Index)
        $needleIx = 'needle' . $simulationNeedle->Index;
      else
        $needleIx = 'needle' . $i;
      $i += 1;

      $needleNode = new DOMElement("needle");
      $needlesNode->appendChild($needleNode);
      $needleNode->setAttribute("index", $needleIx);

      if ($backup)
      {
        $needleNode->setAttribute("id", $needle->Id);
        $needleNode->setAttribute("name", $needle->Name);
      }

      /* isset checks value whether NULL */
      if (isset($needleUserData[$needleIx]) && isset($needleUserData[$needleIx]['class']))
        $needleNode->setAttribute("class", $needleUserData[$needleIx]['class']);
      else if (!empty($needle->Class))
        $needleNode->setAttribute("class", $needle->Class);
      else
        $incompatibilities[] = "Needle class is not given for " . $needleIx;

      if (isset($needleUserData[$needleIx]) && isset($needleUserData[$needleIx]['file']))
        $needleNode->setAttribute("file", $needleUserData[$needleIx]['file']);
      else if (!empty($needle->Geometry))
        $needleNode->setAttribute("file", $needle->Geometry);
      else
        $incompatibilities[] = "Needle file is not given for " . $needleIx;

      $parametersNode = new DOMElement("parameters");
      $needleNode->appendChild($parametersNode);

      $tipParameter = Parameter::whereName("NEEDLE_TIP_LOCATION")->first();
      $tipParameter->Value = json_encode($simulationNeedle->Target->asArray);
      $entryParameter = Parameter::whereName("NEEDLE_ENTRY_LOCATION")->first();
      $entryParameter->Value = json_encode($simulationNeedle->Entry->asArray);

      $tipParameter->xml($parametersNode, $backup);
      $entryParameter->xml($parametersNode, $backup);

      $simulationNeedle->Parameters->each(function ($parameter) use ($parametersNode, $backup) {
        $parameter->xml($parametersNode, $backup);
      });
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

    /*
    $matches = [];
    preg_match_all("/\\$([_a-zA-Z][_a-zA-Z0-9]*)/", $sif, $matches);

    $this->definition = $sif;

    foreach ($matches[1] as $key)
    {
      if (strpos($key, 'CONSTANT_') === 0 || strpos($key, 'SETTING_') === 0 || strpos($key, 'PARAMETER_') === 0)
        $this->placeholder($key);
    }
     */
    $this->loadDefinitionFromString($sif);

    $this->save();
  }

  public function findUnique()
  {
    return self::whereName($this->Name)
      ->whereFamily($this->Family)
      ->first();
  }
}
