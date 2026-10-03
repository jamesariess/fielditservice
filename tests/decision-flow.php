<?php
require dirname(__DIR__).'/includes/DecisionFlow.php';
function check($condition,$label){if(!$condition)throw new RuntimeException($label);echo 'PASS '.$label."\n";}
$nodes=[
 ['id'=>1,'node_type'=>'step','visibility_mode'=>'no_only','visible_for_question_id'=>10,'no_next'=>null],
 ['id'=>2,'node_type'=>'step','visibility_mode'=>'yes_only','visible_for_question_id'=>10,'no_next'=>3],
 ['id'=>3,'node_type'=>'step','visibility_mode'=>'always','no_next'=>1],
 ['id'=>4,'node_type'=>'step','visibility_mode'=>'always','is_terminal'=>1],
];
$no=DecisionFlow::steps($nodes,[10=>'no']);$yes=DecisionFlow::steps($nodes,[10=>'yes']);
check(array_column($no,'id')===[1,3],'No selects power checks and shared alternatives');
check(array_column($yes,'id')===[2,3],'Yes skips no-power branch');
check(DecisionFlow::next($no,$nodes[0],[])['id']===3,'Missing failure link continues instead of escalating');
check(DecisionFlow::next($yes,$nodes[1],[])['id']===3,'Valid failure branch followed');
check(DecisionFlow::next($no,$nodes[2],[1])===null,'Cycles never repeat failed steps; exhaustion ends path');
check(array_column(DecisionFlow::steps($nodes,[]),'id')===[3],'Unanswered questions do not imply No');
$nodes[0]['visibility_mode']='no';$nodes[1]['visibility_mode']='yes';
check(array_column(DecisionFlow::steps($nodes,[10=>'no']),'id')===[1,3],'Legacy No branch supported');
check(array_column(DecisionFlow::steps($nodes,[10=>'yes']),'id')===[2,3],'Legacy Yes branch supported');
