@extends('master')

@section('content')
<style type="text/css" media="screen">
    #editor { 
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }
</style>
    

<h1>{{ $numerical_model->Name }}</h1>
<h2>{{ $numerical_model->Family }}</h2>

<p>{{ link_to_route('numerical_model.index', '&larr; all numerical models') }}</p>
<p>Arguments: {{ $numerical_model->Arguments->implode('Name', ', ') }}</p>
<p>Regions: {{ $numerical_model->Regions->implode('Name', ', ') }}</p>

{{ Form::open(['route' => ['numerical_model.update', $numerical_model->Id], 'method' => 'PATCH']) }}
  {{ Form::button('Submit', ['class' => 'submit_button']) }}

  <h3>Definition</h3>
  <div style='width: 100%; position: relative; height: 500px'>
<input type='hidden' name="definition" />
<div id="editor">{{{ $numerical_model->Definition }}}</div>
  </div>

  <h3>Parameters</h3>
  <table class='parameter-table'>
  @foreach ($numerical_model->ParameterAttributions as $pa)
    <tr>
      <td>{{ $pa->Format ?: $pa->Parameter->Type }}</td><td>{{ $pa->asHtml }}</td><td>{{ $pa->Context ? $pa->Context->Name : '' }}</td>
    </tr>
  @endforeach
  </table>
{{ Form::close() }}

<script src="/ace-builds/src-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
<script>
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/monokai");
    editor.getSession().setMode("ace/mode/javascript");
    $('.submit_button').click(function (e) {
       var form = $(this).closest('form');
       var editor = ace.edit("editor");
       var input = $("input[name=definition]");
       input.val(editor.getSession().getValue());
       form.submit();
       console.log(form);
    });
</script>
@stop
