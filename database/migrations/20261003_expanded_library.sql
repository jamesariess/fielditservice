-- Expanded field-service reference library. Review drafts before publication.
SET NAMES utf8mb4;
START TRANSACTION;
SET @author_id = (SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE u.status='active' AND u.deleted_at IS NULL AND r.name IN ('Admin','Super Admin') ORDER BY u.id LIMIT 1);

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Laptop does not charge','Hardware','Battery percentage does not increase on AC','Battery percentage does not increase on AC','Requires diagnosis; do not infer a faulty part from the symptom alone.','Distinguish:Record whether AC is detected, whether charging is intentionally limited and whether behavior changes with temperature.
Supply:Test an approved compatible charger directly without the dock; inspect the external connector for damage.
Diagnostics:Run supported battery and adapter tests; record battery health and failure IDs.
Repair:Follow the exact model manual for authorized battery or charging-path replacement. Stop use for swelling or unsafe heat.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','laptop',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Laptop does not charge');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Laptop short battery runtime','Hardware','Battery drains faster than expected','Battery drains faster than expected','Requires diagnosis; do not infer a faulty part from the symptom alone.','Baseline:Record workload, brightness, battery age and actual runtime; compare only under comparable conditions.
Isolation:Compare idle and normal-workload behavior; review high CPU and background activity.
Evidence:Collect a battery report and manufacturer battery diagnostics; capacity estimates alone are not a complete fault diagnosis.
Resolution:Correct excess load or replace a confirmed degraded battery under the approved model procedure.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','laptop',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Laptop short battery runtime');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Overheating and thermal throttling','Hardware','Reduced performance or shutdown under load','Reduced performance or shutdown under load','Requires diagnosis; do not infer a faulty part from the symptom alone.','Scope:Record ambient conditions, workload and whether the fault occurs on AC or battery.
Airflow:Inspect unobstructed external vents; use a stable hard surface and check fan activity.
Isolation:Run approved thermal and fan diagnostics; compare temperatures against model guidance, not a universal threshold.
Service:Power-isolate before approved cleaning or fan replacement; follow model instructions for thermal materials. Stop if unsafe heat is present.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Overheating and thermal throttling');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Intermittent memory errors','Hardware','Crashes, POST failures or diagnostic memory errors','Crashes, POST failures or diagnostic memory errors','Requires diagnosis; do not infer a faulty part from the symptom alone.','Evidence:Record error codes and whether crashes follow a memory upgrade.
Compatibility:Verify supported memory type, configuration and firmware using the exact machine type.
Isolation:Run extended manufacturer memory tests; authorized technicians may test supported modules and slots one at a time following the manual.
Validation:Retest each changed configuration and document the isolated faulty module or slot before replacing a FRU.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Intermittent memory errors');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Storage not detected in firmware','Hardware','Drive absent from firmware or diagnostics','Drive absent from firmware or diagnostics','Requires diagnosis; do not infer a faulty part from the symptom alone.','Protect data:Ask whether recovery is required before changes; do not initialize or format the drive.
Configuration:Record firmware storage settings and recent changes without blindly switching controller modes.
Isolation:Use model diagnostics and approved connector inspection after power isolation.
Next action:If detection remains absent, arrange authorized drive or connector assessment; preserve the original drive for approved recovery.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Storage not detected in firmware');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Slow storage or recurring disk errors','Hardware','Slow file access, I/O errors or drive warnings','Slow file access, I/O errors or drive warnings','Requires diagnosis; do not infer a faulty part from the symptom alone.','Protect data:Back up readable critical data first; minimize testing if hardware failure is suspected.
Evidence:Record disk-related events, free space and manufacturer storage diagnostics.
Distinguish:Compare drive health, workload and file-system evidence; a health status of OK does not rule out every failure.
Resolution:Replace a confirmed failing drive under approved recovery procedures; use file-system repair only when evidence and backup justify it.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Slow storage or recurring disk errors');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Windows boot failure after change','Software','Windows fails after a driver, update or hardware change','Windows fails after a driver, update or hardware change','Requires diagnosis; do not infer a faulty part from the symptom alone.','Record:Capture exact error and the most recent change; distinguish firmware detection from Windows startup.
Protect access:Confirm backup and encryption recovery-key availability before recovery actions.
Isolate:Use approved Windows recovery or rollback appropriate to the specific change; avoid indiscriminate firmware resets.
Validate:Retest boot and workload; escalate with recovery logs if the next approved action risks data loss.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Windows boot failure after change');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Blue screen evidence collection','Software','Windows stop error or recurrent crash','Windows stop error or recurrent crash','Requires diagnosis; do not infer a faulty part from the symptom alone.','Capture:Record stop code, timestamp, workload and recently changed drivers or hardware.
Collect:Save permitted crash dump and System events while protecting sensitive content.
Isolate:Correlate diagnostics, drivers and reproducible workload; a stop code alone does not prove a particular FRU is faulty.
Next action:Use approved driver rollback or manufacturer component tests, changing one variable at a time.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Blue screen evidence collection');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: USB-C dock has no external display','Hardware','Dock connected but external monitor blank','Dock connected but external monitor blank','Requires diagnosis; do not infer a faulty part from the symptom alone.','Compatibility:Confirm dock, host port, cable and display support the required video capability.
Direct test:Connect the display directly to a supported host output to isolate the dock path.
Power and input:Check dock supply, display input and a known-good compatible video cable.
Software:Apply model-approved dock firmware and drivers under stable power; retest each output separately.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','laptop',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: USB-C dock has no external display');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: USB peripheral not detected','Hardware','USB device absent or intermittently disconnects','USB device absent or intermittently disconnects','Requires diagnosis; do not infer a faulty part from the symptom alone.','Scope:Determine whether one peripheral, one port or all ports are affected.
Cross-test:Use a known-good peripheral and supported alternate port; test the suspect device on another approved machine.
Evidence:Inspect Device Manager status and relevant events without uninstalling unrelated devices.
Repair:Isolate cable, peripheral or port fault; avoid forcing a damaged connector and use model-approved port servicing.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: USB peripheral not detected');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Keyboard keys do not respond','Hardware','Some keys or entire keyboard fail','Some keys or entire keyboard fail','Requires diagnosis; do not infer a faulty part from the symptom alone.','Scope:Check layout, accessibility settings and whether the failure occurs outside the affected application.
Cross-test:Try a known-good external keyboard and model-supported preboot keyboard test.
Inspect:Record liquid damage or physical obstruction; do not continue powering a liquid-damaged unit.
Service:Follow the model manual for approved keyboard or connector repair and verify every affected key.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','laptop',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Keyboard keys do not respond');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Touchpad unavailable','Hardware','Pointer works on external mouse but touchpad does not','Pointer works on external mouse but touchpad does not','Requires diagnosis; do not infer a faulty part from the symptom alone.','Settings:Check supported touchpad enable controls and OS settings.
Evidence:Record Device Manager state and model-specific driver version.
Isolation:Compare behavior with approved preboot diagnostics or a supported clean driver state.
Service:If isolated to hardware, power-isolate and follow the exact touchpad/cable service procedure.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','laptop',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Touchpad unavailable');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Camera or microphone unavailable','Software','Application cannot use camera or microphone','Application cannot use camera or microphone','Requires diagnosis; do not infer a faulty part from the symptom alone.','Scope:Compare an approved alternate application and record whether only one program is affected.
Privacy:Check physical privacy controls and OS/application permissions; do not bypass organization policy.
Evidence:Confirm correct input device and inspect device/driver status.
Isolation:Test a known-good approved external device to distinguish application, driver and integrated-device faults.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Camera or microphone unavailable');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Ethernet link unavailable','Network','No wired link or adapter reports disconnected','No wired link or adapter reports disconnected','Requires diagnosis; do not infer a faulty part from the symptom alone.','Physical:Check cable connection and link indicators; test a known-good cable and approved switch port.
Adapter:Record adapter state with Get-NetAdapter and inspect driver/device status.
Network:Ask the network team to verify switch-port configuration and VLAN; do not make unauthorized changes.
Verify:Retest link, address and target service separately; log which stage fails.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Ethernet link unavailable');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: DHCP address unavailable','Network','No usable DHCP address or self-assigned address','No usable DHCP address or self-assigned address','Requires diagnosis; do not infer a faulty part from the symptom alone.','Baseline:Record ipconfig /all and confirm whether the interface is intended to use DHCP.
Physical:Verify link and the correct network before renewing anything.
Service:Ask the network team to check DHCP scope, VLAN, relay and lease evidence.
Retest:Perform approved lease renewal only when disruption is acceptable; verify gateway, DNS and target service.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: DHCP address unavailable');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Name resolution failure','Network','Service works by address but not hostname','Service works by address but not hostname','Requires diagnosis; do not infer a faulty part from the symptom alone.','Scope:Compare the exact hostname and an approved known-working name; record full error messages.
Configuration:Record assigned DNS servers and suffixes with ipconfig /all.
Evidence:Use Resolve-DnsName or nslookup against the intended resolver; compare internal versus external scope.
Next action:Correct authorized DNS configuration or ask the DNS owner to review records; do not replace enterprise DNS with a public resolver blindly.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Name resolution failure');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Wi-Fi intermittent disconnects','Network','Wireless connection drops during work','Wireless connection drops during work','Requires diagnosis; do not infer a faulty part from the symptom alone.','Scope:Record SSID, location, timing, signal context and whether other users are affected.
Compare:Test approved wired connectivity and another permitted access point if available.
Evidence:Collect wireless connection and driver events; correlate roam, sleep and workload changes.
Next action:Apply approved adapter updates or involve the wireless team with timestamps; do not disable required security settings.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','laptop',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Wi-Fi intermittent disconnects');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: VPN connects but service unavailable','Network','VPN session active but internal service unreachable','VPN session active but internal service unreachable','Requires diagnosis; do not infer a faulty part from the symptom alone.','Scope:Record target hostname, port and whether other approved services work.
Evidence:Compare DNS resolution and routing before and after VPN connection.
Service test:Use an approved TCP test against the exact internal service endpoint.
Escalate:Send route, DNS and service-test evidence to the VPN/network owner; do not disable firewall or change split-tunnel policy without approval.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: VPN connects but service unavailable');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Printer queue stuck','Printer','Jobs remain queued or fail repeatedly','Jobs remain queued or fail repeatedly','Requires diagnosis; do not infer a faulty part from the symptom alone.','Scope:Check whether one job, one user or all users are affected; record printer status and connectivity.
Device test:Run the printer built-in test page to distinguish print-engine issues from client jobs.
Queue evidence:Inspect the specific queue and job status using approved tools.
Recovery:Cancel only authorized affected jobs; restart shared spooler services only with approval because other users may be disrupted.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','printer',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Printer queue stuck');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Printer poor output quality','Printer','Streaks, fading or repeated marks','Streaks, fading or repeated marks','Requires diagnosis; do not infer a faulty part from the symptom alone.','Baseline:Print a built-in quality page and record which colors or areas are affected.
Consumables:Check correct supported toner/ink and supplies without substituting incompatible parts.
Maintenance:Run the manufacturer-approved calibration or cleaning cycle for the exact model.
Service:Use model-specific consumable or maintenance-unit replacement; allow hot components to cool and do not bypass safety covers.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','printer',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Printer poor output quality');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Display flicker or intermittent signal','Hardware','Image flickers or drops intermittently','Image flickers or drops intermittently','Requires diagnosis; do not infer a faulty part from the symptom alone.','Scope:Record refresh rate, resolution, dock path and workload.
Direct isolation:Bypass adapters/dock with a supported direct connection where possible.
Cross-test:Compare a known-good cable, display and source one at a time.
Resolution:Use supported display settings and approved graphics updates; replace only the isolated defective component.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','monitor',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Display flicker or intermittent signal');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: BitLocker recovery prompt after change','Security','Device requests recovery key','Device requests recovery key','Requires diagnosis; do not infer a faulty part from the symptom alone.','Protect information:Obtain recovery material only through the authorized organization process; never paste keys into public chat or reports.
Context:Record recent firmware, TPM, boot-order or hardware changes.
Recovery:Use the approved recovery process after identity and device ownership checks.
Prevent recurrence:Ask the endpoint owner to review the triggering change; do not clear TPM or disable encryption as a generic fix.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: BitLocker recovery prompt after change');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Suspicious endpoint activity','Security','Possible malware or unauthorized activity','Possible malware or unauthorized activity','Requires diagnosis; do not infer a faulty part from the symptom alone.','Preserve:Record observed indicators and notify the security team; do not delete evidence blindly.
Contain:Follow the organization isolation procedure rather than improvising security changes.
Protect:Avoid copying suspicious executables or confidential logs into chat.
Handover:Provide approved evidence, timestamps and actions taken; security approval governs restoration.
Reference:https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Suspicious endpoint activity');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`symptoms`,`root_cause`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Field library: Firmware update preflight','Hardware','Approved BIOS or device firmware maintenance','Approved BIOS or device firmware maintenance','Requires diagnosis; do not infer a faulty part from the symptom alone.','Identify:Verify exact machine type, applicable release and manufacturer instructions.
Protect:Confirm backup, encryption recovery-key handling and organization authorization.
Stability:Use approved stable power and satisfy battery or other model prerequisites.
Execute:Follow manufacturer update steps without interruption; capture versions and verify operation afterward. Do not flash firmware on unstable hardware.
Reference:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Field library: Firmware update preflight');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Service documentation: Service intake','Software','Internal service documentation template; not a completed service record.','Identification:Record real ticket, device model and serial, contact and location.
Symptom:Record exact behavior and customer impact without assuming root cause.
Consent:Record authorization, data protection and access restrictions.
Baseline:Record indicators and existing damage before service.','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Service documentation: Service intake');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Service documentation: Diagnostic test record','Software','Internal service documentation template; not a completed service record.','Test:Record test name, tool version, applicable device and start/end times.
Conditions:Record supply, connected peripherals and reproduction workload.
Result:Record exact code, outcome and saved evidence location.
Next action:Explain what the result rules in or out and the next untried test.','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Service documentation: Diagnostic test record');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Service documentation: Parts replacement record','Software','Internal service documentation template; not a completed service record.','Part:Record verified FRU and authorization; never invent part identifiers.
Evidence:Record diagnosis supporting replacement and prior failed checks.
Action taken:Record actual removal/replacement under the applicable manual.
Validation:Record post-repair test results and disposition of the old part.','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Service documentation: Parts replacement record');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Service documentation: Network escalation handover','Software','Internal service documentation template; not a completed service record.','Scope:Record affected users, site, interface, service and timestamps.
Evidence:Record link state, address, gateway, DNS and service tests.
Changes:List approved changes and outcomes, including failed attempts.
Owner:Identify the responsible team and next action without exposing passwords.','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Service documentation: Network escalation handover');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Service documentation: Device deployment acceptance','Software','Internal service documentation template; not a completed service record.','Asset:Record actual machine type, serial and assigned recipient.
Configuration:Record approved image, management enrollment and required applications.
Validation:Verify authorized network, peripherals, updates and encryption status.
Acceptance:Record recipient confirmation and unresolved items without publishing recovery keys.','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Service documentation: Device deployment acceptance');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Service documentation: Preventive maintenance record','Software','Internal service documentation template; not a completed service record.','Authorization:Record maintenance scope and approved service window.
Baseline:Record health and existing faults before intervention.
Work:List only performed inspection, cleaning and approved updates.
Validation:Record post-maintenance diagnostics and any corrective actions needed.','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Service documentation: Preventive maintenance record');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Service documentation: Return to service checklist','Software','Internal service documentation template; not a completed service record.','Original issue:Reproduce the original symptom and confirm its current state.
Diagnostics:Record relevant post-repair tests and their actual outcomes.
Configuration:Confirm supported settings and required management/security controls.
Closure:Record confirmation, remaining risks and whether the ticket is solved or unresolved.','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Service documentation: Return to service checklist');

