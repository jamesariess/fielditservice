-- TEST ACCOUNTS ONLY. Import after 20261002_team_chat.sql.
-- Existing users and their passwords are never modified.
-- Non-deliverable .invalid emails intentionally prevent impersonating real people.
START TRANSACTION;
SET @test_role = (SELECT id FROM roles WHERE name='Field IT' LIMIT 1);
SET @test_department = (SELECT d.id FROM departments d JOIN organizations o ON o.id=d.organization_id WHERE d.name='Asset & Deployment' ORDER BY d.id LIMIT 1);
INSERT INTO users (email,password_hash,full_name,role_id,department_id,status,invitation_token)
SELECT 'fieldit.test01@example.invalid','$2y$10$RcB5Ce./9tPF0njEH/bF6ODlu8blNDPJOBosLeLDqMgxzgnXH6QOm','TEST Technician 01',@test_role,@test_department,'active',''
WHERE @test_role IS NOT NULL AND @test_department IS NOT NULL AND NOT EXISTS (SELECT 1 FROM users WHERE email='fieldit.test01@example.invalid');
INSERT INTO users (email,password_hash,full_name,role_id,department_id,status,invitation_token)
SELECT 'fieldit.test02@example.invalid','$2y$10$RIArFx0H..jlnAFxrjocx.YJVyEIoiiQmLax0zor0uTiCvo4rq3Zi','TEST Technician 02',@test_role,@test_department,'active',''
WHERE @test_role IS NOT NULL AND @test_department IS NOT NULL AND NOT EXISTS (SELECT 1 FROM users WHERE email='fieldit.test02@example.invalid');
INSERT INTO users (email,password_hash,full_name,role_id,department_id,status,invitation_token)
SELECT 'fieldit.test03@example.invalid','$2y$10$/.vzjl56B7IznSmjZuuN9uTL.sXlI9VfAQKGTGq5ZFRlWg0f4k1Na','TEST Technician 03',@test_role,@test_department,'active',''
WHERE @test_role IS NOT NULL AND @test_department IS NOT NULL AND NOT EXISTS (SELECT 1 FROM users WHERE email='fieldit.test03@example.invalid');
INSERT INTO users (email,password_hash,full_name,role_id,department_id,status,invitation_token)
SELECT 'fieldit.test04@example.invalid','$2y$10$qxvlNmtD3lGpaKlay08IVeQ9dC2uzPs.ihjAvFAlxTbS2jkI9vW1.','TEST Technician 04',@test_role,@test_department,'active',''
WHERE @test_role IS NOT NULL AND @test_department IS NOT NULL AND NOT EXISTS (SELECT 1 FROM users WHERE email='fieldit.test04@example.invalid');
COMMIT;
-- Expect four active accounts. If fewer, inspect missing role/department or existing emails.
SELECT u.id,u.full_name,u.email,u.status,d.name AS department,r.name AS role
FROM users u JOIN departments d ON d.id=u.department_id JOIN roles r ON r.id=u.role_id
WHERE u.email IN ('fieldit.test01@example.invalid','fieldit.test02@example.invalid','fieldit.test03@example.invalid','fieldit.test04@example.invalid');
