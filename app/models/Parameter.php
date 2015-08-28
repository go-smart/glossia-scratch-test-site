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

  protected $editableStrings = [
    "[NOTDEFINED]",
    "Never shown",
    "Shown when needed",
    "Always shown"
  ];


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

  public function getEditableStringAttribute()
  {
    return $this->editableStrings[$this->Editable];
  }

  //FIXME: deprecated
  public function as_html()
  {
    return $this->asHtml;
  }

  public function getUnitsAttribute($units) {
    return ($units !== null ? json_decode($units) : null);
  }

  public function setUnitsAttribute($units) {
    if ($units !== null) {
      $units = json_encode($units);
    }

    $this->attributes['Units'] = $units;
  }

  public function getWidgetAttribute($widget) {
    return ($widget !== null ? json_decode($widget) : null);
  }

  public function setWidgetAttribute($widget) {
    if ($widget !== null) {
      if (!is_array($widget))
        $widget = [$widget];

      $widget = json_encode($widget);
    }

    $this->attributes['Widget'] = $widget;
  }

  public function getAsHtmlAttribute()
  {
    $combined = "<span class='parameter' title='Type: $this->Type";

    if ($this->Units)
    {
      $combined .= "; Units: " . (is_array($this->Units) ? implode('|', $this->Units) : $this->Units);
    }

    if ($this->Restriction)
      $combined .= "; Must be from $this->Restriction";

    if ($this->Widget)
    {
      $combined .= "; Can be given by user using " . $this->Widget[0];
      if (count($this->Widget) > 1)
        $combined .= "(" . implode(', ', array_slice($this->Widget, 1)) . ")";
    }


    if ($this->Description)
      $combined .= "; ($this->Description)";

    $combined .= "'>$this->Name</span>";

    return $combined;
  }

  /* More Presenter logic hack */
  public function xml($parent, $backup=false) {
    $name = $this->Name;

    $xml = new DOMElement("parameter");
    $parent->appendChild($xml);
    $xml->setAttribute('name', $this->Name);

    if ($backup)
      $xml->setAttribute('id', $this->Id);

    if ($this->Value !== null)
      $xml->setAttribute('value', $this->Value);

    if (!empty($this->Format))
      $xml->setAttribute('type', $this->Format);
    else if (!empty($this->Type))
      $xml->setAttribute('type', $this->Type);
    return $xml;
  }

  public function findUnique() {
    return self::whereName($this->Name)->first();
  }
}
