#RESPOJSİVE
# Cansan Çelik Üretim Fabrikası Kalite Kontrol Sistemi

Bu sistem, Cansan Çelik Üretim Fabrikası'nın kalite kontrol biriminde kullanılmak üzere geliştirilmiştir. Ocaktan alınan provaların ölçümlerini düzenli bir şekilde kayıt eder ve günlük, haftalık, aylık otomatik raporlar sunar.

## 🏗️ Sistem Mimarisi

### Fabrika İşleyiş Sistemi
- **6 Ocak**: 3 set halinde düzenlenmiş (Set 1: Ocak 1-2, Set 2: Ocak 3-4, Set 3: Ocak 5-6)
- **Aktif Çalışma**: Her sette sadece 1 ocak aktif çalışır
- **Prova Süreci**: Ocaktan prova alınır → Kalite kontrol analizi → Telsizle sonuç bildirilir → Gerekirse ham madde eklenir

### Sistem Özellikleri
- ✅ Ocak döküm sayısı takibi
- ✅ Prova değerleri kayıt sistemi
- ✅ Hangi provanın hangi ocağın hangi dökümüne ait olduğu bilgisi
- ✅ Aktif ocak durumu yönetimi
- ✅ Otomatik raporlama (günlük, haftalık, aylık)
- ✅ Telsiz entegrasyonu
- ✅ Ham madde ekleme takibi

## 🚀 Kurulum

### Gereksinimler
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js & NPM (frontend assets için)

### Adım Adım Kurulum

1. **Proje dosyalarını kopyalayın**
```bash
# Dosyalar zaten C:\xampp\htdocs\cansan\kk-cansan klasöründe
cd C:\xampp\htdocs\cansan\kk-cansan
```

2. **Composer bağımlılıklarını yükleyin**
```bash
composer install
```

3. **Environment dosyasını oluşturun**
```bash
copy env.example .env
```

4. **Veritabanı ayarlarını yapın**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cansan
DB_USERNAME=root
DB_PASSWORD=
```

5. **Uygulama anahtarı oluşturun**
```bash
php artisan key:generate
```

6. **Veritabanını oluşturun**
- phpMyAdmin'de `cansan` adında veritabanı oluşturun

7. **Migration'ları çalıştırın**
```bash
php artisan migrate
```

8. **Seed verilerini yükleyin**
```bash
php artisan db:seed
```

9. **Geliştirme sunucusunu başlatın**
```bash
php artisan serve
```

Sistem `http://localhost:8000` adresinde çalışacaktır.

## 📊 Veritabanı Yapısı

### Ana Tablolar

#### `furnace_sets` (Ocak Setleri)
- Set 1, Set 2, Set 3 bilgileri
- Her set aktif/pasif durumu

#### `furnaces` (Ocaklar)
- 6 ocağın bilgileri
- Hangi sete ait oldukları
- Aktif/pasif durumları

#### `castings` (Dökümler)
- Her ocaktan yapılan döküm kayıtları
- Döküm numarası, tarihi, vardiya
- Döküm durumu (aktif, tamamlandı, iptal)

#### `samples` (Provalar)
- Her dökümden alınan prova örnekleri
- Kimyasal analiz sonuçları (C, Mn, Si, P, S, Cr, Ni, Mo)
- Kalite durumu (onaylandı, reddedildi, beklemede, düzeltme gerekli)
- Telsiz bildirimi bilgileri

#### `quality_standards` (Kalite Standartları)
- Çelik sınıflarına göre kabul edilebilir değer aralıkları
- ST37, ST52, S235JR, S355JR, A36 vb. standartlar

#### `adjustments` (Ham Madde Eklemeleri)
- Prova sonuçlarına göre yapılan ham madde eklemeleri
- Eklenen malzeme türü ve miktarı
- Ekleme öncesi/sonrası değerler

## 🎯 Kullanım Kılavuzu

### Ana Kontrol Paneli
- Günlük istatistikler
- Aktif ocak durumları
- Son provalar ve ham madde eklemeleri
- Kalite dağılım grafikleri

### Ocak Yönetimi
- Ocak aktif/pasif durumu değiştirme
- Yeni döküm başlatma
- Döküm tamamlama
- Ocak performans raporları

