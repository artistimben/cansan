# Çelik Fabrikası İndüksiyon Ocak Yönetim Sistemi

Bu sistem, çelik üretim fabrikasının 6 adet indüksiyon ocağını dijital olarak yönetmek için geliştirilmiş kapsamlı bir Laravel uygulamasıdır.

## 🏭 Sistem Özellikleri

### Ana Özellikler
- **6 İndüksiyon Ocak Yönetimi**: 3 set halinde (1-2, 3-4, 5-6) organize edilmiş ocaklar
- **7/24 Kesintisiz Üretim**: Her anda 3 aktif ocak ile sürekli döküm
- **Otomatik Döküm Takibi**: 120 dakikalık döküm süreleri otomatik takip
- **Bakım Yönetimi**: 30 döküm sonrası otomatik bakım planlaması
- **Kalite Kontrol**: Karbon, silisyum, mangan vb. kimyasal analiz
- **Günlük Raporlama**: Sabah 08:00'da otomatik günlük raporlar
- **Real-time Dashboard**: Anlık sistem durumu ve istatistikler

### Teknik Özellikler
- **Laravel 10** framework
- **MySQL** veritabanı
- **Bootstrap 5** responsive tasarım
- **Real-time** AJAX güncellemeler
- **Otomatik** background işlemler
- **Comprehensive** logging sistemi

## 📋 Sistem Gereksinimleri

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Web sunucu (Apache/Nginx)
- Node.js ve NPM (opsiyonel, frontend geliştirme için)

## 🚀 Kurulum

### 1. Proje Kurulumu
```bash
# Projeyi klonlayın
git clone [repository-url]
cd steel-factory-management

# Bağımlılıkları yükleyin
composer install

# Environment dosyasını oluşturun
cp .env.example .env

# Uygulama anahtarını oluşturun
php artisan key:generate
```

### 2. Veritabanı Konfigürasyonu
`.env` dosyasında veritabanı ayarlarını yapılandırın:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=steel_factory_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Veritabanı Migration
```bash
# Veritabanını oluşturun
mysql -u root -p -e "CREATE DATABASE steel_factory_db"

# Migration'ları çalıştırın
php artisan migrate

# Sistem kurulumunu yapın
php artisan factory:setup
```

### 4. Otomatik İşlemler (Cron Jobs)
Sürekli üretim için cron job'ları ayarlayın:
```bash
# Crontab'ı düzenleyin
crontab -e

# Aşağıdaki satırı ekleyin
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Web Sunucu Konfigürasyonu
Apache için `.htaccess` dosyası otomatik olarak gelir. Nginx için:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 🎮 Kullanım

### İlk Başlatma
1. Tarayıcıda `http://localhost/steel-factory-management` adresine gidin
2. Ana dashboard açılacaktır
3. Sistem otomatik olarak 3 aktif ocak ve döküm başlatır

### Ana Dashboard Kullanımı

#### Aktif Ocaklar Bölümü
- **Ocak Durumu**: Her ocağın anlık durumu (aktif/bakım/bekleme)
- **Döküm İlerlemesi**: Mevcut döküm süreci ve kalan süre
- **Bakım Durumu**: Bakıma ne kadar döküm kaldığı
- **Manuel Kontroller**: Döküm başlatma/tamamlama/bakıma gönderme

#### Döküm Listesi
- **Genel Sıralama**: Tüm ocakların dökümlerinin kronolojik listesi
- **Ocak Bazında**: Her ocağın kendi döküm numaraları
- **Anlık Takip**: Devam eden dökümler ve progress bar'lar
- **Kalite Kontrol**: Her döküm için kalite test sonuçları

#### Kalite Kontrol Sistemi
- **Kimyasal Analiz**: Karbon, silisyum, mangan, fosfor, kükürt değerleri
- **Otomatik Değerlendirme**: Standart limitlerle otomatik karşılaştırma
- **Test Sonuçları**: Başarılı/başarısız/beklemede durumları
- **Limit Uyarıları**: Standart dışı değerler için otomatik uyarılar

