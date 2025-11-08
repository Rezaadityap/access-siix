@forelse ($rows as $r)
    <tr>
        <td>{{ \Illuminate\Support\Carbon::parse($r->date)->toDateString() }}</td>
        <td>{{ $r->line }}</td>
        <td>{{ $r->supplier }}</td>
        <td>{{ $r->model }}</td>
        <td>{{ $r->po_number }}</td>
        <td>{{ $r->item }}</td>
        <td>{{ $r->description }}</td>
        <td>{{ $r->usage_total }}</td>
        <td>$ {{ $r->unit_price }}</td>
        <td>{{ $r->rec_qty }}</td>
        <td>{{ $r->qty_lcr }}</td>
        <td>{{ $r->qty_lcr * $r->unit_price }}</td>
        <td>{{ $r->rec_qty - $r->qty_lcr }}</td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-muted text-center">No material detail.</td>
    </tr>
@endforelse
