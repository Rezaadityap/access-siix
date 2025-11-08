<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link {{ Route::is('home') ? '' : 'collapsed' }}" href="{{ route('home') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <!-- End Dashboard Nav -->
        {{-- <li class="nav-heading">Data Master</li> --}}
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-layers-half"></i><span>All Production</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('op-kitting.index') }}">
                        <i class="bi bi-circle"></i><span>OP Kitting</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('wi-document.index') }}">
                        <i class="bi bi-circle"></i><span>WI Document</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#reports" data-bs-toggle="collapse" href="#">
                <i class="bi bi-file-earmark"></i><span>Reports Production</span><i
                    class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="reports" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('reports.kitting') }}">
                        <i class="bi bi-circle"></i><span>Reports Kitting</span>
                    </a>
                </li>
                {{-- <li>
                    <a href="{{ route('reports-wi.index') }}">
                        <i class="bi bi-circle"></i><span>Reports WI</span>
                    </a>
                </li> --}}
            </ul>
        </li>
        <!-- End Components Nav -->

        @if (Auth::user()->employee->department == 'IT')
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-journal-text"></i><span>IT</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    {{-- <li>
                    <a href="forms-elements.html">
                        <i class="bi bi-circle"></i><span>Form Elements</span>
                    </a>
                </li> --}}
                </ul>
            </li>
            <!-- End Forms Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-box-fill"></i><span>Finish Good</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="tables-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="tables-general.html">
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
            <!-- End Tables Nav -->
        @endif
    </ul>

</aside>
<!-- End Sidebar-->
