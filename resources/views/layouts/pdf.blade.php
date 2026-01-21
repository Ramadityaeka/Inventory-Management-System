<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Report')</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .pdf-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .pdf-header h2 {
            color: #007bff;
            margin: 0 0 10px 0;
            font-size: 18px;
        }

        .pdf-header p {
            margin: 0;
            color: #666;
        }

        .pdf-content {
            min-height: 500px;
        }

        .summary-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin-bottom: 10px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            display: block;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .pdf-table th,
        .pdf-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .pdf-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pdf-table td {
            font-size: 11px;
        }

        .pdf-table .text-center {
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
        }

        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .pdf-footer {
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }

        .text-muted {
            color: #6c757d;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        code {
            background-color: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 10px;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>