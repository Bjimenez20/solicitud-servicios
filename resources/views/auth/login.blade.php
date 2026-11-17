<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <title>Iniciar sesión</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            background: #10152b;
        }

        /* Fondo */
        .background {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px 15px;
            position: relative;
            overflow: hidden;

            background:
                radial-gradient(circle at 10% 10%, #176ba5 0%, transparent 35%),
                radial-gradient(circle at 90% 90%, #012e52 0%, transparent 35%),
                linear-gradient(135deg, #024880, #01345c);
        }

        /* Círculos decorativos */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .04);
            pointer-events: none;
        }

        .circle-one {
            width: 400px;
            height: 400px;
            top: -180px;
            left: -100px;
        }

        .circle-two {
            width: 350px;
            height: 350px;
            bottom: -150px;
            right: -100px;
        }

        /* Contenedor principal */
        .login-container {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border-radius: 24px;
            padding: 48px 45px;
            position: relative;
            z-index: 2;

            box-shadow: 0 30px 90px rgba(0, 0, 0, .35);

            animation: appear .7s ease;
        }

        /* Encabezado */
        .form-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .brand-icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 22px;
            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 20px;
            color: white;
            font-size: 29px;

            background: linear-gradient(135deg, #6250dc, #9251d2);
            box-shadow: 0 10px 25px rgba(98, 80, 220, .25);
        }

        .form-header span {
            color: #024880;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .form-header h2 {
            font-size: 29px;
            color: #024880;
            font-weight: 800;
            margin: 12px 0;
        }

        .form-header p {
            font-size: 13px;
            color: #999bad;
            line-height: 1.7;
            margin-bottom: 0;
        }

        /* Mensaje de sesión */
        .status-message {
            background: #eefaf3;
            border: 1px solid #c8ead5;
            color: #218653;
            border-radius: 10px;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        /* Campos */
        .input-group-custom {
            position: relative;
            margin-bottom: 23px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 700;
            color: #363950;
            margin-bottom: 10px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 17px;
            transform: translateY(-50%);
            color: #999bb0;
            font-size: 18px;
            z-index: 3;
        }

        .form-control-custom {
            width: 100%;
            height: 55px;
            border-radius: 12px;
            border: 1px solid #e6e7ef;
            background: #f8f8fc;
            padding: 0 48px;
            outline: none;
            font-size: 13px;
            color: #282a40;
            transition: .3s;
        }

        .form-control-custom:focus {
            border-color: #024880;
            background: white;
            box-shadow: 0 0 0 4px rgba(118, 86, 223, .12);
        }

        .form-control-custom::placeholder {
            color: #b4b5c4;
        }

        /* Mostrar contraseña */
        .password-eye {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #999bb0;
            font-size: 18px;
            cursor: pointer;
            z-index: 4;
        }

        .password-eye:hover {
            color: #024880;
        }

        /* Opciones */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin: 5px 0 28px;
            flex-wrap: wrap;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #77798e;
            font-size: 12px;
            cursor: pointer;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: #024880;
        }

        .forgot-password {
            color: #024880;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        /* Botón */
        .btn-login {
            width: 100%;
            height: 55px;
            border: none;
            border-radius: 12px;

            background: linear-gradient(100deg, #024880, #024880);

            color: white;
            font-size: 14px;
            font-weight: 700;

            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;

            cursor: pointer;
            transition: .3s;

            box-shadow: 0 8px 20px rgba(98, 80, 220, .25);
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(98, 80, 220, .35);
            color: white;
        }

        .btn-login i {
            transition: .3s;
        }

        .btn-login:hover i {
            transform: translateX(5px);
        }

        /* Seguridad */
        .security-info {
            margin-top: 30px;
            text-align: center;
            color: #a3a4b4;
            font-size: 11px;
        }

        .security-info i {
            color: #32b98c;
            margin-right: 5px;
        }

        /* Errores Laravel */
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 6px;
        }

        /* Footer */
        .login-footer {
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid #f0f0f5;
            text-align: center;
            color: #b0b1bf;
            font-size: 10px;
        }

        /* Animación */
        @keyframes appear {
            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media(max-width: 480px) {
            .background {
                padding: 20px 14px;
            }

            .login-container {
                max-width: 100%;
                padding: 38px 25px;
                border-radius: 20px;
            }

            .form-header h2 {
                font-size: 26px;
            }

            .brand-icon {
                width: 60px;
                height: 60px;
                font-size: 25px;
            }
        }

        @media(max-width: 360px) {
            .login-container {
                padding: 32px 19px;
            }

            .form-options {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media(prefers-reduced-motion: reduce) {
            .login-container {
                animation: none;
            }

            * {
                transition: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="background">

        <div class="circle circle-one"></div>
        <div class="circle circle-two"></div>

        <div class="login-container">
            <div class="form-header">
                <img src="{{ asset('assets/img/Overall 1.png') }}" alt="Logo" class="me-2" style="height: 40px;">
            </div>

            <!-- MENSAJE DE SESIÓN -->
            @if (session('status'))
            <div class="status-message">
                <i class="bi bi-check-circle me-1"></i>
                {{ session('status') }}
            </div>
            @endif

            <!-- FORMULARIO -->
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <!-- EMAIL -->
                <div class="input-group-custom">
                    <label for="email" class="form-label">
                        Correo electrónico
                    </label>
                    <div class="input-wrapper">

                        <i class="bi bi-envelope input-icon"></i>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control-custom"
                            placeholder="ejemplo@correo.com"
                            required
                            autofocus
                            autocomplete="username">

                    </div>

                    @error('email')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                    @enderror

                </div>

                <!-- PASSWORD -->
                <div class="input-group-custom">

                    <label for="password" class="form-label">
                        Contraseña
                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-lock input-icon"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control-custom"
                            placeholder="Ingresa tu contraseña"
                            required
                            autocomplete="current-password">

                        <button
                            type="button"
                            class="password-eye"
                            onclick="togglePassword()"
                            aria-label="Mostrar contraseña">

                            <i class="bi bi-eye" id="eyeIcon"></i>

                        </button>

                    </div>

                    @error('password')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                    @enderror

                </div>
                <!-- OPCIONES -->
                <div class="form-options">

                    <label for="remember_me" class="remember">

                        <input
                            type="checkbox"
                            id="remember_me"
                            name="remember">

                        <span>Recordarme</span>

                    </label>

                    @if (Route::has('password.request'))
                    <!-- <a
                        href="{{ route('password.request') }}"
                        class="forgot-password">

                        ¿Olvidaste tu contraseña?

                    </a> -->
                    @endif
                </div>
                <!-- BOTÓN -->
                <button type="submit" class="btn-login">
                    Iniciar sesión
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
    <!-- JAVASCRIPT -->
    <script>
        function togglePassword() {

            const password = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');

            if (password.type === 'password') {

                password.type = 'text';

                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');

            } else {

                password.type = 'password';

                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');

            }
        }
    </script>

</body>

</html>