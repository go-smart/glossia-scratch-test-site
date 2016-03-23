<p>SOMETHING IS WRONG!!!!!</p>

<ul>
@foreach ($servers as $server)
  <li>{{{ $server[2] }}} [{{{ $server[0] }}}] : {{{ $server[1] ? 'OK' : 'LOST!' }}}</li>
@endforeach
</ul>
