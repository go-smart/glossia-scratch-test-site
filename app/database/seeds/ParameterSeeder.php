<?php

use \DB;
use \Seeder;

class ParameterSeeder extends Seeder {
  public function clean()
  {
    \Parameter::whereNotExists(function ($q) {
      $q->select(DB::raw(1))
        ->from('Simulation_Parameter')
        ->whereRaw('Simulation_Parameter.ParameterId = Parameter.Id');
    })->whereNotExists(function ($q) {
      $q->select(DB::raw(1))
        ->from('Simulation_Needle_Parameter')
        ->whereRaw('Simulation_Needle_Parameter.ParameterId = Parameter.Id');
    })->whereNotExists(function ($q) {
      $q->select(DB::raw(1))
        ->from('Parameter_Attribution')
        ->whereRaw('Parameter_Attribution.Parameter_Id = Parameter.Id');
    })->delete();
  }

  public function run()
  {
    Parameter::create(['Name' => 'NEEDLE_TIP_LOCATION', 'Type' => 'array(float)', 'Widget' => 'textbox', 'Description' => 'location of the needle tip']);
    Parameter::create(['Name' => 'NEEDLE_ENTRY_LOCATION', 'Type' => 'array(float)', 'Widget' => 'textbox', 'Description' => 'location of the needle tip']);
  }

}
