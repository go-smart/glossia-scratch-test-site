@extends('master')

@section('content')

<h1>{{{$protocol->Modality->Name}}}: {{{ $protocol->Name }}}</h1>

<p>{{ link_to_route('protocol.index', "Back to the index") }}</p>

<p>This protocol is marked for use in the following combinations:</p>
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
@foreach ($protocol->Combinations as $combination)
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
@endforeach
  </tbody>
</table>

<p>This protocol is comprised of the following algorithms</p>

<table class='protocol-algorithms-table'>
@foreach ($protocol->Algorithms as $algorithm)
  <tbody>
    <tr><td class='algorithm-result'>{{ $algorithm->Result->as_html() }}</td><td class='algorithm-arguments'>({{{ $algorithm->arguments_as_string() }}})</td></tr>
    <tr><td class='algorithm-parameters' colspan=2><span class='inline-label'>&lt;Parameters&gt;</span> {{ $algorithm->parameters_as_html() }}</td></tr>
    <tr><td class='padding'></td><td><pre class='algorithm-content'>{{{ $algorithm->Content }}}</pre></td></tr>
  </tbody>
@endforeach
</table>

@stop
