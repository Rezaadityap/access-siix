@if ($batches->isEmpty())
    <div class="card">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-striped history-material-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>PO Number</th>
                            <th>Batch</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Source</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center" colspan="7">No batch found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-striped history-material-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>PO Number</th>
                            <th>Batch</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Source</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($batches as $i => $b)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $b->po_number }}</td>
                                <td>{{ $b->batch }}</td>
                                <td>{{ $b->description }}</td>
                                <td>{{ $b->qty }}</td>
                                <td>{{ strtoupper($b->source) }}</td>
                                <td>{{ $b->created_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
