<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cansan Kalite Kontrol Sistemi')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        html, body {
            height: 100%;
            overflow-x: hidden;
        }
        
        :root {
            --cansan-primary: #1e40af;
            --cansan-secondary: #64748b;
            --cansan-success: #059669;
            --cansan-warning: #d97706;
            --cansan-danger: #dc2626;
            --cansan-info: #0284c7;
        }
        
        .navbar {
            height: 56px;
            min-height: 56px;
            max-height: 56px;
            padding: 0.5rem 1rem;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--cansan-primary) !important;
            font-size: 1.25rem;
            line-height: 1.2;
        }
        
        .navbar-text {
            font-size: 0.875rem;
            margin: 0;
        }
        
        .sidebar {
            height: calc(100vh - 56px);
            max-height: calc(100vh - 56px);
            overflow-y: auto;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-right: 1px solid #e5e7eb;
        }
        
        .sidebar .nav-link {
            color: var(--cansan-secondary);
            border-radius: 0.5rem;
            margin: 0.25rem 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(30, 64, 175, 0.1);
            color: var(--cansan-primary);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background-color: var(--cansan-primary);
            color: white;
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            min-height: 120px;
            max-height: 140px;
        }
        
        .stat-card .card-body {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--cansan-primary);
            line-height: 1.1;
            margin: 0;
        }
        
        .badge-quality-approved {
            background-color: var(--cansan-success);
        }
        
        .badge-quality-rejected {
            background-color: var(--cansan-danger);
        }
        
        .badge-quality-pending {
            background-color: var(--cansan-warning);
        }
        
        .badge-quality-needs-adjustment {
            background-color: var(--cansan-info);
        }
        
        .furnace-status {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .furnace-active {
            background-color: var(--cansan-success);
            box-shadow: 0 0 8px rgba(5, 150, 105, 0.5);
        }
        
        .furnace-inactive {
            background-color: var(--cansan-secondary);
        }
        
        .furnace-maintenance {
            background-color: var(--cansan-warning);
        }
        
        .loading-spinner {
            display: none;
        }
        
        .loading .loading-spinner {
            display: inline-block;
        }
        
        .container-fluid {
            height: 100vh;
            overflow: hidden;
        }
        
        main.col-md-9.col-lg-10 {
            height: calc(100vh - 56px);
            overflow-y: auto;
            padding-bottom: 2rem;
        }
        
        .page-header {
            min-height: 60px;
            max-height: 80px;
            flex-shrink: 0;
        }
        
        .page-header h1 {
            font-size: 1.75rem;
            margin: 0;
            line-height: 1.2;
        }
        
        .alert-dismissible {
            animation: slideInDown 0.5s ease;
        }
        
        @keyframes slideInDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(30, 64, 175, 0.05);
        }
        
        .btn-cansan {
            background-color: var(--cansan-primary);
            border-color: var(--cansan-primary);
            color: white;
        }
        
        .btn-cansan:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            color: white;
        }
        
        .progress-bar-cansan {
            background-color: var(--cansan-primary);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-industry me-2"></i>
                Cansan Kalite Kontrol
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text">
                            <i class="fas fa-clock me-1"></i>
                            <span id="current-time">{{ now()->format('H:i:s') }}</span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="navbar-text ms-3">
                            <i class="fas fa-calendar me-1"></i>
                            {{ now()->format('d.m.Y') }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                               href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Kontrol Paneli
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('furnaces.*') ? 'active' : '' }}" 
                               href="{{ route('furnaces.index') }}">
                                <i class="fas fa-fire me-2"></i>
                                Ocaklar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('castings.*') ? 'active' : '' }}" 
                               href="{{ route('castings.index') }}">
                                <i class="fas fa-fire me-2"></i>
                                Dökümler
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('samples.*') ? 'active' : '' }}" 
                               href="{{ route('samples.index') }}">
                                <i class="fas fa-vial me-2"></i>
                                Provalar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
                               href="{{ route('reports.index') }}">
                                <i class="fas fa-chart-bar me-2"></i>
                                Raporlar
                            </a>
                        </li>
                        <hr class="my-3">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('samples.pending') }}">
                                <i class="fas fa-hourglass-half me-2"></i>
                                Bekleyen Provalar
                                <span class="badge bg-warning ms-2" id="pending-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('castings.create') }}">
                                <i class="fas fa-plus-circle me-2"></i>
                                Yeni Döküm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('samples.create') }}">
                                <i class="fas fa-plus me-2"></i>
                                Yeni Prova
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 col-lg-10 px-md-4">
                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('header', 'Sayfa')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        @yield('header-buttons')
                    </div>
                </div>

                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        // CSRF Token ayarla
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Saat güncelleme
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('tr-TR');
            document.getElementById('current-time').textContent = timeString;
        }
        
        // Her saniye saat güncelle
        setInterval(updateTime, 1000);
        
        // Bekleyen prova sayısını güncelle
        function updatePendingCount() {
            fetch('/api/v1/samples/pending/list')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('pending-count').textContent = data.length || 0;
                })
                .catch(error => console.log('Bekleyen prova sayısı güncellenemedi'));
        }
        
        // Sayfa yüklendiğinde ve her 30 saniyede bir güncelle
        document.addEventListener('DOMContentLoaded', function() {
            updatePendingCount();
            setInterval(updatePendingCount, 30000);
        });
        
        // Loading state helper
        function showLoading(element) {
            $(element).addClass('loading').prop('disabled', true);
        }
        
        function hideLoading(element) {
            $(element).removeClass('loading').prop('disabled', false);
        }
        
        // Toast bildirimleri
        function showToast(message, type = 'success') {
            const toastHtml = `
                <div class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            // Toast container yoksa oluştur
            if (!document.getElementById('toast-container')) {
                const container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(container);
            }
            
            const container = document.getElementById('toast-container');
            container.insertAdjacentHTML('beforeend', toastHtml);
            
            const toast = container.lastElementChild;
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Toast kapandıktan sonra DOM'dan kaldır
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
        
        // Kalite durumu badge'i için renk sınıfı
        function getQualityBadgeClass(status) {
            switch(status) {
                case 'approved': return 'badge-quality-approved';
                case 'rejected': return 'badge-quality-rejected';
                case 'pending': return 'badge-quality-pending';
                case 'needs_adjustment': return 'badge-quality-needs-adjustment';
                default: return 'bg-secondary';
            }
        }
        
        // Kalite durumu Türkçe isim
        function getQualityStatusText(status) {
            switch(status) {
                case 'approved': return 'Onaylandı';
                case 'rejected': return 'Reddedildi';
                case 'pending': return 'Beklemede';
                case 'needs_adjustment': return 'Düzeltme Gerekli';
                default: return status;
            }
        }
    </script>
    
    @stack('scripts')
</body>
</html>
