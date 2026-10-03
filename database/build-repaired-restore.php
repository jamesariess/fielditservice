<?php
// Recover omitted schema columns using the project's complete schema snapshot.
$root=dirname(__DIR__);
$source=file_get_contents(__DIR__.'/backups/fielditservice-full-database-20261003.sql');
$reference=file_get_contents(__DIR__.'/backups/sync-live-database.sql');
$repairs=[];
$patch="-- Back up a partially restored database before importing this additive schema patch.\n";
$fixed=preg_replace_callback('/CREATE TABLE `(\w+)`\s*\((.*?)\)\s*(?:ENGINE[^;]*|)\s*;/s',function($m) use($reference,&$repairs,&$patch){
    $table=$m[1];
    if(!preg_match('/CREATE TABLE IF NOT EXISTS `'.preg_quote($table,'/').'`\s*\((.*?)\) ENGINE/s',$reference,$ref))return $m[0];
    preg_match_all('/^\s*`(\w+)`\s+(.+?)(?:,)?\s*$/m',$ref[1],$columns,PREG_SET_ORDER);
    $missing=[];
    foreach($columns as $column){
        if(preg_match('/^\s*`'.preg_quote($column[1],'/').'`\s+/m',$m[2]))continue;
        $definition=rtrim(trim($column[2]),',');
        // JSON checks are omitted here for compatibility with the supplied export.
        $definition=preg_replace('/\s+CHECK\s*\(json_valid\(`[^`]+`\)\)/i','',$definition);
        $missing[]='  `'.$column[1].'` '.$definition;
        $repairs[]=$table.'.'.$column[1];
        $patch.="ALTER TABLE `$table` ADD COLUMN IF NOT EXISTS `{$column[1]}` $definition;\n";
    }
    if(!$missing)return $m[0];
    return 'CREATE TABLE `'.$table.'` ('.rtrim($m[2]).",\n".implode(",\n",$missing)."\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
},$source);
if($fixed===null)throw new RuntimeException('Schema repair failed');
$fixed=preg_replace_callback('/ALTER TABLE `(\w+)`\s+MODIFY `id` ([^;]*AUTO_INCREMENT[^;]*);/s',function($m) use(&$fixed,&$repairs){
    $table=$m[1];
    if(preg_match('/ALTER TABLE `'.preg_quote($table,'/').'`\s+ADD PRIMARY KEY\s*\(`id`\)/s',$fixed))return $m[0];
    if(preg_match('/CREATE TABLE `'.preg_quote($table,'/').'`[^;]*PRIMARY KEY\s*\(`id`\)/s',$fixed))return $m[0];
    $repairs[]=$table.'.PRIMARY KEY(id)';
    return "ALTER TABLE `$table` ADD PRIMARY KEY (`id`);\n".$m[0];
},$fixed);
// Check every explicit INSERT column, including the appended libraries.
preg_match_all('/INSERT INTO `(\w+)` \(([^)]+)\)/',$fixed,$inserts,PREG_SET_ORDER);
foreach($inserts as $insert){
    if(!preg_match('/CREATE TABLE `'.preg_quote($insert[1],'/').'`.*?;/s',$fixed,$definition))throw new RuntimeException('Missing table '.$insert[1]);
    preg_match_all('/`([^`]+)`/',$insert[2],$fields);
    foreach($fields[1] as $field)if(!preg_match('/^\s*`'.preg_quote($field,'/').'`\s+/m',$definition[0]))throw new RuntimeException('Missing column '.$insert[1].'.'.$field);
}
file_put_contents(__DIR__.'/backups/fielditservice-full-database-repaired-20261003.sql',$fixed);
file_put_contents(__DIR__.'/migrations/20261003_restore_missing_columns.sql',$patch);
echo json_encode(['repaired_columns'=>$repairs,'count'=>count($repairs),'checked_inserts'=>count($inserts)],JSON_PRETTY_PRINT)."\n";
