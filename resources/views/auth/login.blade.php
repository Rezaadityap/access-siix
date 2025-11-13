<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Favicons -->
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

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 3rem;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .login-container::before {
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

        .login-container::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background-color: #ff9800;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .logo {
            width: 100px;
            margin-bottom: 1rem;
        }

        .title {
            font-size: 2.5rem;
            font-weight: 200;
            letter-spacing: 4px;
            color: #333;
            margin-bottom: 2rem;
        }

        .form-group-custom {
            position: relative;
            display: flex;
            align-items: center;
            border-bottom: 2px solid #ccc;
            margin-bottom: 1rem;
            transition: border-bottom-color 0.3s ease;
        }

        .password-toggle {
            font-size: 1.2rem;
            color: #aaa;
        }

        .form-group-custom:has(input:focus) {
            border-bottom-color: #0d6efd;
        }

        .form-group-custom i {
            font-size: 1.2rem;
            color: #aaa;
            padding-right: 10px;
            transition: color 0.3s ease;
        }

        .form-group-custom:has(input:focus) i {
            color: #0d6efd;
        }

        .form-control-custom {
            border: none;
            background: transparent;
            outline: none;
            box-shadow: none;
            width: 100%;
            padding: 10px 5px;
            font-size: 1rem;
            color: #333;
        }

        .form-group-custom label {
            position: absolute;
            top: 10px;
            left: 35px;
            color: #aaa;
            pointer-events: none;
            transition: all 0.2s ease-out;
        }

        .form-control-custom:focus+label,
        .form-control-custom:valid+label {
            top: -15px;
            font-size: 0.8rem;
            color: #0d6efd;
        }

        .btn-signin {
            font-size: 1rem;
            padding: 0.6rem 1.5rem;
            display: inline-flex;
            align-items: center;
        }

        .btn-signin i {
            margin-right: 8px;
        }

        .footer-text {
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <img src="{{ asset('assets/img/siix-access-banner.png') }}" alt="Siix Logo" class="logo" style="width: 300px">
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group-wrapper mb-3">
                <div class="form-group-custom">
                    <i class="bi bi-person"></i>
                    <input type="text" id="username" class="form-control-custom" name="nik" required>
                    <label for="username">Username</label>
                </div>
                @error('nik')
                    <small class="text-danger mt-1 d-block">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group-wrapper mb-3">
                <div class="form-group-custom">
                    <i class="bi bi-lock"></i>
                    <input type="password" id="password" class="form-control-custom" name="password" required>
                    <label for="password">Password</label>
                    <i class="bi bi-eye password-toggle" style="cursor:pointer; position:absolute; right:10px;"></i>
                </div>
                @error('password')
                    <small class="text-danger mt-1 d-block">{{ $message }}</small>
                @enderror
            </div>

            <div class="text-start">
                <button type="submit" class="btn btn-primary btn-signin">
                    <i class="bi bi-box-arrow-in-right"></i> Sign in
                </button>
            </div>
        </form>

        <div class="d-flex justify-content-between footer-text">
            <span>Copyright © 2025 Siix Access</span>
            <span>V.1.0</span>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('.password-toggle');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
    @include('sweetalert::alert')

</body>

</html>
