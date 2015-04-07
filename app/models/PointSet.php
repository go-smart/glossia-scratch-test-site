<?php


class PointSet extends UuidModel {

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
	protected $table = 'PointSet';

  protected static $updateByDefault = false;

  public function findUnique()
  {
    return false;
  }
}
