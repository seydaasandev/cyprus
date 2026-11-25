<?php
/**
 * Yönetim panelinde düzenlenen içerik türlerinin ve ayar gruplarının tanımları.
 * Liste ve form ekranları bu tanımlardan otomatik üretilir.
 *
 * Alan türleri: text, textarea, html, image, video, file, select, bool, number,
 * color, slug, icon, relation, url, range, checks
 */
declare(strict_types=1);

function style_options(): array
{
    return ['light' => 'Beyaz', 'soft' => 'Açık ton', 'brand' => 'Marka rengi (koyu)', 'dark' => 'Koyu'];
}

function seo_fields(): array
{
    return [
        'meta_title' => ['label' => 'SEO başlığı', 'type' => 'text', 'help' => 'Boş bırakılırsa sayfa başlığı kullanılır.', 'group' => 'SEO'],
        'meta_desc' => ['label' => 'SEO açıklaması', 'type' => 'textarea', 'rows' => 2, 'max' => 400, 'help' => 'Arama sonuçlarında görünen kısa açıklama.', 'group' => 'SEO'],
    ];
}

function item_entity(string $grp, string $title, string $single, string $icon, array $fields, string $help = ''): array
{
    return [
        'table' => 'items', 'title' => $title, 'single' => $single, 'icon' => $icon, 'label' => 'title',
        'where' => ['grp' => $grp], 'sortable' => true, 'toggle' => 'active', 'help' => $help,
        'columns' => array_intersect_key(['val' => 'Değer', 'title' => 'Başlık', 'body' => 'Açıklama'], $fields),
        'fields' => $fields + ['active' => ['label' => 'Yayında', 'type' => 'bool', 'default' => 1]],
    ];
}

function menu_entity(string $menu, string $title): array
{
    return [
        'table' => 'menu_items', 'title' => $title, 'single' => 'Menü öğesi', 'icon' => 'list', 'label' => 'title',
        'where' => ['menu' => $menu], 'sortable' => true, 'toggle' => 'active', 'tree' => true,
        'help' => 'Sırayı değiştirmek için satırları tutup sürükleyin. Alt menü yapmak için öğeyi düzenleyip "Üst öğe" seçin.',
        'columns' => ['title' => 'Başlık', 'url' => 'Bağlantı'],
        'fields' => [
            'title' => ['label' => 'Başlık', 'type' => 'text', 'required' => true],
            'url' => ['label' => 'Bağlantı', 'type' => 'url', 'help' => 'Site içi sayfa için listeden seçin ya da "oteller" gibi yazın. Dış bağlantı için https:// ile başlayan tam adres girin. Yalnızca alt menü açan öğeler için # yazabilirsiniz.'],
            'parent_id' => ['label' => 'Üst öğe', 'type' => 'relation', 'table' => 'menu_items', 'label_col' => 'title', 'where' => ['menu' => $menu, 'parent_id' => null], 'empty' => '— Ana öğe —', 'help' => 'Seçilirse bu öğe, seçilen öğenin altında açılır menüde görünür.'],
            'new_tab' => ['label' => 'Yeni sekmede aç', 'type' => 'bool'],
            'active' => ['label' => 'Yayında', 'type' => 'bool', 'default' => 1],
        ],
    ];
}

