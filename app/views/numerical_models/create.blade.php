@extends('master')

@section('content')
<?php phpinfo(); ?>

{{ Form::open() }}
<h1>Name: {{ Form::text('name') }}</h1>
<h2>Family: {{ Form::select('family', ['elmer-libnuma' => 'Elmer-libnuma']) }}</h2>

<p>{{ link_to_route('numerical_model.index', '&larr; all numerical models') }}</p>
<p>Arguments:</p>
<ul>
  <li>{{ Form::text('argument-new', null) }} {{ Form::button('Add argument') }}</li>
</ul>
<p>Regions:</p>
<ul>
  <li>{{ Form::select('region-new', ['region1' => 'REPLACE ME']) }} Minimum: {{ Form::text('region-new-min', null) }} Maximum: {{ Form::text('region-new-max', null) }}</p>
</ul>

<div style='width: 40%'>
<h3>Definition</h3>
<div style='height: 600px; overflow: scroll; float: left'>
  {{ Form::textarea('definition') }}
</div>
</div>

<div style='width: 40%; float: right; border'>
  <h3>Parameters</h3>
  <table class='parameter-table'>
    <tr>
      <td>Name:</td>
      <td>{{ Form::text('parameter-new-name') }}</td>
      <td>{{ Form::select('parameter-new-format', [-1 => '--default--', 'integer', 'boolean', 'float', 'array(float)']) }}</td>
      <td>{{ Form::select('parameter-new-context', [-1 => '--default--', 'liver' => 'Liver']) }}</td>
    </tr>
    <tr>
      <td colspan=1>Value:</td>
      <td colspan=3>{{ Form::text('parameter-new-value') }} (leave blank for placeholder)</td>
    </tr>
  </table>
</div>

{{ Form::button('Generate sample SIF') }}

@stop

