<p>All servers responding.</p>

<ul>
@foreach ($servers as $id => $name)
  <li>{{{ $name }}} [{{{ $id }}}]</li>
@endforeach
</ul>
