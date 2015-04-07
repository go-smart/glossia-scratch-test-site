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



class Parameter extends UuidModel {

  public $value = null;

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
	protected $table = 'Parameter';

  /**
   * Polymorphic relationship
   *
   * @var string
   */

  public function ParameterAttributions()
  {
    return $this->hasMany('ParameterAttribution', 'Parameter_Id');
  }


  /**
   * Accessors that should really be in a ViewModel/Presenter decorator
   */

  public function as_html()
  {
    $combined = "<span class='parameter' title='Type: $this->Type";

    if ($this->Units)
    {
      $combined .= "; Units: " . $this->Units;
    }

    if ($this->Restriction)
      $combined .= "; Must be from $this->Restriction";

    if ($this->Widget)
      $combined .= "; Can be given by user using $this->Widget";

    if ($this->Description)
      $combined .= "; ($this->Description)";

    $combined .= "'>$this->Name</span>";

    return $combined;
  }

  /* More Presenter logic hack */
  public function xml($parent) {
    $name = $this->Name;

    $xml = new DOMElement("parameter");
    $parent->appendChild($xml);
    $xml->setAttribute('name', $this->Name);

    if ($this->Value !== null)
      $xml->setAttribute('value', $this->Value);

    if (!empty($this->Type))
      $xml->setAttribute('type', $this->Type);
    return $xml;
  }

  public function findUnique() {
    return self::whereName($this->Name)->first();
  }
}
