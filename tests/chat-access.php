<?php
require dirname(__DIR__).'/config/app.php';
require APP_ROOT.'/includes/Database.php';
require APP_ROOT.'/includes/Auth.php';
require APP_ROOT.'/includes/ChatAccess.php';
$pdo=Database::getInstance();
$pdo->exec(file_get_contents(APP_ROOT.'/database/migrations/20261002_team_chat.sql'));
$pdo->beginTransaction();
try {
 $users=Database::fetchAll("SELECT id,department_id FROM users WHERE status='active' AND deleted_at IS NULL AND department_id IS NOT NULL ORDER BY id LIMIT 2");
 if(count($users)<2) throw new RuntimeException('Need two users');
 $a=(int)$users[0]['id']; $b=(int)$users[1]['id']; $dept=(int)$users[0]['department_id'];
 Database::query('UPDATE users SET department_id=? WHERE id=?',[$dept,$b]);
 if(!ChatAccess::allowed($a,$b)) throw new RuntimeException('Same department rejected');
 $other=Database::fetch('SELECT id FROM departments WHERE id<>? LIMIT 1',[$dept]);
 Database::query('UPDATE users SET department_id=? WHERE id=?',[$other['id'],$b]);
 Database::query('DELETE FROM chat_department_requests WHERE requester_id IN (?,?)',[$a,$b]);
 if(ChatAccess::allowed($a,$b)) throw new RuntimeException('Unapproved access allowed');
 Database::insert('chat_department_requests',['requester_id'=>$a,'target_user_id'=>$b,'reason'=>'QA']);
 if(ChatAccess::allowed($a,$b)) throw new RuntimeException('Pending access allowed');
 Database::query("UPDATE chat_department_requests SET status='approved' WHERE requester_id=?",[$a]);
 if(!ChatAccess::allowed($a,$b)||!ChatAccess::allowed($b,$a)) throw new RuntimeException('Approved access rejected');
 Database::query("UPDATE chat_department_requests SET status='rejected' WHERE requester_id=?",[$a]);
 if(ChatAccess::allowed($a,$b)) throw new RuntimeException('Rejected access allowed');
 echo "PASS: department restriction, pending, approval, reply, rejection\n";
} finally {$pdo->rollBack();}
