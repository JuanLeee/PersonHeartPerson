<!DOCTYPE html>
<html>
<body>

	<!-- THIS SCRIPT IS FOR HANDLING THE PHP INPUT FROM THE SIGNUP FORM -->
	<?php

	$success = True; //keep track of errors so it redirects the page only if there are no errors
	$db_conn = OCILogon("ora_g5x0b", "a26762161", "dbhost.ugrad.cs.ubc.ca:1522/ug");


// takes a plain (no bound variables) SQL command and executes it
	function executePlainSQL($cmdstr) {
	//echo "<br>running ".$cmdstr."<br>";
		global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr);

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

// define variables and set to empty values
// only works if POST is working
$email = $uname = $psw1 = $psw2 = $finalpassword = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if (!empty($_POST["email"])) {
		$email = test_input($_POST["email"]);
	} else {
		echo "ERROR email<br>";
		$success = false;
	}

	if (!empty($_POST["uname"])) {
		$uname = test_input($_POST["uname"]);
	} else {
		echo "ERROR uname<br>";
		$success = false;
	}

	if (!empty($_POST["psw1"])) {
		$psw1 = test_input($_POST["psw1"]);
	} else {
		echo "ERROR psw1<br>";
		$success = false;
	}

	if (!empty($_POST["psw2"])) {
		$psw2 = test_input($_POST["psw2"]);
	} else {
		echo "ERROR psw2<br>";
		$success = false;
	}


		// TODO
		// IF EMAIL AND UNAME ARE AVAILABLE, AND CONFIRMED PASSWORDS MATCH
	if ($psw1 == $psw2) {
		$finalpassword = $psw1;
	} else {
		$success = false;
	}




}

function test_input($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

// ---- THE SQL LOGIC ----
if ($db_conn && $success) {

	// ????????? TODO
	// if the things are defined
	global $email, $uname, $finalpassword;

	// given username, lookup the password in database
	// select password from account where uname = '$uname'
	$sql = "insert into account values ('" .  $uname. "', '"
	 					. $finalpassword . "' , 'sample' , 'sample' , 'sample', '"
						. $email . "' , 'sample' , 'sample', 000, 'aaaa', 'sample' "
						" )";
	executePlainSQL($sql);



	if ($success) {
	// if everything successful, log onto user page
	// set cookie to have them auto logged in
	// I suppose cookie is their global representation?????
	// TODO
		header("location: homepage.html");
		setcookie("username", $uname, time() + (86400 * 30), "/");
		$email = $uname = $psw1 = $psw2 = $finalpassword = "";

/*
		if(isset($_COOKIE["username"])) {
			// then I guess that means they are logged in, have them automatically logged in, and represented as their account
		} else {
			// then to the index.html page they go!
		}
*/
	}
}


	// TODO
	// the failing redirect
	if (!$success) {
		header("location: index.html");
		$email = $uname = $psw1 = $psw2 = $finalpassword = "";
	}

?>


<!-- FOR TESTING -->
Welcome email: <?php echo $email; ?><br>
Welcome username: <?php echo $uname; ?><br>
Your password is: <?php echo $psw1; ?><br>
Your passwordagain is: <?php echo $psw2; ?> <br>
Your finalpassword is: <?php echo $finalpassword; ?>

</body>
</html>
