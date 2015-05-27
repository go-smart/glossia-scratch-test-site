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


class Simulation extends UuidModel {

  protected $cachedParameters = null;

  public function combination() {
    return $this->belongsTo('Combination');
  }

  /* This actually hydrates and then stringifies the parameter value again, but if the Parameter
   * object starts to store values as non-strings this is where it should change */
  public function parameters() {
    if (empty($this->parameter_data))
      return [];

    if ($this->cachedParameters === null)
    {
      $parameterDataArray = json_decode($this->parameter_data);
      $this->cachedParameters = [];
      foreach ($parameterDataArray as $name => $parameterData)
        $this->cachedParameters[$name] = json_encode($parameterData);
    }

    return $this->cachedParameters;
  }

  public function regions() {
    $regions = json_decode($this->region_data, $assoc=true);
    if (empty($regions))
      return [];
    return $regions;
  }

  public function needleParameters() {
    $needles = json_decode($this->needle_data, $assoc=true);
    if (empty($needles))
      return [];

    array_walk($needles, function (&$n, $ix) {
      $n = $n['parameters'];
    });

    return $needles;
  }

  public function needles() {
    $needles = json_decode($this->needle_data, $assoc=true);
    if (empty($needles))
      return [];

    array_walk($needles, function (&$n, $ix) {
      $n = Needle::find($n['id']);
    });

    return $needles;
  }
}
