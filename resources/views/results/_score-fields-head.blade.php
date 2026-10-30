@foreach($scoreFields as $field => $label)
    <th scope="col">{{ $label }} / {{ $scoreMaximums[$field] }}</th>
@endforeach
