@extends('master')

@section('content')

<h1>{{ $numerical_model->Name }}</h1>
<h2>{{ $numerical_model->Family }}</h2>

<p>{{ link_to_route('numerical_model.index', '&larr; all numerical models') }}</p>
<p>Arguments: {{ $numerical_model->Arguments->implode('Name', ', ') }}</p>
<p>Regions: {{ $numerical_model->Regions->implode('Name', ', ') }}</p>

<div style='width: 40%'>
<h3>Definition</h3>
<div style='height: 600px; overflow: scroll; float: left'>
 <pre>
  {{{ $numerical_model->Definition }}}
 </pre>
</div>
</div>

<div style='width: 40%; float: right; border'>
  <h3>Parameters</h3>
  <table class='parameter-table'>
  @foreach ($numerical_model->ParameterAttributions as $pa)
    <tr>
      <td>{{ $pa->Format ?: $pa->Parameter->Type }}</td><td>{{ $pa->Parameter->as_html() }}</td><td>{{ $pa->Context ? $pa->Context->Name : '' }}</td>
    </tr>
  @endforeach
  </table>
</div>

@stop
