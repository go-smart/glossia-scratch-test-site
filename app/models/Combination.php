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
  protected $attributingFields = ['protocol', 'power_generator', 'numerical_model', 'context'];

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
	protected $table = 'combinations';

  protected $cachedParameterTable = null;

  public function simulations() {
    return $this->hasMany('Simulation');
  }

  public function powerGenerator() {
    return $this->belongsTo('PowerGenerator');
  }

  public function needles() {
    return $this->belongsToMany('Needle');
  }

  public function protocol() {
    return $this->belongsTo('Protocol');
  }

  public function context() {
    return $this->belongsTo('Context');
  }

  public function numericalModel() {
    return $this->belongsTo('NumericalModel');
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
    $attributions = ParameterAttribution::join("parameters", "parameters.id", "=", "parameter_attributions.parameter_id")
      ->addSelect("parameter_attributions.*", "parameters.name AS parameterName", "parameter_attributions.value AS parameterValue");

    $automaticFields = array_diff($this->attributingFields, ['protocol']);
    foreach ($automaticFields as $field) {
      $property = camel_case($field);
      $attributionsWithoutNeedle = $attributions->where(function ($q) use ($field, $property) {
        $q->whereNull("${field}_id")
          ->orWhere("${field}_id", "=", $this->$property->id);
      });
    }

    $resultList = new Collection;
    if (in_array('protocol', $this->attributingFields))
    {
      $algorithms = $this->protocol->algorithms;
      $attributionsWithoutNeedle = $attributionsWithoutNeedle->where(function ($q) use ($algorithms) {
        $q->whereNull("algorithm_id");
        foreach ($algorithms as $algorithm)
          $q->orWhere("algorithm_id", "=", $algorithm->id);
      });
      $resultList = $resultList->merge($algorithms->lists('result'));
    }

    $requirements = with(clone $attributions)->whereNull("parameter_attributions.value");
    $supplies = with(clone $attributions)->whereNotNull("parameter_attributions.value");

    $requirements = $requirements->groupBy("parameterName")->lists("parameterName");
    $supplyList = $supplies->groupBy("parameterName")->lists("parameterName");

    $undefinedList = array_diff($requirements, $supplyList);
    $undefinedList = array_diff($undefinedList, $resultList->lists("name"));

    foreach ($undefinedList as $missingParameterName)
      $missing[] = $missingParameterName;

    $supplies = $attributionsWithoutNeedle->whereNull("needle_id")->whereNotNull("parameter_attributions.value");

    $attributions = [];
    foreach ($supplies->get() as $attribution) {
      $name = $attribution->parameter->name;
      if (!isset($attributions[$name]))
        $attributions[$name] = [];
      $attributions[$name][] = $attribution;
    }

    $parameters = [];
    foreach ($attributions as $name => $available)
      $parameters[$name] = $this->chooseAttribution($available);

    return $parameters;
  }

  public function compileParameters($userSupplied, $needles, $needleUserParameters, &$incompatibilities = array()) {
    $needlesCollection = new Collection($needles);
    $disallowedNeedles = $needlesCollection->diff($this->needles);
    foreach ($disallowedNeedles as $needle)
      $incompatibilities[] = "Needle $needle->name is not marked for use in this combination";

    $allowedNeedles = $needlesCollection->intersect($this->needles);

    $attributions = ParameterAttribution::join("parameters", "parameters.id", "=", "parameter_attributions.parameter_id")
      ->addSelect("parameter_attributions.*", "parameters.name AS parameterName", "parameter_attributions.value AS parameterValue");

    $automaticFields = array_diff($this->attributingFields, ['protocol']);
    foreach ($automaticFields as $field) {
      $property = camel_case($field);
      $attributionsWithoutNeedle = $attributions->where(function ($q) use ($field, $property) {
        $q->whereNull("${field}_id")
          ->orWhere("${field}_id", "=", $this->$property->id);
      });
    }

    $resultList = new Collection;
    if (in_array('protocol', $this->attributingFields))
      $resultList = $resultList->merge($this->protocol->algorithms->lists('result'));
      foreach ($this->protocol->algorithms as $algorithm) {
        $algorithms = $this->protocol->algorithms;
        $attributionsWithoutNeedle = $attributionsWithoutNeedle->where(function ($q) use ($algorithms) {
          $q->whereNull("algorithm_id");
          foreach ($algorithms as $algorithm)
            $q->orWhere("algorithm_id", "=", $algorithm->id);
        });
      }

    $needleParametersByNeedle = [];
    if (count($allowedNeedles)) {
      foreach ($needlesCollection as $needleIx => $needle) {
        if (!in_array($needle, $allowedNeedles->all()))
          continue;

        $attributions = with(clone $attributionsWithoutNeedle)
          ->where(function ($q) use ($needle) {
            $q = $q->whereNull("needle_id");
            $q = $q->orWhere("needle_id", "=", $needle->id);
          });

        $requirements = with(clone $attributions)->whereNull("parameter_attributions.value");
        $supplies = with(clone $attributions)->whereNotNull("parameter_attributions.value");

        $needleParameters = [];
        foreach ($supplies->get() as $a) {
          $name = $a->parameter->name;
          if (!isset($needleParameters[$name]))
            $needleParameters[$name] = [];
          $needleParameters[$name][] = $a;
        }

        array_walk($needleParameters, function (&$v, $name) {
          /* Remove any redundant parameters - only needle parameters and parameters
           * overriding a needle-specific parameter count */
          if (!count(array_filter($v, function ($n) { return $n->needle_id !== null; }))) {
            $v = false;
          }
          else {
            $v = $this->chooseAttribution($v);
          }
        });

        $needleParameters = array_filter($needleParameters);

        $needleUser = isset($needleUserParameters[$needleIx]) ? $needleUserParameters[$needleIx] : [];
        foreach ($needleUser as $name => $value) {
          $v = new Parameter;
          $v->name = $name;
          $v->value = $needleUser[$name];
          $needleParameters[$name] = $v;
        }

        $needleParametersByNeedle[$needleIx] = $needleParameters;

        $requirements = $requirements->groupBy("parameterName")->lists("parameterName");
        $supplyList = $supplies->groupBy("parameterName")->lists("parameterName");

        $needleUserSupplied = [];
        if (isset($needleUserParameters[$needleIx]))
          $needleUserSupplied = array_keys($needleUserParameters[$needleIx]);

        $undefinedList = array_diff($requirements, $supplyList, $needleUserSupplied, array_keys($userSupplied));
        $undefinedList = array_diff($undefinedList, $resultList->lists("name"));

        foreach ($undefinedList as $missingParameterName)
          $incompatibilities[] = "Parameter $missingParameterName is missing";
      }
    }
    else {
      $requirements = with(clone $attributions)->whereNull("parameter_attributions.value");
      $supplies = with(clone $attributions)->whereNotNull("parameter_attributions.value");

      $requirements = $requirements->groupBy("parameterName")->lists("parameterName");
      $supplyList = $supplies->groupBy("parameterName")->lists("parameterName");

      $undefinedList = array_diff($requirements, $supplyList, array_keys($userSupplied));

      foreach ($undefinedList as $missingParameterName)
        $incompatibilities[] = "Parameter $missingParameterName is missing";
    }

    $supplies = $attributionsWithoutNeedle->whereNull("needle_id")->whereNotNull("parameter_attributions.value");

    $attributions = [];
    foreach ($supplies->get() as $attribution) {
      $name = $attribution->parameter->name;
      if (!isset($attributions[$name]))
        $attributions[$name] = [];
      $attributions[$name][] = $attribution;
    }

    $parameters = [];
    foreach ($attributions as $name => $available)
      $parameters[$name] = $this->chooseAttribution($available);

    foreach ($userSupplied as $name => $value) {
      $v = new Parameter;
      $v->name = $name;
      $v->value = $value;
      $parameters[$name] = $v;
    }

    return [$parameters, $needleParametersByNeedle];
  }

  public function chooseAttribution($attributions)
  {
    if (count($attributions) == 0)
      return null;

    if (count($attributions) == 1)
    {
      $parameter = $attributions[0]->parameter;
      $parameter->value = $attributions[0]->value;
      return $parameter;
    }

    $winningAttribution = $attributions[0];
    $winningPriority = $winningAttribution->priority();
    $winningSpecificity = $winningAttribution->specificity();

    $swap = true;
    foreach ($attributions as $attribution) {
      /* Primary criterion */
      $priority = $winningAttribution->priority();
      if ($swap === null) {
        if ($priority < $winningPriority)
          $swap = true;
        else if ($priority > $winningPriority)
          $swap = false;
      }

      /* Secondary criterion */
      $specificity = $attribution->specificity();
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

    $parameter = $winningAttribution->parameter;
    $parameter->value = $winningAttribution->value;
    return $parameter;
  }

  /* Presenter logic hack */
  /* $suppliedRegions should be a 2D array organised by name each containing one or more filenames */
  public function xml($userSuppliedParameters = [], $suppliedRegions = [], &$incompatibilities = [], $needles = [], $needleUserParameters = []) {
    list($parameters, $needleParameters) = $this->compileParameters($userSuppliedParameters, $needles, $needleUserParameters, $incompatibilities);

    $xml = new DOMDocument('1.0');
    $root = $xml->createElement('simulationDefinition');
    $xml->appendChild($root);

    if ($parameters !== null) {
      $parametersNode = $xml->createElement("parameters");
      foreach ($parameters as $parameter) {
        $parameter->xml($parametersNode);
      }
      $root->appendChild($parametersNode);
    }

    $algorithms = $this->protocol->algorithms;
    $algorithmsNode = $xml->createElement("algorithms");
    foreach ($algorithms as $algorithm) {
      $algorithm->xml($algorithmsNode);
    }
    $root->appendChild($algorithmsNode);

    $numericalModelNode = $xml->createElement("numericalModel");
    $this->numericalModel->xml($numericalModelNode, $suppliedRegions, $incompatibilities, $needles, $needleParameters);
    $root->appendChild($numericalModelNode);

    return $xml;
  }
}
