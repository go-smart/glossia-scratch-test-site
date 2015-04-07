<table>
  <thead>
    <tr><th>ID</th><th>Modality</th><th>Manufacturer</th><th>Name</th></tr>
  </thead>
  <tbody>
@foreach ($needles as $needle)
    <tr><td>{{ $needle->Id }}</td><td>{{ $needle->Modality->Name }}</td><td>{{ $needle->Manufacturer }}</td><td>{{ $needle->Name }}</td></tr>
@endforeach
  </tbody>
</table>
