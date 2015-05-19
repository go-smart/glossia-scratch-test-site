@extends('master')

@section('content')

<h1>Simulations</h1>
<p>{{ link_to_route('simulation.index', '&rarr; back to simulations') }}</p>

{{ Form::open(['route' => ['simulation.update', $simulation->Id], 'method' => 'PATCH']) }}
<input type='hidden' value=0 name='removing' />
<p>{{ $simulation->Combination->asString }}</p>

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
