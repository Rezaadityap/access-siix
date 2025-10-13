@extends('layouts.app')

@section('title')
    OP Kitting
@endsection

@section('content')
    <main id="main" class="main">
        <section class="section dashboard">
            <div class="d-flex justify-content-center align-items-center">
                <div class="card text-center p-4" style="max-width: 450px;">
                    <i class="bi bi-tools" style="font-size: 4rem; color: #ffc107;"></i>
                    <h2 class="mt-3">Sedang Dalam Pengembangan</h2>
                    <p class="text-muted">Halaman ini sedang dikembangkan. Silakan kembali nanti.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary mt-3">Kembali ke Beranda</a>
                </div>
            </div>
        </section>
    </main>
@endsection