INSERT INTO `knowledge_articles` (`title`,`category`,`issue`,`solution`,`device_type`,`author_id`,`status`)
SELECT 'Service documentation: Unresolved troubleshooting handover','Software','Internal service documentation template; not a completed service record.','Symptom:Record exact persistent fault and reproduction conditions.
Action taken:List each completed check and outcome, especially failed checks.
Evidence:Reference logs and diagnostic codes without including credentials.
Next step:Record the next untried diagnostic, responsible team and authorization needed.','all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `knowledge_articles` WHERE `title`='Service documentation: Unresolved troubleshooting handover');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'ipconfig /all','Display detailed interface addressing and DNS configuration.','Before changing IP or DNS settings.','ipconfig /all','Addresses, DHCP state, gateway and DNS servers.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','safe',0 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='ipconfig /all');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'ipconfig /displaydns','Inspect cached DNS records.','Compare cached results with intended name resolution.','ipconfig /displaydns','Cached entries; absence alone does not prove a DNS fault.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','safe',0 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='ipconfig /displaydns');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'ipconfig /flushdns','Clear the local DNS resolver cache.','Only after documenting evidence of a stale local cache.','ipconfig /flushdns','Cache flush status; does not fix upstream DNS.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','caution',0 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='ipconfig /flushdns');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'ping','Test ICMP reachability to an approved endpoint.','Basic connectivity comparison.','ping gateway.example','Replies or timeouts; ICMP blocking does not prove the service is down.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','safe',0 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='ping');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'tracert','Inspect route hops toward an approved destination.','Compare route paths during a connectivity investigation.','tracert server.example','Hop responses; unresponsive hops may filter ICMP.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','safe',0 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='tracert');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'nslookup','Query DNS information.','Investigate exact hostname resolution.','nslookup server.example','Resolver and query response.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','safe',0 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='nslookup');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Resolve-DnsName','Query DNS records through PowerShell.','Inspect the intended hostname and record type.','Resolve-DnsName server.example','DNS records or a specific query error.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/dnsclient/resolve-dnsname','safe',1 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Resolve-DnsName');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-NetIPConfiguration','Display interface address, gateway and DNS details.','Baseline collection before network changes.','Get-NetIPConfiguration','Configuration per interface.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/nettcpip/get-netipconfiguration','safe',1 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-NetIPConfiguration');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-NetRoute','Inspect the routing table.','Compare routes when VPN or internal service access fails.','Get-NetRoute','Destination prefixes, interfaces and next hops.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/nettcpip/get-netroute','safe',1 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-NetRoute');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'netstat -ano','Display connections with process identifiers.','Correlate a local application with network activity.','netstat -ano','Connection state and PID; protect host/address information.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','safe',0 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='netstat -ano');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'systeminfo','Display OS and system configuration.','Collect a baseline for support.','systeminfo','OS, boot and hardware summary.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','safe',0 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='systeminfo');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-WinEvent','Read event records with targeted filters.','Collect recent System evidence around a failure.','Get-WinEvent -FilterHashtable @{LogName="System"} -MaxEvents 30','Recent events; some logs require elevation.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/microsoft.powershell.diagnostics/get-winevent','safe',1 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-WinEvent');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-Process','Inspect running processes.','Identify sustained workload before terminating anything.','Get-Process | Sort-Object CPU -Descending | Select-Object -First 10','Process identifiers and cumulative CPU time, not instantaneous CPU percentage.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/microsoft.powershell.management/get-process','safe',1 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-Process');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-Service','Read service status.','Check the affected application service without changing it.','Get-Service','Names and states; stopped may be normal for demand-start services.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/microsoft.powershell.management/get-service','safe',1 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-Service');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-CimInstance Win32_ComputerSystem','Read computer manufacturer and model.','Confirm machine identity before selecting manufacturer guidance.','Get-CimInstance Win32_ComputerSystem | Select-Object Manufacturer,Model','Reported model and manufacturer; still verify machine type.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/cimcmdlets/get-ciminstance','safe',1 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-CimInstance Win32_ComputerSystem');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-CimInstance Win32_BIOS','Read BIOS version and reported serial.','Collect firmware and asset baseline.','Get-CimInstance Win32_BIOS | Select-Object SerialNumber,SMBIOSBIOSVersion','Reported identifiers; treat serials as organizational data.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/cimcmdlets/get-ciminstance','safe',1 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-CimInstance Win32_BIOS');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'powercfg /batteryreport','Generate a battery usage and capacity report.','Investigate short runtime or capacity decline.','powercfg /batteryreport','Report location; capacity estimates require diagnostic context.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','safe',0 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='powercfg /batteryreport');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'powercfg /lastwake','Read the recorded last wake source.','Investigate unexpected wake rather than power loss.','powercfg /lastwake','Last recorded wake details, if available.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','safe',0 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='powercfg /lastwake');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-Volume','Read volume capacity and file-system details.','Investigate low space without deleting data.','Get-Volume','Drive letters, file systems and capacity.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/storage/get-volume','safe',1 FROM command_categories c WHERE c.slug='disk' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-Volume');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-Disk','Read disk identity and operational state.','Confirm which disk is affected before any storage action.','Get-Disk','Disk numbers, size and state; do not initialize an unknown disk.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/storage/get-disk','safe',1 FROM command_categories c WHERE c.slug='disk' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-Disk');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-PhysicalDisk','Read storage-provider disk health information.','Gather health evidence alongside manufacturer diagnostics.','Get-PhysicalDisk','Provider-dependent health status; OK is not conclusive.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/storage/get-physicaldisk','safe',1 FROM command_categories c WHERE c.slug='disk' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-PhysicalDisk');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'chkdsk','Inspect file-system status without requested repair.','Use after backup and when a file-system check is appropriate.','chkdsk C:','File-system report; active volumes can affect observations.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/windows-commands','caution',0 FROM command_categories c WHERE c.slug='disk' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='chkdsk');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-Printer','List installed printer queues.','Identify the exact affected queue.','Get-Printer','Queue and driver information; PrintManagement module required.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/printmanagement/get-printer','safe',1 FROM command_categories c WHERE c.slug='printer' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-Printer');

