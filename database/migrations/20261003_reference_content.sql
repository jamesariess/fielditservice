-- Additive reference library. Import into the existing selected database.
-- No accounts, passwords, tickets, serial numbers or connection settings changed.
-- Knowledge/documentation entries are drafts requiring technician review.
SET NAMES utf8mb4;
START TRANSACTION;
SET @author_id = (SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE u.status='active' AND u.deleted_at IS NULL AND r.name IN ('Admin','Super Admin') ORDER BY u.id LIMIT 1);

INSERT INTO knowledge_articles (title,category,issue,symptoms,root_cause,solution,device_type,author_id,status)
SELECT 'Field reference: No power isolation','Hardware','Device has no power indicators','No power LED, no fan activity','Supply, adapter, connector or system-board fault; confirm by isolation',
'Record:Capture exact model, machine type, power indicators and recent changes.
External supply:Test a known-good wall outlet and model-compatible power cable or approved adapter.
Isolate accessories:Disconnect external peripherals and docking station; retry on approved direct power.
Model procedure:Follow the exact hardware maintenance manual for any power-reset or battery-disconnection procedure.
Hardware assessment:If still dead, run model-specific diagnostics where possible; authorized technicians may replace the isolated faulty FRU using the correct manual. Never open a PSU enclosure.
Source:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads',
'all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM knowledge_articles WHERE title='Field reference: No power isolation');

INSERT INTO knowledge_articles (title,category,issue,symptoms,root_cause,solution,device_type,author_id,status)
SELECT 'Field reference: Powered PC with no display','Hardware','Computer powers on but no image appears','Fans or power indicators active, blank display','Display power, input, cable, POST or graphics fault',
'Distinguish symptom:Confirm PC power activity separately from monitor power. Note any beep or diagnostic LED code.
Monitor test:Check monitor power and open its own on-screen menu. If unavailable, isolate monitor supply before testing PC video.
Signal path:Select the connected input and test a known-good cable, display and supported output one at a time.
POST isolation:If the display works on another source, record POST codes and follow the exact model manual for memory and graphics diagnostics.
Authorized repair:Disconnect power and use ESD precautions before permitted internal work. Replace only a component supported by test evidence; retest after each change.
Source:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads',
'desktop',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM knowledge_articles WHERE title='Field reference: Powered PC with no display');

INSERT INTO knowledge_articles (title,category,issue,symptoms,root_cause,solution,device_type,author_id,status)
SELECT 'Field reference: Unexpected shutdown or restart','Hardware','Device shuts down or restarts without request','Sudden power loss, reboot, possible blue screen','Thermal, power, memory, storage or software fault; do not infer cause from one event',
'Collect evidence:Record whether shutdown occurs in firmware diagnostics or only Windows, under load or idle, and on battery or AC.
Logs:Save relevant System events and crash details. A Kernel-Power event records an unexpected shutdown but does not identify the faulty part.
Power isolation:Test an approved compatible adapter or known-good supply through model-approved procedures.
Thermal inspection:Inspect external ventilation and run approved thermal diagnostics; stop use if there is smoke, swelling or unsafe heat.
Component tests:Run extended memory and storage diagnostics and save failure IDs. Use model-specific authorized FRU replacement only after isolating the fault.
Verification:Reproduce the original workload after each change; do not repeat an already-failed test without a changed condition.
Source:https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads',
'all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM knowledge_articles WHERE title='Field reference: Unexpected shutdown or restart');

INSERT INTO knowledge_articles (title,category,issue,solution,device_type,author_id,status)
SELECT 'Field reference: Windows component repair','Software','Suspected Windows system-file corruption',
'Protect data:Back up critical data and confirm recovery options before repair. Run commands from an elevated terminal.
Image repair:Run DISM /Online /Cleanup-Image /RestoreHealth. Check its exit message; repair may need Windows Update or a compatible repair source.
File repair:After DISM completes successfully, run sfc /scannow and record its result.
Verify:Restart if requested and reproduce the original symptom. If corruption remains, collect logs and use the approved recovery process rather than repeatedly running the same repair.
Sources:https://learn.microsoft.com/en-us/windows-hardware/manufacture/desktop/repair-a-windows-image and https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/sfc',
'all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM knowledge_articles WHERE title='Field reference: Windows component repair');

