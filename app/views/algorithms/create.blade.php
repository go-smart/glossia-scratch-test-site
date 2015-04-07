@extends('master')

@section('content')

{{ Form::open(array('route' => 'algorithm.store')) }}
<div class='algorithm-create'>
  {{ Form::hidden('Protocol', null) }}</p>
  <p>{{ Form::label('Result name')}}: {{ Form::text('Result') }}</p>
  <p>{{ Form::label('Result type')}}: {{ Form::text('Result') }}</p>
  <p>Time-dependent arguments required: 
  @foreach ($arguments as $argument)
    {{ Form::checkbox('Arguments[]', $argument->Id) }}{{ Form::label($argument->Name) }}
  @endforeach
  <p>{{ Form::textarea('Description') }}</p>
</div>

{{ Form::close() }}

@stop
