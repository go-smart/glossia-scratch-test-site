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
      <td>{{ link_to_route('protocol.show', $combination->Protocol->Name, [$combination->Protocol->Id]) }}</td>
      <td>{{ link_to_route('numerical_model.show', $combination->Numerical_Model->Name, [$combination->Numerical_Model->Id]) }}</td>
      <td>{{ $combination->Power_Generator->Name }}</td>
      <td>{{ $combination->Context->Name }}</td>
      <td>{{ $combination->Needles->implode('Name', '<br/>') }}</td>
      <td>
      <?php $i = 1; ?>
      @foreach ($combination->Simulations as $simulation)
        [{{ $i++ }}<span style='font-size:xx-small'>{{ link_to_route('simulation.edit', 'e', [$simulation->Id]) }}{{ link_to_route('simulation.show', 'H', [$simulation->Id, 'html' => true]) }}{{ link_to_route('simulation.show', 'X', [$simulation->Id]) }}</span>]
      @endforeach
      </td>
    </tr>
@endforeach
  </tbody>
</table>
