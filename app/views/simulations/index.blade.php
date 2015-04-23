@extends('master')

@section('content')

<h1>Simulations</h1>
<p>{{ link_to_route('home', '&rarr; to index') }}</p>

<script>
var simulations = {
@foreach ($simulations as $simulation)
  '{{ $simulation->Id }}': {{ $simulation->toJson(); }},
@endforeach
  '': ''
}

function updateParameters()
{
  parameter_name = $('#parameter-request').val();

  $('.simulations').each(function (i, simulationRow) {
    var simulation = simulations[simulationRow.id];
    var combination_id = simulation.Combination_Id;
    $.getJSON('/combination/' + simulation.Combination_Id + '/parameter/' + parameter_name, {}, function(data) {
      $('#simulation-' + simulation.Id + '-parameter').html(data);
    });
  });

}
</script>
<input id='parameter-request'/>
<input type='button' value='Show Parameters' onClick='updateParameters()' />
<table class='simulations-table'>
@foreach ($simulations as $simulation)
  <tr id='{{ $simulation->Id }}' class='simulations'>
    <td>{{ $simulation->asHtml }}</td>
    <td>{{ link_to_route('simulation.show', '[XML]', $simulation->Id) }}</td>
    <td>{{ link_to_route('simulation.show', '[HTML]', [$simulation->Id, 'html' => 1]) }}</td>
    <td>{{ link_to_route('simulation.edit', '[edit]', $simulation->Id) }}</td>
    <td id='simulation-{{ $simulation->Id }}-parameter' class='combination-parameters'></td>
  </tr>
@endforeach
</table>

@stop
