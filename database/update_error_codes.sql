UPDATE error_codes SET 
description = 'A critical system process stopped unexpectedly. Windows cannot continue running.\n\nHardware causes: Failing RAM module, overheating CPU, failing motherboard, loose SATA/NVMe cable, dying hard drive.\n\nSoftware causes: Corrupted Windows system files, incompatible driver (especially GPU or antivirus), failed Windows update, malware infection, registry corruption.',
common_causes = 'Hardware: Failing RAM, CPU overheating, loose cables, dying HDD/SSD\nSoftware: Corrupted system files, bad GPU/antivirus driver, failed Windows update, malware, registry corruption'
WHERE code = 'CRITICAL_PROCESS_DIED';

UPDATE error_codes SET 
description = 'Windows failed to read/write kernel data from the page file. Usually indicates disk or RAM failure.\n\nHardware causes: Failing hard drive (bad sectors), failing RAM module, loose SATA/NVMe cable, failing motherboard storage controller.\n\nSoftware causes: Corrupted page file, driver conflict with storage controller, Windows update corrupted storage stack.',
common_causes = 'Hardware: Failing HDD/SSD, bad RAM, loose SATA cable, failing motherboard\nSoftware: Corrupted page file, storage driver conflict, Windows update issue'
WHERE code = 'KERNEL_DATA_INPAGE_ERROR';

UPDATE error_codes SET 
description = 'A driver or process tried to access memory at an unauthorized kernel address.\n\nHardware causes: Failing RAM (most common), overclocking instability, overheating CPU, failing motherboard.\n\nSoftware causes: Faulty GPU driver, faulty network adapter driver, faulty audio driver, malware, incompatible software.',
common_causes = 'Hardware: Failing RAM, CPU overclocking instability, overheating, failing motherboard\nSoftware: Bad GPU/network/audio driver, malware, incompatible third-party software'
WHERE code = 'IRQL_NOT_LESS_OR_EQUAL';

UPDATE error_codes SET 
description = 'Windows requested data from memory not available in the nonpaged area.\n\nHardware causes: Failing RAM module (most common), dying hard drive, loose memory DIMM, failing motherboard memory controller.\n\nSoftware causes: Corrupted page file, faulty device driver, disk corruption.',
common_causes = 'Hardware: Failing RAM, dying HDD/SSD, loose DIMM slot, failing motherboard\nSoftware: Corrupted page file, faulty driver, disk corruption'
WHERE code = 'PAGE_FAULT_IN_NONPAGED_AREA';

UPDATE error_codes SET 
description = 'A system service encountered an unhandled exception.\n\nHardware causes: Faulty GPU causing driver crash, failing RAM, overheating.\n\nSoftware causes: Outdated or corrupt GPU driver (NVIDIA/AMD), Windows update conflict, third-party antivirus blocking system process, malware.',
common_causes = 'Hardware: Faulty GPU, failing RAM, overheating\nSoftware: Bad GPU driver, Windows update conflict, antivirus blocking system process, malware'
WHERE code = 'SYSTEM_SERVICE_EXCEPTION';

UPDATE error_codes SET 
description = 'A Deferred Procedure Call took too long, triggering the watchdog timer.\n\nHardware causes: Failing SSD/NVMe drive, loose SATA/NVMe cable, failing motherboard storage controller, bad RAM.\n\nSoftware causes: Incompatible SSD firmware, outdated Intel RST/AMD RAID driver, USB device conflict, Windows update issue.',
common_causes = 'Hardware: Failing SSD/NVMe, loose cables, failing motherboard, bad RAM\nSoftware: Bad SSD firmware, outdated storage driver, USB conflict, Windows update'
WHERE code = 'DPC_WATCHDOG_VIOLATION';

UPDATE error_codes SET 
description = 'Hardware error reported by Windows Hardware Error Architecture. Usually indicates unrecoverable hardware failure.\n\nHardware causes: CPU overheating or failure, failing motherboard (VRM/capacitors), GPU failure, PSU voltage instability, RAM failure under load.\n\nSoftware causes: Overclocking instability (BIOS settings), BIOS bug, incompatible hardware.',
common_causes = 'Hardware: CPU failure/overheating, failing motherboard, GPU failure, PSU voltage issues, RAM failure\nSoftware: BIOS overclocking settings, BIOS bug, incompatible hardware'
WHERE code = 'WHEA_UNCORRECTABLE_ERROR';

UPDATE error_codes SET 
description = 'A kernel-mode process or driver failed a security check.\n\nHardware causes: Failing RAM, corrupted BIOS, faulty motherboard.\n\nSoftware causes: Incompatible drivers, corrupted Windows system files, antivirus conflict, failed Windows update, malware.',
common_causes = 'Hardware: Failing RAM, corrupted BIOS, faulty motherboard\nSoftware: Incompatible drivers, corrupted system files, antivirus conflict, failed update, malware'
WHERE code = 'KERNEL_SECURITY_CHECK_FAILURE';

