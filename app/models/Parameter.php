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
	protected $table = 'parameters';

  /**
   * Polymorphic relationship
   *
   * @var string
   */

  public function parameterAttributions()
  {
    return $this->hasManyThrough('ParameterAttribution', 'Algorithm');
  }


  /**
   * Accessors that should really be in a ViewModel/Presenter decorator
   */

  public function as_html()
  {
    $combined = "<span class='parameter' title='Type: $this->type";

    if ($this->units)
    {
      $combined .= "; Units: " . $this->units;
    }

    if ($this->restriction)
      $combined .= "; Must be from $this->restriction";

    if ($this->widget)
      $combined .= "; Can be given by user using $this->widget";

    if ($this->description)
      $combined .= "; ($this->description)";

    $combined .= "'>$this->name</span>";

    return $combined;
  }

  /* More Presenter logic hack */
  public function xml($parent) {
    $name = $this->name;

    $xml = new DOMElement("parameter");
    $parent->appendChild($xml);
    $xml->setAttribute('name', $this->name);

    if ($this->value !== null)
      $xml->setAttribute('value', $this->value);

    if (!empty($this->type))
      $xml->setAttribute('type', $this->type);
    return $xml;
  }
}