### Prova Sistemi
- Yeni prova kaydı oluşturma
- Kimyasal analiz sonuçları girme
- Kalite durumu güncelleme
- Telsiz bildirimi kaydetme

### Raporlama
- **Günlük Rapor**: Günlük döküm, prova ve kalite istatistikleri
- **Haftalık Rapor**: Haftalık trend analizi ve ocak karşılaştırması  
- **Aylık Rapor**: Aylık performans ve ham madde tüketim analizi

## 🔧 API Endpoints

### Temel Endpoints
- `GET /api/v1/status` - Sistem durumu
- `GET /api/v1/dashboard` - Dashboard verileri
- `GET /api/v1/furnaces` - Ocak listesi
- `POST /api/v1/samples` - Yeni prova oluştur

### Telsiz Entegrasyonu
- `POST /api/radio/report-sample` - Prova sonucunu telsizle bildir
- `GET /api/radio/active-castings` - Aktif dökümleri getir

### Webhook Entegrasyonları
- `POST /api/webhooks/lab-results` - Laboratuvar sonuçları
- `POST /api/webhooks/erp-integration` - ERP sistemi entegrasyonu

## 🛠️ Konsol Komutları

### Sistem Durumu
```bash
php artisan cansan:status          # Sistem durumu raporu
php artisan cansan:daily-report    # Günlük rapor oluştur
php artisan cansan:sample-reminder # Prova hatırlatması
php artisan cansan:quality-check   # Kalite kontrol raporu
php artisan cansan:cleanup         # Eski kayıtları temizle
```

### Zamanlanmış Görevler
- Her saat: Sistem durumu kontrolü
- Her gün 02:00: Günlük rapor oluşturma
- Her 2 saat: Prova hatırlatması
- Her 4 saat: Kalite kontrol raporu
- Her hafta pazar 03:00: Veritabanı temizliği

## 🎨 Frontend Özellikler

### Modern UI/UX
- Bootstrap 5 tabanlı responsive tasarım
- Real-time dashboard güncellemeleri
- Interactive grafikler (Chart.js)
- Toast bildirimleri
- Loading states

### Klavye Kısayolları
- `Ctrl + R`: Dashboard yenile
- `Ctrl + N`: Yeni prova
- `Ctrl + P`: Bekleyen provalar

## 📱 Mobil Uyuyumluluk
- Responsive tasarım
- Touch-friendly interface
- Mobil cihazlarda optimize edilmiş tablolar
- Offline çalışma desteği (gelecek sürümde)

## 🔒 Güvenlik
- CSRF koruması
- SQL injection koruması
- XSS koruması
- Input validation
- Rate limiting (API endpoints için)

## 🚀 Performans
- Database indexing
- Query optimization
- Caching (Redis desteği)
- Lazy loading
- Pagination

## 📈 İzleme ve Logging
- Application logs
- Error tracking
- Performance monitoring
- Database query logging
- User activity logging

## 🔧 Bakım ve Destek

### Log Dosyaları
```
storage/logs/laravel.log          # Genel uygulama logları
storage/logs/quality-control.log # Kalite kontrol işlem logları
storage/logs/system-health.log   # Sistem sağlık logları
```

### Backup Önerileri
- Günlük veritabanı yedekleme
- Haftalık tam sistem yedekleme
- Kritik konfigürasyon dosyalarının yedeklenmesi

### Sistem Gereksinimleri
- **Minimum**: 2GB RAM, 10GB disk alanı
- **Önerilen**: 4GB RAM, 50GB disk alanı, SSD
- **Network**: Telsiz sistemi ile entegrasyon için local network

## 🤝 Destek

Sistem hakkında sorularınız veya sorunlarınız için:
- Teknik dokümantasyonu inceleyin
- Log dosyalarını kontrol edin
- Sistem durumu komutlarını çalıştırın

---

**Geliştirici Notları:**
Bu sistem Laravel 10 framework'ü kullanılarak geliştirilmiştir. Türkçe dil desteği ve yerel saat dilimi (Europe/Istanbul) ayarlanmıştır. Tüm kullanıcı arayüzü Türkçe olarak tasarlanmış ve fabrika terminolojisine uygun hale getirilmiştir.

**Versiyon:** 1.0.0  
**Son Güncelleme:** {{ date('d.m.Y') }}  
**Laravel Versiyon:** 10.x  
**PHP Versiyon:** 8.1+