UPDATE error_codes SET 
description = 'Windows cannot access the system partition during boot.\n\nHardware causes: SATA mode changed in BIOS (AHCI/IDE/RAID), failing hard drive, loose SATA cable, failing motherboard storage controller.\n\nSoftware causes: Storage driver missing or corrupted, boot sector corruption, Windows update changed storage config.',
common_causes = 'Hardware: SATA mode changed, failing HDD/SSD, loose cable, failing motherboard\nSoftware: Missing storage driver, boot sector corruption, Windows update config change'
WHERE code = 'INACCESSIBLE_BOOT_DEVICE';

UPDATE error_codes SET 
description = 'Hardware reported a fatal unrecoverable error.\n\nHardware causes: CPU failure (most common), CPU overheating, failing motherboard, bad RAM, GPU failure, PSU delivering unstable voltage.\n\nSoftware causes: BIOS overclocking gone wrong, BIOS corruption, incompatible hardware.',
common_causes = 'Hardware: CPU failure/overheating, failing motherboard, bad RAM, GPU failure, PSU issues\nSoftware: BIOS overclocking, BIOS corruption, incompatible hardware'
WHERE code = 'MACHINE_CHECK_EXCEPTION';

UPDATE error_codes SET 
description = 'Windows Update cannot find specified files. Files may be corrupted or missing.\n\nHardware causes: Failing hard drive (corrupted download storage), bad RAM (corrupted file handling).\n\nSoftware causes: Corrupted SoftwareDistribution folder, incorrect date/time, system file corruption, antivirus blocking downloads.',
common_causes = 'Hardware: Failing HDD (rare), bad RAM (rare)\nSoftware: Corrupted SoftwareDistribution, wrong date/time, system file corruption, antivirus blocking'
WHERE code = '0x80070002';

UPDATE error_codes SET 
description = 'Windows Update encountered access denied error.\n\nHardware causes: None directly — this is a software/permissions issue.\n\nSoftware causes: Antivirus blocking update service, Group Policy restriction, corrupted system files, Windows Installer service not running, corrupted CatRoot2 folder.',
common_causes = 'Hardware: N/A\nSoftware: Antivirus blocking, Group Policy restriction, corrupted files, Installer service issue, CatRoot2 corruption'
WHERE code = '0x80070005';

UPDATE error_codes SET 
description = 'Windows Update server timed out or unreachable.\n\nHardware causes: None directly — network infrastructure issue.\n\nSoftware causes: VPN connection active, Windows Firewall blocking, low disk space on C:, Windows Update service stopped or crashed.',
common_causes = 'Hardware: N/A\nSoftware: VPN active, firewall blocking, low disk space, Update service stopped'
WHERE code = '0x800f0922';

UPDATE error_codes SET 
description = 'Generic Windows error appearing in many contexts.\n\nHardware causes: Failing hard drive causing file corruption, bad RAM causing memory errors.\n\nSoftware causes: Corrupted system files, permission issues, antivirus interference, registry corruption, COM component failure.',
common_causes = 'Hardware: Failing HDD, bad RAM\nSoftware: Corrupted system files, permission issues, antivirus interference, registry corruption'
WHERE code = '0x80004005';

UPDATE error_codes SET 
description = 'Critical Windows system process failed. Cannot boot normally.\n\nHardware causes: Failing hard drive, bad RAM, failing motherboard.\n\nSoftware causes: Corrupted system files (winlogon.exe or csrss.exe), failed Windows update, driver conflict, malware, registry corruption.',
common_causes = 'Hardware: Failing HDD, bad RAM, failing motherboard\nSoftware: Corrupted winlogon/csrss, failed update, driver conflict, malware, registry corruption'
WHERE code = '0xc000021a';

UPDATE error_codes SET 
description = 'Windows cannot install to the selected disk.\n\nHardware causes: Disk controller not recognized, incompatible storage driver, failing hard drive.\n\nSoftware causes: Disk not formatted as GPT/MBR matching BIOS boot mode, disk locked by BitLocker, missing storage drivers (Intel RST/AMD RAID).',
common_causes = 'Hardware: Disk controller issue, missing storage driver, failing disk\nSoftware: Wrong GPT/MBR, BitLocker lock, missing Intel RST/AMD RAID driver'
WHERE code = '0x80300024';

UPDATE error_codes SET 
description = 'Browser cannot connect to server. Connection actively refused.\n\nHardware causes: Network cable disconnected, faulty network adapter, switch/router port down.\n\nSoftware causes: Web server not running on target, firewall blocking port, incorrect URL/port, DNS resolving to wrong IP.',
common_causes = 'Hardware: Disconnected cable, faulty NIC, switch port down\nSoftware: Server not running, firewall blocking, wrong URL, DNS issue'
WHERE code = 'ERR_CONNECTION_REFUSED';