INSERT INTO `commands` (`category_id`,`command`,`description`,`when_to_use`,`example`,`expected_output`,`common_errors`,`next_steps`,`risk_level`,`is_powershell`)
SELECT c.id,'Get-PrintJob','Read jobs in a specific printer queue.','Investigate a stuck job without clearing unrelated work.','Get-PrintJob -PrinterName "Office Printer"','Jobs for the named queue; module and permissions required.','Check elevation, module availability, exact target and OS support. Do not infer hardware failure from one unsuccessful command.','Save relevant output with sensitive details redacted, then follow the next evidence-based diagnostic. Reference: https://learn.microsoft.com/en-us/powershell/module/printmanagement/get-printjob','safe',1 FROM command_categories c WHERE c.slug='printer' AND NOT EXISTS (SELECT 1 FROM `commands` WHERE `command`='Get-PrintJob');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Digital multimeter - authorized low-voltage use','gauge','Check supported low-voltage power measurements.','Only when the model procedure specifies permitted measurements.','Use approved ranges and leads according to the service procedure.','Never probe mains or open PSU/adaptor enclosures; this reference does not authorize live electrical work.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Digital multimeter - authorized low-voltage use');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Precision screwdriver and bit kit','wrench','Remove model-approved service fasteners.','Authorized internal service.','Match the documented bit and track screw positions; follow specified torque guidance.','Power-isolate; avoid stripping screws or damaging batteries.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Precision screwdriver and bit kit');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Nonconductive service opening tools','wrench','Release supported case clips during authorized service.','Only where the exact maintenance manual permits opening.','Follow clip sequence and stop if resistance is abnormal.','Never lever against a battery or improvise a puncture risk.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Nonconductive service opening tools');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Anti-static FRU storage bags','package','Protect removed ESD-sensitive parts.','Part transport and temporary storage.','Label and store the actual FRU in approved packaging.','Do not use conductive packaging as a work surface on powered equipment.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Anti-static FRU storage bags');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Known-good Ethernet cable kit','cable','Isolate cable-related link faults.','Wired connection unavailable or unstable.','Compare a verified compatible cable while retaining original test conditions.','Respect cable category and site patching policy.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Known-good Ethernet cable kit');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Network cable continuity tester','cable','Check supported cable continuity and wire mapping.','Suspected physical copper cabling fault.','Disconnect and test according to the tester instructions; record its result.','Continuity alone does not certify performance; confirm powered/PoE compatibility before connection.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Network cable continuity tester');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Approved known-good USB peripheral','usb','Compare USB port and peripheral behavior.','Single USB device or port unavailable.','Test one known-good approved device on affected and alternate ports.','Do not insert unknown USB devices or bypass device-control policy.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Approved known-good USB peripheral');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Manufacturer printer self-test page','printer','Separate print-engine faults from client/queue issues.','Printer offline reports or poor output quality.','Use the exact model built-in print-test procedure and retain evidence.','Do not enter service modes or reset counters without authorization.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Manufacturer printer self-test page');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Windows Event Viewer','list','Inspect time-correlated application and system evidence.','Crashes, service errors and unexpected restart investigation.','Filter the relevant log and time range; export only approved evidence.','Logs may contain names, addresses and sensitive application information.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Windows Event Viewer');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Windows Reliability Monitor','activity','Review recorded failures and change history.','Correlate a reported fault with software changes.','Open Reliability Monitor and inspect events around the reported time.','A correlation is not proof of the root cause.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Windows Reliability Monitor');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Windows Device Manager','cpu','Inspect device state and driver details.','Missing peripheral or device error.','Record exact status and hardware identity before changing a driver.','Do not uninstall unrelated devices or disable required security hardware.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Windows Device Manager');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Windows Task Manager','activity','Inspect resource use and application state.','Performance baseline collection.','Compare CPU, memory, disk and network behavior during the affected workload.','Avoid ending system or business-critical processes without approval.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Windows Task Manager');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Approved bootable diagnostic media','hard-drive','Run manufacturer-supported hardware tests outside the OS.','Distinguish OS behavior from component failure.','Create verified model-compatible media using official guidance; save test IDs.','Confirm boot authorization and recovery-key availability; do not overwrite user disks.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Approved bootable diagnostic media');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Manufacturer parts compatibility lookup','search','Verify compatible replacement FRUs.','Before ordering or installing a replacement.','Use actual machine type/serial and compare the approved FRU listing: https://support.lenovo.com/us/en/parts-lookup','Do not select a part from family name alone or expose serials publicly.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Manufacturer parts compatibility lookup');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Authorized encrypted backup destination','hard-drive','Protect critical data before risky repair or recovery.','Before storage replacement, recovery or potentially destructive changes.','Use the organization-approved destination and verify accessible backup content.','Do not copy customer data to personal media or unapproved cloud storage.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Authorized encrypted backup destination');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Service inspection light','flashlight','Inspect external connector and physical condition.','Visual intake and damage inspection.','Inspect without probing energized contacts; record only approved photos.','Avoid creating shorts or publishing identifying/customer information.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Service inspection light');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Approved replacement thermal materials','thermometer','Restore specified thermal interfaces during authorized repair.','Only when the model service procedure requires replacement.','Use the documented material and application method for that exact assembly.','Do not reuse prohibited pads or substitute arbitrary thickness/material.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Approved replacement thermal materials');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Service screw organization tray','package','Prevent misplaced or incorrectly reinstalled fasteners.','Authorized disassembly.','Map screws to the documented location and retain model-specific screw types.','Wrong screw length can damage boards, display assemblies or batteries.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Service screw organization tray');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Manufacturer display on-screen menu test','monitor','Check whether a display can generate its own menu independently.','Distinguish display power/panel behavior from missing host video.','Open the supported monitor menu and compare with signal connected/disconnected.','A working menu does not prove every display input or cable is healthy.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Manufacturer display on-screen menu test');

