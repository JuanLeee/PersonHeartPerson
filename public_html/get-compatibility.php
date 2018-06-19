
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
	// handles get personality with most common interests
	$i = $_REQUEST['i'];
	if ($i != NULL){
		$sql = "
						select Personality, max(Score) AS Score
						from
							(select PM.username, PM.personality, COUNT(*) AS Score
								from (select A.username, A.personality, C.pcode
      						from checkOff C , account A, account A2
      						where A2.gender = A.genderPreference AND A2.genderPreference = A.gender AND A2.city = A.city AND A2.province = A.province
      						AND A2.username = '". $i ."' AND c.username ='". $i ."' AND C.pcode = A.personality
      						ORDER BY A.username) PM, has H, (select * from has where username ='". $i ."') MI
									where PM.username = H.username AND MI.iname = H.iname AND
									PM.username IN (select PMA.username
                from (select A.username, A.personality, C.pcode
                      from checkOff C , account A, account A2
                      where A2.gender = A.genderPreference AND A2.genderPreference = A.gender AND A2.city = A.city AND A2.province = A.province
                      AND A2.username = '". $i ."' AND c.username ='". $i ."' AND C.pcode = A.personality
                      ORDER BY A.username) PMA, checkOff C, (select username, gender, genderPreference ,personality, city, province
                                                             from account where username='". $i ."') I
                where PMA.username = C.username and I.personality = C.pcode)
								GROUP BY PM.username, PM.personality) myMatches
								GROUP BY Personality
								ORDER BY Score DESC, Personality ASC";

								$ret = executePlainSQL($sql);
								while ($row = OCI_Fetch_Array($ret, OCI_NUM)) {
									echo json_encode($row);
								}
	}

	// handles optimal match
	$o = $_REQUEST['o'];
	if ($o != NULL){
		$sql = "
		select SoM.username, SoM.aname , SoM.Photo, SoM.personality
		from(select distinct h1.username
     	from has h1
     	where username <> '". $o ."'
     	AND not exists
     	(select *
      from (select iname
      from has
      where username ='". $o ."') MI
      where not exists
      (select *
      from has h2
      where h1.username = h2.username AND MI.iname = h2.iname))) PM, (select SM.username, A.aname, P.phoID as Photo, A.personality
                                                                     from (select PM.username, COUNT(*) AS Score
                                                                     from (select A.username, A.personality, C.pcode
                                                                     from checkOff C , account A, account A2
                                                                     where A2.gender = A.genderPreference AND A2.genderPreference = A.gender AND A2.city = A.city AND A2.province = A.province
                                                                     AND A2.username = '". $o ."' AND c.username ='". $o ."' AND C.pcode = A.personality
                                                                    ORDER BY A.username) PM, has H, (select *
                                                                                                    from has
                                                                                                    where username ='". $o ."') MI
                                                                    where PM.username = H.username AND MI.iname = H.iname AND
                                                                    PM.username IN (select PMA.username
                                                                                from (select A.username, A.personality, C.pcode
                                                                    from checkOff C , account A, account A2
                                                                    where A2.gender = A.genderPreference AND A2.genderPreference = A.gender AND A2.city = A.city AND A2.
                                                                    province = A.province
                                                                    AND A2.username = '". $o ."' AND c.username ='". $o ."' AND C.pcode = A.personality
                                                                    ORDER BY A.username) PMA, checkOff C, (select username, gender, genderPreference ,personality, city, province
                                                                                                                    from account
                                                                                                                    where username='". $o ."') I
                                                                                                                    where PMA.username = C.username and I.personality = C.pcode)
                                                                    GROUP BY PM.username) SM, Account A, pictureUpload P where SM.username = A.username AND SM.username = P.username)
                                                                    SoM
        where SoM.username = PM.username
		";
		$ret = executePlainSQL($sql);
		while ($row = OCI_Fetch_Array($ret, OCI_NUM)) {
			echo json_encode($row);
		}
	}


// handles getting personality preference given username//
$p = $_REQUEST["p"];

if ($p != NULL){
	$sql = "
select SM.username, A.aname, P.phoID as Photo, A.personality, SM.Score
from (select PM.username, COUNT(*) AS Score
      from (select A.username, A.personality, C.pcode
      from checkOff C , account A, account A2
      where A2.gender = A.genderPreference AND A2.genderPreference = A.gender AND A2.city = A.city AND A2.province = A.province
      AND A2.username = '". $p ."' AND c.username ='". $p ."' AND C.pcode = A.personality) PM, has H,
                                       (select *
                                       from has
                                       where username ='". $p ."') MI
      where PM.username = H.username AND MI.iname = H.iname AND
      PM.username IN (select PMA.username
                from (select A.username, A.personality, C.pcode
      from checkOff C , account A, account A2
      where A2.gender = A.genderPreference AND A2.genderPreference = A.gender AND A2.city = A.city AND A2.
      province = A.province
      AND A2.username = '". $p ."' AND c.username ='". $p ."' AND C.pcode = A.personality) PMA, checkOff C,
                                                      (select username, gender, genderPreference ,personality, city, province
                                                       from account
                                                       where username='". $p ."') I
                                                       where PMA.username = C.username and I.personality = C.pcode)
                                                       GROUP BY PM.username) SM, Account A, pictureUpload P
where SM.username = A.username AND SM.username = P.username
ORDER BY Score DESC, SM.username ASC
	";

	$ret = executePlainSQL($sql);
	$arr = array();
	while ($row = OCI_Fetch_Array($ret, OCI_NUM)) {
		array_push($arr, $row);

	}
	echo json_encode($arr);
}


// create match
$datamatch = json_decode(stripslashes($_POST['datamatch']));
$lenmatch = sizeof($datamatch);


if($lenmatch > 0){

	$username = $datamatch[0];
	$par = $datamatch[1];

	$validUsername = executePlainSQL("select username from account where username = '$username'");


	if(OCI_Fetch_Array($validUsername	, OCI_BOTH)){
	  global $db_conn, $success;
		$statement2 = OCIParse($db_conn,"insert into match values ('$username', '$par')");
		$r2 = OCIExecute($statement2, OCI_COMMIT_ON_SUCCESS);
		 OCICommit($db_conn);


		echo json_encode("Amazing");

	}
	}

// end of handle javascript
	if ($_POST && $_GET &&  $success) {
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		header("location: get-compatibility.php");
	}
	//Commit to save changes...
	OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}
?>
