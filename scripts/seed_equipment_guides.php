<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';

// Equipment image URLs and guides
$data = [
    // Laptops
    'ThinkPad T14 Gen 3' => [
        'image_url' => 'https://www.lenovo.com/medias/lenovo-laptop-thinkpad-t14-gen3-hero.png?context=bWFzdGVyfHByb2R1Y3RpbWFnZXN8fHx8fGxvY2FsaXphdGlvbnN8fHx8fHx8fDE2MzQ5MzY5MDl8',
        'disassembly_guide' => '1. Power off and disconnect all cables|2. Flip laptop over, remove 7 captive Phillips screws|3. Use plastic pry tool to release bottom panel clips starting from hinges|4. Slide bottom panel toward you to remove|5. Disconnect battery cable from motherboard (MANDATORY before any internal work)|6. To remove fan: disconnect fan cable, remove 2 screws, lift out|7. To access SSD: remove 1 screw, slide M.2 out at 30° angle|8. To access WiFi card: remove 1 screw, disconnect 2 antenna cables (note positions)|9. RAM is soldered - not user-replaceable|10. To remove keyboard: remove 3 screws from bottom (marked with keyboard icon), push through to release|11. To access display cable: remove hinge screws (2 per side), carefully lift display assembly',
        'assembly_guide' => '1. Reconnect display cable to motherboard, ensure connector is fully seated|2. Align hinges and secure with 2 screws per side|3. Place keyboard back, align tabs, press down until clips engage|4. Secure keyboard with 3 screws from bottom|5. Insert M.2 SSD at 30° angle, press down and secure with screw|6. Connect WiFi antenna cables (white=Main, black=Aux)|7. Reconnect fan cable and secure with 2 screws|8. Reconnect battery cable to motherboard|9. Align bottom panel, slide into place, press clips around edges|10. Secure with 7 captive screws (hand-tight only)|11. Power on and verify all components detected in BIOS',
        'guide_videos' => 'https://www.youtube.com/watch?v=example1|https://www.youtube.com/watch?v=example2',
    ],
    'ThinkPad T14s Gen 4' => [
        'image_url' => 'https://www.lenovo.com/medias/lenovo-laptop-thinkpad-t14s-gen4-hero.png',
        'disassembly_guide' => '1. Power off and disconnect all cables|2. Remove 5 captive screws from bottom panel|3. Use plastic pry tool to release clips|4. Slide panel off|5. Disconnect battery immediately|6. Fan: 2 screws + cable|7. SSD: 1 screw, M.2 2280|8. WiFi: 1 screw, 2 antenna cables',
        'assembly_guide' => '1. Connect WiFi antennas (White=Main, Black=Aux)|2. Secure WiFi card with screw|3. Insert SSD at 30°, press and screw|4. Connect fan cable, secure 2 screws|5. Connect battery|6. Align panel, press clips, secure 5 screws',
    ],
    'Dell Latitude 5520' => [
        'image_url' => 'https://i.dell.com/images/dell-product-images/latitude/5520/laptop-latitude-5520-hero.png',
        'disassembly_guide' => '1. Power off, disconnect power|2. Remove 10 bottom screws (3 are longer - note positions)|3. Start prying from front edge with plastic tool|4. Release clips around entire perimeter|5. Lift bottom cover off|6. DISCONNECT BATTERY FIRST - pull cable from connector|7. RAM: spread clips, module pops up at 30°, pull out|8. SSD: 1 screw, slide out|9. WiFi: 1 screw, pop antenna connectors (lift straight up)|10. Keyboard: 3 screws from bottom marked "K", push keyboard out from bottom|11. Fan: 1 screw + cable, lift out|12. Heatsink: 4 screws in diagonal pattern (loosen evenly)',
        'assembly_guide' => '1. Apply fresh thermal paste (pea-sized dot) to CPU|2. Align heatsink, tighten screws in diagonal pattern|3. Connect fan cable|4. Insert WiFi card, connect antennas (White=Main, Black=_AUX), secure screw|5. Insert RAM at 30°, press down until clips click|6. Insert SSD, secure screw|7. Connect battery cable|8. Align keyboard tabs, press down, secure 3 screws|9. Place bottom cover, press around edges to clip|10. Secure all 10 screws (long ones go in back near hinges)|11. Enter BIOS to verify RAM, SSD, WiFi detected',
    ],
    'Dell Latitude 7430' => [
        'image_url' => 'https://i.dell.com/images/dell-product-images/latitude/7430/laptop-latitude-7430-hero.png',
        'disassembly_guide' => '1. Power off, unplug|2. Remove 7 screws (3 captive near hinges)|3. Pry bottom panel from front|4. Disconnect battery cable|5. SSD: 1 screw under panel|6. WiFi: 1 screw, 2 pop connectors',
        'assembly_guide' => '1. WiFi card + antennas + screw|2. SSD + screw|3. Battery cable|4. Panel clips + 7 screws',
    ],
    'HP ProBook 450 G9' => [
        'image_url' => 'https://ssl-product-images.www8-hp.com/digmedialib/prodimg/lowres/c08504650.png',
        'disassembly_guide' => '1. Power off and unplug|2. Remove bottom rubber feet to access 2 hidden screws (total 7)|3. Remove all screws|4. Pry from rear hinge area|5. Disconnect battery cable|6. RAM: 2 SO-DIMM slots, clips on sides|7. SSD: M.2 2280 slot, 1 screw|8. WiFi: 1 screw, 2 antenna cables|9. Fan: single fan, 2 screws + cable|10. HDD: SATA bay accessible with caddy',
        'assembly_guide' => '1. Install WiFi card + antennas + screw|2. Insert RAM modules at 30°, press to click|3. Insert SSD + screw|4. Connect battery cable|5. Align panel clips, press edges|6. Replace all screws|7. Reattach rubber feet|8. Power on, verify in BIOS',
    ],
    'HP EliteBook 840 G10' => [
        'image_url' => 'https://ssl-product-images.www8-hp.com/digmedialib/prodimg/lowres/c08750849.png',
        'disassembly_guide' => '1. Power off, disconnect|2. Remove 5 captive screws|3. Pry bottom panel|4. Disconnect battery|5. SSD: 2x M.2 slots available|6. WiFi: AX211, 2 antennas',
        'assembly_guide' => '1. WiFi module + antennas + screw|2. SSD + screw|3. Battery cable|4. Panel + 5 screws',
    ],

    // Desktops
    'ThinkCentre M70s' => [
        'image_url' => 'https://www.lenovo.com/medias/lenovo-desktop-thinkcentre-m70s-hero.png',
        'disassembly_guide' => '1. Power off, disconnect all cables|2. Pull side panel release latch (rear of case)|3. Slide side panel off|4. Ground yourself with ESD strap|5. RAM: push clips outward, module pops up|6. SSD M.2: remove 1 screw, slide out|7. GPU: remove PCIe bracket screws, press PCIe latch, pull card straight out|8. Fan: single CPU fan, 4 screws on heatsink|9. PSU: 4 screws on rear, disconnect all motherboard connectors|10. Front panel: note connector positions before unplugging',
        'assembly_guide' => '1. Install RAM (align notch, press until clips click)|2. Insert M.2 SSD at angle, secure screw|3. Apply thermal paste if reseating cooler|4. Lower heatsink evenly, tighten 4 screws in diagonal|5. Connect fan cable to CPU_FAN header|6. Connect PSU 24-pin + 8-pin CPU + PCIe power|7. Install GPU in PCIe x16 slot, press until latch clicks|8. Connect front panel headers (check manual for pinout)|9. Replace side panel, reconnect cables|10. Enter BIOS to verify all components',
    ],
    'Dell OptiPlex 7090' => [
        'image_url' => 'https://i.dell.com/images/dell-product-images/optiplex/7090/desktop-optiplex-7090-hero.png',
        'disassembly_guide' => '1. Power off, unplug|2. Remove rear thumbscrew (or unlock with key)|3. Slide side panel off|4. RAM: 4 DIMM slots, push clips|5. GPU: PCIe x16, remove bracket screws|6. M.2 SSD: 1 screw|7. CPU cooler: push-pin or screw mount|8. PSU: standard ATX, 4 screws + all connectors',
        'assembly_guide' => '1. RAM modules (align notch)|2. M.2 SSD + screw|3. Thermal paste + cooler|4. GPU in PCIe slot|5. PSU + all power connectors|6. Side panel + thumbscrew',
    ],
    'Dell OptiPlex 7010 SFF' => [
        'image_url' => 'https://i.dell.com/images/dell-product-images/optiplex/7010/desktop-optiplex-7010-hero.png',
        'disassembly_guide' => '1. Power off, unplug|2. Pull release latch|3. Slide panel off|4. RAM: 2 DDR5 DIMM slots|5. SSD: 2x M.2 slots|6. CPU cooler: clip retention bracket',
        'assembly_guide' => '1. RAM in DDR5 slots|2. M.2 SSDs in slots|3. Cooler alignment + paste|4. Panel + latch',
    ],

    // Printers
    'HP LaserJet Pro M404dn' => [
        'image_url' => 'https://ssl-product-images.www8-hp.com/digmedialib/prodimg/lowres/c06579555.png',
        'disassembly_guide' => '1. Turn off printer, unplug power cord|2. Open rear door (pull down)|3. Remove fuser by pulling green handles outward|4. Check for jammed paper along entire paper path|5. Remove toner cartridge (pull straight out)|6. Check pickup roller (remove tray, pull roller off shaft)|7. Clean rollers with lint-free cloth dampened with water|8. Inspect transfer belt for damage or toner buildup|9. Check paper tray feed mechanism|10. Reassemble in reverse order',
        'assembly_guide' => '1. Clean all paper path rollers|2. Reinstall pickup roller (align flat side with shaft key)|3. Insert toner cartridge (push until click)|4. Align fuser assembly, push green handles to lock|5. Close rear door|6. Load paper, power on, run printer self-test',
    ],
    'HP Color LaserJet Pro MFP M283fdw' => [
        'image_url' => 'https://ssl-product-images.www8-hp.com/digmedialib/prodimg/lowres/c06596707.png',
        'disassembly_guide' => '1. Power off, unplug|2. Open rear door for fuser access|3. Release 2 green levers to remove fuser|4. Remove toner cartridges (4x CMYK, pull straight out)|5. Handle transfer belt by edges only|6. Clean ADF roller with damp cloth|7. Check scanner glass for debris',
        'assembly_guide' => '1. Clean transfer belt with dry cloth|2. Reinstall fuser (2 green levers lock)|3. Insert 4 toner cartridges in order (K-C-M-Y)|4. Close rear door|5. Clean scanner glass|6. Power on, run color calibration',
    ],
    'Brother HL-L2350DW' => [
        'image_url' => 'https://assets.brother.com/en/v1/articleimages/large/HLL2350DW_main%402x.png',
        'disassembly_guide' => '1. Power off, unplug|2. Open front cover|3. Pull drum unit + toner assembly out|4. Separate toner from drum (press green lever)|5. Check paper path from rear|6. Clean pickup roller with alcohol-dampened cotton|7. Remove waste toner box (inside front panel)|8. Inspect fuser from rear panel',
        'assembly_guide' => '1. Insert new toner into drum unit (click)|2. Slide drum+toner assembly into printer|3. Close front cover|4. Reinstall waste toner box|5. Power on, print test page',
    ],

    // Networking
    'Cisco Catalyst 2960-X' => [
        'image_url' => 'https://www.cisco.com/c/dam/en/us/products/collateral/switches/catalyst-2960-x-series-switches/nb-06-cat2960x-bb-cte-en-ag.html',
        'disassembly_guide' => '1. Console access: connect rollover cable (RJ-45 to serial)|2. Open console session (9600 baud, 8N1)|3. Check IOS version: show version|4. Fan module: rear-mounted, pull tab to release|5. Power supply: redundant bay, slide out|6. SFP modules: pull tab, slide out|7. Check flash: dir flash:|8. Verify memory: show memory statistics',
        'assembly_guide' => '1. Reinstall SFP modules (click to lock)|2. Slide PSU into bay until latch engages|3. Connect fan module|4. Verify all ports link (green LED)|5. Save configuration: write memory',
    ],
    'Ubiquiti UniFi AP AC Pro' => [
        'image_url' => 'https://static.ui.com/assets/images/uap-ac-pro/v2/uap-ac-pro@2x.png',
        'disassembly_guide' => '1. Power off (disconnect PoE cable)|2. Twist-mount: rotate AP counter-clockwise from ceiling bracket|3. Reset: hold recessed button 10+ seconds|4. Check PoE status LED|5. Inspect Ethernet port for damage|6. Clean exterior with dry cloth',
        'assembly_guide' => '1. Align mounting bracket tabs with AP back|2. Twist clockwise to lock|3. Connect PoE Ethernet cable|4. Wait for LED to show adopted status|5. Verify in UniFi controller dashboard',
    ],
    'TP-Link TL-SG2008' => [
        'image_url' => 'https://static.tp-link.com/2022/202211/20221109/TL-SG2008(UN)_1.0_1.0_02_normal_1731020420103w.png',
        'disassembly_guide' => '1. Power off, unplug|2. Remove 4 bottom screws|3. Lift top cover|4. Check all port connections|5. Reset: hold button 10+ seconds|6. Check power LED indicators',
        'assembly_guide' => '1. Align cover with base|2. Secure 4 bottom screws|3. Connect power and network cables|4. Access web UI at 192.168.0.1|5. Configure VLANs as needed',
    ],

    // CCTV
    'Hikvision DS-2CD2143G2-I' => [
        'image_url' => 'https://www.hikvision.com/content/dam/hikvision/products/DS-2CD2143G2-I/images/DS-2CD2143G2-I_front.png',
        'disassembly_guide' => '1. Power off (disconnect PoE from switch)|2. Remove 3 screws from mounting bracket|3. Carefully lower camera from ceiling/wall|4. Open weatherproof housing (4 screws)|5. Access microSD slot (under housing)|6. Hold reset button 15+ seconds for factory reset|7. Check RJ-45 connector and cable|8. Inspect IR LEDs for damage|9. Clean lens with microfiber cloth|10. Verify power supply (PoE 802.3af, 15.4W)',
        'assembly_guide' => '1. Verify PoE power at switch/injector|2. Mount bracket with 3 screws + anchors|3. Twist-lock camera onto bracket|4. Secure weatherproof housing (4 screws)|5. Verify LED status (solid = working)|6. Check live feed in NVR or browser|7. Adjust camera angle and focus if needed|8. Enable motion detection in settings',
    ],
    'Hikvision DS-7608NI-K2/8P' => [
        'image_url' => 'https://www.hikvision.com/content/dam/hikvision/products/DS-7608NI-K2/images/DS-7608NI-K2_front.png',
        'disassembly_guide' => '1. Power off NVR, unplug|2. Remove 4 top cover screws|3. Slide cover back and lift|4. Check HDD connections (2x SATA)|5. Verify RAM module seated|6. Check PoE port LEDs|7. Inspect fan for dust buildup|8. Reset: hold rear pinhole 15 seconds',
        'assembly_guide' => '1. Verify HDD connections (SATA + power)|2. Secure HDD in bay with screws|3. Replace top cover|4. Secure 4 screws|5. Connect PoE cameras to ports 1-8|6. Connect Ethernet to router/switch|7. Power on and run setup wizard|8. Verify all camera channels showing video',
    ],
    'Dahua IPC-HDW5442T-AS' => [
        'image_url' => 'https://www.dahuasecurity.com/uploads/image/products/IPC-HDW5442T-AS/IPC-HDW5442T-AS_main.png',
        'disassembly_guide' => '1. Disconnect PoE power|2. Remove from mount bracket|3. Open weatherproof housing|4. Access reset button (hold 15s)|5. Check microSD slot|6. Verify audio mic connection|7. Clean lens|8. Check RJ-45 for corrosion',
        'assembly_guide' => '1. Mount bracket with screws + anchors|2. Lock camera onto bracket|3. Secure housing|4. Connect PoE Ethernet|5. Verify live feed|6. Enable smart motion events|7. Enable audio recording|8. Sync time with NVR',
    ],

    // Servers
    'Dell PowerEdge T350' => [
        'image_url' => 'https://i.dell.com/images/dell-product-images/servers/poweredge-t350/server-poweredge-t350-hero.png',
        'disassembly_guide' => '1. Power off from front panel or iDRAC|2. Disconnect all power cables|3. Remove top cover (2 thumbscrews, slide back)|4. Ground yourself with ESD strap|5. Check RAID card battery (on PCIe card)|6. Remove HDD caddy (tool-less latch)|7. RAM: ECC UDIMM, push clips to remove|8. Check fan connections|9. Verify iDRAC dedicated port connected|10. Note: RAID controller requires cache module battery check',
        'assembly_guide' => '1. Install/verify RAM (ECC modules, match pairs)|2. Insert HDD into caddy, slide into bay until latch|3. Verify RAID card seated in PCIe slot|4. Connect all power cables (24-pin + CPU)|5. Verify all fan connections|6. Connect iDRAC network cable|7. Replace top cover, secure thumbscrews|8. Power on, enter iDRAC (default: root/calvin)|9. Check PERC RAID controller in BIOS|10. Monitor temperatures under load',
    ],
    'HPE ProLiant ML30 Gen10+' => [
        'image_url' => 'https://www.hpe.com/content/dam/hpe/servers/proliant-ml-servers/product-images/ML30_Gen10_plus.png',
        'disassembly_guide' => '1. Power off, disconnect cables|2. Remove rear latch, slide top cover off|3. Check iLO5 status (default: root/calvin)|4. RAM: 4 DIMM slots, push clips|5. HDD: 4x LFF or 8x SFF caddies|6. Check Smart Array cache module|7. ECC error log: check IML (Integrated Management Log)|8. Verify power supply status LED',
        'assembly_guide' => '1. Install ECC RAM in matched pairs|2. Insert HDD caddies (click to lock)|3. Verify Smart Array controller seated|4. Connect all power and data cables|5. Replace top cover|6. Access iLO5 via web browser|7. Check for ECC errors in IML|8. Run HPE Smart Storage Administrator',
    ],
];

$count = 0;
foreach ($data as $modelName => $info) {
    $eq = Database::fetch("SELECT id FROM equipment WHERE model_name = ? AND deleted_at IS NULL", [$modelName]);
    if (!$eq) { echo "SKIP: $modelName not found\n"; continue; }

    $updateData = [];
    if (!empty($info['image_url'])) $updateData['image_url'] = $info['image_url'];
    if (!empty($info['disassembly_guide'])) $updateData['disassembly_guide'] = $info['disassembly_guide'];
    if (!empty($info['assembly_guide'])) $updateData['assembly_guide'] = $info['assembly_guide'];
    if (!empty($info['guide_videos'])) $updateData['guide_videos'] = $info['guide_videos'];

    if (!empty($updateData)) {
        Database::update('equipment', $updateData, 'id = ?', [$eq['id']]);
        $count++;
        echo "Updated: $modelName\n";
    }
}

echo "\nDone! Updated {$count} equipment items with guides and images.\n";