INSERT INTO `tools` (`name`,`icon`,`purpose`,`when_to_use`,`how_to_use`,`safety`)
SELECT 'Approved driver package repository','folder-check','Obtain model-compatible, organization-approved drivers.','Confirmed driver remediation or deployment.','Match machine type, OS and signed package using the official/approved repository.','Do not install random driver bundles or disable signature enforcement.' WHERE NOT EXISTS (SELECT 1 FROM `tools` WHERE `name`='Approved driver package repository');

INSERT INTO `equipment` (`manufacturer`,`model_name`,`device_type`,`category`,`tools_needed`,`repair_guides`)
SELECT 'Lenovo','Reference only: ThinkPad laptop service reference','laptop','Reference family','Model-specific hardware maintenance manual, Manufacturer parts compatibility lookup','Identification:Confirm actual machine type, battery design, approved adapter and supported memory/storage before service.|Service scope:This is a reference entry, not an owned asset or verified SKU. No serial, specification or FRU compatibility is implied.|Procedure:Use the exact manufacturer manual and approved diagnostic workflow. Lenovo source: https://pcsupport.lenovo.com/' WHERE NOT EXISTS (SELECT 1 FROM `equipment` WHERE `model_name`='Reference only: ThinkPad laptop service reference');

INSERT INTO `equipment` (`manufacturer`,`model_name`,`device_type`,`category`,`tools_needed`,`repair_guides`)
SELECT 'Lenovo','Reference only: ThinkCentre Tiny service reference','desktop','Reference family','Model-specific hardware maintenance manual, Manufacturer parts compatibility lookup','Identification:Confirm Tiny machine type, supported external supply and display outputs. Do not assume tower FRUs apply.|Service scope:This is a reference entry, not an owned asset or verified SKU. No serial, specification or FRU compatibility is implied.|Procedure:Use the exact manufacturer manual and approved diagnostic workflow. Lenovo source: https://pcsupport.lenovo.com/' WHERE NOT EXISTS (SELECT 1 FROM `equipment` WHERE `model_name`='Reference only: ThinkCentre Tiny service reference');

