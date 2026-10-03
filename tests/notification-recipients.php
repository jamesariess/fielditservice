<?php
class Auth {public static function userId(){return 1;}}
class Database {
 public static array $saved=[];
 public static function fetch($sql,$params){
  if(str_contains($sql,'SELECT department_id')) return ['department_id'=>7];
  return $params[0]===99 ? null : ['id'=>$params[0]];
 }
 public static function fetchAll($sql,$params){
  if(!str_contains($sql,'u.department_id=?') || $params!==[7]) throw new RuntimeException('Unscoped manager lookup');
  return [['id'=>2],['id'=>3]];
 }
 public static function insert($table,$data){self::$saved[]=$data;}
}
require dirname(__DIR__).'/includes/Activity.php';
if(Activity::managers()!==[2,3]) throw new RuntimeException('Wrong recipients');
Activity::notifyUsers([1,2,2,99],'test','Test','Test','/tickets');
if(count(Database::$saved)!==1 || Database::$saved[0]['user_id']!==2) throw new RuntimeException('Duplicate, self, or inactive recipient notified');
echo "PASS: department-scoped managers; no self, duplicate, or unavailable recipients\n";
