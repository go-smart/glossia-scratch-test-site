<?php

class SimulationNeedle extends UuidModel {

  public $timestamps = false;

  protected static $updateByDefault = false;

  protected $table = "Simulation_Needle";

  public function Needle()
  {
    return $this->belongsTo('Needle', 'Needle_Id');
  }

  public function Simulation()
  {
    return $this->belongsTo('Simulation', 'Simulation_Id');
  }

  public function Parameters()
  {
    return $this->belongsToMany('Parameter', 'Simulation_Needle_Parameter', 'SimulationNeedleId', 'ParameterId')->withPivot(['ValueSet']);
  }

  public function findUnique()
  {
    return false;
  }

  public function Target()
  {
    return $this->hasOne('PointSet', 'Id', 'Target_Id');
  }

  public function Entry()
  {
    return $this->hasOne('PointSet', 'Id', 'Entry_Id');
  }
}
