# SQL Scriptleri Kullanım Kılavuzu

Bu klasörde çelik fabrikası yönetim sisteminin veritabanı güncellemelerini adım adım yapmanıza yarayan SQL scriptleri bulunmaktadır.

## 📋 Script Sırası

### 1️⃣ İlk Kurulum
```sql
-- Dosya: 01_initial_setup.sql
-- Açıklama: Veritabanı ve tabloları oluşturur
-- Ne zaman: Sistem ilk kurulumunda
```

### 2️⃣ Ocak Kurulumu  
```sql
-- Dosya: 02_setup_furnaces.sql
-- Açıklama: 6 ocağı oluşturur ve ayarlar
-- Ne zaman: Ocaklar yoksa veya sıfırlanacaksa
```

### 3️⃣ Örnek Döküm Verileri
```sql
-- Dosya: 03_add_sample_castings.sql
-- Açıklama: Test için örnek döküm verileri ekler
-- Ne zaman: Sistem testinde veya demo için
```

### 4️⃣ Kalite Kontrol Verileri
```sql
-- Dosya: 04_add_quality_controls.sql
-- Açıklama: Tamamlanmış dökümler için kalite kontrol ekler
-- Ne zaman: Kalite kontrol testleri için
```

### 5️⃣ Veri Temizleme (İsteğe Bağlı)
```sql
-- Dosya: 05_cleanup_old_data.sql
-- Açıklama: Tüm verileri siler (dikkatli kullanın!)
-- Ne zaman: Tamamen yeniden başlamak istediğinizde
```

### 6️⃣ Ocak Durumu Güncelleme
```sql
-- Dosya: 06_update_furnace_status.sql
-- Açıklama: Ocakların durumlarını ve charging durumlarını ayarlar
-- Ne zaman: Ocak durumları karışmışsa
```

### 7️⃣ Sistem Durumu Kontrolü
```sql
-- Dosya: 07_system_status_check.sql
-- Açıklama: Sistemin genel durumunu kontrol eder
-- Ne zaman: Her zaman (sadece okuma, değişiklik yapmaz)
```

## 🚀 Kullanım Talimatları

### Yeni Kurulum İçin:
```bash
1. phpMyAdmin'e gidin: http://localhost/phpmyadmin
2. Sırasıyla çalıştırın:
   - 01_initial_setup.sql
   - 02_setup_furnaces.sql
   - 03_add_sample_castings.sql
   - 04_add_quality_controls.sql
   - 06_update_furnace_status.sql
```

### Mevcut Sistemi Güncelleme İçin:
```bash
1. Önce durumu kontrol edin: 07_system_status_check.sql
2. İhtiyaca göre çalıştırın:
   - Sadece döküm verileri: 03_add_sample_castings.sql
   - Sadece kalite kontrol: 04_add_quality_controls.sql
   - Ocak durumları: 06_update_furnace_status.sql
```

### Tamamen Yeniden Başlama İçin:
```bash
1. 05_cleanup_old_data.sql (-- işaretlerini kaldırarak)
2. 02_setup_furnaces.sql
3. 03_add_sample_castings.sql
4. 04_add_quality_controls.sql
5. 06_update_furnace_status.sql
```

## ⚠️ Önemli Notlar

### Güvenlik:
- `05_cleanup_old_data.sql` tüm verileri siler! Dikkatli kullanın.
- Her script çalıştırmadan önce mevcut verileri yedekleyin.

### Script Özellikleri:
- **Çakışma Koruması**: Mevcut verilerle çakışmayacak şekilde tasarlandı
- **Kontrol Mesajları**: Her script sonuç mesajları gösterir
- **Güvenli İşlemler**: `INSERT IGNORE`, `IF NOT EXISTS` gibi güvenli komutlar kullanır

### Sorun Giderme:
- Eğer bir script hata verirse, o script'i tekrar çalıştırabilirsiniz
- `07_system_status_check.sql` ile her zaman sistem durumunu kontrol edebilirsiniz
- Her script kendi kontrol mesajları gösterir

## 📊 Beklenen Sonuçlar

### Başarılı Kurulum Sonrası:
- **6 Ocak**: 3 aktif (1,3,5), 3 standby (2,4,6)
- **6 Döküm**: 3 tamamlanmış, 3 devam eden
- **3 Kalite Kontrol**: Tamamlanmış dökümler için
- **Sistem Durumu**: Sağlıklı ve çalışır durumda

### Kontrol Noktaları:
```sql
-- Hızlı sistem kontrolü
SELECT COUNT(*) AS ocak_sayisi FROM furnaces;
SELECT COUNT(*) AS döküm_sayisi FROM castings WHERE production_date = CURDATE();
SELECT COUNT(*) AS kalite_kontrol FROM quality_controls;
```

## 💡 İpuçları

1. **Adım Adım İlerleyin**: Her script'i tek tek çalıştırın
2. **Sonuçları Kontrol Edin**: Her script sonrası çıktıları inceleyin  
3. **Yedek Alın**: Önemli veriler varsa önce yedekleyin
4. **Test Edin**: Script'lerden sonra sistemi test edin

---

**Not**: Bu script'ler mevcut verilerinizle çakışmayacak şekilde tasarlanmıştır. Güvenle kullanabilirsiniz!
