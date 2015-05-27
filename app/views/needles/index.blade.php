<table>
  <thead>
    <tr><th>ID</th><th>Modality</th><th>Manufacturer</th><th>Name</th></tr>
  </thead>
  <tbody>
@foreach ($needles as $needle)
    <tr><td>{{ $needle->id }}</td><td>{{ $needle->modality->name }}</td><td>{{ $needle->manufacturer }}</td><td>{{ $needle->name }}</td></tr>
@endforeach
  </tbody>
</table>
