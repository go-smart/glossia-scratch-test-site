@extends('master')

@section('content')

<h1>Combination</h1>
<p>{{ link_to_route('combination.index', '&rarr; back to combinations') }}</p>

<h2>{{ $combination->modality->Name }}: {{{ $combination->asString }}}</h2>

<table class='combinations-table'>
  <thead>
    <tr>
      <th>Generator</th>
      <th>Needle</th>
      <th>Numerical Model</th>
      <th>Context / Organ</th>
      <th></th>
      <th>(regions used by numerical model)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>{{{ $combination->PowerGenerator->Name }}}</td>
      <td>
        @foreach ($combination->Needles as $needle)
          {{{ $needle->Name }}}<br/>
        @endforeach
      </td>
      <td>{{{ $combination->Numerical_Model->Name }}}</td>
      <td>{{{ $combination->Context->Family }}} :: {{{ $combination->Context->Name }}}</td>
      <td>{{ HTML::link(route('combination.show', [$combination->Combination_Id, 'html' => 1]), "[XML]") }}</td>
      <td>
      @foreach ($combination->NumericalModel->Regions as $region)
        <span class='context-label'>[
          @if ($region->pivot->Minimum && $region->pivot->Maximum)
            @if ($region->pivot->Minimum == $region->pivot->Maximum)
              ={{{ $region->pivot->Minimum }}}
            @else
              {{{ $region->pivot->Minimum }}}-{{{ $region->pivot->Maximum }}}
            @endif
          @elseif ($region->pivot->Minimum)
            &gt;{{{ $region->pivot->Minimum }}}
          @elseif ($region->pivot->Maximum)
            &lt;{{{ $region->pivot->Maximum }}}
          @endif
        {{{ $region->Name }}} ]</span>
      @endforeach
      </td>
    </tr>
  </tbody>
</table>

<h4>Needles Selected</h4>
{{ Form::open(['method' => 'get']) }}
  <select multiple="multiple" name="needles[]" id="needles">
  @foreach ($combination->Needles as $needle)
    <option value="{{ $needle->Id }}" @if($needles->contains($needle)) selected @endif>{{{ $needle->Name }}}</option>
  @endforeach
  </select>
  {{ Form::submit() }}
{{ Form::close() }}

<h3>User-Required Parameters</h3>
<table>
@foreach ($userRequiredParameters as $parameter)
  <tr><td>{{ $parameter->asHtml }}</td></tr>
@endforeach
</table>

<hr/>

<h3>Unsatisfiable Parameters</h3>
<table>
@foreach ($incompatibilities as $message)
  <tr><td>{{ $message }}</td></tr>
@endforeach
</table>

<hr/>

<h3>All Parameters</h3>

<h4>General Parameters</h4>
<table>
@foreach ($parameters as $parameter)
  <tr><td>{{ $parameter->asHtml }}</td></tr>
@endforeach
</table>

<h4>Needle Parameters</h4>
<table>
@foreach ($needleParameters as $needle => $parameters)
  <th>{{{ $needle }}}</th>
  @foreach ($parameters as $parameter)
    <tr><td>{{ $parameter->asHtml }}</td></tr>
  @endforeach
@endforeach
</table>

@stop
