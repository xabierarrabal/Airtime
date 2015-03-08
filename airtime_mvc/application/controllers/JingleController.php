<?php
define ('JINGLE_TAG', '%kuinak%');
define ('JINGLE_DISABLED_TAG', 'ezgaituta');
define('LOG_LEVEL', 3);

# 1- Connect to PostgreSQL and get the available Jingles.
$dbconn = pg_connect("host=localhost dbname=airtime user=airtime password=airtime")
	or error_log(date('d.m.Y h:i:s')." | ". basename(__FILE__) . " | Could not connect :'".pg_last_error()."'\n",        3, "/var/log/syslog"); 
# 2- Compare the field ISRC Number against today's date:
$query = "SELECT id, isrc_number FROM cc_files WHERE (genre LIKE '".JINGLE_TAG."') AND (mood!='".JINGLE_DISABLED_TAG."')";
$result = pg_query($query) 
	or error_log(date('d.m.Y h:i:s')." | ". basename(__FILE__) . " | Query failed :'".pg_last_error()."'\n",        3, "/var/log/syslog");
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
	$today = date("Ymd");
	// Script runs in a CRON every day at 00:00, disable every jingle in the past
	if (!empty($line['isrc_number']) && $line['isrc_number']<$today) {
		if (LOG_LEVEL>=3) error_log(date('d.m.Y h:i:s')." | " . basename(__FILE__) . " | Id ".$line['id']." needs to be disabled\n");
		$query = "UPDATE cc_files SET mood = '".JINGLE_DISABLED_TAG."' WHERE id=".$line['id'];
		#echo $query."\n";
		$result = pg_query($query) 
			or error_log(date('d.m.Y h:i:s')." | ". basename(__FILE__) . " | Query failed :'".pg_last_error()."'\n",        3, "/var/log/syslog");
	}
}
# Close database
pg_close($dbconn);

?>
