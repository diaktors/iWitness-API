<?php
$servername = "iw-db-01.ce1mskef1ivg.us-east-1.rds.amazonaws.com";
$username = "iwitness";
$password = "hAsw6d";

// Create connection
$conn = mysql_connect($servername, $username, $password) or die("Connection failed");
echo "Connected successfully\n";
$selected = mysql_select_db("iwitness_api",$conn)  or die("Could not connect to Mysql"); 
$handle = fopen("users.txt", "r");
if ($handle) {
	while (($line = fgets($handle)) !== false) {
		   $line= trim($line); 
            $event_sql = mysql_query("select UUID_TO_STR(id) as id from event where UUID_TO_STR(user_id)='{$line}'");
			while($event_row = mysql_fetch_array($event_sql)){
	        $evn_id = $event_row{'id'};
	        $my_file = 'event.txt';
			$handle1 = fopen($my_file, 'a') or die('Cannot open file:  '.$my_file);
			echo "Event Id: \n";
			echo $evn_id;
			fwrite($handle1, $evn_id."\n");
            $asset_sql = mysql_query("select UUID_TO_STR(id) as id from asset where UUID_TO_STR(event_id)='{$evn_id}'");
			while($asset_row = mysql_fetch_array($asset_sql)){
				$asst_id = $asset_row{'id'};
	            $my_file = 'asset.txt';
			    $handle2 = fopen($my_file, 'a') or die('Cannot open file:  '.$my_file);
        		fwrite($handle2, $asst_id."\n");
                echo "Asset Id's:\n";
         	   echo $asset_row{'id'} ."\n";
	}
			}		
			}}
fclose($handle);
fclose($handle1);
fclose($handle2);
mysql_close($conn);

?> 
