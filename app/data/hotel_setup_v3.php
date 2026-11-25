<?php
/**
 * Sürüm 3 güncellemesinin verisi: otel sıralaması ve oda tipleri (müşterinin HOTELS.docx dosyasından).
 * Yalnızca run_migrations() tarafından bir kez okunur; sonraki düzenlemeler panelden yapılır.
 * Galeri görselleri uploads/oteller/galeri/<otel-slug>/ klasöründen dosya adı sırasıyla alınır.
 */
return [
    // Otellerin gösterim sırası; listede olmayan oteller sona eklenir.
    'order' => [
        'concorde-luxury-hotel',
        'acapulco-hotel',
        'salamis-hotel',
        'kaya-artemis-hotel',
        'limak-cyprus-deluxe-hotel-single',
        'concorde-aria-hotel-casino',
        'arkin-iskele-hotel',
        'grand-sapphire-hotel',
        'lords-palace-hotel-kyrenia',
        'cratos-hotel',
        'kaya-palazzo-hotel-kyrenia',
        'vuni-palace-hotel',
        'merit-park-hotel',
        'merit-royal-premium-hotel',
        'merit-royal-diamond-hotel',
        'lords-palace-lagoon-hotel',
    ],
    // otel slug'ı => [[oda adı, açıklama], ...]
    'rooms' => [
        'concorde-luxury-hotel' => [
            ['Deluxe Sea', '45 m² – 1 sofa bed. Max capacity: 2+2 – TRP'],
            ['Deluxe Side Sea', '45 m² – 1 sofa bed. Max capacity: 2+2 – TRP'],
            ['Deluxe Garden', '45 m² – 1 sofa bed. Max capacity: 2+2 – TRP'],
            ['Corner Suite', '60 m² – 1 sofa bed. Max capacity: 2+2 – TRP'],
            ['Panoramic Suite', '60 m² – 1 sofa bed. Max capacity: 2+2 – TRP'],
            ['Ocean Suite', '60 m² – 1 sofa bed. Max capacity: 2+2 – TRP'],
            ['Family Lagoon Suite', "95 m² – There is a double bed in the master bedroom, two single beds in the children's room. Max capacity: 4+1"],
            ['Family Pool Suite', '95 m² – 2nd and 3rd-floor rooms situated around the shared pool with a pool view. Featuring a double bed, a seating area, and a spacious balcony, they offer a comfortable holiday space. Max capacity: 4+1'],
            ['Family Garden Suite', "95 m² – Set on the 2nd and 3rd floors around the communal pool area, Family Garden Suites feature a balcony with garden views, a master bedroom, a children's room, and two bathrooms. Max capacity: 4+1"],
        ],
        'acapulco-hotel' => [
            ['Garden Suite', '60 m² – It offers a spacious stay for families and groups of friends with a double bed, a single bed, and an L-shaped sofa that converts into a double bed. Max capacity: 3+1'],
            ['Palm Suite', '43 m² – Featuring one double bed, one single bed, and an L-shaped pull-out sofa bed, this suite promises a peaceful and pleasant stay for both couples and families. Max capacity: 3+1'],
            ['Standard Rooms – Land and Sea View', '30 m². Max capacity: 2+2 – TRP'],
            ['Villa Rooms', '32 m². Max capacity: 2+2 – TRP'],
            ['Lagoon Suite', '60 m² – Adults only, on request. Max capacity: TRP'],
        ],
        'salamis-hotel' => [
            ['Main Building Rooms', '27 m² – 1 French bed, 2 twin bed – 1 sofa bed. Max capacity: 2+2 – TRP'],
            ['Park Rooms', '24 m² – 1 French bed or 2 twin bed. Max capacity: 2+1'],
            ['Garden Rooms', '30 m² – 1 French bed, 1 twin bed – 1 sofa bed. Max capacity: 2+2 – TRP'],
            ['Duplex Villa', '57 m² – 2 bedroom – 1 living room – 1 French bed – 2 twin bed – 1 sofa bed. Max capacity: 4+1'],
            ['Junior Villa', '36 m² – 1 French bed – 1 sofa bed. Max capacity: 3+1'],
        ],
        'kaya-artemis-hotel' => [
            ['Holiday Village Standard', '1 bedroom (1 French bed and 1 twin bed, or 1 French bed + 1 single bed). Max capacity: 2+2 / 3+1'],
            ['Holiday Village Duplex', '1 bedroom (1 double/French bed) – 1 living room – 1 two-seater sofa bed (converts into 1 single bed) – 1 single daybed (or 1 single built-in sofa bed) – Land view. Max capacity: 3+1 – 4 adult'],
            ['Hotel Standard Room', '1 bedroom (twin beds or 1 double/French bed) – 1 two-seater sofa (folds out into 1 single bed) | Main Building, 1st–4th floor. Max capacity: 2+2 / TRP'],
            ['Superior Room', '1 bedroom (twin beds or 1 French/double bed) – 1 two-seater sofa bed (converts into 1 single bed). Max capacity: 2+2 / TRP'],
        ],
        'limak-cyprus-deluxe-hotel-single' => [
            ['Standard Rooms', '32 m² – 1 king bed or 2 twin beds + 1 sofa bed (run of the house). Max capacity: 2+2 – 3+1'],
            ['Family Rooms', '45 m² – Queen bed, 2 std bed, 1 sofa bed. Max capacity: 3+2 / 4+1'],
        ],
        'concorde-aria-hotel-casino' => [
            ['Deluxe Rooms', '52 m² – Sea and mountain views. Max capacity: 4+1'],
            ['Family Suite', '89 m². Max capacity: 6+1'],
        ],
        'arkin-iskele-hotel' => [
            ['Deluxe Side Sea', '40 m². Max capacity: 2+2 – TRP'],
        ],
        'grand-sapphire-hotel' => [
            ['Superior Room', '30–46 m² | French bed or French + single bed. Max capacity: 2+2 / TRP'],
            ['Residence 1+0 (Studio)', '46 m² – Featuring a luxurious double bed in the bedroom and a stylish lounge set, it offers an ideal atmosphere to unwind and enjoy your stay. Max capacity: 2+1 (baby)'],
            ['Residence 1+1', '60 to 78 sqm in size. Max capacity: 2+2'],
            ['Residence 2+1', "Elegantly decorated, the rooms comprise a master bedroom, a kids' bedroom, a spacious living area, and a scenic balcony. Max capacity: 4+1"],
        ],
        'lords-palace-hotel-kyrenia' => [
            ['Standard Rooms – Sea and Land', '27–34 m² – Standard Rooms specially designed for the comfort of their guests; it is offered with a choice of double (French bed) or two separate beds (twin bed) with land, sea or city views. Max capacity: 2+1 / Triple'],
        ],
        'cratos-hotel' => [
            ['Standard Room', "22 m² – Standard Rooms provide a comfortable stay with modern decor and thoughtful details, offering a choice of a double bed or two separate single beds to suit guests' preferences. Max capacity: 2+1 – TRP"],
            ['Club Superior Room', '42 m² – The rooms consist of a bedroom and a seating area, equipped with a comfy L-shaped sofa and a large balcony. Max capacity: 3+1'],
        ],
        'kaya-palazzo-hotel-kyrenia' => [
            ['Deluxe Room Land', '37–60 m² – Main Building. Twin or French 1 bedroom – Corner sofa or pull-out sofa living area. Max capacity: 2+2'],
            ['Deluxe Room Sea', '45–60 m² – Main Building. Twin or French bed, living area with corner or pull-out sofa, shower or bathtub, French balcony or balcony. Max capacity: 2+2'],
        ],
        'vuni-palace-hotel' => [
            ['Premium Room', '25 m² – The room features two separate double beds. Max capacity: 2+2 – TRP'],
            ['Premium Suite', '45 m² – Featuring one king-size bed, a dedicated work desk for business travelers, and a comfortable seating area. Max capacity: 2+1'],
            ['Premium King Suite', '50 m² – Includes 2 bedrooms, 1 living room, and 1 bathroom. Max capacity: 2+2 – TRP'],
        ],
        'merit-park-hotel' => [
            ['Run of the House', '28–36 m² – French or twin bed options. Max capacity: 3+1'],
        ],
        'merit-royal-premium-hotel' => [
            ['Run of the House', '45 m² – Twin or king bed. Max capacity: TRP'],
        ],
        'merit-royal-diamond-hotel' => [
            ['Run of the House (Adults Only +14)', '35,5 m² – 1 extra-large double bed or 2 single beds. Max capacity: TRP'],
        ],
        'elexus-hotel' => [
            ['Standard Rooms', '40 m² – Sea and land views. Max capacity: 2+1'],
            ['Deluxe Rooms', '45 m² – Sea and land views. Max capacity: 2+1'],
            ['Family Rooms', '70 m² – Sea and land views. Max capacity: 4+1'],
        ],
    ],
];
