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

                            <form id="filterForm" method="GET" action="{{ route('users.index') }}"
                                class="d-flex align-items-center my-3 gap-3" autocomplete="off">

                                {{-- Department --}}
                                <select name="department" id="departmentSelect" class="form-control">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept }}"
                                            {{ request('department') == $dept ? 'selected' : '' }}>
                                            {{ $dept }}
                                        </option>
                                    @endforeach
                                </select>
                                <input id="searchInput" type="text" name="search" value="{{ request('search') }}"
                                    class="form-control" placeholder="Search name or NIK..." />

                                <a href="{{ route('users.index') }}" class="btn btn-gradient-secondary">Reset</a>
                            </form>

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
                                                <td>{{ $users->firstItem() + $index }}</td>
                                                <td>{{ $item->nik }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-modern-view btn-custom btn-gradient-primary"
                                                        data-user-id="{{ $item->id }}"
                                                        aria-label="View {{ $item->name }}">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" data-user-id="{{ $item->id }}"
                                                        class="btn btn-custom btn-gradient-success btn-edit-user"
                                                        aria-label="Edit {{ $item->name }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No records found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($users->hasPages())
                                @php
                                    $qs = request()->except('page');
                                    $qsString = $qs ? '&' . http_build_query($qs) : '';
                                    $current = $users->currentPage();
                                    $last = $users->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                @endphp

                                <div class="modern-pagination">
                                    <div class="modern-page-summary">
                                        Showing <strong>{{ $users->firstItem() }}</strong> –
                                        <strong>{{ $users->lastItem() }}</strong>
                                        of <strong>{{ $users->total() }}</strong> users
                                    </div>

                                    <div class="modern-pager">

                                        {{-- Prev --}}
                                        @php $prev = $users->previousPageUrl() ? $users->previousPageUrl() . $qsString : null; @endphp
                                        <a href="{{ $prev ?? '#' }}"
                                            class="modern-page-btn {{ $prev ? '' : 'disabled' }}"
                                            aria-label="Previous">&laquo;</a>

                                        {{-- First page --}}
                                        @if ($start > 1)
                                            <a href="{{ $users->url(1) . $qsString }}" class="modern-page-btn">1</a>
                                            @if ($start > 2)
                                                <span class="modern-ellipsis">…</span>
                                            @endif
                                        @endif

                                        {{-- Middle pages --}}
                                        @for ($i = $start; $i <= $end; $i++)
                                            <a href="{{ $users->url($i) . $qsString }}"
                                                class="modern-page-btn {{ $i == $current ? 'active' : '' }}">
                                                {{ $i }}
                                            </a>
                                        @endfor

                                        {{-- Last page --}}
                                        @if ($end < $last)
                                            @if ($end < $last - 1)
                                                <span class="modern-ellipsis">…</span>
                                            @endif
                                            <a href="{{ $users->url($last) . $qsString }}"
                                                class="modern-page-btn">{{ $last }}</a>
                                        @endif

                                        {{-- Next --}}
                                        @php $next = $users->nextPageUrl() ? $users->nextPageUrl() . $qsString : null; @endphp
                                        <a href="{{ $next ?? '#' }}"
                                            class="modern-page-btn {{ $next ? '' : 'disabled' }}"
                                            aria-label="Next">&raquo;</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Modal view --}}
        @include('modal.users.view')
        {{-- Modal edit --}}
        @include('modal.users.edit')
    </main>
    <script>
        window.USER_SHOW_URL = "{{ url('users') }}";
    </script>
    @push('script')
        <script src="{{ asset('assets/js/users/view.js') }}" defer></script>
        <script src="{{ asset('assets/js/users/index.js') }}" defer></script>
        <script src="{{ asset('assets/js/users/edit.js') }}" defer></script>
    @endpush
@endsection
