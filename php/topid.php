<?php
/*
 * Copyright (c) 2012 Andy 'Rimmer' Shepherd <andrew.shepherd@ecsc.co.uk> (ECSC Ltd).
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

if($glb_debug==1){
	$starttime_topidchart = microtime();
	$startarray_topidchart = explode(" ", $starttime_topidchart);
	$starttime_topidchart = $startarray_topidchart[1] + $startarray_topidchart[0];
}

# To filter on 'Category' (SSHD) extra table needs adding, but they slow down the query for other things, so lets only put them into the SQL if needed....
if(strlen($wherecategory)>5){
	$wherecategory_tables=", category";
}else{
	$wherecategory_tables="";
}

$query="SELECT count(alert.id) as res_cnt, alert.rule_id
	FROM alert ".$wherecategory_tables."
	WHERE alert.timestamp>'".(time()-($inputhours*60*60))."' 
	AND alert.level>=".$inputlevel."
	".$glb_notrepresentedwhitelist_sql." 
	".$wherecategory." 
	GROUP BY res_id
	ORDER BY count(alert.id) DESC
	LIMIT ".$glb_indexsubtablelimit; 

echo "<div class='top10header'>
	<a href='#' class='tooltip'><img src='./images/help.png' /><span>Busiest rules in given time period.</span></a>
	Top Rule_ID, <span class='tw'>".$inputhours."</span> Hrs (lvl <span class='tw'>".($inputlevel)."</span>+)</div>";

$mainstring="";

if(!$result=mysql_query($query, $db_ossec)){
	$mainstring= "SQL Error: ".$query;

}elseif($glb_debug==1){
	$mainstring="<div style='font-size:24px; color:red;font-family: Helvetica,Arial,sans-serif;'>Debug</div>";
	$mainstring.=$query;

	$endtime_topidchart = microtime();
	$endarray_topidchart = explode(" ", $endtime_topidchart);
	$endtime_topidchart = $endarray_topidchart[1] + $endarray_topidchart[0];
	$totaltime_topidchart = $endtime_topidchart - $starttime_topidchart;
	$mainstring.="<br>Took ".round($totaltime_topidchart,1)." seconds";
}else{

	# Keep this in the same format that detail.php already uses
	$from=date("Hi dmy", (time()-($inputhours*3600)));
	
	while($row = @mysql_fetch_assoc($result)){
		$mainstring.="<div class='fleft top10data' style='width:60px'>".number_format($row['res_cnt'])."</div>
				<div class='fleft top10data'><a class='top10data tooltip_small' href='./detail.php?rule_id=".$row['rule_id']."&from=".$from."&breakdown=source'>".htmlspecialchars(substr($row['rule_id'], 0, 28))."...<span>".htmlspecialchars($row['rule_id'])."</span></a></div>";
	
		$mainstring.="			<div class='clr'></div>";
	}
	
}

if($mainstring==""){
	echo $glb_nodatastring;
}else{
	echo $mainstring;
}



?>
