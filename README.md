# Cyprus Touristic — Kurumsal Web Sitesi

Cyprus Touristic için hazırlanmış, tamamı yönetim panelinden yönetilen kurumsal tanıtım sitesi.
Satış / sepet yoktur; oteller tanıtım amaçlıdır ve ziyaretçi **Talep Formu** ile başvurur.

- **Teknoloji:** PHP 8.1+ (GD, PDO MySQL, fileinfo, mbstring), MariaDB / MySQL, Apache ya da LiteSpeed (`.htaccess` + `mod_rewrite`)
- **Bağımlılık yok:** Composer ya da derleme adımı gerekmez; dosyalar kopyalanır, veritabanı içe aktarılır.
- Site hangi klasöre konursa konsun (kök ya da alt klasör) adreslerini kendisi çözer.

## Klasör yapısı

| Yol | İçerik |
| --- | --- |
| `index.php` | Ön yüz denetleyicisi (tüm sayfa adresleri) |
| `admin/` | Yönetim paneli girişi (`/admin/`) |
| `app/bootstrap.php` | Çekirdek: veritabanı, oturum, CSRF, dosya yükleme, yardımcılar |
| `app/theme.php` | Panelden gelen renk / yazı tipi / stil ayarlarının CSS değişkenlerine dönüşümü |
| `app/forms.php` | İletişim ve talep formu, CAPTCHA, e-posta bildirimi |
| `app/views/` | Sayfa şablonları (anasayfa bölümleri `home.php` içinde) |
| `app/admin/` | Panel kodu; içerik türleri ve ayarlar `entities.php` içinde tanımlıdır |
| `app/tools/admin.php` | Komut satırından yönetici oluşturma / şifre sıfırlama |
| `assets/` | CSS, JavaScript, yazı tipleri |
| `uploads/` | Yüklenen görsel, video ve PDF dosyaları (`uploads/cache/` otomatik üretilir) |
| `database/cyprus.sql` | İçerik dökümü (yönetici hesapları ve form mesajları hariç) |

`app/` ve `database/` klasörleri tarayıcıdan erişime kapalıdır.

## Yeni sunucuya kurulum (ör. cyprustouristic.com)

1. **Dosyalar:** Tüm dosyaları (gizli `.htaccess` dosyaları dahil) sitenin kök klasörüne (`public_html`) kopyalayın.
2. **Veritabanı:** cPanel'den boş bir MySQL veritabanı ve kullanıcı oluşturun, kullanıcıya tüm yetkileri verin.
   phpMyAdmin > İçe Aktar ile veritabanı dökümünü yükleyin:
   - Çalışan sitenin tam yedeğini taşıyorsanız o sitenin kendi dökümünü (yönetici hesabı ve mesajlar dahil),
   - Depodan kuruyorsanız `database/cyprus.sql` dosyasını.
3. **Bağlantı ayarı:** `app/config.sample.php` dosyasını `app/config.php` adıyla kopyalayıp veritabanı bilgilerini yazın.
4. **Yönetici hesabı** (yalnızca `database/cyprus.sql` ile kurduysanız; SSH / Terminal'den):
   ```bash
   php app/tools/admin.php admin "EnAz10KarakterlikSifre"
   ```
5. **Yazma izni:** `uploads/` klasörü web sunucusu kullanıcısı tarafından yazılabilir olmalıdır (cPanel'de genellikle hazırdır).
6. **Panelden son ayarlar** (`/admin/`):
   - *Genel ve Logo* > **Site adresi**: `https://cyprustouristic.com`
   - *İletişim ve Sosyal* > **Form bildirimleri**: e-posta bildirimini açın, gönderen adresi alan adına ait olsun (ör. `noreply@cyprustouristic.com`).
   - PHP sürümü 8.1 ya da üzeri seçili olmalıdır (cPanel > MultiPHP / Select PHP Version; `gd`, `pdo_mysql`, `fileinfo`, `mbstring` açık).

Eski sitedeki adresler korunmuştur (`/hakkimizda`, `/iletisim`, `/mice`, `/kurumsal`, `/tatil`, `/girne`, `/lefkosa` …).
Eski ürün adresleri (`/urun/otel-adi`) yeni otel sayfalarına kalıcı olarak yönlendirilir.

## Yönetim paneli

| Menü | Yönetilenler |
| --- | --- |
| Slider / Video | Anasayfa üstündeki video ve görsel slaytlar, metinler, düğmeler, karartma, sıra |
| Bölümler ve Sıralama | Anasayfa bölümlerinin sırası (sürükle-bırak), görünürlüğü, başlıkları, arka plan stili, otel ızgarası / şerit seçimi |
| Oteller | Otel bilgileri, görsel ve galeri, bölge, konsept, sıra (sürükle-bırak), yayında anahtarı |
| Hizmetler, Bölgeler, Sayfalar, Hotel Factsheets | Zengin metin düzenleyicili içerikler, görseller, PDF'ler, SEO alanları |
| Sayaçlar, Garanti Ediyoruz, Öz Değerlerimiz, Hizmet Listesi, Talep Adımları | Kısa liste öğeleri ve simgeleri |
| Menüler | Üst menü (açılır alt menülerle) ve iki alt menü sütunu |
| Renkler ve Stil | Renkler, yazı tipleri, düğme biçimi ve stili, köşe yuvarlaklığı, kart stili, genişlik, boşluklar, animasyonlar (canlı önizlemeli) |
| Genel ve Logo | Site adı, logolar, favicon, üst şerit yazısı ve düğmeleri |
| İletişim ve Sosyal | Telefon, e-posta, adresler, harita, sosyal medya, CAPTCHA, form bildirimleri |
| Sayfa Metinleri, Alt Bilgi, SEO ve Kodlar, Dil / Çeviri | Sabit başlıklar, alt bilgi, meta bilgiler, analiz kodları, dil seçici |
| Mesajlar | İletişim ve talep formundan gelen başvurular |
| Medya Kütüphanesi | Görsel, video ve PDF yükleme / silme |

## Güvenlik notları

- Şifreler `password_hash` ile saklanır; giriş denemeleri IP ve kullanıcı adına göre sınırlandırılır.
- Durum değiştiren tüm panel istekleri POST + CSRF belirteci ister.
- Yüklenen dosyaların türü içerikten (MIME) doğrulanır; `uploads/` içinde PHP çalıştırılamaz.
- Formlarda CAPTCHA (görsel işlem sorusu ya da Cloudflare Turnstile), gizli tuzak alanı ve saatlik gönderim sınırı vardır.
- `app/config.php` depoya girmez; şifreleri bu dosyanın dışında bir yere yazmayın.
