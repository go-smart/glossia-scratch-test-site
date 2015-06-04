@extends('master')

@section('page-js')
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="/scripts/lib/autobahn.js"></script>
<script src="/scripts/simulations/dashboard.js"></script>
<script>
  function duplicateLink(id) { return "{{ URL::route('simulation.duplicate', ['ID']) }}".replace('ID', id); };
  function rebuildLink(id) { return "{{ URL::route('simulation.rebuild', ['ID']) }}".replace('ID', id); };
  function segmentedLesionLink(id) { return "{{ URL::route('simulation.getSegmentedLesion', ['ID']) }}".replace('ID', id); };
  function xmlLink(id) { return "{{ URL::route('simulation.show', ['ID']) }}".replace('ID', id); };
  function htmlLink(id) { return "{{ URL::route('simulation.show', ['ID', 'html' => '1']) }}".replace('ID', id); };
  function editLink(id) { return "{{ URL::route('simulation.edit', ['ID']) }}".replace('ID', id); };
</script>
<style>
a[name=start], a[name=duplicate] {
  text-decoration: none;
}
</style>
@stop

@section('content')

<h1>Simulations</h1>
<p>{{ link_to_route('home', '&rarr; to index') }}</p>
<p>{{ link_to_route('simulation.backup', 'Backup all with prefix NUMA', ['prefix' => 'NUMA']) }}</p>

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
</table>

<h1>Backups</h1>
<table>
  @foreach ($backups as $backup)
    <tr><td>{{ $backup }}</td><td>{{ link_to_route('simulation.restore', 'Restore', ['batch' => $backup]) }}</td></tr>
  @endforeach
</table>
@stop
