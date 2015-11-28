@extends('master')

@section('page-js')
<script src="/scripts/simulations/create.js"></script>
@stop

@section('content')

<h1>Simulations : {{ $simulation->Id }}</h1>
<p>{{ link_to_route('simulation.index', '&rarr; back to simulations') }}</p>

<p>{{ link_to_route('combination.show', $simulation->Combination->asString, $simulation->Combination->Id) }}</p>

{{ Form::open(['route' => ['simulation.update', $simulation->Id], 'method' => 'PATCH']) }}
<ul>
  <li>Choose context: {{ Form::select('Context_Id', array(-1 => "Please select") + $contexts, -1, array('Id' => 'context-choice')) }}</li>
  <li>Choose modality: {{ Form::select('Modality_Id', array(-1 => "Choose context first"), null, array('Id' => 'modality-choice')) }}</li>
  <li>Choose generator: {{ Form::select('Power_Generator_Id', array(-1 => "Choose modality first"), null, array('Id' => 'power-generator-choice')) }}</li>
  <li>Choose protocol: {{ Form::select('Protocol_Id', array(-1 => "Choose generator first"), null, array('Id' => 'protocol-choice')) }}</li>
  <li>Choose numerical model: {{ Form::select('Numerical_Model_Id', array(-1 => "Choose protocol first"), null, array('Id' => 'numerical-model-choice')) }}</li>
  <!--<li>Choose needle: {{ Form::select('Needle_Id', array(-1 => "Choose generator first"), null, array('Id' => 'needle-choice')) }}</li> -->
  <li>Combination: <span id='combination'>[choose above]</span>{{ Form::hidden('Combination_Id', null) }}</li>
</ul>

{{ Form::submit("Update combination") }}

{{ Form::close() }}

<hr/>

@if (count($lineage) || count($simulation->Children))
<h2>Lineage</h2>

<ol>
@foreach ($lineage as $l)
  <li><a href="{{ URL::route('simulation.edit', [$l->Id]) }}">{{ $l->asHtml }}</a></li>
@endforeach
</ol>

<ul>
@foreach ($simulation->Children as $child)
  @if (!$child->Original_Id)
    <li><a href="{{ URL::route('simulation.edit', [$child->Id]) }}">{{ $child->asHtml }}</a></li>
  @endif
@endforeach
</ul>
<hr/>
@endif

<h2>Segmentations</h2>

<ul>
@foreach ($simulation->Segmentations as $s)
  <li>{{ $s->Name }}</li>
@endforeach
</ul>

<h2>Detail</h2>

{{ Form::open(['route' => ['simulation.update', $simulation->Id], 'method' => 'PATCH']) }}
<input type='hidden' value=0 name='removing' />
<p>Date : {{ $simulation->creationDate }}</p>
<p>Patient : {{ $simulation->PatientAlias }} | {{ $simulation->PatientDescription }}</p>
<p>{{ Form::label('caption', 'Caption') }}: {{ Form::text('caption', $simulation->Caption) }}</p>
<p>{{ Form::submit() }}</p>

<h2>Needles</h2>
<ul>
<input type='hidden' value='' name='simulation-needle-id' />
@foreach ($simulation->SimulationNeedles as $simulationNeedle)
  <li>
    {{ $simulationNeedle->Needle->Name }} : {{ $simulationNeedle->Target->asString }} &larr; {{ $simulationNeedle->Entry->asString }}
    [<a href='#' class='simulation-needle-remover' name='{{ $simulationNeedle->Id }}'>X</a>]
  </li>
@endforeach
  <li>
    <select name='needle-id'>
      <option value=''>[ Choose a needle ]</option>
      @foreach ($needles as $needle)
        <option value='{{ $needle->Id }}'>{{ $needle->Manufacturer }} - {{ $needle->Name }}</option>
      @endforeach
    </select>
    |
    Target: <input name='needle-target' type='textbox'>
    |
    Entry: <input name='needle-entry' type='textbox'>
    |
    <input type='submit' value='Add'>
  </li>
</ul>

<div style='font-style: italic; font-size: small'>
  <p>For your convenience, the following are needle targets from other simulations used with the same patient</p>
  <ul>
  @foreach ($otherSimulationTargets as $target)
    <tr>{{ $target }}</tr>
  @endforeach
  </ul>
</div>

<h2>Segmentations</h2>
<ul>
<input type='hidden' value='' name='region-remove-id' />
<input type='hidden' value='' name='region-remove-location' />
@foreach ($simulation->Segmentations as $segmentation)
  <li>
    {{ $segmentation->Name }} : {{ $segmentation->FileName }}.{{ $segmentation->Extension }}
  </li>
@endforeach
</ul>

<h3>General Parameters</h3>
<table>
<tr><td>{{ Form::text('new-parameter-name') }}</td><td>{{ Form::text('new-parameter-value') }}</td></tr>
@foreach ($simulation->Parameters as $parameter)
<tr><td>{{ $parameter->asHtml }}</td><td>{{ Form::text('parameters-' . $parameter->Id, $parameter->pivot->ValueSet) }}</td></tr>
@endforeach
</table>

<h3>Needle Parameters</h3>
@foreach ($simulation->SimulationNeedles as $simulationNeedle)
<h4>{{ $simulationNeedle->Needle->Name }} : {{ $simulationNeedle->Target->asString }} &larr; {{ $simulationNeedle->Entry->asString }}</h4>
<table>
<tr><td>{{ Form::text('needle-' . $simulationNeedle->Id . '-new-parameter-name') }}</td><td>{{ Form::text('needle-' . $simulationNeedle->Id . '-new-parameter-value') }}</td></tr>
@foreach ($simulationNeedle->Parameters as $parameter)
<tr><td>{{ $parameter->asHtml }}</td><td>{{ Form::text('needle-parameters-' . $simulationNeedle->Id . '-' . $parameter->Id, $parameter->pivot->ValueSet) }}</td></tr>
@endforeach
</table>
@endforeach

{{ Form::close() }}

<script>
$(function () {
  $('a.simulation-needle-remover').click(function (e) {
    e.preventDefault();
    $("input[name=removing]").val(1);
    $("input[name=simulation-needle-id]").val(e.target.name);
    $(e.target).closest('form').submit();
    return false;
  });
  $('a.region-remover').click(function (e) {
    e.preventDefault();
    $("input[name=removing]").val(1);
    vars = e.target.name.split('::');
    $("input[name=region-remove-id]").val(vars[0]);
    $("input[name=region-remove-location]").val(vars[1]);
    $(e.target).closest('form').submit();
    return false;
  });
});
</script>
@stop
