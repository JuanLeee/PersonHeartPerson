<?php

$success = True;
$db_conn = OCILogon("ora_q6r0b", "a24632151", "dbhost.ugrad.cs.ubc.ca:1522/ug");


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
// handles update user NOT TESTED
executePlainSQL($sqlcmd);
$data = json_decode($_POST["data"], true);
if ($data != NULL){
		$username = $data['username'];

		$sqlcmd = "update account set gender = '". $data['gender']. "', "
								. "genderPreference = '" . $data['genderPreference'] . "', "
								. "aname = '" . $data['name'] . "', "
								. "email = '" . $data['email'] . "', "
								. "phoneno = '" . $data['phoneNumber'] . "', "
								. "personality = '" . $data['personality'] . "' "
								. " where username = '". $username ."'";
		executePlainSQL($sqlcmd);
  	OCICommit($db_conn);

}



// handles getting profile information given personality NOT TESTED
$q = $_REQUEST["q"];
if ($q != NULL){
  $ret = executePlainSQL("select * from account where username = '" . $q . "'");
  $ppArray = array();
  while ($row = OCI_Fetch_Array($ret, OCI_NUM)) {
		echo json_encode($row); // not sure this works
	}
	//echo json_encode($q);
}

$datadelete = json_decode($_POST["datadelete"], true);



$lendatadelete = sizeof($datadelete);
if ($lendatadelete > 0){
	$username2 = $datadelete[0];
	$password2 = $datadelete[1];
	global $db_conn, $success;
	$statement21 = OCIParse($db_conn,"delete from account where username = '$username2' and upassword = '$password2'");
	$r21 = OCIExecute($statement21, OCI_COMMIT_ON_SUCCESS);
	OCICommit($db_conn);
	 echo json_encode("George Lucis is the best");
}



// end of handle javascript
	if ($_POST && $_GET && $success) {
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		header("location: get-profile.php");
	}

	//Commit to save changes...
	OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}
?>
