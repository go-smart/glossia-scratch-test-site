@extends('master')

@section('page-js')
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="/scripts/lib/autobahn.js"></script>
<script src="/scripts/simulations/dashboard.js"></script>
@stop

@section('content')

<h1>Simulation Dashboard</h1>

<table>
  <thead>
    <tr>
      <th>Servers Connected</th>
    </tr>
  </thead>
  <tbody id='server-dashboard-table-body'>
  </tbody>
</table>

<table>
  <thead>
    <tr>
      <th>Simulations Running</th>
    </tr>
  </thead>
  <tbody id='simulation-dashboard-table-body'>
  </tbody>
</table>
@stop
