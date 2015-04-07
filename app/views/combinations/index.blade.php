<table>
@foreach ($Combinations as $combination)
    <tr>
      <td>{{ $combination->Protocol->Name }}</td>
      <td>
      @foreach ($combination->Needles as $needle)
        {{ $needle->Name }}</td>
      @endforeach
      <td>{{ $combination->Power_Generator->Name }}</td>
    </tr>
@endforeach
</table>