function entities(): array
{
    return [
        'slides' => [
            'table' => 'slides', 'title' => 'Slider / Video', 'single' => 'Slayt', 'icon' => 'film', 'label' => 'title',
            'sortable' => true, 'toggle' => 'active',
            'help' => 'Anasayfanın en üstündeki tam ekran alan. Her slayt video ya da görsel olabilir; sırayı sürükleyerek değiştirin.',
            'columns' => ['media' => 'Önizleme', 'title' => 'Başlık', 'kind' => 'Tür'],
            'fields' => [
                'kind' => ['label' => 'Tür', 'type' => 'select', 'options' => ['video' => 'Video', 'image' => 'Görsel'], 'default' => 'image'],
                'media' => ['label' => 'Video / görsel dosyası', 'type' => 'image', 'kinds' => ['image', 'video'], 'help' => 'Video için MP4 ya da WebM; görsel için en az 1920 piksel genişlik önerilir.'],
                'poster' => ['label' => 'Video kapak görseli', 'type' => 'image', 'help' => 'Video yüklenene kadar gösterilir (isteğe bağlı).'],
                'kicker' => ['label' => 'Üst küçük başlık', 'type' => 'text'],
                'title' => ['label' => 'Başlık', 'type' => 'text', 'help' => 'Bir sözcüğü vurgulamak için <u>sözcük</u> şeklinde yazın.'],
                'body' => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 3],
                'btn1_text' => ['label' => '1. düğme yazısı', 'type' => 'text', 'group' => 'Düğmeler'],
                'btn1_url' => ['label' => '1. düğme bağlantısı', 'type' => 'url', 'group' => 'Düğmeler'],
                'btn2_text' => ['label' => '2. düğme yazısı', 'type' => 'text', 'group' => 'Düğmeler'],
                'btn2_url' => ['label' => '2. düğme bağlantısı', 'type' => 'url', 'group' => 'Düğmeler'],
                'overlay' => ['label' => 'Karartma (%)', 'type' => 'range', 'min' => 0, 'max' => 90, 'default' => 45, 'help' => 'Yazıların okunması için görselin üzerine binen renk katmanı.', 'group' => 'Görünüm'],
                'align' => ['label' => 'Yazı hizası', 'type' => 'select', 'options' => ['left' => 'Sola', 'center' => 'Ortaya'], 'default' => 'left', 'group' => 'Görünüm'],
                'active' => ['label' => 'Yayında', 'type' => 'bool', 'default' => 1, 'group' => 'Görünüm'],
            ],
        ],
        'sections' => [
            'table' => 'sections', 'title' => 'Anasayfa Bölümleri', 'single' => 'Bölüm', 'icon' => 'layers', 'label' => 'name',
            'sortable' => true, 'toggle' => 'active', 'fixed' => true,
            'help' => 'Anasayfadaki bölümlerin sırası, görünürlüğü, başlıkları ve arka plan stili. Sırayı değiştirmek için satırı sürükleyin; bölümü gizlemek için anahtarı kapatın.',
            'columns' => ['name' => 'Bölüm', 'title' => 'Başlık', 'style' => 'Arka plan'],
            'fields' => [
                'name' => ['label' => 'Bölüm adı (yalnızca panelde görünür)', 'type' => 'text', 'required' => true],
                'kicker' => ['label' => 'Üst küçük başlık', 'type' => 'text'],
                'title' => ['label' => 'Başlık', 'type' => 'text', 'help' => 'Bir sözcüğü vurgulamak için <u>sözcük</u> şeklinde yazın.'],
                'body' => ['label' => 'Metin', 'type' => 'html', 'help' => 'Tanıtım bölümünde zengin metin olarak, diğer bölümlerde başlığın altında kısa açıklama olarak görünür.'],
                'image' => ['label' => 'Görsel', 'type' => 'image', 'help' => 'Tanıtım, sayaç ve çağrı bölümlerinde kullanılır.'],
                'btn_text' => ['label' => 'Düğme yazısı', 'type' => 'text', 'group' => 'Düğme'],
                'btn_url' => ['label' => 'Düğme bağlantısı', 'type' => 'url', 'group' => 'Düğme'],
                'style' => ['label' => 'Arka plan stili', 'type' => 'select', 'options' => style_options(), 'default' => 'light', 'group' => 'Görünüm'],
                'layout' => ['label' => 'Yerleşim', 'type' => 'select', 'options' => ['grid' => '4\'lü ızgara', 'carousel' => 'Kaydırmalı şerit'], 'help' => 'Oteller bölümü için geçerlidir.', 'group' => 'Görünüm'],
                'lim' => ['label' => 'Gösterilecek öğe sayısı', 'type' => 'number', 'min' => 0, 'max' => 60, 'help' => '0 = tümü.', 'group' => 'Görünüm'],
                'active' => ['label' => 'Anasayfada göster', 'type' => 'bool', 'default' => 1, 'group' => 'Görünüm'],
            ],
        ],
        'hotels' => [
            'table' => 'hotels', 'title' => 'Oteller', 'single' => 'Otel', 'icon' => 'building', 'label' => 'name',
            'sortable' => true, 'toggle' => 'active', 'view' => 'oteller/{slug}',
            'gallery' => ['table' => 'hotel_images', 'fk' => 'hotel_id'],
            'rooms' => true,
            'help' => 'Çevrimiçi satış ya da ödeme yoktur. Her otel için oda seçeneklerini ve fiyatlarını otelin düzenleme ekranından yazabilirsiniz; rezervasyon düğmesi açıksa (Sayfa Metinleri > Oteller sayfası) ziyaretçi "Rezervasyon Yap" ile bilgilerini doldurur, talep size e-posta ile gelir ve Mesajlar bölümüne düşer.',
            'columns' => ['image' => 'Görsel', 'name' => 'Otel', 'location' => 'Konum', 'concept' => 'Konsept'],
            'fields' => [
                'name' => ['label' => 'Otel adı', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'Sayfa adresi', 'type' => 'slug', 'from' => 'name', 'prefix' => 'oteller/'],
                'image' => ['label' => 'Ana görsel', 'type' => 'image'],
                'location' => ['label' => 'Konum', 'type' => 'text', 'help' => 'Örn. Çatalköy, Kyrenia / Northern Cyprus'],
                'region_id' => ['label' => 'Bölge', 'type' => 'relation', 'table' => 'regions', 'label_col' => 'title', 'empty' => '— Seçilmedi —'],
                'concept' => ['label' => 'Konsept', 'type' => 'text', 'help' => 'Örn. Ultra All Inclusive (UAI)'],
                'stars' => ['label' => 'Yıldız', 'type' => 'number', 'min' => 0, 'max' => 7, 'default' => 5],
                'summary' => ['label' => 'Öne çıkan bilgiler', 'type' => 'textarea', 'rows' => 7, 'help' => 'Her satır ayrı bir bilgi kutusu olur. "Başlık: açıklama" biçiminde yazılan satırlar başlıklı kutu olarak gösterilir.'],
                'description' => ['label' => 'Açıklama', 'type' => 'html'],
                'features' => ['label' => 'Özellik etiketleri', 'type' => 'textarea', 'rows' => 5, 'help' => 'Her satıra bir özellik (örn. Casino, SPA, Özel plaj).'],
                'factsheet' => ['label' => 'Factsheet (PDF)', 'type' => 'file', 'kinds' => ['file'], 'group' => 'Ek bilgiler'],
                'website' => ['label' => 'Otel web sitesi', 'type' => 'text', 'help' => 'https:// ile başlayan tam adres (isteğe bağlı).', 'group' => 'Ek bilgiler'],
                'featured' => ['label' => 'Anasayfada göster', 'type' => 'bool', 'default' => 1, 'group' => 'Ek bilgiler'],
                'active' => ['label' => 'Yayında', 'type' => 'bool', 'default' => 1, 'group' => 'Ek bilgiler'],
            ] + seo_fields(),
        ],
        'services' => [
            'table' => 'services', 'title' => 'Hizmetler', 'single' => 'Hizmet', 'icon' => 'briefcase', 'label' => 'title',
            'sortable' => true, 'toggle' => 'active', 'view' => '{slug}',
            'columns' => ['image' => 'Görsel', 'title' => 'Hizmet', 'summary' => 'Özet'],
            'fields' => [
                'title' => ['label' => 'Başlık', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'Sayfa adresi', 'type' => 'slug', 'from' => 'title'],
                'kicker' => ['label' => 'Üst küçük başlık', 'type' => 'text'],
                'icon' => ['label' => 'Simge', 'type' => 'icon', 'default' => 'star'],
                'image' => ['label' => 'Görsel', 'type' => 'image'],
                'summary' => ['label' => 'Kısa özet', 'type' => 'textarea', 'rows' => 3, 'help' => 'Anasayfa ve liste kartlarında görünür.'],
                'content' => ['label' => 'İçerik', 'type' => 'html'],
                'active' => ['label' => 'Yayında', 'type' => 'bool', 'default' => 1],
            ] + seo_fields(),
        ],
        'regions' => [
            'table' => 'regions', 'title' => 'Bölgeler (Kuzey Kıbrıs)', 'single' => 'Bölge', 'icon' => 'map-pin', 'label' => 'title',
            'sortable' => true, 'toggle' => 'active', 'view' => '{slug}',
            'columns' => ['image' => 'Görsel', 'title' => 'Bölge', 'summary' => 'Özet'],
            'fields' => [
                'title' => ['label' => 'Bölge adı', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'Sayfa adresi', 'type' => 'slug', 'from' => 'title'],
                'image' => ['label' => 'Kart görseli', 'type' => 'image'],
                'cover' => ['label' => 'Sayfa üst görseli', 'type' => 'image', 'help' => 'Boşsa kart görseli kullanılır.'],
                'summary' => ['label' => 'Kısa özet', 'type' => 'textarea', 'rows' => 3],
                'content' => ['label' => 'İçerik', 'type' => 'html'],
                'active' => ['label' => 'Yayında', 'type' => 'bool', 'default' => 1],
            ] + seo_fields(),
        ],
        'factsheets' => [
            'table' => 'factsheets', 'title' => 'Hotel Factsheets', 'single' => 'Factsheet', 'icon' => 'file', 'label' => 'title',
            'sortable' => true, 'toggle' => 'active', 'view' => '{slug}',
            'columns' => ['title' => 'Başlık', 'file' => 'Dosya'],
            'fields' => [
                'title' => ['label' => 'Başlık', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'Sayfa adresi', 'type' => 'slug', 'from' => 'title'],
                'file' => ['label' => 'PDF dosyası', 'type' => 'file', 'kinds' => ['file']],
                'hotel_id' => ['label' => 'İlgili otel', 'type' => 'relation', 'table' => 'hotels', 'label_col' => 'name', 'empty' => '— Seçilmedi —', 'help' => 'Seçilirse otel sayfasında da gösterilir ve otelin görseli kapak olur.'],
                'image' => ['label' => 'Kapak görseli', 'type' => 'image', 'help' => 'Boşsa ilgili otelin görseli kullanılır.'],
                'active' => ['label' => 'Yayında', 'type' => 'bool', 'default' => 1],
            ],
        ],
        'pages' => [
            'table' => 'pages', 'title' => 'Sayfalar', 'single' => 'Sayfa', 'icon' => 'file', 'label' => 'title',
            'sortable' => true, 'toggle' => 'active', 'view' => '{slug}',
            'help' => 'Hakkımızda, sözleşmeler ve diğer serbest içerikli sayfalar. Yeni sayfa ekleyip menüden bağlantı verebilirsiniz.',
            'columns' => ['title' => 'Sayfa', 'slug' => 'Adres'],
            'fields' => [
                'title' => ['label' => 'Başlık', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'Sayfa adresi', 'type' => 'slug', 'from' => 'title'],
                'subtitle' => ['label' => 'Üst küçük başlık', 'type' => 'text'],
                'content' => ['label' => 'İçerik', 'type' => 'html'],
                'image' => ['label' => 'Yan görsel', 'type' => 'image', 'help' => 'Eklenirse içerik iki sütunlu gösterilir (Hakkımızda gibi).'],
                'cover' => ['label' => 'Sayfa üst görseli', 'type' => 'image'],
                'active' => ['label' => 'Yayında', 'type' => 'bool', 'default' => 1],
            ] + seo_fields(),
        ],
        'counters' => item_entity('counter', 'Sayaçlar', 'Sayaç', 'target', [
            'val' => ['label' => 'Değer', 'type' => 'text', 'required' => true, 'help' => 'Örn. 20+, 50+, 7/24'],
            'title' => ['label' => 'Açıklama', 'type' => 'text', 'required' => true],
            'icon' => ['label' => 'Simge', 'type' => 'icon', 'default' => 'award'],
        ], 'Anasayfadaki ve Hakkımızda sayfasındaki rakamlar.'),
        'guarantees' => item_entity('guarantee', 'Garanti Ediyoruz', 'Madde', 'shield', [
            'title' => ['label' => 'Başlık', 'type' => 'text', 'required' => true],
            'body' => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2],
            'icon' => ['label' => 'Simge', 'type' => 'icon', 'default' => 'shield'],
        ]),
        'values' => item_entity('value', 'Öz Değerlerimiz', 'Değer', 'heart', [
            'title' => ['label' => 'Değer', 'type' => 'text', 'required' => true],
            'icon' => ['label' => 'Simge', 'type' => 'icon', 'default' => 'check'],
        ]),
        'offers' => item_entity('offer', 'Hizmet Listesi (Hakkımızda)', 'Hizmet', 'check-circle', [
            'title' => ['label' => 'Hizmet', 'type' => 'text', 'required' => true],
            'icon' => ['label' => 'Simge', 'type' => 'icon', 'default' => 'check'],
        ], 'Hakkımızda sayfasındaki "Hizmetlerimiz" listesi.'),
        'steps' => item_entity('step', 'Talep Formu Adımları', 'Adım', 'list', [
            'title' => ['label' => 'Başlık', 'type' => 'text', 'required' => true],
            'body' => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2],
        ], 'Talep formu sayfasının solunda numaralı adımlar olarak görünür (isteğe bağlı).'),
        'menu_header' => menu_entity('header', 'Üst Menü'),
        'menu_footer' => menu_entity('footer', 'Alt Menü (1. sütun)'),
        'menu_footer2' => menu_entity('footer2', 'Alt Menü (2. sütun)'),
        'users' => [
            'table' => 'users', 'title' => 'Yöneticiler', 'single' => 'Yönetici', 'icon' => 'user', 'label' => 'username',
            'columns' => ['username' => 'Kullanıcı adı', 'name' => 'Ad Soyad', 'last_login' => 'Son giriş'],
            'toggle' => 'active', 'protect_self' => true,
            'fields' => [
                'username' => ['label' => 'Kullanıcı adı', 'type' => 'text', 'required' => true, 'pattern' => '[A-Za-z0-9_.\-]{3,60}', 'help' => 'En az 3 karakter; harf, rakam, nokta, tire, alt çizgi.'],
                'name' => ['label' => 'Ad Soyad', 'type' => 'text'],
                'email' => ['label' => 'E-posta', 'type' => 'text'],
                'password' => ['label' => 'Şifre', 'type' => 'password', 'help' => 'En az 10 karakter. Düzenlerken boş bırakılırsa şifre değişmez.'],
                'active' => ['label' => 'Giriş yapabilir', 'type' => 'bool', 'default' => 1],
            ],
        ],
    ];
}

