@extends('master')

@section('page-js')
<script src="/scripts/simulations/create.js"></script>
@stop

@section('content')
asdf
<h1>Create Simulation</h1>

{{ Form::open(array('route' => 'simulation.store')) }}
<ul>
  <li>Choose context: {{ Form::select('Context_Id', array(-1 => "Please select") + $contexts, -1, array('Id' => 'context-choice')) }}</li>
  <li>Choose modality: {{ Form::select('Modality_Id', array(-1 => "Choose context first"), null, array('Id' => 'modality-choice')) }}</li>
  <li>Choose generator: {{ Form::select('Power_Generator_Id', array(-1 => "Choose modality first"), null, array('Id' => 'power-generator-choice')) }}</li>
  <li>Choose protocol: {{ Form::select('Protocol_Id', array(-1 => "Choose generator first"), null, array('Id' => 'protocol-choice')) }}</li>
  <li>Choose numerical model: {{ Form::select('Numerical_Model_Id', array(-1 => "Choose protocol first"), null, array('Id' => 'numerical-model-choice')) }}</li>
  <!--<li>Choose needle: {{ Form::select('Needle_Id', array(-1 => "Choose generator first"), null, array('Id' => 'needle-choice')) }}</li> -->
  <li>Combination: <span id='combination'>[choose above]</span>{{ Form::hidden('Combination_Id', null) }}</li>
  <li>Choose patient: {{ Form::select('Patient_Id', array(-1 => "Choose context first"), null, array('Id' => 'patient-choice')) }}</li>
  <li>Enter caption: {{ Form::text('caption') }}</li>
</ul>

{{ Form::submit() }}

{{ Form::close() }}

@stop