UPDATE error_codes SET 
description = 'Browser cannot resolve domain name to IP address.\n\nHardware causes: Network cable disconnected, faulty network adapter, router not providing DNS.\n\nSoftware causes: DNS server down, DNS cache corrupted, hosts file override, VPN blocking DNS, domain expired.',
common_causes = 'Hardware: Disconnected cable, faulty NIC, router DNS issue\nSoftware: DNS server down, cache corrupted, hosts override, VPN DNS issue'
WHERE code = 'ERR_NAME_NOT_RESOLVED';

UPDATE error_codes SET 
description = 'Connection took too long and was abandoned.\n\nHardware causes: Slow network hardware, faulty switch, damaged cable causing packet loss.\n\nSoftware causes: Server overloaded, firewall dropping packets, proxy misconfiguration, ISP throttling, routing issues.',
common_causes = 'Hardware: Slow/faulty network hardware, damaged cable\nSoftware: Server overload, firewall issues, proxy misconfig, ISP throttling'
WHERE code = 'ERR_TIMED_OUT';

UPDATE error_codes SET 
description = 'Chrome detected no internet connection. DNS lookup failed.\n\nHardware causes: Ethernet cable unplugged, WiFi adapter disabled, router/modem down, faulty network adapter.\n\nSoftware causes: IP configuration wrong (DHCP failed), DNS settings incorrect, network adapter driver issue, VPN interfering.',
common_causes = 'Hardware: Unplugged cable, disabled WiFi, router down, faulty NIC\nSoftware: DHCP failure, wrong DNS, driver issue, VPN interference'
WHERE code = 'DNS_PROBE_FINISHED_NO_INTERNET';

UPDATE error_codes SET 
description = 'HDD self-test detected imminent failure. Back up data immediately.\n\nHardware causes: Failing hard drive platters, bad sectors (physical damage), overheating drive, worn motor, power surge damage, age-related wear.\n\nSoftware causes: None — this is a hardware failure indicator.',
common_causes = 'Hardware: Failing platters, bad sectors, overheating, worn motor, power surge, age\nSoftware: N/A — hardware failure'
WHERE code = 'SMART_HARD_DISK_ERROR';

UPDATE error_codes SET 
description = 'System fan stopped spinning or not detected by motherboard.\n\nHardware causes: Fan motor failure, dust buildup blocking blades, loose fan connector on motherboard, fan bearing wear, fan controller chip failure.\n\nSoftware causes: Fan speed settings in BIOS too low, corrupted fan control driver.',
common_causes = 'Hardware: Motor failure, dust buildup, loose connector, bearing wear, controller chip failure\nSoftware: BIOS fan speed settings, fan control driver issue'
WHERE code = 'FAN_FAILURE';

UPDATE error_codes SET 
description = 'USB device drawing too much power, triggering overcurrent protection.\n\nHardware causes: Faulty USB device short-circuiting, damaged USB cable, damaged USB port, power supply unable to provide stable 5V.\n\nSoftware causes: USB driver issue, power management settings wrong, USB selective suspend malfunction.',
common_causes = 'Hardware: Faulty USB device, damaged cable/port, PSU 5V rail issue\nSoftware: USB driver issue, power management misconfigured'
WHERE code = 'USB_PORT_OVERCURRENT';

UPDATE error_codes SET 
description = 'Windows detected memory management problem. Possible RAM failure.\n\nHardware causes: Failing RAM module (most common), RAM overheating, incompatible RAM speeds, loose DIMM, failing motherboard memory controller, overclocking instability.\n\nSoftware causes: Driver writing to wrong memory address, paging file corruption, incompatible RAM XMP profile.',
common_causes = 'Hardware: Failing RAM, overheating RAM, incompatible RAM, loose DIMM, failing motherboard, overclocking\nSoftware: Driver memory bug, page file corruption, bad XMP profile'
WHERE code = 'MEMORY_MANAGEMENT_ERROR';

UPDATE error_codes SET 
description = 'BIOS cannot find a bootable device.\n\nHardware causes: Hard drive/SSD disconnected or failed, loose SATA/NVMe cable, BIOS boot order wrong, motherboard SATA port failure.\n\nSoftware causes: Boot sector corrupted, MBR/GPT damaged, Windows Boot Manager missing, BitLocker lock.',
common_causes = 'Hardware: Failed/disconnected HDD/SSD, loose cable, wrong boot order, SATA port failure\nSoftware: Corrupted boot sector, damaged MBR/GPT, missing Boot Manager, BitLocker'
WHERE code = 'NO_BOOT_DEVICE';

