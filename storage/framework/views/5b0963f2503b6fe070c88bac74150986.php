<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Cansan Kalite Kontrol Sistemi'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    
    <?php echo $__env->yieldPushContent('styles'); ?>
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
            <a class="navbar-brand fw-bold" href="<?php echo e(route('dashboard')); ?>">
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
                            <a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" 
                               href="<?php echo e(route('dashboard')); ?>">
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
                            <a class="nav-link <?php echo e(request()->routeIs('furnaces.*') ? 'active' : ''); ?>" 
                               href="<?php echo e(route('furnaces.index')); ?>">
                                <i class="fas fa-fire me-2"></i>
                                Ocaklar
                                    <span class="badge bg-success ms-auto">3</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('castings.*') ? 'active' : ''); ?>" 
                               href="<?php echo e(route('castings.index')); ?>">
                                    <i class="fas fa-industry me-2"></i>
                                Dökümler
                            </a>
                        </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('furnace-management.*') ? 'active' : ''); ?>" 
                                   href="<?php echo e(route('furnace-management.index')); ?>">
                                    <i class="fas fa-cogs me-2"></i>
                                    Ocak Yönetimi
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                                    <span>Kalite Kontrol</span>
                                </h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('samples.*') ? 'active' : ''); ?>" 
                               href="<?php echo e(route('samples.index')); ?>">
                                <i class="fas fa-vial me-2"></i>
                                Provalar
                            </a>
                        </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('samples.pending') ? 'active' : ''); ?>" 
                                   href="<?php echo e(route('samples.pending')); ?>">
                                    <i class="fas fa-hourglass-half me-2"></i>
                                    Bekleyen Provalar
                                    <span class="badge bg-warning ms-auto" id="pending-count">0</span>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                                    <span>Raporlar</span>
                                </h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>" 
                               href="<?php echo e(route('reports.index')); ?>">
                                <i class="fas fa-chart-bar me-2"></i>
                                    Genel Raporlar
                            </a>
                        </li>
                        <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('furnace-reports.*') ? 'active' : ''); ?>" 
                                   href="<?php echo e(route('furnace-reports.index')); ?>">
                                <i class="fas fa-chart-line me-2"></i>
                                Ocak Raporları
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

                <!-- Main content for desktop -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Alerts -->
                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo e(session('warning')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Page Header -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $__env->yieldContent('header', 'Sayfa'); ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php echo $__env->yieldContent('header-buttons'); ?>
                    </div>
                </div>

                <!-- Page Content -->
                <?php echo $__env->yieldContent('content'); ?>
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
                    <a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" 
                       href="<?php echo e(route('dashboard')); ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Kontrol Paneli
                            </a>
                        </li>
                
                        <li class="nav-item">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">Üretim Yönetimi</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('furnaces.*') ? 'active' : ''); ?>" 
                       href="<?php echo e(route('furnaces.index')); ?>">
                        <i class="fas fa-fire me-2"></i>
                        Ocaklar
                        <span class="badge bg-success ms-auto">3</span>
                            </a>
                        </li>
                        <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('castings.*') ? 'active' : ''); ?>" 
                       href="<?php echo e(route('castings.index')); ?>">
                        <i class="fas fa-industry me-2"></i>
                        Dökümler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('furnace-management.*') ? 'active' : ''); ?>" 
                       href="<?php echo e(route('furnace-management.index')); ?>">
                                <i class="fas fa-cogs me-2"></i>
                                Ocak Yönetimi
                            </a>
                        </li>
                
                        <li class="nav-item">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">Kalite Kontrol</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('samples.*') ? 'active' : ''); ?>" 
                       href="<?php echo e(route('samples.index')); ?>">
                        <i class="fas fa-vial me-2"></i>
                        Provalar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('samples.pending') ? 'active' : ''); ?>" 
                       href="<?php echo e(route('samples.pending')); ?>">
                        <i class="fas fa-hourglass-half me-2"></i>
                        Bekleyen Provalar
                        <span class="badge bg-warning ms-auto" id="pending-count-mobile">0</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">Raporlar</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>" 
                       href="<?php echo e(route('reports.index')); ?>">
                        <i class="fas fa-chart-bar me-2"></i>
                        Genel Raporlar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('furnace-reports.*') ? 'active' : ''); ?>" 
                       href="<?php echo e(route('furnace-reports.index')); ?>">
                                <i class="fas fa-chart-line me-2"></i>
                                Ocak Raporları
                            </a>
                        </li>
                
                <li class="nav-item">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">Hızlı İşlemler</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('castings.create')); ?>">
                        <i class="fas fa-plus-circle me-2"></i>
                        Yeni Döküm
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('samples.create')); ?>">
                        <i class="fas fa-plus me-2"></i>
                        Yeni Prova
                    </a>
                </li>
                    </ul>
                </div>
    </div>

    <!-- Mobile Main Content -->
    <div class="d-lg-none">
        <div class="container-fluid p-3">
            <!-- Alerts -->
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('warning')): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo e(session('warning')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Mobile Page Header -->
            <div class="d-flex justify-content-between flex-wrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h4"><?php echo $__env->yieldContent('header', 'Sayfa'); ?></h1>
                <div class="btn-toolbar">
                    <?php echo $__env->yieldContent('header-buttons'); ?>
                </div>
            </div>

            <!-- Mobile Page Content -->
            <?php echo $__env->yieldContent('content'); ?>
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
    </script>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\cansan\resources\views/layouts/app.blade.php ENDPATH**/ ?>