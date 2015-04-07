@extends('master')

@section('content')

{{{ $errors->first() }}}

<p>{{ link_to_route('protocol.create', 'Create') }}</p>
@foreach ($modalities as $modality)
  <h2>{{{ $modality->Name }}}</h2>
  <table>
  @foreach ($modality->Protocols as $protocol)
      <tr><td>{{ $protocol->Name }}</td><td>{{ link_to_route('protocol.show', 'Show', array('id' => $protocol->Id)) }}</td></tr>
  @endforeach
  </table>
@endforeach

@stop
