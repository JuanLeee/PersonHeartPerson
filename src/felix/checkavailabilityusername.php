<?php

$success = True;
$db_conn = OCILogon("ora_g5x0b", "a26762161", "dbhost.ugrad.cs.ubc.ca:1522/ug");
function executePlainSQL($cmdstr) {
	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr);
	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);
		echo htmlentities($e['message']);
		$success = False;
	}
	$r = OCIExecute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement);
		echo htmlentities($e['message']);
		$success = False;
	}

	return $statement;
}

if ($db_conn) {
// handles choosing personality preference
	$data = json_decode(stripslashes($_POST['data']));
	$len = sizeof($data);
	$username = $data[$len -1];
	if ($username != NULL){
  //executePlainSQL("insert into tab1 values ( '" . $username .  "', '" . $data[$try] . "')");
		global $db_conn, $success;
		for($i=0 ; $i< ($len-1) ; $i++ ){
			$sqlcmd = "insert into tab1 values ( '" . $username .  "', '" . $data[$i] . "')";
			$statement = OCIParse($db_conn, $sqlcmd);
			$r = OCIExecute($statement, OCI_COMMIT_ON_SUCCESS);
		}
		OCICommit($db_conn);
	}
// handles getting personality preference given username
	$q = $_REQUEST["q"];
	if ($q != NULL){
		$ret = executePlainSQL("select * from tab1 where username = '" . $q . "'");
		$ppArray = array();
		while ($row = OCI_Fetch_Array($ret, OCI_BOTH)) {
		array_push($ppArray, $row["PERSONALITYPREF"]); //or just use "echo $row[0]"
	}
	echo json_encode($ppArray);
}
// end of handle javascript
if ($_POST && $success) {
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
	header("location: get-personality-preference.php");
} else {
		// Select data...
	$result = executePlainSQL("select * from tab1");
		//printResult($result);
}
	//Commit to save changes...
OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}

?>