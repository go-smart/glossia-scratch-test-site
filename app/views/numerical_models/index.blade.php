@extends('master')

@section('content')

<h1>Numerical Models</h1>
<p>{{ link_to_route('home', '&rarr; to index') }}</p>
<table>
  @foreach ($models as $numericalModel)
  <tr>
    <td>{{ link_to_route('numerical_model.show', $numericalModel->Name, $numericalModel->Id) }}</td>
    <td>{{ $numericalModel->Family }}</td>
  </tr>
  @endforeach
</table>

@stop
