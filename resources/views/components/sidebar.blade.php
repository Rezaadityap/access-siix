<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link {{ Route::is('home') ? '' : 'collapsed' }}" href="{{ route('home') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#"><i class="bi bi-envelope"></i>
                <span>Inbox</span>
            </a>
        </li>
        @if (Auth::user()->employee->department == 'IT')
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#it-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-pc-display"></i><span>IT</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="it-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('assets-it.index') }}">
                            <i class="bi bi-circle"></i><span>Assets</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!-- End Forms Nav -->
        @endif
        <!-- End Dashboard Nav -->
        {{-- <li class="nav-heading">Data Master</li> --}}
        @if (Auth::user()->employee->department !== 'IT')
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-layers-half"></i><span>All Production</span><i
                        class="bi bi-chevron-down ms-auto"></i>
                </a>

                <ul id="components-nav" class="nav-content collapse ps-3" data-bs-parent="#sidebar-nav">
                    <!-- OP Kitting parent -->
                    <li>
                        <a class="nav-link collapsed ps-4" data-bs-target="#kitting-submenu" data-bs-toggle="collapse"
                            href="#">
                            <i class="bi bi-circle"></i><span>Kitting</span><i
                                class="bi bi-chevron-down ms-auto fs-6"></i>
                        </a>

                        <!-- Sub dari OP Kitting -->
                        <ul id="kitting-submenu" class="nav-content collapse ps-4" data-bs-parent="#components-nav">
                            @if (Auth::user()->employee->department === 'PROD.1')
                                <li>
                                    <a href="{{ route('kitting.prod1') }}" class="ps-4">
                                        <i class="bi bi-circle"></i><span>Production 1</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->employee->department === 'PROD.2')
                                <li>
                                    <a href="#" class="ps-4">
                                        <i class="bi bi-circle"></i><span>Production 2</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                    <!-- WI Document -->
                    <li>
                        <a href="{{ route('wi-document.index') }}" class="ps-4">
                            <i class="bi bi-circle"></i><span>WI Document</span>
                        </a>
                    </li>
                </ul>
            </li>

            @if (Auth::user()->level_id !== null)
                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-target="#reports-nav" data-bs-toggle="collapse"
                        href="#">
                        <i class="bi bi-book"></i><span>Reports Production</span><i
                            class="bi bi-chevron-down ms-auto"></i>
                    </a>

                    <ul id="reports-nav" class="nav-content collapse ps-3" data-bs-parent="#sidebar-nav">
                        <!-- Reports Parent -->
                        <li>
                            <a class="nav-link collapsed ps-4" data-bs-target="#reports-submenu"
                                data-bs-toggle="collapse" href="#">
                                <i class="bi bi-circle"></i><span>Kitting</span><i
                                    class="bi bi-chevron-down ms-auto fs-6"></i>
                            </a>

                            <!-- Sub dari OP Kitting -->
                            <ul id="reports-submenu" class="nav-content collapse ps-4" data-bs-parent="#components-nav">
                                <li>
                                    <a href="{{ route('reports.kitting') }}" class="ps-4">
                                        <i class="bi bi-circle"></i><span>Records</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('reports.kitting.batches') }}" class="ps-4">
                                        <i class="bi bi-circle"></i><span>Batches</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            @endif
        @endif
        <!-- End Components Nav -->

        @if (Auth::user()->employee->department == 'IT')
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-people"></i><span>Users</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('users.index') }}">
                            <i class="bi bi-circle"></i><span>All Users</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!-- End Forms Nav -->
        @endif
        {{-- @if (Auth::user()->employee->department === 'IT')
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-box-fill"></i><span>Finish Good</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="tables-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('fg.checksheet.index') }}">
                            <i class="bi bi-circle"></i><span>Checksheet Export</span>
                        </a>
                    </li>
                    <li>
                        <a href="tables-data.html">
                            <i class="bi bi-circle"></i><span>Documentation Export</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif --}}
        <!-- End Tables Nav -->
    </ul>

</aside>
<!-- End Sidebar-->
