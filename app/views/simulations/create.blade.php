@extends('master')

@section('page-js')
<script src="/scripts/simulations/create.js"></script>
@stop

@section('content')

<h1>Create Simulation</h1>

{{ Form::open(array('route' => 'simulation.store')) }}
<ul>
  <li>Choose context: {{ Form::select('context_id', array(-1 => "Please select") + $contexts, -1, array('id' => 'context-choice')) }}</li>
  <li>Choose modality: {{ Form::select('modality_id', array(-1 => "Choose context first"), null, array('id' => 'modality-choice')) }}</li>
  <li>Choose generator: {{ Form::select('power_generator_id', array(-1 => "Choose modality first"), null, array('id' => 'power-generator-choice')) }}</li>
  <li>Choose needle: {{ Form::select('needle_id', array(-1 => "Choose generator first"), null, array('id' => 'needle-choice')) }}</li>
  <li>Choose numerical model: {{ Form::select('model_id', array(-1 => "Choose needle first"), null, array('id' => 'numerical-model-choice')) }}</li>
</ul>
{{ Form::close() }}

@stop
