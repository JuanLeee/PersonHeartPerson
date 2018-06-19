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
	} else {
	}
	return $statement;
}

function executeBoundSQL($cmdstr, $list) {
	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr);
	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);
		echo htmlentities($e['message']);
		$success = False;
	}
	foreach ($list as $tuple) {
		foreach ($tuple as $bind => $val) {
			OCIBindByName($statement, $bind, $val);
			unset ($val);
    }
		$r = OCIExecute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($statement);
			echo htmlentities($e['message']);
			echo "<br>";
			$success = False;
		}
	}
}

if ($db_conn) {
// handles signup
$data = json_decode(stripslashes($_POST['data']));
$len = sizeof($data);
$username = $data[0];
if ($username != NULL){

	$sql = "insert into account values ('" .  $data[0]. "', '"
						. $data[1] . "' , 'F' , 'F' , 'sample', '"
						. $data[2] . "' , 'sample' , 'NA', 00000, 'ENFP', 002 "
						. " )";

	//$sql = "insert into account values ('loraine123', 'lorain123' , 'N' , 'N' , 'sample', 'loraine123@hotmail.com' , 'sample' , 'NA', 000, 'aaaa', 001 )";
	executePlainSQL($sql);
  OCICommit($db_conn);
}


$user = json_decode($_POST['user']);
echo $user;
if ($user != NULL){
	echo 'hello in';
	$sql = "delete from account where username ='" . $user . "'";
	executePlainSQL($sql);
}



// end of handle javascript
	if ($_POST && $success || $_GET && $success) {
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		header("location: signup.php");
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
