@extends('layouts.app')
@section('title')
    All Users
@endsection

@section('content')
    <main class="main" id="main">
        <div class="pagetitle">
            <h1>Users</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">All Users</li>
                </ol>
            </nav>
        </div>
        <div class="flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-2">
                        <div class="card-body">
                            <h5 class="card-title mb-0">Users list</h5>

                            {{-- Filter Form --}}
                            {{-- <form method="GET" action="{{ route('op-kitting.history') }}"
                                class="d-flex align-items-center my-2 gap-2">
                                <input type="date" name="date" class="form-control" value="{{ $date }}"
                                    onchange="this.form.submit()">

                                <select name="model" id="modelSelect" class="form-control" onchange="this.form.submit()"
                                    {{ $models->isEmpty() ? 'disabled' : '' }}>
                                    <option value="">Select Model</option>
                                    @foreach ($models as $model)
                                        <option value="{{ $model }}"
                                            {{ $selectedModel == $model ? 'selected' : '' }}>
                                            {{ $model }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="2" id="chkDeleteStatus1">
                                    <label class="form-check-label" for="chkDeleteStatus1">
                                        Deleted
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="chkUpdateStatus2">
                                    <label class="form-check-label" for="chkUpdateStatus2">
                                        Updated
                                    </label>
                                </div>
                            </div> --}}

                            {{-- Table --}}
                            <div class="table-responsive mt-3">
                                <table
                                    class="table table-bordered table-sm text-center align-middle sticky-header history-material-table"
                                    id="usersTable">
                                    <thead class="align-middle text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>NIK</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->nik }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>
                                                    <button id="{{ $item->id }}"
                                                        class="btn btn-custom btn-gradient-success"><i
                                                            class="bi bi-pencil-square"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No records found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