INSERT INTO `equipment` (`manufacturer`,`model_name`,`device_type`,`category`,`tools_needed`,`repair_guides`)
SELECT 'Lenovo','Reference only: ThinkCentre tower or SFF service reference','desktop','Reference family','Model-specific hardware maintenance manual, Manufacturer parts compatibility lookup','Identification:Confirm exact chassis and power-supply assembly; FRUs differ between tower and SFF variants.|Service scope:This is a reference entry, not an owned asset or verified SKU. No serial, specification or FRU compatibility is implied.|Procedure:Use the exact manufacturer manual and approved diagnostic workflow. Lenovo source: https://pcsupport.lenovo.com/' WHERE NOT EXISTS (SELECT 1 FROM `equipment` WHERE `model_name`='Reference only: ThinkCentre tower or SFF service reference');

INSERT INTO `equipment` (`manufacturer`,`model_name`,`device_type`,`category`,`tools_needed`,`repair_guides`)
SELECT 'Lenovo','Reference only: ThinkStation workstation service reference','desktop','Reference family','Model-specific hardware maintenance manual, Manufacturer parts compatibility lookup','Identification:Confirm model, supported graphics, memory configuration and diagnostic package before isolation.|Service scope:This is a reference entry, not an owned asset or verified SKU. No serial, specification or FRU compatibility is implied.|Procedure:Use the exact manufacturer manual and approved diagnostic workflow. Lenovo source: https://pcsupport.lenovo.com/' WHERE NOT EXISTS (SELECT 1 FROM `equipment` WHERE `model_name`='Reference only: ThinkStation workstation service reference');

