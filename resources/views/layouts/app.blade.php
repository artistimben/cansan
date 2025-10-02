<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Çelik Fabrikası Yönetim Sistemi')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            /* Resmi Fabrika Renk Paleti */
            --primary-color: #1e3a8a;      /* Koyu mavi - güvenilirlik */
            --secondary-color: #64748b;     /* Gri - profesyonellik */
            --accent-color: #dc2626;        /* Kırmızı - uyarı/acil */
            --success-color: #16a34a;       /* Yeşil - başarı */
            --warning-color: #ca8a04;       /* Sarı - dikkat */
            --info-color: #0284c7;          /* Açık mavi - bilgi */
            --light-bg: #f8fafc;            /* Açık gri - arkaplan */
            --dark-text: #1e293b;           /* Koyu metin */
            --border-color: #e2e8f0;        /* Kenarlık */
        }

        body {
            background-color: var(--light-bg);
            color: var(--dark-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3b82f6 100%);
            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
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

        .card-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #94a3b8 100%);
            color: white;
            font-weight: 600;
            border-bottom: none;
        }

        /* Ocak Durumu Kartları */
        .furnace-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .furnace-active {
            border-left: 5px solid var(--success-color);
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }

        .furnace-maintenance {
            border-left: 5px solid var(--accent-color);
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
        }

        .furnace-standby {
            border-left: 5px solid var(--secondary-color);
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        /* Progress Bar Özelleştirme */
        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e2e8f0;
        }

        .progress-bar {
            border-radius: 4px;
            transition: width 0.6s ease;
        }

        /* Döküm Tablosu */
        .casting-table {
            font-size: 0.9rem;
        }

        .casting-table th {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3b82f6 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px 8px;
        }

        .casting-table td {
            vertical-align: middle;
            padding: 10px 8px;
            border-bottom: 1px solid var(--border-color);
        }

        .casting-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        /* Status Badge'leri */
        .status-badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Kalite Kontrol Modal */
        .quality-control-form .form-label {
            font-weight: 600;
            color: var(--dark-text);
        }

        .quality-control-form .form-control {
            border: 2px solid var(--border-color);
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }

        .quality-control-form .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        /* Animasyonlar */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .charging {
            animation: pulse 2s infinite;
        }

        .delayed {
            background-color: #fef2f2 !important;
            border-left-color: var(--accent-color) !important;
        }

        /* Responsive Düzenlemeler */
        @media (max-width: 768px) {
            .casting-table {
                font-size: 0.8rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .btn {
                font-size: 0.85rem;
                padding: 0.4rem 0.8rem;
            }
        }

        /* Loading Spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        /* İstatistik Kartları */
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            border-left: 4px solid;
        }

        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.info { border-left-color: var(--info-color); }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--secondary-color);
            font-weight: 600;
        }

        /* Prova Kayıtları Stilleri */
        .prova-item {
            background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
            border-left: 4px solid var(--warning-color);
            transition: all 0.3s ease;
        }

        .prova-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateX(5px);
        }

        .prova-item .form-label-sm {
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark-text);
        }

        .prova-item .form-control-sm {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            height: calc(1.5em + 0.5rem + 2px);
        }

        #prova-list .text-muted {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border: 2px dashed var(--border-color);
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-fire"></i>
                Çelik Fabrikası Yönetim Sistemi
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="navbar-text">
                            <i class="bi bi-clock"></i>
                            <span id="current-time">{{ now()->format('d.m.Y H:i:s') }}</span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Ana İçerik -->
    <main class="container-fluid py-4">
        @yield('content')
    </main>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-info-circle text-primary me-2"></i>
                <strong class="me-auto">Sistem Bildirimi</strong>
                <small class="text-muted">şimdi</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <!-- Toast mesajı buraya gelecek -->
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Global JavaScript fonksiyonları
        
        // CSRF Token ayarlama
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toast gösterme fonksiyonu
        function showToast(message, type = 'info') {
            const toast = $('#liveToast');
            const toastBody = toast.find('.toast-body');
            const toastIcon = toast.find('.toast-header i');
            
            // İkon ve renk ayarlama
            toastIcon.removeClass().addClass('bi me-2');
            switch(type) {
                case 'success':
                    toastIcon.addClass('bi-check-circle text-success');
                    break;
                case 'error':
                    toastIcon.addClass('bi-exclamation-triangle text-danger');
                    break;
                case 'warning':
                    toastIcon.addClass('bi-exclamation-circle text-warning');
                    break;
                default:
                    toastIcon.addClass('bi-info-circle text-primary');
            }
            
            toastBody.text(message);
            
            const bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();
        }

        // Saat güncelleme
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleString('tr-TR', {
                day: '2-digit',
                month: '2-digit', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            $('#current-time').text(timeString);
        }

        // Her saniye saat güncelle
        setInterval(updateCurrentTime, 1000);

        // Sayfa yüklendiğinde
        $(document).ready(function() {
            updateCurrentTime();
            
            // Loading state'leri için global handler
            $(document).ajaxStart(function() {
                $('body').addClass('loading');
            }).ajaxStop(function() {
                $('body').removeClass('loading');
            });

            // AJAX hata handler
            $(document).ajaxError(function(event, xhr, settings, thrownError) {
                if (xhr.status === 422) {
                    // Validation hataları
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = 'Girilen değerlerde hata var:\n';
                    for (let field in errors) {
                        errorMessage += '- ' + errors[field][0] + '\n';
                    }
                    showToast(errorMessage, 'error');
                } else if (xhr.status === 500) {
                    showToast('Sunucu hatası oluştu. Lütfen tekrar deneyin.', 'error');
                } else if (xhr.status === 0) {
                    showToast('Bağlantı hatası. İnternet bağlantınızı kontrol edin.', 'error');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
