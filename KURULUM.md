# Çelik Fabrikası Yönetim Sistemi - Kurulum Talimatları

## 🚀 XAMPP Ortamında Hızlı Kurulum

### 1. Dosyaları Yerleştirme
- Tüm proje dosyalarını `C:\xampp\htdocs\cansan\kk-cansan\` klasörüne kopyalayın
- XAMPP Control Panel'den Apache ve MySQL servislerini başlatın

### 2. Veritabanı Kurulumu
- Tarayıcıda `http://localhost/phpmyadmin` adresine gidin
- "SQL" sekmesine tıklayın
- `database.sql` dosyasının içeriğini kopyalayıp yapıştırın
- "Çalıştır" butonuna tıklayın

### 3. Sistem Testi
- Tarayıcıda `http://localhost/cansan/kk-cansan/` adresine gidin
- Ana dashboard açılmalıdır
- Eğer "Aktif ocak bulunamadı" uyarısı görürseniz "Sistem Kurulumu Yap" butonuna tıklayın

### 4. Otomatik İşlemler (Opsiyonel)
Otomatik döküm tamamlama için Windows Task Scheduler kullanabilirsiniz:
- Task Scheduler'ı açın
- "Create Basic Task" seçin
- Trigger: "Daily", her dakika çalışacak şekilde ayarlayın
- Action: `C:\xampp\php\php.exe C:\xampp\htdocs\cansan\kk-cansan\auto_complete.php`

## 📱 Kullanım Kılavuzu

### Ana Dashboard
- **Sistem Durumu**: Üst kısımda 4 ana metrik
- **Aktif Ocaklar**: Sol tarafta 3 aktif ocağın durumu
- **İstatistikler**: Sağ tarafta günlük veriler
- **Döküm Listesi**: Alt kısımda bugünkü tüm dökümler

### Temel İşlemler

#### Döküm Tamamlama
1. Aktif ocak kartında "Dökümü Tamamla" butonuna tıklayın
2. Onay verin
3. Sistem otomatik olarak yeni döküm başlatır

#### Kalite Kontrol (Geliştiriliyor)
1. Döküm listesinde "Kalite Kontrol Ekle" butonuna tıklayın
2. Kimyasal değerleri girin
3. Sistem otomatik olarak değerlendirir

#### Bakım Yönetimi
1. 30 döküme ulaşan ocaklar otomatik uyarı verir
2. "Bakıma Gönder" butonuyla manuel bakım başlatın
3. Aynı setteki yedek ocak otomatik aktif olur

## 🔧 Sorun Giderme

### Yaygın Hatalar

#### "Veritabanı bağlantı hatası"
- XAMPP'da MySQL servisinin çalıştığını kontrol edin
- `config.php` dosyasında veritabanı bilgilerini kontrol edin
- phpMyAdmin'de `steel_factory_db` veritabanının var olduğunu kontrol edin

#### "Aktif ocak bulunamadı"
- "Sistem Kurulumu Yap" butonuna tıklayın
- Veya phpMyAdmin'de `database.sql` dosyasını tekrar çalıştırın

#### Sayfalar yavaş yükleniyor
- XAMPP'ın güncel sürümünü kullandığınızdan emin olun
- PHP memory_limit'ini artırın (php.ini dosyasında)

### Performans İyileştirme

#### PHP Ayarları (php.ini)
```ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 10M
post_max_size = 10M
```

#### MySQL Ayarları (my.ini)
```ini
innodb_buffer_pool_size = 256M
query_cache_size = 64M
```

## 📊 Sistem Özellikleri

### Otomatik İşlemler
- **Her sayfa yenileme**: Real-time veri güncellemesi (30 saniye)
- **Manuel tamamlama**: Döküm işlemlerini manuel kontrol
- **Otomatik uyarılar**: Bakım gerekli ocaklar için uyarı
- **Günlük raporlama**: Manuel rapor oluşturma

### Veri Yapısı
- **6 Ocak**: 3 set halinde (1-2, 3-4, 5-6)
- **3 Aktif**: Her zaman 3 ocak aktif durumda
- **120 Dakika**: Her döküm süresi
- **30 Döküm**: Bakım öncesi maksimum döküm

### Güvenlik
- SQL injection koruması (PDO prepared statements)
- XSS koruması (htmlspecialchars)
- CSRF koruması (session token)
- Input validation

## 🔄 Yedekleme

### Veritabanı Yedekleme
```sql
mysqldump -u root -p steel_factory_db > backup_$(date +%Y%m%d).sql
```

### Dosya Yedekleme
- Tüm proje klasörünü düzenli olarak yedekleyin
- Özellikle `config.php` ve özel ayarlarınızı saklayın

## 📞 Destek

### Log Dosyaları
- PHP hataları: `C:\xampp\apache\logs\error.log`
- MySQL hataları: `C:\xampp\mysql\data\*.err`

### Debug Modu
`config.php` dosyasında:
```php
define('APP_DEBUG', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Sistem Durumu Kontrolü
Tarayıcıda: `http://localhost/cansan/kk-cansan/?action=system_status`

---

**Not**: Bu sistem XAMPP ortamında çalışmak üzere optimize edilmiştir. Production ortamında kullanmadan önce güvenlik ve performans ayarlarını gözden geçirin.