### Otomatik İşlemler

#### Döküm Yönetimi
- Her dakika döküm sürelerini kontrol eder
- Süresi dolan dökümler otomatik tamamlanır
- Yeni dökümler otomatik başlatılır
- Boş kalan ocaklara döküm atar

#### Bakım Yönetimi
- 30 döküm sonrası otomatik bakım uyarısı
- Bakıma giden ocağın yerine aynı setteki yedek ocağı aktif eder
- Bakım süreçlerini takip eder

#### Günlük Raporlama
- Her gün sabah 08:00'da otomatik rapor oluşturur
- Önceki 24 saatin döküm istatistikleri
- Ocak bazında performans analizi
- Verimlilik hesaplamaları

## 🔧 Sistem Komutları

### Temel Komutlar
```bash
# Sistem durumunu kontrol et
php artisan factory:status

# Fabrika sistemini kur
php artisan factory:setup

# Tüm verileri sıfırla
php artisan factory:reset

# Günlük rapor oluştur
php artisan reports:daily

# Otomatik döküm işlemlerini çalıştır
php artisan castings:auto-complete
```

### Test Komutları
```bash
# Test verisi oluştur
php artisan factory:simulate 24

# Kalite kontrol testlerini değerlendir
php artisan tinker
>>> App\Models\QualityControl::where('test_result', 'pending')->get()->each->evaluateTestResult()
```

## 📊 Sistem Mimarisi

### Veritabanı Tabloları
- **furnaces**: Ocak bilgileri ve durumları
- **castings**: Döküm kayıtları ve süreçleri  
- **quality_controls**: Kalite kontrol test sonuçları
- **daily_reports**: Günlük üretim raporları

### Model İlişkileri
```php
Furnace -> hasMany(Casting)
Casting -> belongsTo(Furnace)
Casting -> hasOne(QualityControl)
QualityControl -> belongsTo(Casting)
```

### Controller Yapısı
- **DashboardController**: Ana dashboard ve real-time veri
- **QualityControlController**: Kalite kontrol işlemleri
- **Commands**: Otomatik background işlemler

## 🔒 Güvenlik Özellikleri

- CSRF koruması tüm formlarda
- SQL injection koruması (Eloquent ORM)
- XSS koruması (Blade templating)
- Input validation (Laravel Validation)
- Error handling ve logging

## 📈 Performans Optimizasyonları

- Database indexleri kritik alanlarda
- Eager loading ilişkili veriler için
- AJAX ile partial page updates
- Background job'lar ağır işlemler için
- Caching stratejileri

## 🐛 Sorun Giderme

### Yaygın Sorunlar
1. **Otomatik döküm çalışmıyor**: Cron job'ların çalıştığını kontrol edin
2. **Real-time güncellemeler yok**: AJAX isteklerini browser console'da kontrol edin
3. **Kalite kontrol kaydetmiyor**: Form validation hatalarını kontrol edin

### Log Dosyaları
```bash
# Uygulama logları
tail -f storage/logs/laravel.log

# Otomatik döküm logları  
tail -f storage/logs/auto-complete.log

# Günlük rapor logları
tail -f storage/logs/daily-reports.log
```

### Debug Modu
Development sırasında `.env` dosyasında:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/yeni-ozellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik eklendi'`)
4. Branch'i push edin (`git push origin feature/yeni-ozellik`)
5. Pull Request oluşturun

## 📝 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 📞 Destek

Herhangi bir sorun veya öneriniz için:
- Issue açın
- E-posta gönderin
- Dokümantasyonu inceleyin

---

**Not**: Bu sistem 7/24 kesintisiz çalışan bir üretim ortamı için tasarlanmıştır. Production ortamında kullanmadan önce kapsamlı test yapılması önerilir.