INSERT INTO `equipment` (`manufacturer`,`model_name`,`device_type`,`category`,`tools_needed`,`repair_guides`)
SELECT 'Lenovo','Reference only: ThinkVision display service reference','monitor','Reference family','Model-specific hardware maintenance manual, Manufacturer parts compatibility lookup','Identification:Confirm exact display model, supported power supply, inputs and USB-C capabilities.|Service scope:This is a reference entry, not an owned asset or verified SKU. No serial, specification or FRU compatibility is implied.|Procedure:Use the exact manufacturer manual and approved diagnostic workflow. Lenovo source: https://pcsupport.lenovo.com/' WHERE NOT EXISTS (SELECT 1 FROM `equipment` WHERE `model_name`='Reference only: ThinkVision display service reference');

INSERT INTO `equipment` (`manufacturer`,`model_name`,`device_type`,`category`,`tools_needed`,`repair_guides`)
SELECT 'Lenovo','Reference only: USB-C or Thunderbolt dock service reference','other','Reference family','Model-specific hardware maintenance manual, Manufacturer parts compatibility lookup','Identification:Confirm exact dock and host compatibility; a USB-C connector alone does not establish video or Thunderbolt support.|Service scope:This is a reference entry, not an owned asset or verified SKU. No serial, specification or FRU compatibility is implied.|Procedure:Use the exact manufacturer manual and approved diagnostic workflow. Lenovo source: https://pcsupport.lenovo.com/' WHERE NOT EXISTS (SELECT 1 FROM `equipment` WHERE `model_name`='Reference only: USB-C or Thunderbolt dock service reference');

