<table>
@foreach ($combinations as $combination)
    <tr>
      <td>{{ $combination->protocol->name }}</td>
      <td>
      @foreach ($combination->needles as $needle)
        {{ $needle->name }}</td>
      @endforeach
      <td>{{ $combination->power_generator->name }}</td>
    </tr>
@endforeach
</table>
