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

abstract class ContextEnum
{
  const Liver = 1;
  const Lung = 2;
  const Kidney = 3;
  const Prostate = 4;
  static $all = [1 => 'Liver', 2 => 'Lung', 3 => 'Kidney', 4 => 'Prostate'];

  public static function get($id)
  {
    if (is_numeric($id))
      return self::$all[$id];
    $id = train_case($id);
    try {
      constant('ContextEnum::' . $id); // Check constant exists
    }
    catch (ErrorException $e) {
      return null;
    }
    return $id;
  }
}

class Context extends Paramable {

  /**
   * Look after created_at and modified_at properties automatically
   *
   * @var boolean
   */
  public $timestamps = false;

  public static $whereContext = "whereOrganType";
  public static $idField = "OrganType";

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Context';

  public static function find($id, $columns = [])
  {
    if (Config::get('gosmart.context_as_enum'))
    {
      $context = new Context;
      if ($label = ContextEnum::get($id))
      {
        $context->Id = constant('ContextEnum::' . $label);
        $context->Name = $label;
        $context->Family = "organ";
        return $context;
      }
      else
      {
        return null;
      }
    }

    return parent::find($id);
  }

  public static function byNameFamily($name, $family)
  {
    if (Config::get('gosmart.context_as_enum'))
    {
      return self::find($name);
    }

    return self::whereName($name)->whereFamily($family)->first();
  }

  public function findUnique()
  {
    return self::whereName($this->Name)
      ->whereFamily($this->Family)
      ->first();
  }
}
