<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('page-title', 'Dashboard')</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
            --topbar-height: 60px;
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar .brand {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            background: rgba(255,255,255,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar .brand h5 {
            margin: 0;
            font-weight: 600;
        }

        .sidebar .nav {
            padding: 20px 0;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            transition: all 0.3s ease;
            border: none;
            background: none;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left: 4px solid white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }

        /* Topbar */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: white;
            border-bottom: 1px solid #e9ecef;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: left 0.3s ease;
        }

        .topbar.sidebar-collapsed {
            left: 0;
        }

        .topbar .navbar {
            height: 100%;
            padding: 0 20px;
        }

        .topbar .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--secondary-color);
            margin-right: 15px;
        }

        .topbar .page-title {
            font-weight: 600;
            color: var(--secondary-color);
        }

        /* Content */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            min-height: calc(100vh - var(--topbar-height));
            transition: margin-left 0.3s ease;
        }

        .main-content.sidebar-collapsed {
            margin-left: 0;
        }

        .content-wrapper {
            padding: 30px;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        /* Colored card borders */
        .card-border-left-primary {
            border-left: 4px solid #0d6efd !important;
        }

        .card-border-left-success {
            border-left: 4px solid #198754 !important;
        }

        .card-border-left-info {
            border-left: 4px solid #0dcaf0 !important;
        }

        .card-border-left-warning {
            border-left: 4px solid #ffc107 !important;
        }

        .card-border-left-danger {
            border-left: 4px solid #dc3545 !important;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Dropdowns */
        .dropdown-menu {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .dropdown-item {
            padding: 10px 20px;
            transition: background-color 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .topbar {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar .hamburger {
                display: block;
            }

            .content-wrapper {
                padding: 20px 15px;
            }
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert {
            animation: fadeIn 0.3s ease;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="brand">
            <h5><i class="bi bi-box-seam me-2"></i>Inventory ESDM</h5>
        </div>
        @include('layouts.navigation')
    </nav>

    <!-- Sidebar overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Topbar -->
    <nav class="topbar navbar" id="topbar">
        <div class="d-flex align-items-center">
            <button class="hamburger" id="hamburgerBtn">
                <i class="bi bi-list"></i>
            </button>
            <h6 class="page-title mb-0">@yield('page-title', 'Dashboard')</h6>
        </div>

        <div class="d-flex align-items-center">
            <!-- Notifications -->
            <div class="dropdown me-3">
                <button class="btn btn-link position-relative" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-bell fs-5"></i>
                    @if(isset($unreadNotifications) && $unreadNotifications > 0)
                        <span class="notification-badge">{{ $unreadNotifications }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="width: 350px; max-width: 90vw; max-height: 400px; overflow-y: auto;">
                    <li><h6 class="dropdown-header">Notifikasi</h6></li>
                    @if(isset($notifications) && $notifications->count() > 0)
                        @foreach($notifications as $notification)
                            <li>
                                <a class="dropdown-item {{ $notification->is_read ? '' : 'bg-light' }}" href="{{ route('notifications.read', $notification->id) }}" style="white-space: normal; word-wrap: break-word;">
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="fw-bold text-truncate" style="max-width: 200px;" title="{{ $notification->title }}">
                                                {{ $notification->title }}
                                            </div>
                                            <small class="text-muted text-nowrap ms-2">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                        <small class="text-muted" style="line-height: 1.4;">{{ Str::limit($notification->message, 80) }}</small>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    @else
                        <li><span class="dropdown-item text-muted">Tidak ada notifikasi baru</span></li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center fw-semibold text-primary" href="{{ route('notifications.index') }}">Lihat Semua</a></li>
                </ul>
            </div>

            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn btn-link d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <i class="bi bi-person"></i>
                    </div>
                    <span class="ms-2 d-none d-md-inline">{{ auth()->user()->name }}</span>
                    <i class="bi bi-chevron-down ms-1"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">{{ auth()->user()->name }}</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="button" class="dropdown-item" onclick="confirmLogout()">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="content-wrapper">
            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Main Content -->
            @yield('content')
        </div>
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Sidebar toggle functionality
            $('#hamburgerBtn, #sidebarOverlay').on('click', function() {
                $('#sidebar').toggleClass('show');
                $('#sidebarOverlay').toggleClass('show');
                $('#topbar').toggleClass('sidebar-collapsed');
                $('#mainContent').toggleClass('sidebar-collapsed');
            });

            // Close sidebar when clicking outside on mobile
            $(document).on('click', function(e) {
                if ($(window).width() <= 768) {
                    if (!$(e.target).closest('#sidebar, #hamburgerBtn').length) {
                        $('#sidebar').removeClass('show');
                        $('#sidebarOverlay').removeClass('show');
                    }
                }
            });

            // Handle window resize
            $(window).on('resize', function() {
                if ($(window).width() > 768) {
                    $('#sidebar').removeClass('show');
                    $('#sidebarOverlay').removeClass('show');
                    $('#topbar').removeClass('sidebar-collapsed');
                    $('#mainContent').removeClass('sidebar-collapsed');
                }
            });
        });
        
        // Logout function with better error handling
        window.confirmLogout = function() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                const form = document.getElementById('logout-form');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Update CSRF token in form
                const csrfInput = form.querySelector('input[name="_token"]');
                if (csrfInput && csrfToken) {
                    csrfInput.value = csrfToken;
                }
                
                // Submit with timeout fallback
                form.submit();
                
                // Fallback: If form doesn't submit within 3 seconds, try GET logout
                setTimeout(function() {
                    if (window.location.pathname !== '/login') {
                        window.location.href = '{{ route("logout.get") }}';
                    }
                }, 3000);
            }
        };
    </script>

    @stack('scripts')
</body>
</html>