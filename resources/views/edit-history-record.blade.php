@extends('layouts.app')

@section('content')
    <main class="main" id="main">
        <div class="pagetitle">
            <h1>Edit History</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('op-kitting.index') }}">Op Kitting</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('op-kitting.history') }}">Record History</a></li>
                    <li class="breadcrumb-item active">Edit History</li>
                </ol>
            </nav>
        </div>

        <div class="container">
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">PO Item</label>
                            <input type="text" name="po_item" value="{{ old('po_item', $record->po_item) }}"
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Material</label>
                            <input type="text" name="material" value="{{ old('material', $record->material) }}"
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Material Description</label>
                            <input type="text" name="material_desc"
                                value="{{ old('material_desc', $record->material_desc) }}" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Received Qty</label>
                            <input type="number" name="rec_qty" value="{{ old('rec_qty', $record->rec_qty) }}"
                                class="form-control" min="0" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            <a href="{{ route('op-kitting.history', ['tanggal' => $record->recordMaterialTrans->created_at->format('Y-m-d'), 'model' => $record->recordMaterialTrans->model]) }}"
                                class="btn btn-danger btn-sm">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection
