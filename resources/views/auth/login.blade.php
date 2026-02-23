<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'Inventory Inspektorat Jendral KESDM') }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --esdm-blue: #1e3c72;
            --esdm-blue-light: #2a5298;
            --esdm-gold: #f5b041;
            --overlay-dark: rgba(15, 32, 39, 0.85);
            --overlay-blue: rgba(30, 60, 114, 0.85);
        }

        body {
            /* Menggunakan gambar gedung sebagai background utama dengan efek overlay gelap-biru */
            background: linear-gradient(135deg, var(--overlay-blue) 0%, var(--overlay-dark) 100%), 
                        url('/images/login-bg.png') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            position: relative;
        }
        
        .login-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            margin: 20px;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255,255,255,0.15);
        }
        
        .login-header {
            /* Menampilkan kembali gambar gedung di area header dengan perpaduan gradasi */
            background: linear-gradient(to bottom, rgba(30, 60, 114, 0.75), rgba(42, 82, 152, 0.95)), 
                        url('/images/login-bg.png') center center / cover no-repeat;
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
            position: relative;
            border-bottom: 4px solid var(--esdm-gold);
        }
        
        .logo-icon {
            font-size: 2.5rem;
            color: var(--esdm-gold);
            background: rgba(255, 255, 255, 0.15);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            margin: 0 auto 1.2rem auto;
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        
        .login-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.4);
        }
        
        .login-header p {
            margin: 0.5rem 0 0 0;
            color: #e2e8f0;
            font-weight: 400;
            font-size: 0.95rem;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .login-header small {
            display: inline-block;
            margin-top: 0.6rem;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
            letter-spacing: 0.5px;
            backdrop-filter: blur(4px);
        }
        
        .login-body {
            padding: 2.5rem 2rem 2rem;
            background: #ffffff;
        }
        
        .form-floating {
            margin-bottom: 1.2rem;
        }
        
        .form-control {
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            padding: 1rem 0.75rem;
            font-size: 1rem;
            color: #334155;
            background: #f8fafc;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--esdm-blue-light);
            box-shadow: 0 0 0 4px rgba(42, 82, 152, 0.1);
            background: #ffffff;
        }
        
        .form-floating > label {
            color: #64748b;
            font-weight: 500;
            padding: 1rem 0.75rem;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--esdm-blue) 0%, var(--esdm-blue-light) 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.25);
            margin-top: 0.5rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 60, 114, 0.4);
            color: white;
            background: linear-gradient(135deg, #152c5b 0%, var(--esdm-blue) 100%);
        }
        
        .password-field {
            position: relative;
        }
        
        .password-field .form-control {
            padding-right: 3rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            z-index: 5;
            padding: 0.5rem;
            border-radius: 8px;
            transition: color 0.2s ease;
            height: 100%;
            display: flex;
            align-items: center;
        }
        
        .password-toggle:hover {
            color: var(--esdm-blue);
        }
        
        .form-check {
            margin-bottom: 1.5rem !important;
            display: flex;
            align-items: center;
        }
        
        .form-check-label {
            color: #64748b;
            font-weight: 500;
            font-size: 0.9rem;
            margin-top: 2px;
            cursor: pointer;
        }
        
        .form-check-input {
            border-radius: 4px;
            border: 1.5px solid #cbd5e1;
            margin-right: 0.5rem;
            cursor: pointer;
            width: 1.1em;
            height: 1.1em;
            margin-top: 0;
        }

        .form-check-input:checked {
            background-color: var(--esdm-blue);
            border-color: var(--esdm-blue);
        }
        
        /* Alerts Styling */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border-left: 4px solid #22c55e;
        }
        
        .login-footer {
            text-align: center;
            padding: 1.5rem;
            color: #64748b;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }
        
        .login-footer p strong {
            color: #475569;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 15px;
                border-radius: 16px;
            }
            .login-header { padding: 2rem 1.5rem 1.5rem; }
            .logo-icon { width: 65px; height: 65px; line-height: 65px; font-size: 2rem; }
            .login-body { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-icon">
                <i class="bi bi-boxes"></i>
            </div>
            <h2>Inventory Inspektorat Jendral KESDM</h2>
            <p>Sistem Manajemen Inventori </p>
            <small style="opacity: 0.9; font-size: 0.85rem;">Inspektorat Jendral KESDM</small>
        </div>
        
        <div class="login-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                    <div>
                        <strong>Login Gagal!</strong>
                        <ul class="mb-0 mt-1 ps-3" style="font-size: 0.85rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2 mt-1"></i>
                    <div>
                        <strong>Berhasil!</strong> {{ session('status') }}
                    </div>
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                    <div>
                        <strong>Error!</strong> {{ session('error') }}
                    </div>
                </div>
            @endif
            
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
                    <button type="button" class="password-toggle" onclick="togglePassword()" tabindex="-1">
                        <i class="bi bi-eye" id="passwordToggleIcon"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        Ingat Saya
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
        
        <div class="login-footer">
            <p class="mb-1">
                <i class="bi bi-shield-check me-1 text-success"></i>
                <strong>Akses Aman & Terpercaya</strong>
            </p>
            <p class="mb-0" style="font-size: 0.8rem;">
                © {{ date('Y') }} Inspektorat Jendral KESDM<br>Sistem Inventory Management
            </p>
        </div>
    </div>
    
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
                errorHtml += '<i class="bi bi-exclamation-circle me-2 mt-1"></i>';
                errorHtml += '<div><strong>Error!</strong> Silakan isi semua field yang diperlukan.</div>';
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
                errorHtml += '<i class="bi bi-exclamation-circle me-2 mt-1"></i>';
                errorHtml += '<div><strong>Error!</strong> Please fill in all required fields.</div>';
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
                loginBtnText.textContent = 'Masuk ke Sistem';
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
                loginContainer.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
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