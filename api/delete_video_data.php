<?php
$servername = "iw-db-01.ce1mskef1ivg.us-east-1.rds.amazonaws.com";
$username = "iwitness";
$password = "hAsw6d";

// Create connection
$conn = mysql_connect($servername, $username, $password) or die("Connection failed");
echo "Connected successfully\n";
$selected = mysql_select_db("iwitness_api",$conn)  or die("Could not connect to Mysql"); 
$handle = fopen("asset-27-07-2016.txt", "r");
if ($handle) {
	while (($line = fgets($handle)) !== false) {
		$line= trim($line); 
        echo $line."\n"; 
        //echo "delete from subscription where UUID_TO_STR(user_id)='{$line}'\n";
        //echo "delete from contact where UUID_TO_STR(user_id)='{$line}'\n";
		//echo "delete from user where UUID_TO_STR(id)='{$line}'\n";
        //mysql_query("delete from subscription where UUID_TO_STR(user_id)='{$line}'");
        //mysql_query("delete from contact where UUID_TO_STR(user_id)='{$line}'");
        //mysql_query("delete from user_device where UUID_TO_STR(user_id)='{$line}'");
        //mysql_query("delete from user_message where UUID_TO_STR(user_id)='{$line}'");
        //mysql_query("delete from oauth_access_tokens where UUID_TO_STR(user_id)='{$line}'");
        //mysql_query("delete from oauth_authorization_codes where UUID_TO_STR(user_id)='{$line}'");
       //mysql_query("delete from oauth_clients where UUID_TO_STR(user_id)='{$line}'");
        //#mysql_query("delete from oauth_refresh_tokens where UUID_TO_STR(user_id)='{$line}'");
        mysql_query("delete from asset where UUID_TO_STR(id)='{$line}'");
        //mysql_query("delete from user where UUID_TO_STR(id)='{$line}'");
        //mysql_query("delete from event where UUID_TO_STR(id)='{$line}'");
			}}
fclose($handle);
mysql_close($conn);

?> 
