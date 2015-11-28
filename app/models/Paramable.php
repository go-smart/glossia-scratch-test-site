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
  public function placeholder($name, $context = null, $type = null, $overwrite = true, $widget = null, $units = null, $overwrite_parameter = false) {
    $data = ['Name' => $name, 'Value' => null];

    if ($type)
      $data['Type'] = $type;

    if ($widget)
      $data['Widget'] = $widget;

    if ($units)
      $data['Units'] = $units;

    $this->attribute($data, $context, $overwrite, $overwrite_parameter);
  }

  public function attribute($data, $context = null, $overwrite = true, $overwrite_parameter = false) {
    $name = $data['Name'];

    if (!$this->Id)
      throw new InvalidArgumentException("Cowardly refusing to create an empty (universal) attribute");

    /* Convention over configuration */
    $id_name = train_case(get_class($this)) . '_Id';
    if ($id_name == 'Context_Id')
      $id_name = Context::$idField;

    $parameterClause = ParameterAttribution::where($id_name, '=', $this->Id);
    $activeFields = [snake_case(get_class($this))];

    if ($context !== null)
    {
      $activeFields[] = 'context';
      $parameterClause = $parameterClause->where(Context::$idContext, '=', $context->Id);
    }

    $parameterAttributions = $parameterClause
        ->whereHas('parameter', function ($q) use ($name) {
          $q->where('Name', '=', $name);
        })
        ->get()
        ->filter(function ($pa) use ($activeFields) {
          return empty(array_diff(array_values($pa->activeFields()), array_values($activeFields)));
        });
    $parameterAttribution = $parameterAttributions->first();
    $parameterAttributions->each(function ($pa) { $pa->delete(); });


    if (array_key_exists('Value', $data))
    {
      $value = $data['Value'];
      unset($data['Value']);
    }
    else
    {
      $value = null;
    }

    if (array_key_exists('Widget', $data))
    {
      $widget = $data['Widget'];
      unset($data['Widget']);
    }
    else
    {
      $widget = null;
    }

    if (array_key_exists('Units', $data))
    {
      $units = $data['Units'];
      unset($data['Units']);
    }
    else
    {
      $units = null;
    }

    if (array_key_exists('Editable', $data))
    {
      $editable = $data['Editable'];
      unset($data['Editable']);
    }
    else
    {
      $editable = 2;
    }

    if ($parameterAttribution)
    {
      $parameter = $parameterAttribution->parameter;
      if ($overwrite)
      {
        $parameterAttribution->Value = $value;
        $parameterAttribution->Editable = $editable;
        $parameterAttribution->Widget = $widget;
        $parameterAttribution->Units = $units;
        $parameterAttribution->save();
      }
      else
      {
        $value = $parameterAttribution->Value;
        $editable = $parameterAttribution->Editable;
        $widget = $parameterAttribution->Widget;
        $units = $parameterAttribution->Units;
      }
    }
    else
    {
      $parameter = Parameter::whereName($data['Name'])->first();

      if (empty($parameter))
        $parameter = Parameter::create($data);
      else
        $parameter->update(array_filter($data));

      /* Convention over configuration */
      $id_name = train_case(get_class($this)) . '_Id';
      if ($id_name == 'Context_Id')
        $id_name = Context::$idField;

      $type = array_key_exists('Type', $data) ? $data['Type'] : $parameter->Type;
      $widget = isset($widget) ? $widget : $parameter->Widget;
      $units = isset($units) ? $units : $parameter->Units;

      $attribution = [
        $id_name => $this->Id,
        'Parameter_Id' => $parameter->Id,
        'Value' => $value,
        'Format' => $type,
        'Editable' => $editable,
        'Widget' => $widget,
        'Units' => $units
      ];

      if ($context !== null)
        $attribution[Context::$idContext] = $context->Id;

      $parameterAttribution = ParameterAttribution::create($attribution);

    }

    if (($parameter->Widget === null && $parameter->Units === null) || $overwrite_parameter)
    {
      $parameter->Widget = $widget;
      $parameter->Units = $units;
    }

    $parameter->Value = $value;
    $parameter->Editable = $editable;
    $parameter->Widget = $widget;
    $parameter->Units = $units;
    $parameterAttribution->save();

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
