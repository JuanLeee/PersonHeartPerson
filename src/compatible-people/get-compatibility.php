
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

// handles getting personality preference given username//
$p = $_REQUEST["p"];

if ($p != NULL){
	executePlainSQL("drop table info cascade constraints");
	executePlainSQL("drop table myInterest cascade constraints");
	executePlainSQL("drop table possibleMatches cascade constraints");
  $sql1 ="
					create table info
					AS
					select username, gender, genderPreference ,personality, city, province
					from account
					where username='" . $p . "'
					";
  executePlainSQL($sql1);

	$sql2 ="
					create table myInterest
					AS
					select *
					from has
					where username ='" . $p . "'
					";
	executePlainSQL($sql2);

	$sql3 ="
					create table possibleMatches
					AS
					select A.username, C.pcode
					from checkOff C , account A, info I
					where I.gender = A.genderPreference AND I.genderPreference = A.gender AND I.city = A.city AND I.province = A.province
					AND c.username ='" . $p . "' AND C.pcode = A.personality
					";
	executePlainSQL($sql3);
}
//
$g = $_REQUEST["g"];
if ($g != NULL){
	//$sql2 = 'select * from possibleMatches';

  $sql2 ="select PM.username, COUNT(*) AS Score
          from possibleMatches PM, has H, myInterest MI
          where PM.username = H.username AND MI.iname = H.iname AND
          PM.username IN (select PMA.username
                from possibleMatches PMA, checkOff C, info I
                where PMA.username = C.username and I.personality = C.pcode)
          GROUP BY PM.username";


  $ret = executePlainSQL($sql2);
	while ($row = OCI_Fetch_Array($ret, OCI_NUM)) {
		echo json_encode($row); // not sure this works
	}
}



// end of handle javascript
	if ($_POST && $success) {
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		header("location: get-compatibility.php");
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
