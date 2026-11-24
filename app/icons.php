<?php
/**
 * Satır içi SVG simge seti (24x24, çizgi stili). Yönetim panelinde ada göre seçilir.
 */
declare(strict_types=1);

function icon_paths(): array
{
    return [
        'arrow-right' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'arrow-left' => '<path d="M19 12H5"/><path d="m11 6-6 6 6 6"/>',
        'arrow-up' => '<path d="M12 19V5"/><path d="m6 11 6-6 6 6"/>',
        'arrow-up-right' => '<path d="M7 17 17 7"/><path d="M8 7h9v9"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        'menu' => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/>',
        'close' => '<path d="M6 6l12 12"/><path d="M18 6 6 18"/>',
        'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'map-pin' => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'star' => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1L3.200 9.5l6.1-.9z"/>',
        'building' => '<rect x="4" y="3" width="16" height="18" rx="1.500"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"/><path d="M10 21v-3h4v3"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/>',
        'users' => '<circle cx="9" cy="8" r="3.500"/><path d="M2.500 20a6.500 6.500 0 0 1 13 0"/><path d="M16 4.700a3.500 3.500 0 0 1 0 6.600"/><path d="M18 14.500a6.500 6.500 0 0 1 3.500 5.500"/>',
        'palm' => '<path d="M12 21c0-5 .5-8 2-11"/><path d="M14 10c-3-4-7-4-10-2 3 0 6 .5 10 2z"/><path d="M14 10c0-4 3-6 7-6-2 1.500-4 3-7 6z"/><path d="M14 10c4-1 6 1 7 4-2-1.500-4-3-7-4z"/><path d="M14 10c-4 0-6 3-6 6 1.500-2.500 3-4.500 6-6z"/><path d="M7 21h10"/>',
        'plane' => '<path d="M21 3 3 10.500l7 2.500 2.500 7z"/><path d="m10 13 4.500-4.500"/>',
        'bed' => '<path d="M3 18V7"/><path d="M3 11h14a4 4 0 0 1 4 4v3"/><path d="M3 15h18"/><circle cx="7.500" cy="8.500" r="1.500"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18"/><path d="M8 3v4M16 3v4"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18z"/>',
        'compass' => '<circle cx="12" cy="12" r="9"/><path d="m15.500 8.500-2 5-5 2 2-5z"/>',
        'shield' => '<path d="M12 3 4.500 6v5.500c0 4.500 3 8 7.500 9.500 4.500-1.500 7.500-5 7.500-9.500V6z"/><path d="m9 12 2 2 4-4"/>',
        'award' => '<circle cx="12" cy="9" r="5.500"/><path d="m8.500 13.500-1.500 7.500 5-2.500 5 2.500-1.500-7.500"/>',
        'heart' => '<path d="M12 20s-7.500-4.600-7.500-10.200A4.300 4.300 0 0 1 12 7.300a4.300 4.300 0 0 1 7.500 2.500C19.500 15.400 12 20 12 20z"/>',
        'check' => '<path d="m5 12.500 4.500 4.500L19 7.500"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8 12.500 3 3 5-6"/>',
        'sparkles' => '<path d="M12 3l1.800 5.200L19 10l-5.200 1.800L12 17l-1.800-5.200L5 10l5.200-1.800z"/><path d="M19 15l.800 2.200L22 18l-2.200.800L19 21l-.800-2.200L16 18l2.200-.800z"/>',
        'lightbulb' => '<path d="M9 18h6"/><path d="M10 21h4"/><path d="M12 3a6 6 0 0 0-3.500 10.900c.600.500 1 1.200 1 2.100h5c0-.900.400-1.600 1-2.100A6 6 0 0 0 12 3z"/>',
        'handshake' => '<path d="m3 11 4-4 4 1.500L14 7l4 1 3 3"/><path d="m3 11 5 5.500a1.500 1.500 0 0 0 2.200 0L11 15.700"/><path d="m21 11-5.500 6a1.500 1.500 0 0 1-2.200 0L11 14.500"/><path d="m11 8.500-2.500 2.800a1.500 1.500 0 0 0 2.200 2L13 11"/>',
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.200"/>',
        'headset' => '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2.500" y="13" width="4" height="6" rx="1.500"/><rect x="17.500" y="13" width="4" height="6" rx="1.500"/><path d="M19.500 19a4 4 0 0 1-4 3H13"/>',
        'file' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/>',
        'download' => '<path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>',
        'eye' => '<path d="M2.500 12S6 5.500 12 5.500 21.500 12 21.500 12 18 18.500 12 18.500 2.500 12 2.500 12z"/><circle cx="12" cy="12" r="3"/>',
        'send' => '<path d="M21 3 3 10.500l7 2.500 2.500 7z"/><path d="m10 13 4.500-4.500"/>',
        'waves' => '<path d="M2 8c2.500 0 2.500 2 5 2s2.500-2 5-2 2.500 2 5 2 2.500-2 5-2"/><path d="M2 13c2.500 0 2.500 2 5 2s2.500-2 5-2 2.500 2 5 2 2.500-2 5-2"/><path d="M2 18c2.500 0 2.500 2 5 2s2.500-2 5-2 2.500 2 5 2 2.500-2 5-2"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2.500M12 19.500V22M2 12h2.500M19.500 12H22M4.900 4.900l1.800 1.800M17.300 17.300l1.800 1.800M4.900 19.100l1.800-1.800M17.300 6.700l1.800-1.800"/>',
        'car' => '<path d="M4 16v-3l2-5.500A2 2 0 0 1 7.900 6h8.200a2 2 0 0 1 1.900 1.500L20 13v3"/><path d="M3 16h18v2.500H3z"/><circle cx="7.500" cy="14" r="1"/><circle cx="16.500" cy="14" r="1"/>',
        'ticket' => '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z"/><path d="M14 6v12" stroke-dasharray="2 2"/>',
        'mic' => '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5.500 11a6.500 6.500 0 0 0 13 0"/><path d="M12 17.500V21"/>',
        'gift' => '<rect x="3.500" y="8" width="17" height="4" rx="1"/><path d="M5 12v8h14v-8"/><path d="M12 8v12"/><path d="M12 8C10 3 5.500 4.500 7 7c.700 1 3 1 5 1zM12 8c2-5 6.500-3.500 5-1-.700 1-3 1-5 1z"/>',
        'play' => '<path d="M8 5.500v13l11-6.500z"/>',
        'quote' => '<path d="M9.500 7C6.500 8 5 10.500 5 14v3h5v-5H7.500c0-1.700.800-2.800 2-3.500zM18.500 7C15.500 8 14 10.500 14 14v3h5v-5h-2.500c0-1.700.800-2.800 2-3.500z"/>',
        'search' => '<circle cx="11" cy="11" r="6.500"/><path d="m16 16 4.500 4.500"/>',
        'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
        'facebook' => '<path d="M14 8.500V7c0-1 .500-1.500 1.500-1.500H17V2.500h-2.500C11.500 2.500 10 4.500 10 7v1.500H7.500v3H10v9h4v-9h2.500l.500-3z"/>',
        'instagram' => '<rect x="3.500" y="3.500" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r=".800" fill="currentColor"/>',
        'x-twitter' => '<path d="M4 4l16 16"/><path d="M20 4 4 20"/>',
        'youtube' => '<rect x="2.500" y="6" width="19" height="12" rx="3.500"/><path d="m10 9.500 5 2.500-5 2.500z"/>',
        'linkedin' => '<rect x="3.500" y="3.500" width="17" height="17" rx="2.500"/><path d="M8 10.500V17M8 7.200v.100M12 17v-4a2.200 2.200 0 0 1 4.400 0v4M12 10.500V17"/>',
        'whatsapp' => '<path d="M4 20l1.300-4A8 8 0 1 1 8.300 19z"/><path d="M9 8.500c0 3 2.500 6 6 6.500l1.200-1.500-2-1-.800.800c-1-.400-1.800-1.200-2.200-2.200l.800-.800-1-2z"/>',
        'tiktok' => '<path d="M14 3v11.500a3.500 3.500 0 1 1-3.500-3.500"/><path d="M14 3c.300 2.500 2 4.300 4.500 4.500"/>',
        'telegram' => '<path d="M21 4 3 11l5.500 2 2 6 3-4 4.500 3.500z"/><path d="m8.500 13 8-5.500"/>',
        'home' => '<path d="m3 11 9-7.500 9 7.500"/><path d="M5.500 9.500V20h13V9.500"/><path d="M10 20v-5.500h4V20"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.500" cy="9.500" r="1.800"/><path d="m3.500 17.500 5-5 4 4 3-3 5 5"/>',
        'layers' => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 12.500 9 5 9-5"/><path d="m3 17 9 5 9-5"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2.500v3M12 18.500v3M2.500 12h3M18.500 12h3M5.300 5.300l2.100 2.100M16.600 16.600l2.100 2.100M5.300 18.700l2.100-2.100M16.600 7.400l2.100-2.100"/>',
        'palette' => '<path d="M12 3a9 9 0 1 0 0 18c1.500 0 2-1 2-2 0-1.500 1-2.500 2.500-2.500H18a3 3 0 0 0 3-3C21 7 17 3 12 3z"/><circle cx="7.500" cy="11.500" r="1"/><circle cx="10" cy="7.500" r="1"/><circle cx="15" cy="7.500" r="1"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3.500 6h.100M3.500 12h.100M3.500 18h.100"/>',
        'inbox' => '<path d="M3 13 5.500 5h13L21 13v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M3 13h5l1.500 3h5L16 13h5"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'logout' => '<path d="M9 4H5.500A1.500 1.500 0 0 0 4 5.500v13A1.500 1.500 0 0 0 5.500 20H9"/><path d="M10 12h10"/><path d="m16 8 4 4-4 4"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="M4 20h4L19.500 8.500a2.100 2.100 0 0 0-3-3L5 17z"/><path d="m14.500 7.500 3 3"/>',
        'trash' => '<path d="M4 7h16"/><path d="M9 7V4.500h6V7"/><path d="M6 7l1 13h10l1-13"/><path d="M10 11v5.500M14 11v5.500"/>',
        'grip' => '<circle cx="9" cy="6" r="1.200" fill="currentColor"/><circle cx="15" cy="6" r="1.200" fill="currentColor"/><circle cx="9" cy="12" r="1.200" fill="currentColor"/><circle cx="15" cy="12" r="1.200" fill="currentColor"/><circle cx="9" cy="18" r="1.200" fill="currentColor"/><circle cx="15" cy="18" r="1.200" fill="currentColor"/>',
        'external' => '<path d="M14 4h6v6"/><path d="M20 4 11 13"/><path d="M18 14v4.500A1.500 1.500 0 0 1 16.500 20h-11A1.500 1.500 0 0 1 4 18.500v-11A1.500 1.500 0 0 1 5.500 6H10"/>',
        'film' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7.500 4v16M16.500 4v16M3 9h4.500M3 15h4.500M16.500 9H21M16.500 15H21"/>',
        'grid' => '<rect x="3.500" y="3.500" width="7" height="7" rx="1.500"/><rect x="13.500" y="3.500" width="7" height="7" rx="1.500"/><rect x="3.500" y="13.500" width="7" height="7" rx="1.500"/><rect x="13.500" y="13.500" width="7" height="7" rx="1.500"/>',
        'utensils' => '<path d="M7 3v8M4.500 3v5a2.500 2.500 0 0 0 5 0V3M7 11v10"/><path d="M17 3c-2 1.500-3 4-3 7h3zM17 10v11"/>',
        'spa' => '<path d="M12 21c-5-1-8-4.500-8-9 3 0 6 1.500 8 5 2-3.500 5-5 8-5 0 4.500-3 8-8 9z"/><path d="M12 14c-1.500-3-1.500-7 0-10 1.500 3 1.500 7 0 10z"/>',
        'dice' => '<rect x="4" y="4" width="16" height="16" rx="3"/><circle cx="8.500" cy="8.500" r="1" fill="currentColor"/><circle cx="15.500" cy="15.500" r="1" fill="currentColor"/><circle cx="12" cy="12" r="1" fill="currentColor"/><circle cx="15.500" cy="8.500" r="1" fill="currentColor"/><circle cx="8.500" cy="15.500" r="1" fill="currentColor"/>',
    ];
}

function icon(string $name, int $size = 20, string $class = ''): string
{
    $paths = icon_paths();
    $d = $paths[$name] ?? $paths['star'];
    return '<svg class="ic ' . e($class) . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}
