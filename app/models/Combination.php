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


class Combination extends UuidModel {

  /**
   * Fields that can attribute, in order of precedence
   *
   * @var array(string)
   */
  protected $attributingFields = ['Protocol', 'Power_Generator', 'Numerical_Model', 'Context'];

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
	protected $table = 'Combination';

  protected $primaryKey = 'Combination_Id';

  protected $cachedParameterTable = null;

  public function getCombinationIdAttribute($id) {
    if ($id)
      return substr($id, 0, 36);
    return $id;
  }

  public function getModalityAttribute() {
    if (!$this->PowerGenerator)
      return null;

    return $this->PowerGenerator->Modality;
  }

  public function Simulations() {
    return $this->hasMany('Simulation', 'Combination_Id');
  }

  public function PowerGenerator() {
    return $this->belongsTo('PowerGenerator', 'Power_Generator_Id');
  }

  public function Needles() {
    return $this->belongsToMany('Needle', 'Combination_Needle', 'Combination_Id', 'Needle_Id');
  }

  public function Protocol() {
    return $this->belongsTo('Protocol', 'Protocol_Id');
  }

  public function Context() {
    return $this->belongsTo('Context', Context::$idField);
  }

  public function NumericalModel() {
    return $this->belongsTo('NumericalModel', 'Numerical_Model_Id');
  }

  public function getContextAttribute() {
    return Context::find($this->{Context::$idField});
  }

  public function getAsStringAttribute() {
    return $this->NumericalModel->Name .
      ' - ' . $this->PowerGenerator->Name .
      ' - ' . $this->Protocol->Name .
      ' - ' . $this->Context->Name;
  }

  public function getProtocolIdAttribute($id) {
      return substr($id, 0, 36);
  }

  public function getPowerGeneratorIdAttribute($id) {
      return substr($id, 0, 36);
  }

  public function getNumericalModelIdAttribute($id) {
      return substr($id, 0, 36);
  }

  /**
   * Find the value for a given parameter name based on the parameter
   * aggregation logic of this Combination. Caches results, but not
   * currently implemented to account for dirty parameters or invalidation
   * by changes to parameters_attributions or parameters tables.
   *
   * @param string Name of parameter
   * @return mixed Preferred parameter value if found, else null
   */
  public function retrieveParameter($parameterName){
    if ($this->cachedParameterTable === null) {
      $missing = [];
      $this->cachedParameterTable = $this->retrieveCoreParameters($missing);
    }

    if (array_key_exists($parameterName, $this->cachedParameterTable))
      return $this->cachedParameterTable[$parameterName];
    else
      return null;
  }

  /**
   * This function gets the core parameters - i.e. all those associated with
   * the Combination, not counting user-supplied parameters and needles (as
   * the number and type of needles is simulation-dependent)
   */
  public function retrieveCoreParameters(&$missing) {
    $attributions = ParameterAttribution::join("Parameter", "Parameter.Id", "=", "Parameter_Attribution.Parameter_Id")
      ->addSelect("Parameter_Attribution.*", "Parameter.Name AS parameterName", "Parameter_Attribution.Value AS parameterValue");

    $automaticFields = array_diff($this->attributingFields, ['Protocol']);
    foreach ($automaticFields as $field) {
      $property = train_case($field);
      $class = get_class($this->$property);
      $attributionsWithoutNeedle = $attributions->where(function ($q) use ($field, $property, $class) {
        $q->whereNull($class::$idField)
          ->orWhere($class::$idField, "=", $this->$property->Id);
      });
    }

    $resultList = new Collection;
    if (in_array('Protocol', $this->attributingFields))
    {
      $algorithms = $this->Protocol->Algorithms;
      $attributionsWithoutNeedle = $attributionsWithoutNeedle->where(function ($q) use ($algorithms) {
        $q->whereNull("Algorithm_Id");
        foreach ($algorithms as $algorithm)
          $q->orWhere("Algorithm_Id", "=", $algorithm->Id);
      });
      $resultList = $resultList->merge($algorithms->lists('Result'));
    }

    $requirements = with(clone $attributions)->whereNull("Parameter_Attribution.Value");
    $supplies = with(clone $attributions)->whereNotNull("Parameter_Attribution.Value");

    $requirements = $requirements->lists("parameterName");
    $supplyList = $supplies->lists("parameterName");

    $undefinedList = array_diff($requirements, $supplyList);
    $undefinedList = array_diff($undefinedList, $resultList->lists("Name"));

    foreach ($undefinedList as $missingParameterName)
      $missing[] = $missingParameterName;

    $supplies = $attributionsWithoutNeedle->whereNull("Needle_Id")->whereNotNull("Parameter_Attribution.Value");

    $attributions = [];
    foreach ($supplies->get() as $attribution) {
      $name = $attribution->Parameter->Name;
      if (!isset($attributions[$name]))
        $attributions[$name] = [];
      $attributions[$name][] = $attribution;
    }

    $parameters = [];
    foreach ($attributions as $name => $available)
      $parameters[$name] = $this->chooseAttribution($available);

    return $parameters;
  }

