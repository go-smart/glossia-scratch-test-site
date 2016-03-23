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
use Illuminate\Database\Eloquent\Builder;

class SimulationExtensionScope implements Illuminate\Database\Eloquent\ScopeInterface {
  public function apply(Builder $builder)
  {
    $builder->with([
      'Combination.NumericalModel',
      'Combination.Protocol',
      'Combination.PowerGenerator',
      'Combination.PowerGenerator.Modality'
    ])
    ->leftJoin('ItemSet as SimulationItem', 'SimulationItem.Id', '=', 'Simulation.Id')
    ->leftJoin('ItemSet as PatientItem', 'PatientItem.Id', '=', 'Simulation.Patient_Id')
    ->leftJoin('ItemSet_Patient', 'ItemSet_Patient.Id', '=', 'Simulation.Patient_Id')
    ->leftJoin('ItemSet_VtkFile as SimulatedLesionSurface', 'SimulatedLesionSurface.Simulation_Id', '=', 'Simulation.Id')
    ->leftJoin('ItemSet_VtuFile as SimulatedLesionVolume', 'SimulatedLesionVolume.Simulation_Id', '=', 'Simulation.Id')
    ->leftJoin('ItemSet_Segmentation', function ($leftJoin) {
      $leftJoin->on('ItemSet_Segmentation.Patient_Id', '=', 'Simulation.Patient_Id');
      $leftJoin->on('ItemSet_Segmentation.State', '=', DB::raw('3'));
      $leftJoin->on('ItemSet_Segmentation.SegmentationType', '=', DB::raw(SegmentationTypeEnum::Lesion));
    })
    ->leftJoin('ItemSet_VtkFile as LesionFile', 'LesionFile.Segmentation_Id', '=', 'ItemSet_Segmentation.Id')
    ->leftJoin('AspNetUsers as Clinician', 'Clinician.Id', '=', 'ItemSet_Patient.AspNetUsersId')
    ->leftJoin('Combination', 'Combination.Combination_Id', '=', 'Simulation.Combination_Id')
    ->leftJoin('Power_Generator', 'Power_Generator.Id', '=', 'Combination.Power_Generator_Id')
    ->select(
      'Simulation.*',
      'SimulationItem.CreationDate as creationDate',
      'LesionFile.Id as SegmentedLesionId',
      'Clinician.Id as ClinicianId',
      'Clinician.UserName as ClinicianUserName',
      'ItemSet_Patient.Alias as PatientAlias',
      'ItemSet_Patient.Description as PatientDescription',
      'SimulatedLesionSurface.Id as SimulatedLesionSurfaceId',
      'SimulatedLesionVolume.Id as SimulatedLesionVolumeId',
      'Power_Generator.Modality_Id'
    );
  }

  public function remove(Builder $builder)
  {
    $builder->select('Simulation.*');
  }
}

trait SimulationExtensionTrait {
  public static function bootSimulationExtensionTrait()
  {
    static::addGlobalScope(new SimulationExtensionScope);
  }
}


class Simulation extends UuidModel {
  use SimulationExtensionTrait;

  protected $cachedParameters = null;

  protected $hidden = ['Combination'];

  public $timestamps = false;

  protected $table = "Simulation";

  protected $cachedAsString = false;

  protected $appends = ['asHtml', 'asString', 'clinician', 'hasSimulatedLesion', 'hasSegmentedLesion', 'modality', 'patient', 'contextName'];

  protected static $updateByDefault = false;

  public function getContextNameAttribute() {
    return $this->Combination->Context->Name;
  }

  public function getModalityAttribute() {
    return $this->Combination->PowerGenerator->Modality;
  }

  public function getReplicaIdsAttribute() {
    return $this->Replicas->lists('Id');
  }

  public function getCombinationIdAttribute($id) {
      return substr($id, 0, 36);
  }

  public function Parent() {
    return $this->belongsTo('Simulation', 'Parent_Id');
  }

  public function Children() {
    return $this->hasMany('Simulation', 'Parent_Id', 'Id');
  }

  public function Original() {
    return $this->belongsTo('Simulation', 'Original_Id');
  }

  public function Replicas() {
    return $this->hasMany('Simulation', 'Original_Id', 'Id');
  }

  public function Combination() {
    return $this->belongsTo('Combination', 'Combination_Id');
  }

  /* This actually hydrates and then stringifies the parameter value again, but if the Parameter
   * object starts to store values as non-strings this is where it should change */
  public function Parameters() {
    return $this->belongsToMany('Parameter', 'Simulation_Parameter', 'SimulationId', 'ParameterId')->withPivot(['ValueSet', 'Editable', 'Format']);
  }

  public function SimulationNeedles() {
    return $this->hasMany('SimulationNeedle', 'Simulation_Id');
  }

  public function isDevelopment() {
    $development = Parameter::whereName('DEVELOPMENT')->first();
    $pa = $this->Parameters()->where("ParameterId", "=", $development->Id)->first();
    return $pa && json_decode($pa->pivot->ValueSet);
  }

  public function getPatientAttribute() {
    $patient = ['Id' => $this->Patient_Id, 'Alias' => $this->PatientAlias, 'Description' => $this->PatientDescription];
    return $patient;
  }

