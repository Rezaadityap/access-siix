@extends('layouts.app')

@section('title')
    Kitting Production 1
@endsection

@section('content')
    <main id="main" class="main">
        <section class="section dashboard">
            <div class="container">
                <div class="row">
                    <div class="pagetitle">
                        <h1>Kitting Production 1</h1>
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Kitting Production 1</li>
                            </ol>
                        </nav>
                    </div><!-- End Page Title -->
                    <div class="col-lg-6">
                        <div class="card shadow-lg border-0 rounded-4 card-hover-effect">
                            <div class="card-body p-4">
                                <h4 class="card-title text-center mb-4 fw-bolder gradient-text-primary">
                                    Input & Scan
                                </h4>
                                <div class="d-grid gap-3">
                                    <button class="btn btn-gradient-info py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#poNumber" id="reset-po">
                                        <i class="bi bi-plus-circle me-2"></i> New PO Number
                                    </button>
                                    <button class="btn btn-gradient-success py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-target="#scanSmd" data-bs-toggle="modal">
                                        <i class="bi bi-bookshelf me-2"></i> Scan Rack SMD Production
                                    </button>
                                    <button class="btn btn-gradient-primary py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#scanMaterial">
                                        <i class="bi bi-qr-code-scan me-2"></i> Scan WH Material
                                    </button>
                                    <button class="btn btn-gradient-danger py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#scanSto">
                                        <i class="bi bi-box-seam me-2"></i> Scan STO Material
                                    </button>
                                    <button class="btn btn-gradient-warning py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#scanMar">
                                        <i class="bi bi-caret-right-square me-2"></i> Scan Material After Running
                                    </button>
                                    <button class="btn btn-gradient-secondary py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#searchPO">
                                        <i class="bi bi-search me-2"></i> Search PO Number
                                    </button>
                                    @if (Auth::user()->level_id !== null)
                                        <a href="{{ route('op-kitting.history') }}"
                                            class="btn btn-gradient-custom py-2 fw-bold rounded-3 shadow-sm">
                                            <i class="bi bi-clock-history me-2"></i> History Record
                                        </a>
                                    @endif
                                    <button class="btn btn-gradient-purple py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#scanMismatchModal">
                                        <i class="bi bi-boxes me-2"></i> Scan Stock
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mx-auto">
                        <div class="card shadow-lg border-0 rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-center align-items-center mb-3">
                                    <h5
                                        class="card-title fw-bolder gradient-text-primary mb-0 d-flex align-items-center text-center">
                                        Record Material Information
                                    </h5>
                                </div>
                                <hr class="mt-0 mb-4 text-muted">
                                <form>
                                    <div id="infoContainer"></div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label for="infoProduction" class="form-label small text-muted mb-1">PRODUCTION
                                                AREA</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Production Area" disabled id="infoProduction">
                                        </div>
                                        <div class="col-6">
                                            <label for="infoLine" class="form-label small text-muted mb-1">LINE AREA</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Line Area" id="infoLine" disabled>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <label for="infoChecker"
                                                class="form-label small text-muted mb-1">Checker</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Checker" id="infoChecker" disabled>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="infoDate" class="form-label small text-muted mb-1">DATE</label>
                                            <input type="date"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Date" id="infoDate" disabled>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <label for="infoLotSize" class="form-label small text-muted mb-1">LOT
                                                SIZE</label>
                                            <input type="number"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Lot Size" id="infoLotSize" disabled>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="infoActual" class="form-label small text-muted mb-1">ACTUAL LOT
                                                SIZE</label>
                                            <input type="number"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Act Lot Size" id="infoActual" disabled>
                                        </div>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-center text-end gap-1">
                                    @if (Auth::user()->level_id !== null)
                                        <button id="btnEditInfo"
                                            class="btn btn-sm btn-gradient-success text-white rounded-pill px-4 shadow-sm">Edit</button>
                                        <button
                                            class="btn btn-sm btn-gradient-danger text-white rounded-pill px-4 shadow-sm"
                                            id="deletePO">
                                            Delete
                                        </button>
                                    @else
                                        <button id="nextProcess"
                                            class="btn btn-sm btn-gradient-primary text-white rounded-pill px-4 shadow-sm">Save
                                            Record</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Table Record --}}
            @include('table.table-record')
            {{-- Table History --}}
            @include('table.table-history-batch')
        </section>
        {{-- Modal Import PO --}}
        @include('modal.kitting.import-kitting-prod1')
        {{-- Modal Scan SMD --}}
        @include('modal.kitting.scan-smd')
        {{-- Modal Scan WH --}}
        @include('modal.kitting.scan-wh')
        {{-- Modal Scan STO --}}
        @include('modal.kitting.scan-sto')
        {{-- Modal Scan MAR --}}
        @include('modal.kitting.scan-mar')
        {{-- Modal search record material --}}
        @include('modal.kitting.search-record')
        {{-- Modal Scan Stock --}}
        @include('modal.kitting.scan-stock')
        {{-- Modal edit info --}}
        @include('modal.kitting.edit-information')
    </main>
    @push('script')
        <script src="{{ asset('assets/js/kitting/import-kitting.js') }}" defer></script>
        <script src="{{ asset('assets/js/kitting/render-save-records.js') }}" defer></script>
        <script src="{{ asset('assets/js/kitting/update-info-fields.js') }}" defer></script>
        <script src="{{ asset('assets/js/kitting/scan-handler.js') }}" defer></script>
        <script src="{{ asset('assets/js/kitting/search-record.js') }}" defer></script>
        <script src="{{ asset('assets/js/kitting/history-load.js') }}" defer></script>
        <script src="{{ asset('assets/js/kitting/collapse-button.js') }}" defer></script>
    @endpush
@endsection
