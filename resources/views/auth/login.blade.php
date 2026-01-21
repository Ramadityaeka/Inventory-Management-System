<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'Inventory ESDM') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            opacity: 0.3;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 460px;
            margin: 20px;
            position: relative;
            z-index: 1;
        }
        
        .login-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%) translateY(-100%); }
            50% { transform: translateX(0) translateY(0); }
        }
        
        .login-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
            position: relative;
            z-index: 1;
        }
        
        .login-header p {
            margin: 0.8rem 0 0 0;
            opacity: 0.9;
            font-weight: 300;
            position: relative;
            z-index: 1;
        }
        
        .login-body {
            padding: 2.5rem;
            background: white;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-control {
            border-radius: 16px;
            border: 2px solid #e8f0fe;
            padding: 1.2rem 1rem;
            font-size: 1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fafbff;
        }
        
        .form-control:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 0 4px rgba(30, 60, 114, 0.1);
            background: white;
            transform: translateY(-2px);
        }
        
        .form-floating > label {
            padding: 1rem;
            color: #6c7b7f;
            font-weight: 500;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            border-radius: 16px;
            padding: 1.2rem;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(30, 60, 114, 0.4);
            color: white;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .form-check-input:checked {
            background-color: #1e3c72;
            border-color: #1e3c72;
        }
        
        .alert {
            border-radius: 16px;
            border: none;
            margin-bottom: 1.5rem;
            padding: 1rem 1.2rem;
            background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1f2eb 0%, #a3d9cc 100%);
            color: #0f5132;
        }
        
        .logo-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .login-footer {
            text-align: center;
            padding: 1.5rem 2rem 2.5rem;
            color: #6c757d;
            font-size: 0.9rem;
            background: #f8f9ff;
        }
        
        .divider {
            margin: 2rem 0;
            text-align: center;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e9ecef, transparent);
        }
        
        .divider span {
            background: white;
            padding: 0 1.5rem;
            color: #6c757d;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .input-group {
            position: relative;
        }
        
        .password-field .form-control {
            padding-right: 3.5rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #1e3c72;
            background: #f0f4ff;
        }
        
        .form-check {
            margin-bottom: 2rem !important;
        }
        
        .form-check-label {
            color: #5a6c7d;
            font-weight: 500;
        }
        
        .form-check-input {
            border-radius: 6px;
            margin-right: 0.5rem;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                border-radius: 20px;
            }
            
            .login-header {
                padding: 2rem 1.5rem;
            }
            
            .login-body {
                padding: 2rem 1.5rem;
            }
            
            .login-footer {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="logo-icon">
                <i class="bi bi-boxes"></i>
            </div>
            <h2>Inventory ESDM</h2>
            <p>Sistem Manajemen Inventori Terpadu</p>
            <small style="opacity: 0.8; font-size: 0.85rem;">Kementerian Energi dan Sumber Daya Mineral</small>
        </div>
        
        <!-- Body -->
        <div class="login-body">
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Login Gagal!</strong>
                    <ul class="mb-0 mt-2" style="font-size: 0.9rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Berhasil!</strong> {{ session('status') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Error!</strong> {{ session('error') }}
                </div>
            @endif
            
            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                
                <div class="form-floating">
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           placeholder="nama@email.com" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus>
                    <label for="email">
                        <i class="bi bi-person-fill me-2"></i>Alamat Email
                    </label>
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                
                <div class="form-floating password-field">
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="Kata Sandi" 
                           required>
                    <label for="password">
                        <i class="bi bi-key-fill me-2"></i>Kata Sandi
                    </label>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="passwordToggleIcon"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        <i class=""></i>Ingat Saya
                    </label>
                </div>
                
                <button type="submit" class="btn btn-login" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    <span id="loginBtnText">Masuk ke Sistem</span>
                    <span id="loginSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </span>
                </button>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="login-footer">
            <p class="mb-2">
                <i class="bi bi-shield-check me-1"></i>
                <strong>Akses Aman & Terpercaya</strong>
            </p>
            <p class="mb-0" style="font-size: 0.8rem; opacity: 0.7;">
                © {{ date('Y') }} Kementerian ESDM - Sistem Inventory Management
            </p>
            <p class="mb-0 mt-2" style="font-size: 0.75rem; opacity: 0.6;">
                Gunakan kredensial resmi untuk mengakses sistem
            </p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                passwordField.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        }
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            const loginBtnText = document.getElementById('loginBtnText');
            const loginSpinner = document.getElementById('loginSpinner');
            
            // Show loading state
            loginBtn.disabled = true;
            loginBtnText.textContent = 'Memproses...';
            loginSpinner.classList.remove('d-none');
            
            // Basic validation
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                
                let errorHtml = '<div class="alert alert-danger">';
                errorHtml += '<i class="bi bi-exclamation-circle me-2"></i>';
                errorHtml += '<strong>Error!</strong> Silakan isi semua field yang diperlukan.';
                errorHtml += '</div>';
                
                const existingAlert = document.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.outerHTML = errorHtml;
                } else {
                    document.querySelector('.login-body').insertAdjacentHTML('afterbegin', errorHtml);
                }
                
                // Reset button state
                loginBtn.disabled = false;
                loginBtnText.textContent = 'Masuk ke Sistem';
                loginSpinner.classList.add('d-none');
                
                return false;
            }
            
            // Allow normal form submission
            return true;
        });
        
        // Auto-focus email field on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
        
        // Enhanced form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                
                // Show custom error
                let errorHtml = '<div class="alert alert-danger">';
                errorHtml += '<i class="bi bi-exclamation-circle me-2"></i>';
                errorHtml += '<strong>Error!</strong> Please fill in all required fields.';
                errorHtml += '</div>';
                
                // Find existing alerts and replace or add new one
                const existingAlert = document.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.outerHTML = errorHtml;
                } else {
                    document.querySelector('.login-body').insertAdjacentHTML('afterbegin', errorHtml);
                }
                
                // Reset button state
                const loginBtn = document.getElementById('loginBtn');
                const loginBtnText = document.getElementById('loginBtnText');
                const loginSpinner = document.getElementById('loginSpinner');
                
                loginBtn.disabled = false;
                loginBtnText.textContent = 'Sign In';
                loginSpinner.classList.add('d-none');
                
                return false;
            }
        });
        
        // Smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const loginContainer = document.querySelector('.login-container');
            loginContainer.style.opacity = '0';
            loginContainer.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                loginContainer.style.transition = 'all 0.6s ease';
                loginContainer.style.opacity = '1';
                loginContainer.style.transform = 'translateY(0)';
            }, 100);
        });
        
        // Enter key handling
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const activeElement = document.activeElement;
                if (activeElement.id === 'email') {
                    document.getElementById('password').focus();
                } else if (activeElement.id === 'password') {
                    document.getElementById('loginForm').submit();
                }
            }
        });
    </script>
</body>
</html>