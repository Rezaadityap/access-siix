<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>@yield('title')</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    {{-- Fetch JS --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="upload-url" content="{{ route('record-material.upload') }}">
    <meta name="store-url" content="{{ route('record-material.store') }}">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/logo/logo-siix.png') }}" rel="icon">
    <link href="{{ asset('assets/img/logo/logo-siix.png') }}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

    {{-- Datatable --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css" />

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>

<body>
    @include('components.header')

    <!-- ======= Sidebar ======= -->
    @include('components.sidebar')

    {{-- Main Content --}}
    @yield('content')

    <!-- ======= Footer ======= -->
    @include('components.footer')

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chart.js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/quill/quill.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>

    <!-- Template Main JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- CDN JQUERY --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // PIC List
            $('#tablePic').DataTable({
                pageLength: 5,
                dom: '<"d-flex justify-content-between"f>rtip',
                initComplete: function(settings, json) {
                    const wrapper = $('#tablePic_wrapper');
                    const title = $('<h6 class="fw-bold mb-0 text-success pt-2">PIC List</h6>');
                    wrapper.find('div.d-flex').prepend(title);
                }
            });

            // Absent List
            $('#tableAbsent').DataTable({
                pageLength: 5,
                dom: '<"d-flex justify-content-between"f>rtip',
                initComplete: function(settings, json) {
                    const wrapper = $('#tableAbsent_wrapper');
                    const title = $('<h6 class="fw-bold mb-0 text-danger pt-2">Absent List</h6>');
                    wrapper.find('div.d-flex').prepend(title);
                }
            });

            $(document).ready(function() {
                const breadcrumbData = @json($breadcrumb ?? []);

                $('#WiTable').DataTable({
                    pageLength: 5,
                    dom: '<"d-flex justify-content-between align-items-center"f>rtip',
                    initComplete: function(settings, json) {
                        const wrapper = $('#WiTable_wrapper');
                        const flexContainer = wrapper.find('div.d-flex');

                        const breadcrumb = $(
                            '<nav aria-label="breadcrumb" class="mb-0"><ol class="breadcrumb mb-0"></ol></nav>'
                        );
                        const ol = breadcrumb.find('ol');

                        ol.append(`
                <li class="breadcrumb-item">
                    <i class="bi bi-folder2"></i>
                    <a href="{{ route('wi-document.index') }}">WI Document</a>
                </li>
            `);

                        breadcrumbData.forEach((item, index) => {
                            const isLast = index === breadcrumbData.length - 1;
                            const icon = isLast ? 'bi-folder2' : 'bi-folder2';
                            const active = isLast ? 'active" aria-current="page' : '';
                            const url = isLast ? '#' :
                                `{{ route('wi-document.index') }}?path=${encodeURIComponent(item.path)}`;

                            ol.append(`
                    <li class="breadcrumb-item ${active}">
                        <i class="bi ${icon}"></i>
                        ${isLast ? item.name : `<a href="${url}">${item.name}</a>`}
                    </li>
                `);
                        });

                        flexContainer.prepend(breadcrumb);

                        flexContainer.css({
                            'gap': '1rem',
                            'flex-wrap': 'wrap'
                        });
                    }
                });
            });

        });
    </script>
    @include('sweetalert::alert')
    @stack('script')

</body>

</html>