  public function compileParameters(
        $userSupplied,
        $needles,
        $needleUserParameters,
        &$incompatibilities = array(),
        &$userRequiredParameters
      ) {
    if (is_array($needles))
      $needlesCollection = new Collection($needles);
    else
      $needlesCollection = $needles;

    $disallowedNeedles = $needlesCollection->diff($this->Needles);
    foreach ($disallowedNeedles as $needle)
      $incompatibilities[] = "Needle $needle->Name is not marked for use in this combination";

    $allowedNeedles = $needlesCollection->intersect($this->Needles);

    $attributionsWithoutNeedle = ParameterAttribution::join("Parameter", "Parameter.Id", "=", "Parameter_Attribution.Parameter_Id")
      ->addSelect("Parameter_Attribution.*", "Parameter.Name AS parameterName", "Parameter_Attribution.Value AS parameterValue");

    $automaticFields = array_diff($this->attributingFields, ['Protocol']);
    foreach ($automaticFields as $field) {
      $property = train_case($field);
      $class = get_class($this->$property);
      $attributionsWithoutNeedle = $attributionsWithoutNeedle->where(function ($q) use ($field, $property, $class) {
        $q->whereNull($class::$idField)
          ->orWhere($class::$idField, "=", $this->$property->Id);
      });
    }

    $resultList = new Collection;
    if (in_array('Protocol', $this->attributingFields))
      $resultList = $resultList->merge($this->Protocol->Algorithms->lists('Result'));
      foreach ($this->Protocol->Algorithms as $algorithm) {
        $algorithms = $this->Protocol->Algorithms;
        $attributionsWithoutNeedle = $attributionsWithoutNeedle->where(function ($q) use ($algorithms) {
          $q->whereNull("Algorithm_Id");
          foreach ($algorithms as $algorithm)
            $q->orWhere("Algorithm_Id", "=", $algorithm->Id);
        });
      }

    $needleParametersByNeedle = [];
    if (count($allowedNeedles)) {
      foreach ($needlesCollection as $needleIx => $needle) {
        if (!in_array($needle, $allowedNeedles->all()))
          continue;

        $attributions = with(clone $attributionsWithoutNeedle)
          ->where(function ($q) use ($needle) {
            $q = $q->whereNull("Needle_Id");
            $q = $q->orWhere("Needle_Id", "=", $needle->Id);
          });

        $requirements = with(clone $attributions)->whereNull("Parameter_Attribution.Value");
        $supplies = with(clone $attributions)->whereNotNull("Parameter_Attribution.Value");

        $needleParameters = [];
        foreach ($supplies->get() as $a) {
          $name = $a->Parameter->Name;
          if (!isset($needleParameters[$name]))
            $needleParameters[$name] = [];
          $needleParameters[$name][] = $a;
        }

        array_walk($needleParameters, function (&$v, $name) {
          /* Remove any redundant parameters - only needle parameters and parameters
           * overriding a needle-specific parameter count */
          if (!count(array_filter($v, function ($n) { return $n->Needle_Id !== null; }))) {
            $v = false;
          }
          else {
            $v = $this->chooseAttribution($v);
          }
        });

        $needleParameters = array_filter($needleParameters);

        $needleUser = isset($needleUserParameters[$needleIx]) ? $needleUserParameters[$needleIx] : [];

        foreach ($needleUser as $needleUserParameter) {
          $needleParameters[$needleUserParameter->Name] = $needleUserParameter;
        }

        $needleParametersByNeedle[$needleIx] = $needleParameters;

        $requirementsList = $requirements->lists("parameterName");
        $supplyList = $supplies->lists("parameterName");

        $needleUserSupplied = [];
        if (isset($needleUserParameters[$needleIx]))
          $needleUserSupplied = $needleUserParameters[$needleIx]->lists('Name');

        $undefinedList = array_diff($requirementsList, $supplyList, $needleUserSupplied, $userSupplied->lists('Name'));
        $undefinedList = array_diff($undefinedList, $resultList->lists("Name"));

        if (!empty($undefinedList))
        {
          $requirementsMap = array_combine($requirements->lists("parameterName"), $requirements->get()->all());
          foreach ($undefinedList as $missingParameterName)
          {
            $editable = ($requirementsMap[$missingParameterName]->Editable >= 2);

            if ($editable)
              $userRequiredParameters[] = $requirementsMap[$missingParameterName]->Parameter;
            else
              $incompatibilities[] = "Parameter $missingParameterName is missing";
          }
        }
      }
    }
    else {
      $requirements = with(clone $attributionsWithoutNeedle)->whereNull("Parameter_Attribution.Value");
      $supplies = with(clone $attributionsWithoutNeedle)->whereNotNull("Parameter_Attribution.Value");

      $requirementsList = $requirements->lists("parameterName");
      $supplyList = $supplies->lists("parameterName");

      $undefinedList = array_diff($requirementsList, $supplyList, $userSupplied->lists('Name'));
      $undefinedList = array_diff($undefinedList, $resultList->lists("Name"));

      if (!empty($undefinedList))
      {
        $requirementsMap = array_combine($requirements->lists("parameterName"), $requirements->get()->all());
        foreach ($undefinedList as $missingParameterName)
        {
          $editable = ($requirementsMap[$missingParameterName]->Editable >= 2);

          if ($editable)
            $userRequiredParameters[] = $requirementsMap[$missingParameterName]->Parameter;
          else
            $incompatibilities[] = "Parameter $missingParameterName is missing";
        }
      }
    }

    $supplies = $attributionsWithoutNeedle->whereNull("Needle_Id")->whereNotNull("Parameter_Attribution.Value");

    $attributions = [];
    foreach ($supplies->get() as $attribution) {
      $name = $attribution->Parameter->Name;
      if (!isset($attributions[$name]))
        $attributions[$name] = [];
      $attributions[$name][] = $attribution;
    }

    $parameters = [];
    foreach ($attributions as $name => $available)
    {
      $parameters[$name] = $this->chooseAttribution($available);
    }

    foreach ($userSupplied as $userParameter) {
      if ($userParameter->Value === null)
        $userParameter->Value = $userParameter->pivot->ValueSet;
      $parameters[$userParameter->Name] = $userParameter;
    }

    foreach ($parameters as $parameter)
      if ($parameter->Editable == 3) /* Always editable */
        $userRequiredParameters[] = $parameter;

    foreach ($needleParametersByNeedle as $needle => $needleParameters)
      foreach ($needleParameters as $parameter)
        if ($parameter->Editable == 3) /* Always editable */
          $userRequiredParameters[] = $parameter;

    return [$parameters, $needleParametersByNeedle];
  }

