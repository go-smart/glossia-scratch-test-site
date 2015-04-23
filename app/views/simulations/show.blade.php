@extends('master')

@section('content')

<h1>Simulations</h1>
<p>{{ link_to_route('simulation.index', '&rarr; back to simulations') }}</p>

<pre>{{{ $simulationXml->saveXML() }}}</pre>

@stop
