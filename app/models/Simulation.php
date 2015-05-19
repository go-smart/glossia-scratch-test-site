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

  protected $appends = ['asHtml', 'asString', 'clinician'];

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
      SELECT IS_F.Id AS FileId, IS_S.SegmentationType AS SegmentationType, IS_F.FileName AS FileName, IS_F.Extension AS Extension
      FROM ItemSet_Segmentation IS_S
       JOIN ItemSet_Patient IS_P ON IS_S.Patient_Id=IS_P.Id
       JOIN ItemSet_VtkFile IS_V ON IS_S.Id=IS_V.Segmentation_Id
       JOIN ItemSet_File IS_F ON IS_F.Id=IS_V.Id
      WHERE IS_P.Id=:PatientId AND IS_S.State=3
    ', ['PatientId' => $this->Patient_Id]));
    $segmentations->each(function ($s) { $s->Name = SegmentationTypeEnum::get($s->SegmentationType); });
    return $segmentations;
  }

  public function findUnique()
  {
    return false;
  }

  public function getClinicianAttribute()
  {
    $clinician = DB::table('AspNetUsers')
      ->join('ItemSet_Patient', 'ItemSet_Patient.AspNetUsersId', '=', 'AspNetUsers.Id')
      ->where('ItemSet_Patient.Id', '=', $this->Patient_Id)->first();

    return $clinician;
  }

  public function getAsStringAttribute()
  {
    return $this->Combination->asString . ' (' . $this->Id . ')';
  }

  public function getAsHtmlAttribute()
  {
    $simulation = "<span class='parameter' title='" . htmlentities($this->asString) . "'>" . $this->Caption . "</span>";
    if ($this->Patient_Id)
    {
      $patient = DB::table('ItemSet_Patient')->whereId($this->Patient_Id)->first();
      $simulation .= " [<span class='parameter' title='" . htmlentities($patient->Description) . "'>" . $patient->Alias . "</span>]";
    }

    return $simulation;
  }

  public static function fromXml($xml)
  {
    $xpath = new DOMXpath($xml);
    $simulation = new static;
    $simulationNode = $xpath->query('//simulationDefinition/simulation')->item(0);
    $simulation->Id = strtoupper($simulationNode->getAttribute('id'));

    $simulationAttributes = [
      'Caption',
      'SegmentationType',
      'Progress',
      'State',
      'Color',
      'Active'
    ];

    foreach ($simulationAttributes as $simulationAttribute)
      $simulation->{$simulationAttribute} = $simulationNode->getAttribute(strtolower($simulationAttribute));

    //if (Simulation::find($simulation->Id))
    //  return [false, "Simulation with this ID already exists"];

    $simulationNeedle = [];
    $combination = Combination::find($xpath->query('//simulationDefinition/combination/@id')->item(0)->value);
    if (!$combination)
      throw new Exception("Cannot find combination (you may be able to work around this manually from the XML)");

    $patient = DB::table('ItemSet_Patient')->where('Id', '=', $xpath->query('//simulationDefinition/simulation/patient/@id')->item(0)->value)->get();
    if (empty($patient))
      throw new Exception("Patient no longer exists");
    $patient = $patient[0];

    $simulation->Combination_Id = $combination->Combination_Id;
    $simulation->Patient_Id = $patient->Id;
    $simulation->Id = null;
    $simulation->save();

    $parameterNodes = $xpath->query('//simulationDefinition/parameters/parameter');
    $parameters = [];
    foreach ($parameterNodes as $parameterNode)
    {
      $parameter = Parameter::whereName($parameterNode->getAttribute("name"))->first();
      $simulation->Parameters()->attach($parameter, ["ValueSet" => $parameterNode->getAttribute("value")]);
    }

    $needleNodes = $xpath->query('//simulationDefinition/numericalModel/needles/needle');
    foreach ($needleNodes as $needleNode)
    {
      $needle = Needle::find($needleNode->getAttribute("id"));
      if (!$needle)
        throw new Exception("Needle not found");

      $simulationNeedle = new SimulationNeedle;
      $simulationNeedle->Needle_Id = $needle->Id;
      $simulationNeedle->Simulation_Id = $simulation->Id;
      $simulationNeedle->save();

      $parameterNodes = $xpath->query('//simulationDefinition/numericalModel/needles/needle/parameters/parameter');
      $parameters = [];
      foreach ($parameterNodes as $parameterNode)
      {
        $parameter = Parameter::whereName($parameterNode->getAttribute("name"))->first();
        switch ($parameter->Name)
        {
        case "NEEDLE_TIP_LOCATION":
          $target = PointSet::fromArray(json_decode($parameterNode->getAttribute("value")));
          $target->save();
          $simulationNeedle->Target_Id = $target->Id;
          break;
        case "NEEDLE_ENTRY_LOCATION":
          $entry = PointSet::fromArray(json_decode($parameterNode->getAttribute("value")));
          $entry->save();
          $simulationNeedle->Entry_Id = $entry->Id;
          break;
        default:
          $simulationNeedle->Parameters()->attach($parameter, ["ValueSet" => $parameterNode->getAttribute("value")]);
        }
      }

      $simulationNeedle->save();
    }
    //foreach ($parameters as $sP)
    //{
    //  $sP->Simulation_Id = $simulation->Id;
    //  $sP->save();
    //}
    $simulation->save();

    return $simulation;
  }

  /* Presenter logic hack */
  /* $suppliedRegions should be a 2D array organised by name each containing one or more filenames */
  public function xml($root, $backup=false) {
    //list($parameters, $needleParameters) = $this->compileParameters($userSuppliedParameters, $needles, $needleUserParameters, $incompatibilities);
    $parameters = $this->Parameters;

    if ($backup)
    {
      $root->setAttribute("backup", "true");
      $simulationNode = new DOMElement("simulation");
      $root->appendChild($simulationNode);
      $simulationNode->setAttribute("id", $this->Id);

      $simulationAttributes = [
        'Caption',
        'SegmentationType',
        'Progress',
        'State',
        'Color',
        'Active'
      ];

      foreach ($simulationAttributes as $simulationAttribute)
        $simulationNode->setAttribute(strtolower($simulationAttribute), $this->$simulationAttribute);

      $patientNode = new DOMElement("patient");
      $simulationNode->appendChild($patientNode);
      $patientNode->setAttribute("id", $this->Patient_Id);
    }

    if ($parameters !== null) {
      $parametersNode = new DOMElement("parameters");
      $root->appendChild($parametersNode);
      foreach ($parameters as $parameter) {
        $parameter->Value = $parameter->pivot->ValueSet;
        $parameter->xml($parametersNode, $backup);
      }
    }

    $algorithms = $this->Combination->Protocol->Algorithms;
    $algorithmsNode = new DOMElement("algorithms");
    $root->appendChild($algorithmsNode);
    foreach ($algorithms as $algorithm) {
      $algorithm->xml($algorithmsNode, $backup);
    }

    $numericalModelNode = new DOMElement("numericalModel");
    $root->appendChild($numericalModelNode);
    $this->Combination->NumericalModel->xml($numericalModelNode, $this->Segmentations, $incompatibilities, $this->SimulationNeedles, [], $backup);

    if ($backup)
    {
      $combinationNode = new DOMElement("combination");
      $root->appendChild($combinationNode);
      $modality = $this->Combination->PowerGenerator->Modality;
      $combinationNode->setAttribute("id", $this->Combination->Combination_Id);
      $combinationNode->setAttribute("modality", $modality->Name);

      $numericalModelNode = new DOMElement("numericalModel");
      $combinationNode->appendChild($numericalModelNode);
      $numericalModelNode->setAttribute("id", $this->Combination->NumericalModel->Id);
      $numericalModelNode->setAttribute("name", $this->Combination->NumericalModel->Name);

      $powerGeneratorNode = new DOMElement("powerGenerator");
      $combinationNode->appendChild($powerGeneratorNode);
      $powerGeneratorNode->setAttribute("id", $this->Combination->PowerGenerator->Id);
      $powerGeneratorNode->setAttribute("name", $this->Combination->PowerGenerator->Name);

      $contextNode = new DOMElement("context");
      $combinationNode->appendChild($contextNode);
      $contextNode->setAttribute("id", $this->Combination->Context->Id);
      $contextNode->setAttribute("name", $this->Combination->Context->Name);

      $protocolNode = new DOMElement("protocol");
      $combinationNode->appendChild($protocolNode);
      $protocolNode->setAttribute("id", $this->Combination->Protocol->Id);
      $protocolNode->setAttribute("name", $this->Combination->Protocol->Name);
    }
  }

}
