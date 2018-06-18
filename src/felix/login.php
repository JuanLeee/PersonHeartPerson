<!DOCTYPE html>
<html>
<body>


	<!-- THIS SCRIPT IS FOR HANDLING THE PHP INPUT FROM THE LOGIN FORM -->
	<?php 

// define variables and set to empty values
// only works if POST is working
	$username = $pw = $remember = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (!empty($_POST["username"])) {
			$username = test_input($_POST["username"]);
		} else {
			echo "ERROR username<br>";
		}

		if (!empty($_POST["pw"])) {
			$pw = password_hash(test_input($_POST["pw"]),PASSWORD_DEFAULT);
		} else {
			echo "ERROR pw<br>";
		}

		$remember = test_input($_POST["remember"]);
		
	} else {
	// ERROR POST isn't working
		echo "PHP IS NOT USING POST<br>";
	}

	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_l7f1b", "a34607151", "dbhost.ugrad.cs.ubc.ca:1522/ug");

// takes a plain (no bound variables) SQL command and executes it
function executePlainSQL($cmdstr) { 
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
	} 

	return $statement;
}

// when same statement will be several times, but only the value of variables need to be changed
function executeBoundSQL($cmdstr, $list) {
	/* In this case you don't need to create the statement several times; 
	 using bind variables can make the statement be shared and just 
	 parsed once. This is also very useful in protecting against SQL injection. */

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
			unset ($val); //do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

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

// ---- THE SQL LOGIC ----
if ($db_conn) {

	// ?????????
	global $username, $pw, $remember;

	//executePlainSQL("select password from user where username='".$username."'");
	// if something is returned, check if their inputted password matches this
	// if not, refresh page and alert that they can't be logged in
	// check their password using password_verify($userPassword, $userHash)
	// ALERT IF USER NOT FOUND
	// ALERT IF WRONG PASSWORD





}

?>


	<!-- --------------------FOR TESTING--------------------- -->
	Welcome: <?php echo $username; ?><br>
	Your password is: <?php print $pw; ?><br>
	You remember: <?php echo $remember; ?>

</body>
</html>