@extends('layouts.app')

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
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                @if (Auth::user()->employee->department == 'IT')
                    <!-- Left side columns -->
                    <div class="col-lg-12">
                        <div class="row">

                            <!-- Sales Card -->
                            <div class="col-xxl-6 col-md-12">
                                <div class="card info-card sales-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Employees</h5>

                                        <div class="d-flex align-items-center">
                                            <div
                                                class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-people"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>{{ $totalEmployees }}</h6>
                                                <span class="small pt-1">Active Employees</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div><!-- End Sales Card -->

                            <!-- Customers Card -->
                            <div class="col-xxl-6 col-md-12">

                                <div class="card info-card customers-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Users</h5>

                                        <div class="d-flex align-items-center">
                                            <div
                                                class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-people"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>{{ $totalUsers }}</h6>
                                                <span class="small pt-1">Active Users</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- End Customers Card -->
                        </div>
                    </div><!-- End Left side columns -->
                @endif
                <div class="col-xl-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active profile-overview" id="profile-overview"
                                    role="tabpanel">
                                    <h5 class="card-title">Profile Details</h5>

                                    <div class="row">
                                        <div class="col-lg-6 col-md-4 fw-bold label">Employee Name</div>
                                        <div class="col-lg-6 col-md-8">{{ Auth::user()->name }}</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 col-md-4 fw-bold label">NIK</div>
                                        <div class="col-lg-6 col-md-8">{{ Auth::user()->nik }}</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 col-md-4 fw-bold label">Email</div>
                                        <div class="col-lg-6 col-md-8">{{ Auth::user()->email }}</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 col-md-4 fw-bold label">Department</div>
                                        <div class="col-lg-6 col-md-8">{{ Auth::user()->employee->department }}</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 col-md-4 fw-bold label">Section</div>
                                        <div class="col-lg-6 col-md-8">{{ Auth::user()->employee->section }}</div>
                                    </div>
                                </div>
                            </div><!-- End Bordered Tabs -->

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>
    <!-- End #main -->
@endsection