/** Ayar grupları: sekme => [başlık, simge, alanlar]. */
function settings_schema(): array
{
    $d = theme_defaults();
    $fonts = array_combine(array_keys(font_catalog()), array_keys(font_catalog()));
    $color = static fn(string $label, string $key, string $help = ''): array => ['label' => $label, 'type' => 'color', 'default' => $d[$key], 'help' => $help, 'group' => 'Renkler'];
    $langNames = ['tr' => 'Türkçe', 'en' => 'English', 'de' => 'Deutsch', 'ru' => 'Русский', 'ar' => 'العربية', 'fr' => 'Français', 'el' => 'Ελληνικά', 'iw' => 'עברית', 'sk' => 'Slovenčina', 'it' => 'Italiano', 'es' => 'Español', 'fa' => 'فارسی', 'nl' => 'Nederlands', 'pl' => 'Polski', 'uk' => 'Українська', 'zh-CN' => '中文'];

    return [
        'general' => ['title' => 'Genel ve Logo', 'icon' => 'settings', 'fields' => [
            'site_name' => ['label' => 'Site adı', 'type' => 'text', 'default' => $d['site_name'], 'group' => 'Kimlik'],
            'site_url' => ['label' => 'Site adresi (alan adı)', 'type' => 'text', 'help' => 'Örn. https://cyprustouristic.com — klasör yazmayın. Boş bırakılırsa ziyaret edilen adres kullanılır. E-posta bildirimlerindeki ve arama motorlarına verilen bağlantılar bu adrese göre üretilir; siteyi taşıdığınızda güncelleyin.', 'group' => 'Kimlik'],
            'tagline' => ['label' => 'Slogan', 'type' => 'text', 'default' => $d['tagline'], 'group' => 'Kimlik'],
            'logo' => ['label' => 'Logo (açık zemin için, renkli)', 'type' => 'image', 'group' => 'Logolar'],
            'logo_light' => ['label' => 'Logo (koyu zemin için, beyaz)', 'type' => 'image', 'help' => 'İsteğe bağlı. Yüklenirse menü video üzerinde şeffaf olur ve bu logo kullanılır; boşsa menü beyaz bant olarak görünür ve renkli logo kullanılır.', 'group' => 'Logolar'],
            'favicon' => ['label' => 'Tarayıcı simgesi (favicon)', 'type' => 'image', 'group' => 'Logolar'],
            'logo_height' => ['label' => 'Logo yüksekliği (piksel)', 'type' => 'range', 'min' => 24, 'max' => 120, 'default' => $d['logo_height'], 'group' => 'Logolar'],
            'topbar_text' => ['label' => 'Üst şerit yazısı', 'type' => 'text', 'group' => 'Üst şerit ve düğmeler'],
            'hbtn1_text' => ['label' => '1. üst şerit düğmesi yazısı (dolgulu)', 'type' => 'text', 'help' => 'Örn. Talep Formu. Boş bırakılırsa düğme gizlenir.', 'group' => 'Üst şerit ve düğmeler'],
            'hbtn1_url' => ['label' => '1. üst şerit düğmesi bağlantısı', 'type' => 'url', 'group' => 'Üst şerit ve düğmeler'],
            'hbtn2_text' => ['label' => '2. üst şerit düğmesi yazısı (çerçeveli)', 'type' => 'text', 'help' => 'Örn. Kurumsal Giriş. Boş bırakılırsa gizlenir.', 'group' => 'Üst şerit ve düğmeler'],
            'hbtn2_url' => ['label' => '2. üst şerit düğmesi bağlantısı', 'type' => 'url', 'group' => 'Üst şerit ve düğmeler'],
        ]],
        'appearance' => ['title' => 'Renkler ve Stil', 'icon' => 'palette', 'fields' => [
            'c_primary' => $color('Ana renk', 'c_primary', 'Düğmeler, başlık bantları, bağlantılar.'),
            'c_secondary' => $color('İkinci renk', 'c_secondary', 'Geçişler ve simgeler.'),
            'c_accent' => $color('Vurgu rengi', 'c_accent', 'Etiketler, çizgiler, öne çıkan düğmeler.'),
            'c_dark' => $color('Koyu renk', 'c_dark', 'Başlık yazıları ve koyu bölümler.'),
            'c_text' => $color('Metin rengi', 'c_text'),
            'c_muted' => $color('Soluk metin rengi', 'c_muted'),
            'c_bg' => $color('Sayfa zemini', 'c_bg'),
            'c_soft' => $color('Açık ton bölüm zemini', 'c_soft'),
            'c_footer' => $color('Alt bilgi zemini', 'c_footer'),
            'font_heading' => ['label' => 'Başlık yazı tipi', 'type' => 'select', 'options' => $fonts, 'default' => $d['font_heading'], 'group' => 'Yazı tipleri'],
            'font_body' => ['label' => 'Metin yazı tipi', 'type' => 'select', 'options' => $fonts, 'default' => $d['font_body'], 'group' => 'Yazı tipleri'],
            'font_size' => ['label' => 'Temel yazı boyutu (piksel)', 'type' => 'range', 'min' => 13, 'max' => 20, 'default' => $d['font_size'], 'group' => 'Yazı tipleri'],
            'heading_weight' => ['label' => 'Başlık kalınlığı', 'type' => 'select', 'options' => ['400' => 'İnce', '500' => 'Orta', '600' => 'Yarı kalın', '700' => 'Kalın'], 'default' => $d['heading_weight'], 'group' => 'Yazı tipleri'],
            'btn_shape' => ['label' => 'Düğme biçimi', 'type' => 'select', 'options' => ['pill' => 'Tam yuvarlak', 'rounded' => 'Hafif yuvarlak', 'square' => 'Köşeli'], 'default' => $d['btn_shape'], 'group' => 'Düğmeler ve kartlar'],
            'btn_style' => ['label' => 'Düğme stili', 'type' => 'select', 'options' => ['gradient' => 'Renk geçişli', 'solid' => 'Düz renk', 'outline' => 'Çerçeveli'], 'default' => $d['btn_style'], 'group' => 'Düğmeler ve kartlar'],
            'btn_upper' => ['label' => 'Düğme yazıları BÜYÜK HARF', 'type' => 'bool', 'group' => 'Düğmeler ve kartlar'],
            'radius' => ['label' => 'Köşe yuvarlaklığı (piksel)', 'type' => 'range', 'min' => 0, 'max' => 40, 'default' => $d['radius'], 'group' => 'Düğmeler ve kartlar'],
            'card_style' => ['label' => 'Kart stili', 'type' => 'select', 'options' => ['shadow' => 'Gölgeli', 'border' => 'Çerçeveli', 'flat' => 'Düz'], 'default' => $d['card_style'], 'group' => 'Düğmeler ve kartlar'],
            'container' => ['label' => 'İçerik genişliği (piksel)', 'type' => 'range', 'min' => 960, 'max' => 1600, 'step' => 20, 'default' => $d['container'], 'group' => 'Düzen'],
            'section_space' => ['label' => 'Bölümler arası boşluk (piksel)', 'type' => 'range', 'min' => 40, 'max' => 200, 'step' => 5, 'default' => $d['section_space'], 'group' => 'Düzen'],
            'hero_height' => ['label' => 'Slider yüksekliği (ekranın %\'si)', 'type' => 'range', 'min' => 50, 'max' => 100, 'default' => $d['hero_height'], 'group' => 'Düzen'],
            'hero_autoplay' => ['label' => 'Slayt süresi (saniye)', 'type' => 'range', 'min' => 3, 'max' => 30, 'default' => $d['hero_autoplay'], 'group' => 'Düzen'],
            'header_transparent' => ['label' => 'Menü görselin üzerinde şeffaf başlasın', 'type' => 'bool', 'default' => 1, 'group' => 'Üst menü'],
            'header_sticky' => ['label' => 'Menü kaydırınca üstte sabit kalsın', 'type' => 'bool', 'default' => 1, 'group' => 'Üst menü'],
            'topbar' => ['label' => 'Üst bilgi şeridini göster', 'type' => 'bool', 'default' => 1, 'group' => 'Üst menü'],
            'anim' => ['label' => 'Animasyonlar açık', 'type' => 'bool', 'default' => 1, 'group' => 'Hareket'],
            'anim_style' => ['label' => 'Belirme animasyonu', 'type' => 'select', 'options' => ['fade-up' => 'Aşağıdan yukarı', 'zoom' => 'Yakınlaşma', 'slide' => 'Yandan kayma', 'blur' => 'Bulanıktan netleşme'], 'default' => $d['anim_style'], 'group' => 'Hareket'],
            'preloader' => ['label' => 'Açılış ekranını göster', 'type' => 'bool', 'default' => 1, 'group' => 'Hareket'],
            'float_whatsapp' => ['label' => 'Yüzen WhatsApp düğmesi', 'type' => 'bool', 'default' => 1, 'help' => 'İletişim ayarlarında WhatsApp numarası girildiyse görünür.', 'group' => 'Hareket'],
            'custom_css' => ['label' => 'Özel CSS', 'type' => 'code', 'rows' => 8, 'help' => 'İleri düzey: buraya yazılan CSS tüm sayfalara eklenir.', 'group' => 'Gelişmiş'],
        ]],
        'contact' => ['title' => 'İletişim ve Sosyal', 'icon' => 'phone', 'fields' => [
            'phone' => ['label' => 'Telefon', 'type' => 'text', 'group' => 'İletişim'],
            'email' => ['label' => 'E-posta', 'type' => 'text', 'group' => 'İletişim'],
            'whatsapp' => ['label' => 'WhatsApp numarası', 'type' => 'text', 'help' => 'Ülke koduyla, yalnızca rakam (örn. 905391232020).', 'group' => 'İletişim'],
            'address_label' => ['label' => '1. adres başlığı', 'type' => 'text', 'default' => 'KKTC', 'group' => 'Adresler'],
            'address' => ['label' => '1. adres', 'type' => 'textarea', 'rows' => 2, 'group' => 'Adresler'],
            'address2_label' => ['label' => '2. adres başlığı', 'type' => 'text', 'default' => 'Türkiye', 'group' => 'Adresler'],
            'address2' => ['label' => '2. adres', 'type' => 'textarea', 'rows' => 2, 'group' => 'Adresler'],
            'map_embed' => ['label' => 'Google Haritalar yerleştirme adresi', 'type' => 'text', 'help' => 'Google Haritalar > Paylaş > Harita yerleştir bölümündeki src adresi (https://www.google.com/maps/embed?... ile başlar).', 'group' => 'Adresler'],
            'social_mail' => ['label' => 'Sosyal ikonlardaki e-posta', 'type' => 'text', 'help' => 'Sosyal medya ikonlarının yanında zarf ikonu olarak görünür (ör. rezervasyon adresi). Boşsa gösterilmez.', 'group' => 'Sosyal medya'],
            'social_instagram' => ['label' => 'Instagram', 'type' => 'text', 'group' => 'Sosyal medya'],
            'social_facebook' => ['label' => 'Facebook', 'type' => 'text', 'group' => 'Sosyal medya'],
            'social_x-twitter' => ['label' => 'X (Twitter)', 'type' => 'text', 'group' => 'Sosyal medya'],
            'social_youtube' => ['label' => 'YouTube', 'type' => 'text', 'group' => 'Sosyal medya'],
            'social_linkedin' => ['label' => 'LinkedIn', 'type' => 'text', 'group' => 'Sosyal medya'],
            'social_tiktok' => ['label' => 'TikTok', 'type' => 'text', 'group' => 'Sosyal medya'],
            'social_telegram' => ['label' => 'Telegram', 'type' => 'text', 'group' => 'Sosyal medya'],
            'captcha_mode' => ['label' => 'Form doğrulaması (CAPTCHA)', 'type' => 'select', 'options' => ['math' => 'Görsel işlem sorusu (anahtar gerekmez)', 'turnstile' => 'Cloudflare Turnstile', 'off' => 'Kapalı'], 'default' => 'math', 'help' => 'Turnstile için aşağıdaki iki anahtar gerekir; anahtarlar boşsa görsel işlem sorusu kullanılır.', 'group' => 'Form güvenliği'],
            'turnstile_site' => ['label' => 'Turnstile site anahtarı', 'type' => 'text', 'group' => 'Form güvenliği'],
            'turnstile_secret' => ['label' => 'Turnstile gizli anahtarı', 'type' => 'text', 'group' => 'Form güvenliği'],
            'mail_enabled' => ['label' => 'Form gönderimlerini e-posta ile bildir', 'type' => 'bool', 'help' => 'Kapalıyken mesajlar yalnızca paneldeki "Mesajlar" bölümüne düşer.', 'group' => 'Form bildirimleri'],
            'mail_to' => ['label' => 'Bildirimlerin gideceği e-posta', 'type' => 'text', 'help' => 'Boşsa yukarıdaki e-posta adresi kullanılır.', 'group' => 'Form bildirimleri'],
            'reservation_mail_to' => ['label' => 'Rezervasyon taleplerinin gideceği e-posta', 'type' => 'text', 'help' => 'Örn. reservations@cyprustouristic.com. Boşsa yukarıdaki bildirim adresi kullanılır.', 'group' => 'Form bildirimleri'],
            'mail_from' => ['label' => 'Gönderen e-posta adresi', 'type' => 'text', 'help' => 'Sitenin alan adına ait bir adres olmalı (örn. noreply@alanadi.com).', 'group' => 'Form bildirimleri'],
        ]],
        'texts' => ['title' => 'Sayfa Başlıkları ve Metinler', 'icon' => 'edit', 'fields' => [
            'page_hero_image' => ['label' => 'Varsayılan sayfa üst görseli', 'type' => 'image', 'help' => 'Kendi görseli olmayan sayfaların başlık bandında kullanılır.', 'group' => 'Genel'],
            'label_details' => ['label' => '"Detaylar" bağlantı yazısı', 'type' => 'text', 'default' => 'Detaylar', 'group' => 'Genel'],
            'label_request' => ['label' => '"Talep Gönder" düğme yazısı', 'type' => 'text', 'default' => 'Talep Gönder', 'group' => 'Genel'],
            'label_all' => ['label' => '"Tümü" süzgeç yazısı', 'type' => 'text', 'default' => 'Tümü', 'group' => 'Genel'],
            'hotels_title' => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Oteller', 'group' => 'Oteller sayfası'],
            'hotels_kicker' => ['label' => 'Üst küçük başlık', 'type' => 'text', 'default' => 'Kuzey Kıbrıs', 'group' => 'Oteller sayfası'],
            'hotels_text' => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2, 'group' => 'Oteller sayfası'],
            'hotels_image' => ['label' => 'Üst görsel', 'type' => 'image', 'group' => 'Oteller sayfası'],
            'label_rooms' => ['label' => 'Oda listesi başlığı', 'type' => 'text', 'default' => 'Oda Seçenekleri', 'group' => 'Oteller sayfası'],
            'reserve_enabled' => ['label' => 'Rezervasyon düğmesini ve formunu göster', 'type' => 'bool', 'help' => 'Kapalıyken otel sayfalarında "Rezervasyon Yap" düğmesi çıkmaz ve rezervasyon formu sayfası açılmaz; oda seçenekleri yalnızca bilgi olarak listelenir.', 'group' => 'Oteller sayfası'],
            'label_book' => ['label' => 'Rezervasyon düğmesi yazısı', 'type' => 'text', 'default' => 'Rezervasyon Yap', 'group' => 'Oteller sayfası'],
            'rooms_note' => ['label' => 'Oda listesi altındaki not', 'type' => 'textarea', 'rows' => 2, 'help' => 'Örn. Fiyatlar bilgilendirme amaçlıdır; kesin fiyat rezervasyon onayında bildirilir. Boşsa gösterilmez.', 'group' => 'Oteller sayfası'],
            'label_features' => ['label' => 'Özellikler başlığı', 'type' => 'text', 'default' => 'Öne Çıkan Özellikler', 'group' => 'Oteller sayfası'],
            'label_other_hotels' => ['label' => 'Diğer oteller başlığı', 'type' => 'text', 'default' => 'Diğer Oteller', 'group' => 'Oteller sayfası'],
            'services_title' => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Hizmetlerimiz', 'group' => 'Hizmetler sayfası'],
            'services_kicker' => ['label' => 'Üst küçük başlık', 'type' => 'text', 'default' => 'Cyprus Touristic', 'group' => 'Hizmetler sayfası'],
            'services_text' => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2, 'group' => 'Hizmetler sayfası'],
            'services_image' => ['label' => 'Üst görsel', 'type' => 'image', 'group' => 'Hizmetler sayfası'],
            'regions_title' => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Kuzey Kıbrıs', 'group' => 'Bölgeler sayfası'],
            'regions_kicker' => ['label' => 'Üst küçük başlık', 'type' => 'text', 'default' => 'Cyprus Touristic', 'group' => 'Bölgeler sayfası'],
            'regions_text' => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2, 'group' => 'Bölgeler sayfası'],
            'regions_image' => ['label' => 'Üst görsel', 'type' => 'image', 'group' => 'Bölgeler sayfası'],
            'factsheets_title' => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Hotel Factsheets', 'group' => 'Factsheets sayfası'],
            'factsheets_kicker' => ['label' => 'Üst küçük başlık', 'type' => 'text', 'default' => 'Cyprus Touristic', 'group' => 'Factsheets sayfası'],
            'factsheets_text' => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2, 'group' => 'Factsheets sayfası'],
            'factsheets_image' => ['label' => 'Üst görsel', 'type' => 'image', 'group' => 'Factsheets sayfası'],
            'contact_title' => ['label' => 'Sayfa başlığı', 'type' => 'text', 'default' => 'İletişim', 'group' => 'İletişim sayfası'],
            'contact_heading' => ['label' => 'İçerik başlığı', 'type' => 'text', 'default' => 'Bizimle İletişime Geçin', 'group' => 'İletişim sayfası'],
            'contact_text' => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2, 'group' => 'İletişim sayfası'],
            'contact_form_title' => ['label' => 'Form başlığı', 'type' => 'text', 'default' => 'Bize Yazın', 'group' => 'İletişim sayfası'],
            'contact_image' => ['label' => 'Üst görsel', 'type' => 'image', 'group' => 'İletişim sayfası'],
            'request_title' => ['label' => 'Sayfa başlığı', 'type' => 'text', 'default' => 'Talep Formu', 'group' => 'Talep formu sayfası'],
            'request_heading' => ['label' => 'İçerik başlığı', 'type' => 'text', 'default' => 'Talebinizi Bize İletin', 'group' => 'Talep formu sayfası'],
            'request_text' => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2, 'group' => 'Talep formu sayfası'],
            'request_image' => ['label' => 'Üst görsel', 'type' => 'image', 'group' => 'Talep formu sayfası'],
            'reserve_title' => ['label' => 'Sayfa başlığı', 'type' => 'text', 'default' => 'Rezervasyon Talebi', 'group' => 'Rezervasyon formu sayfası'],
            'reserve_heading' => ['label' => 'İçerik başlığı', 'type' => 'text', 'default' => 'Rezervasyon Talebinizi İletin', 'group' => 'Rezervasyon formu sayfası'],
            'reserve_text' => ['label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2, 'group' => 'Rezervasyon formu sayfası'],
            'reserve_note' => ['label' => 'Bilgi notu', 'type' => 'textarea', 'rows' => 2, 'help' => 'Formun yanında küçük kutu olarak görünür. Boşsa gösterilmez.', 'group' => 'Rezervasyon formu sayfası'],
            'label_book_send' => ['label' => 'Gönder düğmesi yazısı', 'type' => 'text', 'default' => 'Rezervasyon Talebi Gönder', 'group' => 'Rezervasyon formu sayfası'],
            'reserve_ok_title' => ['label' => 'Rezervasyon formu başarı başlığı', 'type' => 'text', 'default' => 'Rezervasyon talebiniz bize ulaştı.', 'group' => 'Form mesajları'],
            'form_ok_title' => ['label' => 'İletişim formu başarı başlığı', 'type' => 'text', 'default' => 'Mesajınız bize ulaştı.', 'group' => 'Form mesajları'],
            'request_ok_title' => ['label' => 'Talep formu başarı başlığı', 'type' => 'text', 'default' => 'Talebiniz bize ulaştı.', 'group' => 'Form mesajları'],
            'form_ok_text' => ['label' => 'Başarı açıklaması', 'type' => 'text', 'default' => 'En kısa sürede sizinle iletişime geçeceğiz.', 'group' => 'Form mesajları'],
            'about_slug' => ['label' => 'Hakkımızda sayfasının adresi', 'type' => 'text', 'default' => 'hakkimizda', 'help' => 'Bu adresteki sayfanın altında sayaçlar, hizmet listesi ve değerler gösterilir.', 'group' => 'Hakkımızda'],
            'offers_title' => ['label' => 'Hizmet listesi başlığı', 'type' => 'text', 'default' => 'Hizmetlerimiz', 'group' => 'Hakkımızda'],
            'guarantee_title' => ['label' => 'Garanti başlığı', 'type' => 'text', 'default' => 'Garanti ediyoruz', 'group' => 'Hakkımızda'],
            'values_title' => ['label' => 'Değerler başlığı', 'type' => 'text', 'default' => 'Öz Değerlerimiz', 'group' => 'Hakkımızda'],
        ]],
        'footer' => ['title' => 'Alt Bilgi', 'icon' => 'layers', 'fields' => [
            'footer_about' => ['label' => 'Logo altındaki tanıtım yazısı', 'type' => 'textarea', 'rows' => 3],
            'footer_menu_title' => ['label' => '1. menü sütunu başlığı', 'type' => 'text', 'default' => 'Kurumsal'],
            'footer_menu2_title' => ['label' => '2. menü sütunu başlığı', 'type' => 'text', 'default' => 'Keşfet'],
            'footer_contact_title' => ['label' => 'İletişim sütunu başlığı', 'type' => 'text', 'default' => 'İletişim'],
            'copyright' => ['label' => 'Telif yazısı', 'type' => 'text', 'default' => 'Tüm Hakları Saklıdır © {yil} Cyprus Touristic.', 'help' => '{yil} yazan yere içinde bulunulan yıl gelir.'],
            'footer_credit' => ['label' => 'Tasarım imzası', 'type' => 'text', 'help' => 'Bağlantı için <a href="https://...">Ad</a> biçimini kullanabilirsiniz.'],
        ]],
        'seo' => ['title' => 'SEO ve Kodlar', 'icon' => 'search', 'fields' => [
            'meta_title' => ['label' => 'Anasayfa başlığı (title)', 'type' => 'text'],
            'meta_desc' => ['label' => 'Varsayılan site açıklaması', 'type' => 'textarea', 'rows' => 3],
            'og_image' => ['label' => 'Sosyal medya paylaşım görseli', 'type' => 'image'],
            'head_code' => ['label' => '<head> içine eklenecek kod', 'type' => 'code', 'rows' => 6, 'help' => 'Google Analytics, Search Console doğrulaması vb. Yalnızca güvendiğiniz kodları ekleyin.'],
            'body_code' => ['label' => '</body> öncesine eklenecek kod', 'type' => 'code', 'rows' => 6],
        ]],
        'translate' => ['title' => 'Dil / Çeviri', 'icon' => 'globe', 'fields' => [
            'tr_enabled' => ['label' => 'Dil seçiciyi göster (Google Çeviri)', 'type' => 'bool', 'default' => 1, 'help' => 'Ziyaretçi dil seçtiğinde sayfa Google Çeviri ile otomatik çevrilir.'],
            'tr_langs' => ['label' => 'Gösterilecek diller', 'type' => 'checks', 'options' => $langNames, 'default' => $d['tr_langs']],
        ]],
    ];
}
