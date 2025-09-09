<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cansan Kalite Kontrol Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 mb-4">
                <i class="fas fa-industry me-3"></i>
                Cansan Kalite Kontrol Sistemi
            </h1>
            <p class="lead mb-5">Çelik üretim fabrikası için gelişmiş kalite kontrol ve prova takip sistemi</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <i class="fas fa-fire fa-3x mb-2"></i>
                                <h5>6 Ocak</h5>
                                <small>3 Set Halinde</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <i class="fas fa-vial fa-3x mb-2"></i>
                                <h5>Prova Sistemi</h5>
                                <small>Otomatik Analiz</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <i class="fas fa-chart-bar fa-3x mb-2"></i>
                                <h5>Raporlama</h5>
                                <small>Günlük/Haftalık/Aylık</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <i class="fas fa-radio fa-3x mb-2"></i>
                                <h5>Telsiz Entegrasyonu</h5>
                                <small>Anlık Bildirim</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a href="/dashboard" class="btn btn-light btn-lg mt-4">
                <i class="fas fa-tachometer-alt me-2"></i>
                Sisteme Giriş
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 mb-3">Sistem Özellikleri</h2>
                    <p class="lead text-muted">Modern teknoloji ile fabrika kalite kontrol süreçlerinizi dijitalleştirin</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-fire text-primary fa-3x mb-3"></i>
                            <h5 class="card-title">Ocak Yönetimi</h5>
                            <p class="card-text">6 ocağın durumunu takip edin. Her sette sadece 1 ocak aktif çalışır. Döküm başlatma ve tamamlama işlemleri.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-vial text-success fa-3x mb-3"></i>
                            <h5 class="card-title">Prova Analizi</h5>
                            <p class="card-text">Kimyasal analiz sonuçlarını kaydedin. C, Mn, Si, P, S, Cr, Ni, Mo değerlerini takip edin. Kalite kontrol süreci.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-chart-line text-info fa-3x mb-3"></i>
                            <h5 class="card-title">Otomatik Raporlama</h5>
                            <p class="card-text">Günlük, haftalık ve aylık detaylı raporlar. Performans analizi ve trend takibi. Excel export desteği.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-tools text-warning fa-3x mb-3"></i>
                            <h5 class="card-title">Ham Madde Takibi</h5>
                            <p class="card-text">Prova sonuçlarına göre ocağa eklenen karbon, mangan vb. malzemelerin takibi. Başarı oranı analizi.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-radio text-danger fa-3x mb-3"></i>
                            <h5 class="card-title">Telsiz Entegrasyonu</h5>
                            <p class="card-text">Prova sonuçlarını telsizle bildirin. API endpoints ile dış sistemlere entegrasyon. Webhook desteği.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-shield-alt text-secondary fa-3x mb-3"></i>
                            <h5 class="card-title">Kalite Standartları</h5>
                            <p class="card-text">ST37, ST52, S235JR, S355JR gibi çelik standartları. Otomatik uygunluk kontrolü ve uyarı sistemi.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Status Section -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h3 class="mb-4">Sistem Durumu</h3>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <i class="fas fa-database text-success fa-2x mb-2"></i>
                        <h5>Veritabanı</h5>
                        <span class="badge bg-success">Aktif</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <i class="fas fa-server text-success fa-2x mb-2"></i>
                        <h5>Laravel Sunucu</h5>
                        <span class="badge bg-success">Çalışıyor</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <i class="fas fa-fire text-info fa-2x mb-2"></i>
                        <h5>Ocak Sistemi</h5>
                        <span class="badge bg-info">Hazır</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                        <h5>Sistem</h5>
                        <span class="badge bg-success">Operasyonel</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">© 2025 Cansan Çelik Üretim Fabrikası - Kalite Kontrol Sistemi v1.0</p>
            <small class="text-muted">Laravel 10 ile geliştirilmiştir</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