UPDATE error_codes SET 
description = 'Printer detected paper jam or feed error. Common on Epson and Canon printers.\n\nHardware causes: Worn pickup roller, paper feed gear failure, foreign object in paper path, damaged print head carriage.\n\nSoftware causes: Wrong paper type setting, incorrect paper size in driver, printer firmware bug.',
common_causes = 'Hardware: Worn pickup roller, gear failure, foreign object, damaged carriage\nSoftware: Wrong paper type/size setting, firmware bug'
WHERE code = 'E3';

UPDATE error_codes SET 
description = 'Paper tray empty or paper not detected by sensor.\n\nHardware causes: Paper sensor dirty or broken, paper tray spring not pushing paper up, pickup roller not grabbing paper.\n\nSoftware causes: Paper size setting mismatch, printer queue holding jobs, driver reporting wrong tray status.',
common_causes = 'Hardware: Dirty/broken sensor, tray spring issue, worn pickup roller\nSoftware: Paper size mismatch, queue issue, driver reporting wrong status'
WHERE code = 'E1';

UPDATE error_codes SET 
description = 'Epson printer waste ink pad saturated. Printer stops to prevent overflow.\n\nHardware causes: Waste ink pad physically full (absorbed ink), ink tube clogged, waste tank saturated.\n\nSoftware causes: Waste ink counter needs reset via adjustment program, firmware counting pages.',
common_causes = 'Hardware: Full waste ink pad, clogged ink tube, saturated waste tank\nSoftware: Counter needs reset via adjustment program'
WHERE code = 'WASTE_INK_PAD';

UPDATE error_codes SET 
description = 'Brother laser printer drum needs replacement or cleaning.\n\nHardware causes: Drum surface worn out (12K-15K page life), drum scratched, incompatible drum unit, dirty drum surface.\n\nSoftware causes: Drum counter needs reset after replacement, wrong drum unit model selected.',
common_causes = 'Hardware: Worn drum surface, scratches, incompatible unit\nSoftware: Drum counter needs reset, wrong model selected'
WHERE code = 'DRUM_ERROR';

UPDATE error_codes SET 
description = 'Windows cannot communicate with printer. Appears offline.\n\nHardware causes: Printer network cable loose, printer WiFi disconnected, printer IP address changed, printer in sleep mode.\n\nSoftware causes: SNMP enabled causing false offline status, printer driver outdated, IP address mismatch in port settings, print spooler corrupted.',
common_causes = 'Hardware: Loose network cable, printer WiFi down, IP changed, sleep mode\nSoftware: SNMP false offline, outdated driver, IP mismatch, corrupted spooler'
WHERE code = 'PRINTER_OFFLINE_0x803C010B';

UPDATE error_codes SET 
description = 'Windows stopped a device due to reported problems. Common with GPUs and USB devices.\n\nHardware causes: Faulty GPU, loose GPU power connector, GPU overheating, USB device malfunctioning, damaged USB port, loose PCIe slot connection.\n\nSoftware causes: Driver corruption, driver incompatibility, Windows unable to reset device, power management conflict.',
common_causes = 'Hardware: Faulty GPU, loose power connector, GPU overheating, USB malfunction, loose PCIe\nSoftware: Driver corruption, incompatibility, power management conflict'
WHERE code = 'CODE_43';

UPDATE error_codes SET 
description = 'Device cannot start. Driver may be corrupted or missing.\n\nHardware causes: Hardware failure (device broken), resource conflict (IRQ/DMA), loose connection, incompatible hardware.\n\nSoftware causes: Corrupted driver, wrong driver installed, registry entry corrupted, Windows Plug and Play failure.',
common_causes = 'Hardware: Device failure, IRQ/DMA conflict, loose connection, incompatible hardware\nSoftware: Corrupted driver, wrong driver, registry corruption, PnP failure'
WHERE code = 'CODE_10';

UPDATE error_codes SET 
description = 'Driver loaded but hardware not detected.\n\nHardware causes: Loose hardware connection, hardware failure, BIOS not detecting device, wrong PCIe slot.\n\nSoftware causes: Driver loaded for wrong device, BIOS needs update to recognize hardware, ACPI table issue.',
common_causes = 'Hardware: Loose connection, hardware failure, BIOS not detecting, wrong slot\nSoftware: Wrong driver loaded, BIOS needs update, ACPI issue'
WHERE code = 'CODE_41';

UPDATE error_codes SET 
description = 'A driver attempted to access invalid memory at elevated IRQL.\n\nHardware causes: Failing RAM causing driver to read garbage data, overheating causing memory errors.\n\nSoftware causes: Outdated GPU driver (most common), faulty network driver, incompatible third-party driver, driver verifier finding bugs.',
common_causes = 'Hardware: Failing RAM, overheating\nSoftware: Bad GPU driver, bad network driver, incompatible driver, driver verifier'
WHERE code = 'DRIVER_IRQL_NOT_LESS_OR_EQUAL';
