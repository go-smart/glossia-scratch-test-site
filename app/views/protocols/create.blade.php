@extends('master')

@section('page-js')
<script src="/scripts/protocols/create.js"></script>
@stop

@section('content')

<h1>Create Protocol</h1>

{{ Form::open(array('route' => 'protocol.store')) }}
<ul>
  <li>Choose modality: {{ Form::select('Modality_Id', array(-1 => "Please select") + $modalities, -1, array('Id' => 'modality-choice')) }}</li>
  <li>Choose numerical model: {{ Form::select('Model_Id', array(-1 => "Choose modality first"), null, array('Id' => 'numerical-model-choice')) }}</li>
  <li>Choose generator: {{ Form::select('Power_Generator_Id', array(-1 => "Choose modality first"), null, array('Id' => 'power-generator-choice')) }}</li>
  <li>Choose needle: {{ Form::select('Needle_Id', array(-1 => "Choose generator first"), null, array('Id' => 'needle-choice')) }}</li>
</ul>

<div style='clear: both'></div>

<div id='parameter-selection'>
  <table>
    <tr>
      <td colspan=3>Pick the parameters your algorithms require from those available</td>
    <tr>
      <td><p class='protocol-parameters-header'>FROM GENERATOR</p><ul id='parameters-generator'></ul></td>
      <td><p class='protocol-parameters-header'>FROM NEEDLE</p><ul id='parameters-needle'></ul></td>
      <td><p class='protocol-parameters-header'>FROM MODEL</p><ul id='parameters-model'></ul></td>
    </tr>
  </table>
</div>

<div id='protocol-entry'>
  <p>Add algorithms that can supply new model parameters programmatically.</p>
  <div id='algorithm-block'>
  </div>
  <p><a href='javascript:add_algorithm_entry()'>Add algorithm</a></p>
</div>

{{ Form::close() }}

@stop
