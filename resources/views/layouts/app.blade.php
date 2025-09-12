<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cansan Kalite Kontrol Sistemi')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container-fluid">
            <!-- Mobile menu toggle -->
            <button class="btn btn-outline-light me-3 d-lg-none" type="button" data-bs-toggle="offcanvas" 
                    data-bs-target="#sidebar" aria-controls="sidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Brand -->
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
                <i class="fas fa-industry me-2"></i>
                <span class="d-none d-md-inline">Cansan Kalite Kontrol</span>
                <span class="d-inline d-md-none">Cansan</span>
            </a>
            
            <!-- Right side content -->
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2"></i>
                        <span class="d-none d-lg-inline">Admin</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Admin Paneli</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Ayarlar</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-chart-line me-2"></i>İstatistikler</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-question-circle me-2"></i>Yardım</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-sign-out-alt me-2"></i>Çıkış Yap</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar for Desktop -->
    <div class="d-none d-lg-block">
    <div class="container-fluid">
        <div class="row">
                <nav class="col-lg-2 d-lg-block bg-light sidebar collapse">
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
                                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                                    <span>Üretim Yönetimi</span>
                                </h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('furnaces.*') ? 'active' : '' }}" 
                               href="{{ route('furnaces.index') }}">
                                <i class="fas fa-fire me-2"></i>
                                Ocaklar
                                    <span class="badge bg-success ms-auto">3</span>
                            </a>
                        </li>
                            
                            <li class="nav-item">
                                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                                    <span>Kalite Kontrol</span>
                                </h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('samples.*') ? 'active' : '' }}" 
                               href="{{ route('samples.index') }}">
                                <i class="fas fa-vial me-2"></i>
                                Provalar
                            </a>
                        </li>
                            
                            <li class="nav-item">
                                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                                    <span>Raporlar</span>
                                </h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
                               href="{{ route('reports.index') }}">
                                <i class="fas fa-chart-bar me-2"></i>
                                    Genel Raporlar
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

                <!-- Main content for desktop -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
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
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
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
    </div>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarLabel">
                <i class="fas fa-industry me-2"></i>Cansan KK
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav flex-column">
                        <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                       href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Kontrol Paneli
                            </a>
                        </li>
                
                        <li class="nav-item">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">Üretim Yönetimi</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('furnaces.*') ? 'active' : '' }}" 
                       href="{{ route('furnaces.index') }}">
                        <i class="fas fa-fire me-2"></i>
                        Ocaklar
                        <span class="badge bg-success ms-auto">3</span>
                            </a>
                        </li>
                
                        <li class="nav-item">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">Kalite Kontrol</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('samples.*') ? 'active' : '' }}" 
                       href="{{ route('samples.index') }}">
                        <i class="fas fa-vial me-2"></i>
                        Provalar
                    </a>
                </li>
                
                <li class="nav-item">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">Raporlar</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
                       href="{{ route('reports.index') }}">
                        <i class="fas fa-chart-bar me-2"></i>
                        Genel Raporlar
                    </a>
                </li>
                
                <li class="nav-item">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">Hızlı İşlemler</h6>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link btn btn-link" data-bs-toggle="modal" data-bs-target="#addProvaModal">
                        <i class="fas fa-plus-circle me-2"></i>
                        Yeni Döküm
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link btn btn-link" data-bs-toggle="modal" data-bs-target="#addProvaModal">
                        <i class="fas fa-plus me-2"></i>
                        Yeni Prova
                    </button>
                </li>
                    </ul>
                </div>
    </div>

    <!-- Mobile Main Content -->
    <div class="d-lg-none">
        <div class="container-fluid p-3">
            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Mobile Page Header -->
            <div class="d-flex justify-content-between flex-wrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h4">@yield('header', 'Sayfa')</h1>
                <div class="btn-toolbar">
                    @yield('header-buttons')
                </div>
            </div>

            <!-- Mobile Page Content -->
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        // CSRF Token setup
        if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        }
        
        // Update pending sample count
        function updatePendingCount() {
            fetch('/api/v1/samples/pending/list')
                .then(response => response.json())
                .then(data => {
                    const desktopCount = document.getElementById('pending-count');
                    const mobileCount = document.getElementById('pending-count-mobile');
                    const count = data.length || 0;
                    if (desktopCount) desktopCount.textContent = count;
                    if (mobileCount) mobileCount.textContent = count;
                })
                .catch(error => console.log('Could not update pending count'));
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updatePendingCount();
            setInterval(updatePendingCount, 30000);
        });
        
        // Yeni prova ekleme - Sadece bir kez bağla
        $(document).off('click', '.add-prova-btn').on('click', '.add-prova-btn', function() {
            const castingId = $(this).data('casting-id');
            $('#castingId').val(castingId);
            $('#addProvaForm')[0].reset();
            $('#addProvaModal').modal('show');
        });

        // Yeni prova kaydetme - Sadece bir kez bağla
        $(document).off('click', '#saveNewProvaBtn').on('click', '#saveNewProvaBtn', function(e) {
            e.preventDefault();
            
            console.log('Prova ekleme butonu tıklandı'); // Debug
            
            const castingId = $('#castingId').val();
            console.log('Casting ID:', castingId); // Debug
            
            if (!castingId) {
                alert('Döküm ID bulunamadı!');
                return;
            }
            
            const formData = {
                casting_id: castingId,
                carbon: $('#new_carbon').val() || 0,
                silicon: $('#new_silicon').val() || 0,
                manganese: $('#new_manganese').val() || 0,
                sulfur: $('#new_sulfur').val() || 0,
                phosphorus: $('#new_phosphorus').val() || 0,
                copper: $('#new_copper').val() || 0,
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            console.log('Gönderilen veri:', formData); // Debug
            
            // Butonu devre dışı bırak
            $(this).prop('disabled', true).text('Ekleniyor...');
            
            $.ajax({
                url: '/samples',
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                },
                success: function(response) {
                    console.log('Başarılı yanıt:', response);
                    alert('Prova başarıyla eklendi!');
                    // Modalı kapat
                    $('#addProvaModal').modal('hide');
                    // Sayfayı yenile
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Hatası:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    
                    let errorMessage = 'Bilinmeyen hata';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                        }
                    } else if (xhr.status === 422) {
                        errorMessage = 'Doğrulama hatası - Lütfen tüm alanları kontrol edin';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Sunucu hatası - Lütfen tekrar deneyin';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Sayfa bulunamadı - Route kontrol edin';
                    }
                    
                    alert('Hata: ' + errorMessage);
                },
                complete: function() {
                    // Butonu tekrar aktif et
                    $('#saveNewProvaBtn').prop('disabled', false).text('Prova Ekle');
                }
            });
        });
    </script>
    
    <!-- Yeni Prova Ekleme Modalı -->
    <div class="modal fade" id="addProvaModal" tabindex="-1" aria-labelledby="addProvaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProvaModalLabel">Yeni Prova Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProvaForm">
                        <input type="hidden" id="castingId" name="casting_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_carbon" class="form-label">Karbon (C)</label>
                                <input type="number" class="form-control" id="new_carbon" name="carbon" step="0.01" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_silicon" class="form-label">Silisyum (Sİ)</label>
                                <input type="number" class="form-control" id="new_silicon" name="silicon" step="0.01" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_manganese" class="form-label">Mangan (MN)</label>
                                <input type="number" class="form-control" id="new_manganese" name="manganese" step="0.01" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_sulfur" class="form-label">Kükürt (S)</label>
                                <input type="number" class="form-control" id="new_sulfur" name="sulfur" step="0.01" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_phosphorus" class="form-label">Fosfor (P)</label>
                                <input type="number" class="form-control" id="new_phosphorus" name="phosphorus" step="0.01" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_copper" class="form-label">Bakır (CU)</label>
                                <input type="number" class="form-control" id="new_copper" name="copper" step="0.01" min="0">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-success" id="saveNewProvaBtn">Prova Ekle</button>
                </div>
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>
