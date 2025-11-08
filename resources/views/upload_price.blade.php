<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Price Unit</title>

    <link href="{{ asset('assets/img/logo/logo-siix.png') }}" rel="icon">
    <link href="{{ asset('assets/img/logo/logo-siix.png') }}" rel="apple-touch-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f2f2f2;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .import-container {
            width: 100%;
            max-width: 450px;
            padding: 3rem;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .import-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background-color: #0d6efd;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .logo {
            width: 100px;
            margin-bottom: 1rem;
        }

        .title {
            font-size: 2rem;
            font-weight: 300;
            letter-spacing: 2px;
            color: #333;
            margin-bottom: 2rem;
        }

        .file-input {
            border: 2px dashed #0d6efd;
            padding: 2rem;
            border-radius: 10px;
            background: #f9f9f9;
            cursor: pointer;
            transition: background 0.3s ease, border-color 0.3s ease;
            position: relative;
        }

        .file-input:hover {
            background: #eef5ff;
            border-color: #0a58ca;
        }

        .file-input input[type="file"] {
            display: none;
        }

        .file-name {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #333;
            font-weight: 500;
        }

        .btn-import {
            font-size: 1rem;
            padding: 0.6rem 1.5rem;
            margin-top: 1.5rem;
        }

        .footer-text {
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="import-container">
        <img src="{{ asset('assets/img/siix-access-banner.png') }}" alt="Siix Logo" class="logo">
        <h1 class="title">IMPORT PRICE UNIT</h1>

        {{-- Pesan sukses / error --}}
        @if (session('success'))
            <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger py-2">{{ session('error') }}</div>
        @endif

        {{-- Form Upload --}}
        <form action="{{ route('upload-price') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label class="file-input w-100" for="file" id="file-label">
                <i class="bi bi-upload" style="font-size: 2rem; color: #0d6efd;"></i>
                <p class="mt-2 mb-0">Klik untuk memilih file Excel</p>
                <input type="file" id="file" name="file" accept=".xlsx,.xls" required>
                <div class="file-name" id="file-name">Belum ada file dipilih</div>
            </label>

            @error('file')
                <small class="text-danger mt-2 d-block">{{ $message }}</small>
            @enderror

            <button type="submit" class="btn btn-primary btn-import">
                Import File
            </button>
        </form>

        <div class="footer-text">
            <span>Copyright © 2025 Siix Access</span> | <span>V.1.0</span>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('file');
        const fileName = document.getElementById('file-name');

        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileName.textContent = this.files[0].name;
            } else {
                fileName.textContent = "Belum ada file dipilih";
            }
        });
    </script>

</body>

</html>
