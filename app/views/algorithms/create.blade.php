@extends('master')

@section('content')

{{ Form::open(array('route' => 'algorithm.store')) }}
<div class='algorithm-create'>
  {{ Form::hidden('protocol', null) }}</p>
  <p>{{ Form::label('Result name')}}: {{ Form::text('result') }}</p>
  <p>{{ Form::label('Result type')}}: {{ Form::text('result') }}</p>
  <p>Time-dependent arguments required: 
  @foreach ($arguments as $argument)
    {{ Form::checkbox('arguments[]', $argument->id) }}{{ Form::label($argument->name) }}
  @endforeach
  <p>{{ Form::textarea('description') }}</p>
</div>

{{ Form::close() }}

@stop
