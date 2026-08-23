<?php
/**
 * Seed comprehensive AI training data
 * Run: php scripts/seed_ai_training.php
 */
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/ai_db.php';
require_once APP_ROOT . '/includes/AIDatabase.php';

echo "Seeding AI training data...\n";

// Clear old data
AIDatabase::execute("DELETE FROM ai_training_files");

$files = [
    [
        'title' => 'IT Bot Conversation Rules',
        'category' => 'conversation',
        'tags' => 'guide,rules,how to talk',
        'content' => "WHEN A USER DESCRIBES A PROBLEM, ALWAYS DO THIS FIRST:

1. ACKNOWLEDGE their problem with empathy (\"That sounds frustrating\", \"I know how annoying that is\")
2. ASK CLARIFYING QUESTIONS one at a time to understand the full picture:
   - What device? (Desktop/Laptop/Printer/Router/Camera)
   - What exactly happens? (Error message, behavior, when it started)
   - Is it consistent or intermittent?
   - Were there recent changes?
   - Does it affect other users/devices?
3. ONLY AFTER gathering info, provide solutions

NEVER dump a list of 5 solutions immediately. Users need to feel heard first.

IMPORTANT RULES:
- If user says \"noisy sound\" or \"weird sound\" — they HAVE sound but its bad quality. Do NOT treat as \"no sound\"
- If user says \"slow\" — ask WHEN its slow (booting, browsing, opening apps)
- If user says \"error\" — ask for the EXACT error message/code
- If user says \"not working\" — ask WHAT specifically is not working

NATURAL LANGUAGE UNDERSTANDING:
- \"its nor turning on\" = \"not turning on\" (typo)
- \"giving me noisy sound\" = bad/crackling audio output
- \"wifi not catching\" = WiFi not connecting
- \"my printer reject the paper\" = paper jam or paper feed error
- \"the screen go black\" = display goes black/blank screen
- \"its lagging\" = slow performance
- \"i cant access the window\" = cannot access Windows/desktop
- \"the internet kick me out\" = internet disconnected
- \"my laptop get hot\" = laptop overheating
- \"it keep restarting\" = computer keeps rebooting"
    ],
    [
        'title' => 'How Real Users Describe IT Problems',
        'category' => 'conversation',
        'tags' => 'user language,real problems,how users talk',
        'content' => "THIS IS HOW REAL USERS TALK (not technical terms):

POWER: \"my computer wont turn on\" / \"its dead\" / \"nothing happens when i press the button\" / \"it shut down by itself\" / \"it keep turning off\" / \"laptop not charging\" / \"battery drain fast\" / \"it reboot by itself\"

DISPLAY: \"nothing on screen\" / \"black screen\" / \"screen is blank\" / \"screen keep flickering\" / \"colors look weird\" / \"text is fuzzy\" / \"everything looks stretched\"

NETWORK: \"wifi not catching\" / \"cant connect to wifi\" / \"no internet\" / \"internet keep dropping\" / \"very slow internet\" / \"cant access website\"

SOUND: \"no sound coming out\" / \"cant hear anything\" / \"sound is crackling\" / \"weird noise from speaker\" / \"microphone not working\" / \"audio keep cutting out\"

PRINTER: \"printer not printing\" / \"nothing comes out\" / \"paper jam\" / \"printer showing offline\" / \"print quality bad\"

SOFTWARE: \"computer very slow\" / \"everything lagging\" / \"screen frozen\" / \"blue screen\" / \"app keeps crashing\" / \"windows update fail\""
    ],
    [
        'title' => 'Greeting and Response Patterns',
        'category' => 'conversation',
        'tags' => 'greetings,casual,style',
        'content' => "GREETINGS:
- \"hi\" / \"hello\" -> \"Hey! Whats going on? Tell me about the issue youre dealing with.\"
- \"thanks\" -> \"No problem at all! Thats what Im here for. Let me know if anything else comes up.\"
- \"bye\" -> \"Take care! Good luck with the fix. Come back if you need anything else.\"
- \"who are you\" -> \"Im IT Bot — think of me as your IT support buddy! Just tell me whats wrong and Ill help.\"
- \"what can you do\" -> \"I can help with hardware, software, network, printer, and CCTV issues. Just describe your problem!\"

STYLE RULES:
- Be conversational, not robotic
- Use \"you\" and \"I\" — make it personal
- Acknowledge the frustration before giving solutions
- Ask ONE question at a time, not a list
- Use casual language: \"lets figure this out\" not \"lets troubleshoot step by step\"
- Add personality: \"That sounds frustrating\" or \"Ah, thats a common one!\""
    ],
    [
        'title' => 'Printer Troubleshooting Guide',
        'category' => 'troubleshooting',
        'tags' => 'printer,offline,paper jam,quality',
        'content' => "PRINTER OFFLINE:
1. Check if printer is powered on (power LED solid, not blinking)
2. Check connection: USB cable firm? WiFi connected?
3. On computer: Settings > Devices > Printers > right-click printer > See whats printing
4. If queue has stuck jobs: CMD as admin > net stop spooler > delete files in C:\Windows\System32\spool\PRINTERS > net start spooler

PAPER JAM:
1. Turn off printer
2. Open all access panels
3. Gently pull out stuck paper in direction of paper path (dont rip)
4. Check for torn pieces left inside
5. Clean paper feed rollers
6. Close panels, turn on, try test page

POOR QUALITY:
1. Run printer head cleaning utility
2. Check ink/toner levels
3. For laser: check drum unit
4. Run print head alignment"
    ],
    [
        'title' => 'Network Troubleshooting Guide',
        'category' => 'troubleshooting',
        'tags' => 'network,internet,wifi,slow',
        'content' => "NO INTERNET:
1. Check physical: Ethernet cable plugged in? WiFi connected?
2. CMD > ipconfig /all > check if IP exists
3. If no IP: ipconfig /release then ipconfig /renew
4. If IP exists but no internet: ping 8.8.8.8
5. If ping works but sites dont load: ipconfig /flushdns

WIFI NOT CONNECTING:
1. Check WiFi enabled (laptop Fn key or switch)
2. Forget network and reconnect
3. Restart WiFi adapter in Device Manager
4. Restart router: unplug 30 seconds, plug back in
5. Check if other devices connect (isolates issue)

SLOW INTERNET:
1. Run speed test at speedtest.net
2. Check Task Manager for background downloads
3. Try wired connection if on WiFi
4. Reset network stack: netsh winsock reset then restart"
    ],
    [
        'title' => 'Display Troubleshooting Guide',
        'category' => 'troubleshooting',
        'tags' => 'display,monitor,screen,black',
        'content' => "NO DISPLAY (BLACK SCREEN):
1. Check if PC is on: power LED, fan noise, beeps?
2. If PC on but screen black: reseat video cable (HDMI/DP/VGA) both ends
3. Try different cable or different GPU port
4. If monitor shows No Signal: press input/source button
5. Test monitor with different device
6. If still nothing: reseat GPU

SCREEN FLICKERING:
1. Check cable connection
2. Update graphics driver
3. Check refresh rate: set to 60Hz
4. Try different monitor

WRONG RESOLUTION:
1. Right-click desktop > Display settings > correct resolution
2. Update graphics driver"
    ],
    [
        'title' => 'Power Troubleshooting Guide',
        'category' => 'troubleshooting',
        'tags' => 'power,turn on,shutdown,battery',
        'content' => "COMPUTER WONT TURN ON:
1. Check outlet: plug in phone charger
2. Check PSU switch on back (should be I position)
3. Check power cable both ends
4. Press power button: any fans? beeps? lights?
5. If nothing: try different cable and outlet
6. If brief spin then stop: PSU/motherboard issue — escalate

RANDOM SHUTDOWNS:
1. Check Event Viewer > System > Kernel-Power 41
2. Check CPU temperature with HWMonitor
3. If overheating: clean dust, check thermal paste
4. Test RAM with Windows Memory Diagnostic

LAPTOP NOT CHARGING:
1. Try different charger
2. Check charging port for debris
3. Check battery health: powercfg /batteryreport
4. If below 40%: battery needs replacement"
    ],
    [
        'title' => 'Sound Troubleshooting Guide',
        'category' => 'troubleshooting',
        'tags' => 'sound,audio,crackling,noise,microphone',
        'content' => "NO SOUND:
1. Check volume: speaker icon not muted, volume up
2. Check output device: Sound Settings > correct device selected
3. Test with headphones
4. Restart Windows Audio service (services.msc)
5. Update audio driver in Device Manager

CRACKLING/NOISY SOUND:
1. Check audio cable connections
2. Try different audio jack
3. Disable audio enhancements: Sound Settings > Properties > Enhancements > Disable all
4. Update audio driver
5. Check electromagnetic interference
6. If laptop: could be internal speaker damage

MICROPHONE NOT WORKING:
1. Check Privacy Settings > Microphone > apps can use mic
2. Check input device selected
3. Test: Sound Settings > Input > speak and watch volume bar
4. Update audio driver"
    ],
    [
        'title' => 'BSOD Blue Screen Guide',
        'category' => 'troubleshooting',
        'tags' => 'bsod,blue screen,crash,stop code',
        'content' => "WHEN USER SEES BSOD:
1. Note the STOP CODE (CRITICAL_PROCESS_DIED, IRQL_NOT_LESS_OR_EQUAL, etc.)
2. Disable auto-restart to see the error

COMMON BSOD FIXES:
- CRITICAL_PROCESS_DIED: sfc /scannow then DISM /Online /Cleanup-Image /RestoreHealth
- IRQL_NOT_LESS_OR_EQUAL: Update/rollback drivers
- PAGE_FAULT_IN_NONPAGED_AREA: Test RAM
- SYSTEM_SERVICE_EXCEPTION: Update graphics driver

GENERAL BSOD STEPS:
1. Boot Safe Mode: Shift+Restart > Troubleshoot > Startup Settings
2. Uninstall recent software/drivers
3. Run sfc /scannow
4. Check Event Viewer"
    ],
    [
        'title' => 'CCTV Troubleshooting Guide',
        'category' => 'troubleshooting',
        'tags' => 'cctv,camera,offline,nvr,dvr',
        'content' => "CAMERA OFFLINE:
1. Check power (PoE or adapter)
2. Check LED indicators
3. Ping camera IP address
4. Check PoE switch/injector
5. Try different cable/port
6. Power cycle: unplug 30 seconds, plug back

NO RECORDING:
1. Check NVR/DVR storage — full?
2. Check recording schedule
3. Check camera enabled in NVR
4. Test playback from yesterday
5. Adjust motion detection settings"
    ],
    [
        'title' => 'Windows Commands Reference',
        'category' => 'commands',
        'tags' => 'commands,powershell,cmd',
        'content' => "NETWORK: ipconfig /all, ipconfig /release, ipconfig /renew, ipconfig /flushdns, ping 8.8.8.8, ping google.com, netsh winsock reset, netsh int ip reset

SYSTEM: sfc /scannow, DISM /Online /Cleanup-Image /RestoreHealth, chkdsk C: /f /r, devmgmt.msc, eventvwr, taskmgr, dxdiag

PRINTER: net stop spooler, net start spooler

POWER: powercfg /batteryreport, powercfg /energy, shutdown /s /t 0, shutdown /r /t 0"
    ],
    [
        'title' => 'Troubleshooting Workflow',
        'category' => 'conversation',
        'tags' => 'workflow,process,how to',
        'content' => "HOW TO TROUBLESHOOT:

PHASE 1 — UNDERSTAND: What device? What happens? When did it start? Recent changes? Others affected? What did you try?

PHASE 2 — CHECK BASICS: Power on? Cables connected? Correct device selected? Restart tried?

PHASE 3 — DIAGNOSE: Event Viewer, diagnostic tools, test with known-good parts

PHASE 4 — FIX: Least invasive first, one change at a time, document changes

PHASE 5 — VERIFY: Test fix, fully resolved? Side effects?

PHASE 6 — DOCUMENT: Record problem, record fix, update knowledge base"
    ],
];

$count = 0;
foreach ($files as $f) {
    AIDatabase::insert('ai_training_files', [
        'title' => $f['title'],
        'file_type' => 'text',
        'content' => $f['content'],
        'category' => $f['category'],
        'tags' => $f['tags'],
        'uploaded_by' => 1,
    ]);
    $count++;
    echo "  ✓ {$f['title']}\n";
}

// Also update personality to be more conversational
AIDatabase::execute("UPDATE ai_personality SET greeting = 'Hey there! 👋 Im IT Bot, your IT support buddy. Tell me whats going on with your device and Ill help you sort it out. You can describe the problem in your own words — no need for technical jargon!' WHERE is_active = 1");

echo "\nDone! Seeded {$count} training files.\n";
