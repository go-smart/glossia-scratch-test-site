<style>
  td {
    vertical-align: top
  }
  a {
    text-decoration: none;
  }
</style>
<table>
  <thead>
    <tr>
      <th>Modality</th>
      <th>Protocol</th>
      <th>Numerical Model</th>
      <th>Power Generator</th>
      <th>Context</th>
      <th>Needles</th>
      <th>Simulations</th>
    </tr>
  </thead>
  <tbody>
@foreach ($combinations as $combination)
    <tr>
      <td style='font-weight: bold'>{{ $combination->Power_Generator->Modality->Name }}</td>
      <td>{{ $combination->Protocol->Name }}</td>
      <td>{{ $combination->Numerical_Model->Name }}</td>
      <td>{{ $combination->Power_Generator->Name }}</td>
      <td>{{ $combination->Context->Name }}</td>
      <td>{{ $combination->Needles->implode('Name', '<br/>') }}</td>
      <td>
      <?php $i = 1; ?>
      @foreach ($combination->Simulations as $simulation)
        {{ link_to_route('simulation.edit', '[' . $i++ . ']', [$simulation->Id]) }}
      @endforeach
      </td>
    </tr>
@endforeach
  </tbody>
</table>
