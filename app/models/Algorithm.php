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


class Algorithm extends Paramable {

  /**
   * Look after created_at and modified_at properties automatically
   *
   * @var boolean
   */
  public $timestamps = false;

  public static $idField = 'Algorithm_Id';

  protected static $updateByDefault = false;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Algorithm';

  public function Protocol() {
    return $this->belongsTo('Protocol', 'Protocol_Id');
  }

  public function Result() {
    return $this->belongsTo('Parameter', 'Result_Id');
  }

  public function Arguments() {
    return $this->belongsToMany('Argument', 'Algorithm_Argument', 'Algorithm_Id', 'Argument_Id');
  }

  /**
   * Accessors that should really be in a ViewModel/Presenter decorator
   */

  /* Web UI */
  public function arguments_as_string() {
    return $this->Arguments->implode('Name', ', ');
  }

  /* XML */
  public function xml($parent) {
    $xml = new DOMElement("algorithm");
    $parent->appendChild($xml);
    $xml->setAttribute('result', $this->Result->Name);

    $arguments = new DOMElement("arguments");
    $xml->appendChild($arguments);
    foreach ($this->Arguments as $argument)
    {
      $argument->xml($arguments);
    }

    $content = new DOMElement("content");
    $contentText = new DOMText($this->Content);
    $xml->appendChild($content);
    $content->appendChild($contentText);

    return $xml;
  }

  public function findUnique()
  {
    return false;
  }
}