INSERT INTO `equipment` (`manufacturer`,`model_name`,`device_type`,`category`,`tools_needed`,`repair_guides`)
SELECT 'Generic','Reference only: Network printer service reference','printer','Reference family','Model-specific hardware maintenance manual, Manufacturer parts compatibility lookup','Identification:Identify actual manufacturer/model before selecting consumables, maintenance or reset procedures.|Service scope:This is a reference entry, not an owned asset or verified SKU. No serial, specification or FRU compatibility is implied.|Procedure:Use the exact manufacturer manual and approved diagnostic workflow. Lenovo source: https://pcsupport.lenovo.com/' WHERE NOT EXISTS (SELECT 1 FROM `equipment` WHERE `model_name`='Reference only: Network printer service reference');

INSERT INTO `equipment` (`manufacturer`,`model_name`,`device_type`,`category`,`tools_needed`,`repair_guides`)
SELECT 'Generic','Reference only: Managed Ethernet switch service reference','switch','Reference family','Model-specific hardware maintenance manual, Manufacturer parts compatibility lookup','Identification:Identify model, port capabilities and site configuration. Configuration changes require network-owner approval.|Service scope:This is a reference entry, not an owned asset or verified SKU. No serial, specification or FRU compatibility is implied.|Procedure:Use the exact manufacturer manual and approved diagnostic workflow. Lenovo source: https://pcsupport.lenovo.com/' WHERE NOT EXISTS (SELECT 1 FROM `equipment` WHERE `model_name`='Reference only: Managed Ethernet switch service reference');

