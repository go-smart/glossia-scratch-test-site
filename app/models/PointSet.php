<?php


class PointSet extends UuidModel {

  /**
   * Look after created_at and modified_at properties automatically
   *
   * @var boolean
   */
  public $timestamps = false;

  public $guarded = [];

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'PointSet';

  protected static $updateByDefault = false;

  public function findUnique()
  {
    return false;
  }

  public function getAsArrayAttribute()
  {
    return [(float)$this->X, (float)$this->Y, (float)$this->Z];
  }

  public function getAsStringAttribute()
  {
    return json_encode($this->asArray);
  }
}
