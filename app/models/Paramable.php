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
  public function placeholder($name, $context = null) {
    /* Convention over configuration */
    $id_name = snake_case(get_class($this)) . '_id';

    $parameterClause = ParameterAttribution::where($id_name, '=', $this->id);

    if ($context !== null)
      $parameterClause = $parameterClause->where('context_id', '=', $context->id);

    $placeholderExists = ($parameterClause
      ->whereHas('parameter', function ($q) use ($name) {
        $q->where('name', '=', $name);
      })->count() > 0);

    if (!$placeholderExists) {
      $parameter = Parameter::whereName($name)->first();

      if (empty($parameter))
        $parameter = Parameter::create(['name' => $name]);

      $attribution = [$id_name => $this->id, 'parameter_id' => $parameter->id, 'value' => null];

      $parameterAttribution = ParameterAttribution::create($attribution);
    }
  }

  public function attribute($data, $context = null) {
    if (array_key_exists('value', $data))
    {
      $value = $data['value'];
      unset($data['value']);
    }
    else
    {
      $value = null;
    }

    $parameter = Parameter::whereName($data['name'])->first();

    if (empty($parameter))
      $parameter = Parameter::create($data);
    else
      $parameter->update(array_filter($data));

    /* Convention over configuration */
    $id_name = snake_case(get_class($this)) . '_id';
    $attribution = [$id_name => $this->id, 'parameter_id' => $parameter->id, 'value' => $value];

    if ($context !== null)
      $attribution['context_id'] = $context->id;

    $parameterAttribution = ParameterAttribution::create($attribution);

    $parameter->value = $value;
    return $parameter;
  }

  public function parameterAttributions() {
    return $this->hasMany('ParameterAttribution');
  }

  /* If this gets called more than once in a request, it makes more sense to cache */
  public function parameterAttributionsByName() {
    $parameterAttributions = [];

    foreach ($this->parameterAttributions as $a) {
      $name = $a->parameter->name;
      if (!isset($parameterAttributions[$name]))
        $parameterAttributions[$name] = [];
      $parameterAttributions[$name][] = $a;
    };

    return $parameterAttributions;
  }

  public function parameters() {
    $id_name = snake_case(get_class($this)) . '_id';
    return Parameter::join('parameter_attributions', 'parameters.id', '=', 'parameter_attributions.parameter_id')
      ->where('parameter_attributions.' . $id_name, '=', $this->id);
  }

  public function parameters_as_html() {
    $parameters_as_html = $this->parameters()->get()->map(function ($parameter) {
      return $parameter->as_html();
    });
    return implode(', ', $parameters_as_html->all());
  }
}
