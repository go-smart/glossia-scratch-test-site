@extends('master')

@section('content')

{{{ $errors->first() }}}

<p>{{ link_to_route('protocol.create', 'Create') }}</p>
@foreach ($modalities as $modality)
  <h2>{{{ $modality->name }}}</h2>
  <table>
  @foreach ($modality->protocols as $protocol)
      <tr><td>{{ $protocol->name }}</td><td>{{ link_to_route('protocol.show', 'Show', array('id' => $protocol->id)) }}</td></tr>
  @endforeach
  </table>
@endforeach

@stop