  public function getSimulatedLesionSurfaceAttribute() {
    if (!$this->SimulatedLesionSurfaceId)
      return null;

    $segmentation = DB::select('
      SELECT IS_F.Id AS FileId, IS_F.FileName AS FileName, IS_F.Extension AS Extension
      FROM ItemSet_File IS_F
      WHERE Id=:FileId
    ', ['FileId' => $this->SimulatedLesionSurfaceId])[0];

    $segmentation->SegmentationType = SegmentationTypeEnum::Simulation;

    return $segmentation;
  }

  public function getSimulatedLesionAttribute() {
    if (!$this->SimulatedLesionVolumeId)
      return null;

    $segmentation = DB::select('
      SELECT IS_F.Id AS FileId, IS_F.FileName AS FileName, IS_F.Extension AS Extension
      FROM ItemSet_File IS_F
      WHERE Id=:FileId
    ', ['FileId' => $this->SimulatedLesionVolumeId])[0];

    $segmentation->SegmentationType = SegmentationTypeEnum::Simulation;

    return $segmentation;
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
    if ($this->Parent_Id && $this->Parent->hasSimulatedLesion)
    {
      $segmentations[] = $this->Parent->SimulatedLesion;
    }
    $segmentations->each(function ($s) { $s->Name = SegmentationTypeEnum::get($s->SegmentationType); });
    return $segmentations;
  }

  public function findUnique()
  {
    return false;
  }

  public function getSegmentedLesionAttribute()
  {
    return null;
    /*
    return $this->newQuery()
      ->where('Simulation.Id', '=', $this->Id)
      ->join('ItemSet_Segmentation as ISS', 'ISS.Patient_Id', '=', 'Simulation.Patient_Id')
      ->join('ItemSet_VtkFile as ISV', 'ISV.Segmentation_Id', '=', 'ISS.Id')
      ->join('ItemSet_File as ISF', 'ISF.Id', '=', 'ISV.Id')
      ->select('ISF.*')
      ->where('ISS.State', '=', '3')
      ->where('ISS.SegmentationType', '=', SegmentationTypeEnum::Lesion)
      ->first();
     */
  }

  public function getHasSimulatedLesionAttribute()
  {
    return $this->SimulatedLesionVolumeId !== null;
  }

  public function getHasSegmentedLesionAttribute()
  {
    return $this->SegmentedLesionId !== null;
  }

  public function getClinicianAttribute()
  {
    return ['Id' => $this->ClinicianId, 'UserName' => $this->ClinicianUserName];
    /*
    $clinician = DB::table('AspNetUsers')
      ->select('AspNetUsers.*')
      ->join('ItemSet_Patient', 'ItemSet_Patient.AspNetUsersId', '=', 'AspNetUsers.Id')
      ->where('ItemSet_Patient.Id', '=', $this->Patient_Id)->first();

    return $clinician;
     */
  }

  public function getAsStringAttribute()
  {
    if (!$this->cachedAsString)
    {
      $this->cachedAsString = $this->Combination->asString . ' (' . $this->Id . ')';
    }
    return $this->cachedAsString;
  }

  public function getAsHtmlAttribute()
  {
    $simulation = "<span class='parameter";
    if ($this->hasSimulatedLesion)
      $simulation .= " simulation-simulated";
    $simulation .= "' title='" . htmlentities($this->asString) . "'>" . $this->Caption . "</span>";
    /*
    if ($this->Patient_Id)
    {
      if (!$this->cachedPatient)
        $this->cachedPatient = DB::table('ItemSet_Patient')->whereId($this->Patient_Id)->remember(1)->first();
      $patient = $this->cachedPatient;
      $simulation .= "</br><span class='parameter' title='" . htmlentities($this->Caption) . "'>" . htmlentities($patient->Description) . "</span> [<span class='parameter'>" . $patient->Alias . "</span>]";
    }
     */

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
        $parameter->Format = $parameter->pivot->Format;
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

  public function buildXml($transferrerBase)
  {
    $incompatibilities = [];

    $userParameters = $this->Parameters;
    //$regions = $this->Regions;
    $regions = $this->Segmentations;
    $combination = $this->Combination;
    $needles = [];
    $needleParameters = [];

    foreach ($this->SimulationNeedles as $sn)
    {
      $t = (string)$sn->Id;
      $needles[$t] = $sn->Needle;
      $needleParameters[$t] = new Collection;

      /* Check in MySQL
      var_dump($sn->Id === '236548FB-F08A-4420-A922-E1806C61A19B');
      var_dump(mb_detect_encoding($sn->Id));
      //dd(gettype($sn->Id));
      $t = (string)($sn->Id);
      var_dump(mb_detect_encoding($t));
      var_dump($t);
      var_dump($sn->Id);
      //var_dump($t[0]);
      //$t[0] = 'E';
      //var_dump($t);
      var_dump($sn->Id);
      $needleParameters[$t] = $needleParameters[$sn->Id];
      dd($needleParameters);
      */

      foreach ($sn->Parameters as $snp)
      {
        $needleParameters[$t][$snp->Name] = $snp;
        $needleParameters[$t][$snp->Name]->Value = $snp->pivot->ValueSet;
      }

      foreach (["NEEDLE_TIP_LOCATION" => $sn->Target, "NEEDLE_ENTRY_LOCATION" => $sn->Entry] as $name => $pointSet)
      {
        $location = new Parameter;
        $location->Name = $name;
        $location->Type = "array(float)";
        $location->Value = json_encode([
          (float)$pointSet->X,
          (float)$pointSet->Y,
          (float)$pointSet->Z
        ]);
        $needleParameters[$t][$name] = $location;
      }
    }

    $xml = new DOMDocument('1.0');
    $root = $xml->createElement('simulationDefinition');
    $xml->appendChild($root);

    $transferrer = $xml->createElement('transferrer');
    $transferrer->setAttribute('class', 'http');
    $transferrerUrl = $xml->createElement('url');
    $transferrerUrl->nodeValue = $transferrerBase;
    $transferrer->appendChild($transferrerUrl);
    $root->appendChild($transferrer);

    $this->xml($root);

    if ($xml !== null) {
      $xml->preserveWhiteSpace = false;
      $xml->formatOutput = true;
    }

    return $xml;
  }

}