INSERT INTO knowledge_articles (title,category,issue,solution,device_type,author_id,status)
SELECT 'Documentation template: Authorized hardware replacement','Hardware','Record an evidence-based FRU replacement',
'Identification:Record ticket number, actual machine type/model, actual serial number and authorization reference.
Evidence:Record symptom, diagnostics failure IDs and tests that isolated the component. Do not mark an unperformed test as completed.
Action taken:List each action actually performed, its outcome, old/new FRU identifiers and approved manual revision.
Protection:Record required power isolation, ESD controls and data protection measures.
Validation:Record post-repair diagnostics and original workload results, remaining issues and customer confirmation.
Source:Use the exact manufacturer hardware maintenance manual for this machine; this template is not a disassembly procedure.',
'all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM knowledge_articles WHERE title='Documentation template: Authorized hardware replacement');

INSERT INTO knowledge_articles (title,category,issue,solution,device_type,author_id,status)
SELECT 'Documentation template: Unresolved service handover','Software','Transfer an unresolved case without repeating failed work',
'Issue:Record the exact symptom, affected device and reproduction conditions.
Action taken:List only performed actions with their individual outcomes. Include failed checks so the next technician does not repeat them blindly.
Evidence:Attach diagnostic logs, photos with sensitive information removed, error codes and relevant timestamps.
Next action:Identify the next untried diagnostic or approved part assessment and the responsible team.
Status:Mark unresolved when the original symptom persists; do not report success merely because a secondary indicator changed.',
'all',@author_id,'draft' WHERE @author_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM knowledge_articles WHERE title='Documentation template: Unresolved service handover');

INSERT INTO commands (category_id,command,description,when_to_use,example,expected_output,common_errors,next_steps,risk_level,is_powershell)
SELECT c.id,'sfc /verifyonly','Verify protected system files without repairing them.','Suspected system-file damage.','sfc /verifyonly','Verification result; record exact message.','Requires elevated terminal.','Review evidence before repair. Microsoft reference: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/sfc','safe',0 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM commands WHERE command='sfc /verifyonly');
INSERT INTO commands (category_id,command,description,when_to_use,example,expected_output,common_errors,next_steps,risk_level,is_powershell)
SELECT c.id,'DISM /Online /Cleanup-Image /RestoreHealth','Repair the running Windows component store.','Confirmed or suspected Windows component corruption; back up data first.','DISM /Online /Cleanup-Image /RestoreHealth','Repair status and exit message.','Needs elevation; source files or network policy may prevent repair.','Run SFC after successful repair. Source: https://learn.microsoft.com/en-us/windows-hardware/manufacture/desktop/repair-a-windows-image','caution',0 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM commands WHERE command='DISM /Online /Cleanup-Image /RestoreHealth');
INSERT INTO commands (category_id,command,description,when_to_use,example,expected_output,common_errors,next_steps,risk_level,is_powershell)
SELECT c.id,'sfc /scannow','Scan and repair protected system files.','After component-store repair when indicated.','sfc /scannow','No violations, repaired files or unrepaired corruption.','Requires elevation; some files may remain unrepaired.','Save result and repair logs, then verify symptom. Source: https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/sfc','caution',0 FROM command_categories c WHERE c.slug='system' AND NOT EXISTS (SELECT 1 FROM commands WHERE command='sfc /scannow');
INSERT INTO commands (category_id,command,description,when_to_use,example,expected_output,common_errors,next_steps,risk_level,is_powershell)
SELECT c.id,'Test-NetConnection','Test reachability of a specific TCP service.','Distinguish service connection failure from general network connectivity.','Test-NetConnection -ComputerName server.example -Port 443','TcpTestSucceeded and connection details.','A failed test may reflect firewall or service state, not a failed adapter.','Check the exact endpoint and approved firewall policy. Source: https://learn.microsoft.com/en-us/troubleshoot/azure/virtual-machines/windows/serial-console-cmd-ps-commands','safe',1 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM commands WHERE command='Test-NetConnection');
INSERT INTO commands (category_id,command,description,when_to_use,example,expected_output,common_errors,next_steps,risk_level,is_powershell)
SELECT c.id,'Get-NetAdapter','Display network adapter status.','Identify disconnected or disabled interfaces before changing network settings.','Get-NetAdapter | Format-List Name,Status,LinkSpeed,InterfaceDescription','Adapter identity, link state and speed.','Adapter names vary; do not assume a displayed interface is the affected connection.','Compare link state with cable and switch-port evidence. Source: https://learn.microsoft.com/en-us/troubleshoot/azure/virtual-machines/windows/serial-console-cmd-ps-commands','safe',1 FROM command_categories c WHERE c.slug='network' AND NOT EXISTS (SELECT 1 FROM commands WHERE command='Get-NetAdapter');

