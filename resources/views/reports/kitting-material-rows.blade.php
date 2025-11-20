@forelse ($rows as $r)
    @php $total_qty = ($r->rec_qty - $r->qty_smd - $r->qty_wh) * -1 + $r->qty_sto @endphp
    <tr>
        <td>{{ \Illuminate\Support\Carbon::parse($r->date)->toDateString() }}</td>
        <td>{{ $r->line }}</td>
        <td>{{ $r->supplier }}</td>
        <td>{{ $r->model }}</td>
        <td>{{ $r->po_number }}</td>
        <td>{{ $r->lot_size }}</td>
        <td>{{ $r->item }}</td>
        <td>{{ $r->description }}</td>
        <td>{{ $r->usage_total }}</td>
        <td>$ {{ $r->unit_price }}</td>
        <td>{{ $total_qty }}</td>
        <td>{{ $r->qty_lcr }}</td>
        <td>$ {{ $r->qty_lcr * $r->unit_price }}</td>
        <td>{{ $total_qty - $r->qty_lcr }}</td>
        <td>$ {{ ($total_qty - $r->qty_lcr) * $r->unit_price }}</td>
        <td>
            {{ $r->usage_total > 0 ? number_format((($total_qty - $r->qty_lcr) / $r->usage_total) * 100, 2) . '%' : '0%' }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="15" class="text-muted text-center">No material detail.</td>
    </tr>
@endforelse
