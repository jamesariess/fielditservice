# Expanded Field-Service Library

This bundle contains the original starter content plus 86 additional candidate entries:

- 24 troubleshooting guides: power/charging, battery runtime, thermal faults, memory, storage, boot/crashes, docking, peripherals, networking, printing, encryption/security handover and firmware preparation.
- 8 service-documentation templates: intake, test evidence, replacement, network handover, deployment, maintenance, return to service and unresolved cases.
- 24 command references with examples, interpretation limits, risk levels and Microsoft links.
- 20 practical tools with usage and safety guidance.
- 10 explicitly labeled equipment service references. These are not verified SKUs or actual inventory; no serial numbers or specifications are invented.

Back up your domain database first. Select that database in phpMyAdmin and import:

1. `20261002_team_chat.sql` (if not already imported).
2. `20261003_reference_content.sql` (starter library; optional if already imported).
3. `20261003_expanded_library.sql` (new expansion).
4. `20261003_test_users.sql` (only if you need the four test accounts).

Repeated imports skip matching content keys and test emails. Counts are candidate counts, not guaranteed additions: existing matching commands/names/titles are not duplicated. Commands use existing categories. Articles require an active Admin/Super Admin author.

Knowledge and documentation entries are drafts, not reviewed production procedures. Review and publish appropriate entries in KB Management. Documentation is the existing solution-submission workflow, so templates live in Knowledge Base; no separate documentation table is introduced.

Equipment references, commands and tools become available after import. Exact FRU procedures still require the matching manufacturer maintenance manual. These general guides do not establish a hardware diagnosis or authorize otherwise prohibited work.

The export schema and duplicate guards were checked. A live database import and application-page verification have not been performed; do not treat the bundle as an assurance that production is fully validated. No database connection settings are changed.

Keep all ZIP/SQL/credentials files out of publicly accessible website folders. Test credentials are in `TEST-ACCOUNT-LOGINS.txt`; deactivate these test accounts after testing. Existing accounts and messages are preserved, and cross-department chat still requires approval.
