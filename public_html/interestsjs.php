
<?php
//this tells the system that it's no longer just parsing
//html; it's now parsing PHP

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_g5x0b", "a26762161", "dbhost.ugrad.cs.ubc.ca:1522/ug");

function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
	//echo "<br>running ".$cmdstr."<br>";
	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn); // For OCIParse errors pass the
		// connection handle
		echo htmlentities($e['message']);
		$success = False;
	}

	$r = OCIExecute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
		echo htmlentities($e['message']);
		$success = False;
	} else {

	}
	return $statement;

}


function executeBoundSQL($cmdstr, $list) {
	/* Sometimes a same statement will be excuted for severl times, only
	 the value of variables need to be changed.
	 In this case you don't need to create the statement several times;
	 using bind variables can make the statement be shared and just
	 parsed once. This is also very useful in protecting against SQL injection. See example code below for       how this functions is used */

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
			//echo $val;
			//echo "<br>".$bind."<br>";
			OCIBindByName($statement, $bind, $val);
			unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

		}
		$r = OCIExecute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($statement); // For OCIExecute errors pass the statementhandle
			echo htmlentities($e['message']);
			echo "<br>";
			$success = False;
		}
	}

}

/*
function printResult($result) { //prints results from a select statement
	echo "<br>Got data from table tab1:<br>";
	echo "<table>";
	echo "<tr><th>ID</th><th>Name</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row["NID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]"
	}
	echo "</table>";

}
*/

// Connect Oracle...
if ($db_conn) {

$a = executePlainSQL("SELECT iname FROM interests");
  // get the q parameter from URL
$q = $_REQUEST["interest"];
$hint = "";

// lookup all hints from array if $q is different from ""
if ($q !== NULL) {

	if($q !== ""){
    $q = strtolower($q);
    $len=strlen($q);
    while($row = OCI_Fetch_Array($a, OCI_BOTH)) {
			$interest = (string)$row[0];
      if (stristr($q, substr($interest, 0, $len))) {
            if ($hint === "") {
                $hint = $interest;
            } else {
                $hint .= ", $interest";
            }
       }
    }
	}

		// Output "no suggestion" if no hint was found or output correct values
		echo $hint === "" ? "no suggestion" : $hint;
}


$data = json_decode(stripslashes($_POST['data']));
$len = sizeof($data);


if($len > 0){

	$username = $data[0];
	$interest = $data[1];

	$validUsername = executePlainSQL("select username from account where username = '$username'");
	/*
	 $row = OCI_Fetch_Array($validUsername, OCI_BOTH);
	 echo json_encode($row);
	 */


	if(OCI_Fetch_Array($validUsername	, OCI_BOTH)){



		$tuple = array (
			":bind1" => $username,
			":bind2" => $interest
		);

		$alltuples = array (
				$tuple
		);
/*
		$row = OCI_Fetch_Array($searchInterest, OCI_BOTH);
 	 	echo json_encode($row);
*/

	  global $db_conn, $success;
		$statement = OCIParse($db_conn, "insert into interests values ('$interest')");
		$r = OCIExecute($statement, OCI_COMMIT_ON_SUCCESS);
		OCICommit($db_conn);

		$statement2 = OCIParse($db_conn,"insert into has values ('$username', '$interest')");
		$r2 = OCIExecute($statement2, OCI_COMMIT_ON_SUCCESS);
		 OCICommit($db_conn);
		/*
			executePlainSQL("insert into interests values ('$interest')");
			executePlainSQL("insert into has values ('$username', '$interest')");
*/




		$interests_user = executePlainSQL("select iname from has where username = '$username'");

		$int = array();


		while($row = OCI_Fetch_Array($interests_user, OCI_BOTH)){
			array_push($int,$row);
		}

		echo json_encode($int);


		//array_push($int,OCI_Fetch_Array($interests_user, OCI_BOTH));
	//	$intJSON = json_encode($int);

		//echo json_encode(OCI_Fetch_Array($interests_user, OCI_BOTH));
	}



	}

	$data2 = json_decode(stripslashes($_POST['datadelete']));
	$len2 = sizeof($data2);


	if($len2 > 0){

		$username2 = $data2[0];
		$interest2 = $data2[1];

		$validUsername2 = executePlainSQL("select username from account where username = '$username2'");
		/*
		 $row = OCI_Fetch_Array($validUsername, OCI_BOTH);
		 echo json_encode($row);
		 */


		if(OCI_Fetch_Array($validUsername2	, OCI_BOTH)){



			$tuple2 = array (
				":bind1" => $username2,
				":bind2" => $interest2
			);

			$alltuples2 = array (
					$tuple2
			);
	/*
			$row = OCI_Fetch_Array($searchInterest, OCI_BOTH);
	 	 	echo json_encode($row);
	*/

		  global $db_conn, $success;
			$statement21 = OCIParse($db_conn,"delete from has where username = '$username2' and iname = '$interest2'");
			$r21 = OCIExecute($statement21, OCI_COMMIT_ON_SUCCESS);
			 OCICommit($db_conn);
			$interests_user2 = executePlainSQL("select iname from has where username = '$username2'");
			$int2 = array();
			while($row2 = OCI_Fetch_Array($interests_user2, OCI_BOTH)){
				array_push($int2,$row2);
				}
			echo json_encode($int2);
			}
		}









  //check if connection is successful
  if ($_POST && $_GET && $success) {
    //POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
    header("location: interestsjs.php");
  }

  //Commit to save changes...
  OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}

 ?>
