<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';

$equip = [
    // Laptops
    ['Lenovo','ThinkPad T14 Gen 3','Laptop','laptop','2022','AMD Ryzen 5 Pro 6650U','16GB DDR4-3200','512GB NVMe SSD','14" FHD IPS Anti-glare','2x USB-A 3.2, 2x USB-C 3.2 (PD+DP), HDMI 2.0b, RJ-45, 3.5mm','Battery swelling (check during service), WiFi disconnects (update driver), Thunderbolt firmware update needed','Phillips PH0, Plastic Pry Tool, ESD Wrist Strap','Back Cover Removal:Remove 7 screws and slide cover off.||Battery Disconnect:Disconnect battery connector before working on internals.||RAM Reseat:RAM is soldered - check for error codes.||SSD Replacement:M.2 2280 slot - remove single screw to replace.','lenovo-thinkpad-t14','Room 201'],
    ['Lenovo','ThinkPad T14s Gen 4','Laptop','laptop','2023','AMD Ryzen 7 Pro 7840U','32GB LPDDR5x','1TB NVMe SSD','14" 2.8K OLED','2x USB4, 2x USB-A 3.2, HDMI 2.1, 3.5mm','OLED burn-in after extended static display, Fan noise under load (clean dust regularly)','Phillips PH0, Plastic Pry Tool, ESD Wrist Strap','Back Panel:Remove 5 captive screws.||Battery:Disconnect before internal work.||Thermal Paste:Reapply every 2 years for optimal cooling.','lenovo-thinkpad-t14s','Room 201'],
    ['Dell','Latitude 5520','Laptop','laptop','2021','Intel Core i5-1145G7','8GB DDR4','256GB NVMe SSD','15.6" FHD IPS','2x USB-A 3.2, 1x USB-C Thunderbolt 4, HDMI 2.0, RJ-45, SD card, 3.5mm','Hinge looseness on some units, Thunderbolt dock recognition issues, Battery drain in sleep mode','Phillips PH0, Plastic Spudger, ESD Wrist Strap','Bottom Cover:Remove 10 screws, pry clips around edge.||Battery:6 screws, slide out.||Keyboard:3 screws from bottom, push through to release.||HDD Bay:Accessible after removing bottom cover.','dell-latitude-5520','Room 202'],
    ['Dell','Latitude 7430','Laptop','laptop','2022','Intel Core i7-1265U','16GB LPDDR5','512GB NVMe SSD','14" FHD+ Anti-glare','2x Thunderbolt 4, 1x USB-A 3.2, HDMI 2.0, 3.5mm','Limited upgradeability (RAM soldered), Touchpad intermittently unresponsive (firmware fix)','Phillips PH0, Torx T5, Plastic Pry Tool','Bottom Panel:Remove 7 screws (3 captive).||M.2 SSD:Single screw under bottom panel.||WiFi Card:Located near battery, 1 antenna cable each.','dell-latitude-7430','Room 202'],
    ['HP','ProBook 450 G9','Laptop','laptop','2022','Intel Core i5-1235U','8GB DDR4-3200','512GB NVMe SSD','15.6" FHD IPS','3x USB-A 3.2, 1x USB-C 3.2, HDMI 2.0, RJ-45, SD card, 3.5mm','Fan error after dust buildup, USB-C charging intermittent, BIOS update needed','Phillips PH0, Plastic Pry Tool','Bottom Cover:5 captive screws, rubber feet hide 2 more.||RAM:2 SO-DIMM slots, upgradable to 64GB.||Battery:Connected via cable, 4 screws.||Fan:Single fan, accessible after removing bottom cover.','hp-probook-450-g9','Room 203'],
    ['HP','EliteBook 840 G10','Laptop','laptop','2023','Intel Core i7-1355U','16GB DDR5-5200','512GB NVMe SSD','14" WUXGA IPS Anti-glare','2x Thunderbolt 4, 2x USB-A 3.2, HDMI 2.0, Nano SIM, 3.5mm','Thunderbolt dock hot-plug issues, Power button intermittent','Phillips PH0, Torx T5','Bottom Cover:5 captive screws.||Battery:Internal, 4 screws + cable.||SSD:M.2 2280 single screw.||WiFi:AX211 module, 2 antenna cables.','hp-elitebook-840-g10','Room 203'],

    // Desktops
    ['Lenovo','ThinkCentre M70s','Desktop','desktop','2022','Intel Core i5-12400','8GB DDR4-3200','512GB NVMe SSD','Integrated Intel UHD 730','4x USB-A 3.2, 2x USB-A 2.0, 1x USB-C, 1x DisplayPort, 1x HDMI, RJ-45','Front panel USB loose on some units, Fan warning after dust buildup','Phillips PH1, Phillips PH0','Side Panel:Slide release latch, remove panel.||RAM:2 DIMM slots, up to 64GB.||Storage:M.2 + 3.5" SATA bay.||Fan:Single CPU fan, clip release.','lenovo-m70s','Room 204'],
    ['Dell','OptiPlex 7090','Desktop','desktop','2021','Intel Core i5-11500','16GB DDR4-3200','256GB NVMe SSD','Integrated Intel UHD 750','4x USB-A 3.2, 2x USB-A 2.0, 1x DisplayPort, 1x HDMI, RJ-45, 3.5mm','CMOS battery failure after 3+ years, Thermal throttling when dusty','Phillips PH1, Flathead','Side Panel:Thumbscrew or key lock, slide off.||RAM:4 DIMM slots.||GPU:PCIe x16 slot.||Power Supply:Standard ATX, tool-less removal.','dell-optiplex-7090','Room 204'],
    ['Dell','OptiPlex 7010 SFF','Desktop','desktop','2023','Intel Core i5-13400','16GB DDR5-4800','512GB NVMe SSD','Integrated Intel UHD 730','4x USB-A 3.2, 2x USB-A 2.0, 1x DisplayPort 1.4a, 1x HDMI 2.1, RJ-45','DDR5 compatibility with older modules, PCIe lane sharing with NVMe','Phillips PH1','Chassis:Pull release latch.||RAM:2 DDR5 DIMM slots.||Storage:2x M.2 slots + 1x 3.5" bay.||CPU Cooler:Clip-style retention bracket.','dell-optiplex-7010','Room 204'],

    // Printers
    ['HP','LaserJet Pro M404dn','Printer','printer','2020','N/A','N/A','N/A','N/A','1x USB-B 2.0, 1x Ethernet RJ-45, Wireless optional','Paper jam in fuser area, 50.0 fuser error, Toner low warning early','Phillips PH1, Long-nose pliers, Cleaning cloth','Fuser Access:Open rear door, pull fuser by green handles.||Paper Path:Remove rear cover to access paper path.||Toner:Pull cartridge straight out.||Pickup Roller:Remove tray, pull roller off shaft.','hp-m404dn','Server Room'],
    ['HP','Color LaserJet Pro MFP M283fdw','Printer','printer','2021','N/A','N/A','N/A','N/A','1x USB-B 2.0, 1x Ethernet, Wireless, NFC touch-to-print','Color calibration drift, Fuser error 50.1, Scanner jam on ADF','Phillips PH0, PH1, Lint-free cloth','Fuser:Open rear door, release 2 green levers.||Transfer Belt:Under toner cartridges, handle by edges.||Scanner:ADF roller clean with damp cloth.||Toner:4 cartridges - CMYK, pull straight out.','hp-m283fdw','Server Room'],
    ['Brother','HL-L2350DW','Printer','printer','2021','N/A','N/A','N/A','N/A','1x USB-B, 1x Ethernet, Wireless, NFC','Toner sensor false alarm, Paper feed roller wear, Sleep mode wake issues','Phillips PH1, Cotton gloves','Fuser:Open back panel, pull fuser assembly.||Paper Feed:Remove tray, clean pickup roller with alcohol.||Toner:Slide out drum unit, replace toner separately.||Waste Box:Located inside front panel.','brother-hl-l2350dw','Room 105'],

    // Networking
    ['Cisco','Catalyst 2960-X','Switch','network','2019','N/A','N/A','N/A','N/A','24x Gigabit Ethernet, 4x SFP+, Console port','Spanning tree topology change alerts, Power supply fan failure, IOS upgrade needed','Console cable, Phillips PH1, Anti-static mat','Console:Rollover cable to RJ-45 console port.||Power Supply:Redundant PSU bay, hot-swappable.||Fan Module:Rear-mounted, single fan unit.||Flash:4GB internal, upgradeable via USB.','cisco-2960x','Server Room'],
    ['Ubiquiti','UniFi AP AC Pro','Access Point','network','2020','N/A','N/A','N/A','N/A','1x GbE PoE In, 1x GbE Passthrough','Firmware upgrade failures, WiFi clients not roaming, LED stuck solid white','PoE injector or PoE switch, Phillips PH0, Ladder','Mounting:Twist-lock ceiling mount bracket.||Reset:Hold recessed button 10+ seconds.||Adopt:Connect to UniFi controller, click Adopt.||PoE:Requires 802.3af PoE (48V).','ubiquiti-uap-ac-pro','Building A'],
    ['TP-Link','TL-SG2008','Switch','network','2021','N/A','N/A','N/A','N/A','8x Gigabit Ethernet, 2x SFP','Loop detection false positives, Web UI slow after extended uptime','Phillips PH1','Reset:Hold button 10+ seconds for factory reset.||Management:Access via 192.168.0.1 default.||Firmware:Update via web admin panel.||Mounting:Desktop or rack mount with bracket.','tplink-sg2008','Room 105'],

    // CCTV
    ['Hikvision','DS-2CD2143G2-I','CCTV Camera','cctv','2022','N/A','N/A','N/A','N/A','1x RJ-45 PoE (802.3af), microSD slot (up to 256GB)','No IR at night, Camera offline, Image freezing, Time sync drift','PoE switch/injector, Ladder, Phillips PH0','Power:Requires PoE 802.3af (15.4W).||Reset:Hold button inside weatherproof housing 15s.||Lens:Fixed 2.8mm/4mm/6mm options.||Mounting:3-axis bracket, 3 screws + anchors.','hikvision-ds2cd2143g2','Building B'],
    ['Hikvision','DS-7608NI-K2/8P','NVR','cctv','2022','N/A','N/A','N/A','N/A','8x PoE ports, 2x SATA, 1x HDMI, 1x VGA, 2x USB, RJ-45','HDD full not recording, PoE port dead, Playback not working, Remote access fail','Phillips PH1, SATA cable (spare), Hard drive (surveillance grade)','HDD Bay:Remove top cover, 4 screws per drive.||PoE Ports:8 ports, 60W total budget.||Network:Config via browser or NVR screen.||Reset:Rear pinhole button, hold 15s.','hikvision-ds7608ni','Server Room'],
    ['Dahua','IPC-HDW5442T-AS','CCTV Camera','cctv','2023','N/A','N/A','N/A','N/A','1x RJ-45 PoE+, microSD, Alarm I/O','Audio not recording, Smart motion events not triggering, Firmware compatibility','PoE+ switch, Ladder, Phillips PH0','Power:PoE+ 802.3at (25.4W max).||Audio:Built-in mic, enable in Web UI.||Reset:Hold reset button 15 seconds.||SD Card:Waterproof slot under housing.','dahua-ipchdw5442t','Building B'],

    // Servers
    ['Dell','PowerEdge T350','Server','server','2023','Intel Xeon E-2336','32GB DDR4 ECC UDIMM','2x 1TB SAS 10K RAID1','N/A','2x USB-A 3.2, 2x USB-A 2.0, 1x iDRAC, 1x VGA, 2x GbE, 1x serial','RAID controller battery warning, iDRAC license expired, Fan noise after PSU swap','Torx T10, Phillips PH1, Anti-static mat','Top Cover:2 thumbscrews, slide back.||RAID Card:PCIe slot, battery on card.||HDD Caddy:Tool-less latch on each bay.||iDRAC:Default login root/calvin.','dell-poweredge-t350','Server Room'],
    ['HPE','ProLiant ML30 Gen10+','Server','server','2022','Intel Xeon E-2324G','16GB DDR4 ECC UDIMM','2x 480GB SATA SSD RAID1','N/A','4x USB-A 3.2, 2x GbE, 1x iLO5, 1x VGA, 1x serial','iLO5 default password not changed, Smart Array cache module, ECC error log','Torx T10, Phillips PH0','Top Cover:Rear latch, slide off.||HDD Bays:4x LFF or 8x SFF caddies.||RAM:4 DIMM slots, max 128GB.||iLO5:Access via dedicated port or shared NIC.','hpe-ml30-gen10','Server Room'],
];

$count = 0;
foreach ($equip as $e) {
    $existing = Database::fetch("SELECT id FROM equipment WHERE manufacturer = ? AND model_name = ? AND deleted_at IS NULL", [$e[0], $e[1]]);
    if ($existing) continue;
    Database::insert('equipment', [
        'manufacturer' => $e[0],
        'model_name' => $e[1],
        'device_type' => $e[2],
        'category' => $e[3],
        'year' => $e[4],
        'cpu' => $e[5],
        'ram' => $e[6],
        'storage' => $e[7],
        'display_spec' => $e[8],
        'ports' => $e[9],
        'known_issues' => $e[10],
        'tools_needed' => $e[11],
        'repair_guides' => $e[12],
        'notes' => $e[13],
        'location' => $e[14],
        'status' => 'active',
        'created_by' => 1,
    ]);
    $count++;
    echo "Added: {$e[0]} {$e[1]}\n";
}
echo "\nDone! Added {$count} equipment items.\n";
