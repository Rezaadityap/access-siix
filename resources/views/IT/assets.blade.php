@extends('layouts.app')

@section('title')
    Assets
@endsection

@section('content')
    <main class="main" id="main">
        <div class="pagetitle">
            <h1>Assets</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Assets</li>
                </ol>
            </nav>
        </div>
    </main>
@endsection