  public function chooseAttribution($attributions)
  {
    if (count($attributions) == 0)
      return null;

    if (count($attributions) == 1)
    {
      $parameter = $attributions[0]->Parameter;
      $parameter->Value = $attributions[0]->Value;
      $parameter->Editable = $attributions[0]->Editable;
      $parameter->Format = $attributions[0]->Format;
      return $parameter;
    }

    $winningAttribution = $attributions[0];
    $winningPriority = $winningAttribution->Priority();
    $winningSpecificity = $winningAttribution->Specificity();

    $swap = true;
    foreach ($attributions as $attribution) {
      /* Primary criterion */
      $priority = $winningAttribution->Priority();
      if ($swap === null) {
        if ($priority < $winningPriority)
          $swap = true;
        else if ($priority > $winningPriority)
          $swap = false;
      }

      /* Secondary criterion */
      $specificity = $attribution->Specificity();
      if ($swap === null) {
        if ($winningSpecificity < $specificity)
          $swap = true;
        else
          $swap = false;
      }

      if ($swap) {
        $winningAttribution = $attribution;
        $winningPriority = $priority;
        $winningSpecificity = $specificity;
      }

      $swap = null;
    }

    $parameter = $winningAttribution->Parameter;
    $parameter->Editable = $winningAttribution->Editable;
    $parameter->Value = $winningAttribution->Value;
    $parameter->Format = $winningAttribution->Format;
    return $parameter;
  }

  /*
  public function xml($root, $userSuppliedParameters = [], $suppliedRegions = [], &$incompatibilities = [], $needles = [], $needleUserParameters = []) {
    list($parameters, $needleParameters) = $this->compileParameters($userSuppliedParameters, $needles, $needleUserParameters, $incompatibilities);

    if ($parameters !== null) {
      $parametersNode = new DOMElement("parameters");
      $root->appendChild($parametersNode);
      foreach ($parameters as $parameter) {
        $parameter->xml($parametersNode);
      }
    }

    $algorithms = $this->Protocol->Algorithms;
    $algorithmsNode = new DOMElement("algorithms");
    $root->appendChild($algorithmsNode);
    foreach ($algorithms as $algorithm) {
      $algorithm->xml($algorithmsNode);
    }

    $numericalModelNode = new DOMElement("numericalModel");
    $root->appendChild($numericalModelNode);
    $this->NumericalModel->xml($numericalModelNode, $suppliedRegions, $incompatibilities, $needles, $needleParameters);
  }
   */

  public function findUnique()
  {
    return self::whereNumericalModelId($this->Numerical_Model_Id)
      ->whereProtocolId($this->Protocol_Id)
      ->wherePowerGeneratorId($this->Power_Generator_Id)
      ->where("OrganType", "=", $this->{Context::$idField})
      ->first();
  }
}
