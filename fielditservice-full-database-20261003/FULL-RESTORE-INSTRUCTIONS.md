# Complete Database Restore

This is a full restore, not an incremental patch. It contains the original supplied database export followed by Team Chat setup, both reference libraries and four test accounts.

1. In your domain phpMyAdmin, select `if0_42963990_fieldit_hub`.
2. Confirm it is empty: there should be no tables. If tables remain, back them up and resolve that before importing; do not import this full restore over a populated database.
3. Import `fielditservice-full-database-20261003.sql` once. Do not import the older patches separately afterward.
4. Confirm the import completed without errors, then sign in with an original exported account. Existing account password hashes are preserved.
5. Review draft articles in KB Management and publish approved entries. Test users are Field IT users in Asset & Deployment; credentials are in the private test-login text. Deactivate test accounts after testing.

The original export contains 57 tables, including users, roles, departments, permissions, tickets, knowledge, equipment, commands, tools and conversation records. The appended chat migration adds its request table and online-presence column when missing. Both content libraries contribute up to 105 candidate entries; matching existing entries are skipped. Documentation templates use the existing Knowledge Base workflow.

Only records present in your supplied export can be restored. Changes after that export are not recoverable from this file. Technical content is drafted reference material, not reviewed production instructions. Equipment reference entries are not actual inventory or verified model specifications.

No DB_HOST, DB_PORT, database password or production configuration is changed. The combined file was checked for completeness against the original ZIP but has not been imported into the live database.

PRIVATE: this bundle contains original personal records, conversations and password hashes, plus temporary test credentials. Never upload the SQL, ZIP or login text into a publicly accessible website folder. Import through phpMyAdmin only.
