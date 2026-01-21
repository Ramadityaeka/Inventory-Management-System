<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired - {{ config('app.name', 'Inventory ESDM') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            margin: 20px;
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
        
        .error-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 1rem;
        }
        
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            border-radius: 16px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 0.5rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 60, 114, 0.3);
        }
        
        .btn-secondary {
            border-radius: 16px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            margin: 0 0.5rem;
        }
        
        .countdown {
            color: #1e3c72;
            font-weight: 600;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        
        <h1 class="error-title">Session Expired</h1>
        
        <p class="error-message">
            Session Anda telah berakhir untuk alasan keamanan.<br>
            Silakan login kembali untuk melanjutkan.
        </p>
        
        <div class="d-flex justify-content-center flex-wrap">
            <a href="{{ route('login') }}" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login Kembali
            </a>
            <button onclick="history.back()" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </button>
        </div>
        
        <div class="countdown">
            Redirect otomatis dalam <span id="countdown">10</span> detik...
        </div>
    </div>
    
    <script>
        // Auto redirect to login after 10 seconds
        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = '{{ route("login") }}';
            }
        }, 1000);
    </script>
</body>
</html>