INSERT INTO tools (name,icon,purpose,when_to_use,how_to_use,safety)
SELECT 'Lenovo Diagnostics reference','activity','Manufacturer component diagnostics and failure evidence.','Before authorized FRU replacement when the device can run the applicable diagnostic environment.','Select the supported Windows or bootable diagnostic package for the exact machine. Save test results and failure codes. https://support.lenovo.com/us/en/solutions/ht506581-lenovo-diagnostic-solutions-downloads','Use the appropriate model package and protect data before testing.' WHERE NOT EXISTS (SELECT 1 FROM tools WHERE name='Lenovo Diagnostics reference');
INSERT INTO tools (name,icon,purpose,when_to_use,how_to_use,safety)
SELECT 'Model-specific hardware maintenance manual','book-open','Correct FRU identification and approved service procedures.','Any internal repair or part replacement.','Look up the exact machine type and serial on manufacturer support; verify manual applicability before work. https://pcsupport.lenovo.com/','Disconnect power and follow the manual battery and ESD instructions. Never substitute a generic teardown.' WHERE NOT EXISTS (SELECT 1 FROM tools WHERE name='Model-specific hardware maintenance manual');
INSERT INTO tools (name,icon,purpose,when_to_use,how_to_use,safety)
SELECT 'Known-good display and video cable kit','monitor','Isolate display and signal-path faults.','Powered PC with missing image.','Test one known-good cable or compatible display at a time and record each result.','Match connector and supported input; do not force plugs.' WHERE NOT EXISTS (SELECT 1 FROM tools WHERE name='Known-good display and video cable kit');
INSERT INTO tools (name,icon,purpose,when_to_use,how_to_use,safety)
SELECT 'ESD workstation kit','shield-check','Reduce electrostatic damage during authorized internal servicing.','Handling memory, system boards and other ESD-sensitive FRUs.','Use an approved grounded workstation, wrist strap and anti-static storage according to the service manual.','ESD controls do not make live electrical work safe; isolate power first.' WHERE NOT EXISTS (SELECT 1 FROM tools WHERE name='ESD workstation kit');
INSERT INTO tools (name,icon,purpose,when_to_use,how_to_use,safety)
SELECT 'Compatible known-good power adapter','plug','Isolate external laptop supply faults.','No power or unstable operation on AC.','Verify manufacturer-approved voltage, wattage, connector and compatibility; compare behavior using the known-good adapter.','Do not use an arbitrary adapter or open its enclosure.' WHERE NOT EXISTS (SELECT 1 FROM tools WHERE name='Compatible known-good power adapter');

-- Reference families, not actual owned assets; variant specifications deliberately omitted.
INSERT INTO equipment (manufacturer,model_name,device_type,category,tools_needed,repair_guides)
SELECT 'Lenovo','ThinkPad - model-specific service reference','laptop','Reference family','Model-specific hardware maintenance manual; ESD workstation kit','Identify the actual machine type and variant first. Obtain its exact hardware maintenance manual and diagnostics from https://pcsupport.lenovo.com/. Specifications and FRUs vary; this is not an inventory asset.' WHERE NOT EXISTS (SELECT 1 FROM equipment WHERE model_name='ThinkPad - model-specific service reference' AND manufacturer='Lenovo');
INSERT INTO equipment (manufacturer,model_name,device_type,category,tools_needed,repair_guides)
SELECT 'Lenovo','ThinkCentre - model-specific service reference','desktop','Reference family','Model-specific hardware maintenance manual; ESD workstation kit','Confirm tower, SFF or Tiny variant and machine type before selecting procedures or parts. Use https://pcsupport.lenovo.com/. Never open a PSU enclosure.' WHERE NOT EXISTS (SELECT 1 FROM equipment WHERE model_name='ThinkCentre - model-specific service reference' AND manufacturer='Lenovo');
INSERT INTO equipment (manufacturer,model_name,device_type,category,tools_needed,repair_guides)
SELECT 'Lenovo','ThinkStation - model-specific service reference','desktop','Reference family','Model-specific hardware maintenance manual; Lenovo Diagnostics reference','Confirm exact workstation machine type, supported diagnostics and FRU compatibility through https://pcsupport.lenovo.com/. Do not infer specifications from the family name.' WHERE NOT EXISTS (SELECT 1 FROM equipment WHERE model_name='ThinkStation - model-specific service reference' AND manufacturer='Lenovo');
COMMIT;
