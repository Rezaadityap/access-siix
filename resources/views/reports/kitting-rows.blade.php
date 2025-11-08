@forelse ($records as $row)
    <tr>
        <td>
            <input type="checkbox" class="form-check-input row-check" data-group="{{ $row->group_id }}"
                data-id="{{ $row->id }}" data-line="{{ $row->line }}" data-lot_size="{{ $row->lot_size }}"
                data-act_lot_size="{{ $row->act_lot_size }}">

            {{ $row->model }}
        </td>
        <td>{{ $row->po_number }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($row->date)->toDateString() }}</td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="text-center text-muted">No data reports.</td>
    </tr>
@endforelse
