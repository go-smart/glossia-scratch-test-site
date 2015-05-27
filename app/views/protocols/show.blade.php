@extends('master')

@section('content')

<h1>{{{$protocol->modality->name}}}: {{{ $protocol->name }}}</h1>

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
@foreach ($protocol->combinations as $combination)
    <tr>
      <td>{{{ $combination->powerGenerator->name }}}</td>
      <td>
        @foreach ($combination->needles as $needle)
          {{{ $needle->name }}}<br/>
        @endforeach
      </td>
      <td>{{{ $combination->numericalModel->name }}}</td>
      <td>{{{ $combination->context->family }}} :: {{{ $combination->context->name }}}</td>
      <td>{{ HTML::link(route('combination.show', [$combination->id, 'html' => 1]), "[XML]") }}</td>
      <td>
      @foreach ($combination->numericalModel->regions as $region)
        <span class='context-label'>[
          @if ($region->pivot->minimum && $region->pivot->maximum)
            @if ($region->pivot->minimum == $region->pivot->maximum)
              ={{{ $region->pivot->minimum }}}
            @else
              {{{ $region->pivot->minimum }}}-{{{ $region->pivot->maximum }}}
            @endif
          @elseif ($region->pivot->minimum)
            &gt;{{{ $region->pivot->minimum }}}
          @elseif ($region->pivot->maximum)
            &lt;{{{ $region->pivot->maximum }}}
          @endif
        {{{ $region->name }}} ]</span>
      @endforeach
      </td>
    </tr>
@endforeach
  </tbody>
</table>

<p>This protocol is comprised of the following algorithms</p>

<table class='protocol-algorithms-table'>
@foreach ($protocol->algorithms as $algorithm)
  <tbody>
    <tr><td class='algorithm-result'>{{ $algorithm->result->as_html() }}</td><td class='algorithm-arguments'>({{{ $algorithm->arguments_as_string() }}})</td></tr>
    <tr><td class='algorithm-parameters' colspan=2><span class='inline-label'>&lt;Parameters&gt;</span> [{{ $algorithm->parameters_as_html() }}]</td></tr>
    <tr><td class='padding'></td><td><pre class='algorithm-content'>{{{ $algorithm->content }}}</pre></td></tr>
  </tbody>
@endforeach
</table>

@stop