INSERT INTO `equipment` (`manufacturer`,`model_name`,`device_type`,`category`,`tools_needed`,`repair_guides`)
SELECT 'Generic','Reference only: Enterprise access point service reference','access_point','Reference family','Model-specific hardware maintenance manual, Manufacturer parts compatibility lookup','Identification:Identify model, controller and approved management process; avoid factory resets during live service.|Service scope:This is a reference entry, not an owned asset or verified SKU. No serial, specification or FRU compatibility is implied.|Procedure:Use the exact manufacturer manual and approved diagnostic workflow. Lenovo source: https://pcsupport.lenovo.com/' WHERE NOT EXISTS (SELECT 1 FROM `equipment` WHERE `model_name`='Reference only: Enterprise access point service reference');

INSERT INTO `equipment` (`manufacturer`,`model_name`,`device_type`,`category`,`tools_needed`,`repair_guides`)
SELECT 'Generic','Reference only: External power adapter service reference','other','Reference family','Model-specific hardware maintenance manual, Manufacturer parts compatibility lookup','Identification:Verify exact device compatibility, voltage, current/wattage and connector through manufacturer guidance.|Service scope:This is a reference entry, not an owned asset or verified SKU. No serial, specification or FRU compatibility is implied.|Procedure:Use the exact manufacturer manual and approved diagnostic workflow. Lenovo source: https://pcsupport.lenovo.com/' WHERE NOT EXISTS (SELECT 1 FROM `equipment` WHERE `model_name`='Reference only: External power adapter service reference');

COMMIT;
