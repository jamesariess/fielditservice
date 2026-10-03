# Reference Library Import

Back up the live database first. In domain phpMyAdmin, select the existing database and import `20261003_reference_content.sql`. Do not reimport your full export into the populated database.

This additive patch adds up to 6 draft Knowledge Base entries (including 2 documentation templates), 5 command references, 5 tools and 3 explicitly labeled Lenovo equipment reference families. Existing matching titles, names and commands are skipped. An active Admin or Super Admin is selected as draft author; if none exists, no articles are inserted. Commands use existing System/Network categories.

Review drafts in KB Management before publishing. Equipment entries are reference families, not actual assets: no serial numbers, purchased specifications or completed repair records are invented. Documentation is a solution-submission page using the knowledge review workflow, not a separate documentation table.

Import `20261002_team_chat.sql` separately to enable presence and department access requests. It preserves messages and does not grant cross-department access automatically.

The user authorized four new testing accounts. Import `20261003_test_users.sql` after the chat migration. It adds four clearly labeled test technicians with the existing Field IT role in Asset & Deployment. Existing user ID 4 is not modified. Same-department chat is available; cross-department approval remains required. See `TEST-ACCOUNT-LOGINS.txt` for unique temporary passwords. Non-deliverable .invalid emails cannot receive password-reset emails. Deactivate all four accounts when testing finishes.

Keep this bundle private: never upload the ZIP, SQL files or credentials text to a public website directory. Import order: `20261002_team_chat.sql`, `20261003_reference_content.sql`, `20261003_test_users.sql`. Account insertion is skipped if the expected role/department is missing or an email already exists; the final query shows the resulting accounts.

The SQL was matched to the supplied export schema, but has not been imported into the live database. Connection configuration is unchanged.
