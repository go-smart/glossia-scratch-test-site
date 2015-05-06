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


abstract class Paramable extends UuidModel
{
  /**
   * This checks for any placeholder specific to this object
   * (or more specific) on this parameter. If none exists,
   * it adds it
   */
  public function placeholder($name, $context = null, $type = '') {
    /* Convention over configuration */
    $id_name = train_case(get_class($this)) . '_Id';

    if (!$this->Id)
      throw new InvalidArgumentException("Cowardly refusing to create an empty (universal) placeholder");

    $parameterClause = ParameterAttribution::where($id_name, '=', $this->Id);

    if ($context !== null)
      $parameterClause = $parameterClause->where(Context::$idContext, '=', $context->Id);

    $placeholderExists = ($parameterClause
      ->whereHas('parameter', function ($q) use ($name) {
        $q->where('Name', '=', $name);
      })->count() > 0);

    if (!$placeholderExists) {
      $parameter = Parameter::whereName($name)->first();

      if (empty($parameter))
        $parameter = Parameter::create(['Name' => $name, 'Type' => $type]);
      else if (empty($parameter->Type))
        $parameter->update(['Type' => $type]);

      $attribution = [$id_name => $this->Id, 'Parameter_Id' => $parameter->Id, 'Value' => null, 'Format' => $type];

      $parameterAttribution = ParameterAttribution::create($attribution);
    }
  }

  public function attribute($data, $context = null) {
    if (!$this->Id)
      throw new InvalidArgumentException("Cowardly refusing to create an empty (universal) attribute");

    if (array_key_exists('Value', $data))
    {
      $value = $data['Value'];
      unset($data['Value']);
    }
    else
    {
      $value = null;
    }

    $parameter = Parameter::whereName($data['Name'])->first();

    if (empty($parameter))
      $parameter = Parameter::create($data);
    else
      $parameter->update(array_filter($data));

    /* Convention over configuration */
    $id_name = train_case(get_class($this)) . '_Id';

    $type = array_key_exists('Type', $data) ? $data['Type'] : $parameter->Type;

    $attribution = [$id_name => $this->Id, 'Parameter_Id' => $parameter->Id, 'Value' => $value, 'Format' => $type];

    if ($context !== null)
      $attribution[Context::$idContext] = $context->Id;

    $parameterAttribution = ParameterAttribution::create($attribution);

    $parameter->value = $value;
    return $parameter;
  }

  public function ParameterAttribution() {
    return $this->hasMany('ParameterAttribution', train_case(get_class($this)) . '_Id');
  }

  /* If this gets called more than once in a request, it makes more sense to cache */
  public function ParameterAttributionsByName() {
    $parameterAttributions = [];

    foreach ($this->parameterAttributions as $a) {
      $name = $a->parameter->name;
      if (!isset($parameterAttributions[$name]))
        $parameterAttributions[$name] = [];
      $parameterAttributions[$name][] = $a;
    };

    return $parameterAttributions;
  }

  public function Parameters() {
    $id_name = train_case(get_class($this)) . '_Id';
    return Parameter::join('Parameter_Attribution', 'Parameter.Id', '=', 'Parameter_Attribution.Parameter_Id')
      ->where('Parameter_Attribution.' . $id_name, '=', $this->Id);
  }

  public function parameters_as_html() {
    //$parameters_as_html = $this->parameter()->get()->map(function ($parameter) {
    //  return $parameter->as_html();
    //});
    return $this->ParameterAttribution->implode('asHtml', ', ');
  }
}
