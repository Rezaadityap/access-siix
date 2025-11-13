@extends('layouts.app')

@section('title', 'Dashboard - Home')

@section('content')
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Dashboard</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
        <section class="section dashboard">
            <div class="row">
                @if (Auth::user()->employee->department == 'IT')
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-xxl-6 col-md-6 mb-4">
                                <div class="card info-card shadow-lg border-0 h-100 transition-hover dashboard-card-blue">
                                    <div class="card-body p-4">
                                        <div class="row d-flex align-items-center justify-content-center">
                                            <div class="col me-2">
                                                <div class="text-sm fw-bold text-white text-uppercase mb-1">
                                                    Total Active Employees
                                                </div>
                                                <div class="h5 mb-0 text-white">
                                                    {{ $totalEmployees }}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="bi bi-people-fill fs-2 text-white opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-6 col-md-6 mb-4">
                                <div class="card info-card shadow-lg border-0 h-100 transition-hover dashboard-card-orange">
                                    <div class="card-body p-4">
                                        <div class="row d-flex justify-content-center align-items-center">
                                            <div class="col me-2">
                                                <div class="text-sm fw-bold text-white text-uppercase mb-1">
                                                    Total System Users
                                                </div>
                                                <div class="h5 mb-0 text-white">
                                                    {{ $totalUsers }}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="bi bi-person-badge-fill fs-2 text-white opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-xl-12 mt-2">
                    <div class="card mobile-profile-card shadow-xl border-0"
                        style="border-radius: 2rem; overflow: hidden; background: linear-gradient(135deg, #e0f7fa 0%, #bbdefb 100%);">
                        {{-- Card Body --}}
                        <div class="card-body p-4 pt-4 text-dark">
                            <div class="row align-items-start">
                                <div class="col-md-4 text-center mb-4 mb-md-0">
                                    {{-- Photo --}}
                                    <div class="p-1 border border-3 border-orange rounded-circle mx-auto mb-3 shadow-sm"
                                        style="width: 140px; height: 140px;">
                                        <img src="{{ 'http://192.168.61.8/photos/employee/' . Auth::user()->employee->photo }}"
                                            alt="Profile Photo" class="rounded-circle"
                                            style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                    </div>

                                    <h4 class="mt-3 fw-bolder text-dark">{{ Auth::user()->name }}</h4>

                                    {{-- Status Badge --}}
                                    <span class="badge px-3 py-2 rounded-pill shadow-sm text-white"
                                        style="background-color: #ff9800;">
                                        <i class="bi bi-building me-1"></i> {{ Auth::user()->employee->department }}
                                    </span>
                                </div>

                                <div class="col-md-8">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3">Employee Details</h6>

                                    <div class="row g-3">
                                        {{-- Detail Box --}}
                                        <div class="col-sm-6">
                                            <div class="detail-box p-3 rounded-xl border-0 shadow-sm"
                                                style="background-color: rgba(255, 255, 255, 0.7); border-left: 5px solid #4e73df !important;">
                                                <p class="text-primary small mb-0 fw-semibold">NIK</p>
                                                <h5 class="fw-bold mb-0 text-dark">{{ Auth::user()->nik }}</h5>
                                            </div>
                                        </div>

                                        {{-- Detail Box --}}
                                        <div class="col-sm-6">
                                            <div class="detail-box p-3 rounded-xl border-0 shadow-sm"
                                                style="background-color: rgba(255, 255, 255, 0.7); border-left: 5px solid #1cc88a !important;">
                                                <p class="text-success small mb-0 fw-semibold">Email</p>
                                                <h6 class="fw-bold mb-0 text-dark text-truncate">{{ Auth::user()->email }}
                                                </h6>
                                            </div>
                                        </div>

                                        {{-- Detail Box --}}
                                        <div class="col-sm-6">
                                            <div class="detail-box p-3 rounded-xl border-0 shadow-sm"
                                                style="background-color: rgba(255, 255, 255, 0.7); border-left: 5px solid #ff9800 !important;">
                                                <p class="text-orange small mb-0 fw-semibold"
                                                    style="color: #ff9800 !important;">Section</p>
                                                <h5 class="fw-bold mb-0 text-dark">{{ Auth::user()->employee->section }}
                                                </h5>
                                            </div>
                                        </div>

                                        {{-- Detail Box --}}
                                        <div class="col-sm-6">
                                            <div class="detail-box p-3 rounded-xl border-0 shadow-sm"
                                                style="background-color: rgba(255, 255, 255, 0.7); border-left: 5px solid #6c757d !important;">
                                                <p class="text-secondary small mb-0 fw-semibold">Status</p>
                                                <h5 class="fw-bold mb-0 text-dark">Active</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="card-footer bg-white text-center p-3 border-0"
                            style="border-bottom-left-radius: 2rem; border-bottom-right-radius: 2rem;">
                            <p class="text-muted small mb-0">
                                PT SIIX EMS INDONESIA
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>
    <style>
        .transition-hover {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .transition-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15) !important;
        }

        .rounded-xl {
            border-radius: 1rem !important;
        }

        .dashboard-card-blue {
            background: linear-gradient(45deg, #4e73df 0%, #748DAE 100%);
        }

        .dashboard-card-orange {
            background: linear-gradient(45deg, #f6c23e 0%, #f9845b 100%);
        }

        .border-orange {
            border-color: #ff9800 !important;
        }

        .text-xs {
            font-size: 0.75rem;
        }
    </style>
@endsection
