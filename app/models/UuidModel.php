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


use Rhumsaa\Uuid\Uuid;

abstract class UuidModel extends Eloquent {
  public $incrementing = false;

  protected $primaryKey = 'Id';

  protected static $updateByDefault = true;

  public static function boot() {
    parent::boot();

    static::creating(function ($model) {
      $model->{$model->getKeyName()} = substr((string)$model->generateUuid(), 0, 36);
    });
  }

  public function getIdAttribute($id) {
    if ($id)
      return substr($id, 0, 36);

    if (strtolower($this->primaryKey) != 'id')
      return $this->{$this->primaryKey};

    return $id;
  }

  public function generateUuid() {
    return Uuid::uuid4();
  }

  public function save(array $options = [])
  {
    if (!$this->exists)
    {
      $existing = $this->findUnique();
      if ($existing)
      {
        $this->{$this->primaryKey} = $existing->{$this->primaryKey};
        $this->exists = true;
      }
    }

    return parent::save($options);
  }

  public static function create(array $attributes) {
    if (static::$updateByDefault)
    {
      $model = new static($attributes);
      $existing = $model->findUnique();
      if ($existing)
      {
        $existing->fill($attributes);
        $existing->save();
        return $existing;
      }
    }
    return parent::create($attributes);
  }

  public abstract function findUnique();
}
