@extends('layouts.app')

@section('title')
    OP Kitting
@endsection

@section('content')
    <main id="main" class="main">
        <section class="section dashboard">
            <div class="container">
                <div class="row">
                    <div class="pagetitle">
                        <h1>OP Kitting</h1>
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">OP Kitting</li>
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
                                        data-bs-toggle="modal" data-bs-target="#poNumber">
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
                                    <a href="{{ route('op-kitting.history') }}"
                                        class="btn btn-gradient-custom py-2 fw-bold rounded-3 shadow-sm">
                                        <i class="bi bi-clock-history me-2"></i> History Record
                                    </a>
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
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title fw-bolder gradient-text-primary mb-0 d-flex align-items-center">
                                        Record Material Information
                                    </h5>
                                    <button class="btn btn-sm btn-danger text-white rounded-pill px-4 shadow-sm"
                                        id="deletePO">
                                        Delete
                                    </button>
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
                                                value="{{ Auth::user()->name }}">
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
        @include('modal.import-po')
        {{-- Modal search record material --}}
        @include('modal.search-record')
        {{-- Scan Rack SMD --}}
        @include('modal.scan-rack-smd')
        {{-- Modal scan WH Material --}}
        @include('modal.scan-material')
        {{-- Modal Scan STO --}}
        @include('modal.scan-sto')
        {{-- Modal Scan MAR --}}
        @include('modal.scan-mar')
        {{-- Moda Scan Mismatch --}}
        @include('modal.scan-mismatch')
    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/render-saved-po.js') }}" defer></script>
    <script src="{{ asset('assets/js/update-info-fields.js') }}" defer></script>
    <script src="{{ asset('assets/js/history-load.js') }}" defer></script>
    <script src="{{ asset('assets/js/import-po.js') }}" defer></script>
    <script src="{{ asset('assets/js/search-po.js') }}" defer></script>
    <script src="{{ asset('assets/js/init-po.js') }}" defer></script>
    <script src="{{ asset('assets/js/scan-handler.js') }}" defer></script>
    <script src="{{ asset('assets/js/copy-batch.js') }}" defer></script>
@endsection
