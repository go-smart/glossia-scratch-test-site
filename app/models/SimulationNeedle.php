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
    return $this->belongsToMany('Parameter', 'Simulation_Needle_Parameter', 'Simulation_Needle_Id', 'Parameter_Id')->withPivot(['ValueSet']);
  }

  public function findUnique()
  {
    return false;
  }
}
