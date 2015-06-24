<!DOCTYPE HTML> 
<html>
	<title>DreamFactory on Verizon Cloud</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<style type="text/css">
	.stylebut {
		color: #333;
		background-color: #FA2;
		border-radius: 5px;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
		border: none;
		font-size: 16px;
		font-weight: 700;
		height: 32px;
		padding: 4px 16px;
		width: 200px;
	}
	label {
		display: inline-block;
		width: 150px;
	}
	ul {
		font-size: 13px;
	}
	input {
		width: 350px;
	}
	p {
		font-size: 16px;
	}
	</style>
<head>
</head>
<body style="margin: 0;"> 

<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

$firstNameErr = $emailErr = $lastNameErr = $passwordErr = $phoneErr = $companyErr = $confirmErr = "";
$firstname = $email = $lastname = $password = $confirm = $phone = $company = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if (empty($_POST["firstname"])) {
		$firstNameErr = "First name is required";
	} else {
		$firstname = test_input($_POST["firstname"]);
		if (!preg_match("/^[a-zA-Z ]*$/",$firstname)) {
			$firstNameErr = "Only letters and white space allowed"; 
		}
	}

	if (empty($_POST["lastname"])) {
		$lastNameErr = "Last name is required";
	} else {
		$lastname = test_input($_POST["lastname"]);
		if (!preg_match("/^[a-zA-Z ]*$/",$lastname)) {
			$lastNameErr = "Only letters and white space allowed"; 
		}
	}

	if (empty($_POST["company"])) {
		$companyErr = "Company name is required";
	} else {
		$company = test_input($_POST["company"]);
		if (!preg_match("/^[a-zA-Z ]*$/",$company)) {
			$companyErr = "Only letters and white space allowed"; 
		}
	}

	if (empty($_POST["phone"])) {
		$phoneErr = "Phone number is required";
	} else {
		$phone = test_input($_POST["phone"]);
		if (!preg_match("/^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/",$phone)) {
			$phoneErr = "Only numbers and dashes allowed"; 
		}
	}

	if (empty($_POST["email"])) {
		$emailErr = "Email is required";
	} else {
		$email = test_input($_POST["email"]);
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$emailErr = "Invalid email format"; 
		}
	}

	if (empty($_POST["password"])) {
		$passwordErr = "Password is required";
	} else {
		$password = test_input($_POST["password"]);
		if (strlen($password) < 3) {
			$passwordErr = "Password must have at least 3 characters";
		}
	}

	if ($_POST["password"] != $_POST["confirm"]) {
		$confirmErr = "Passwords do not match";
	} else {
		$confirm = test_input($_POST["confirm"]);
	}
	
	if ($firstNameErr == "" and $emailErr == "" and $lastNameErr == "") {
		if ($passwordErr == "" and $phoneErr == "" and $companyErr == "" and $confirmErr == "") {
			if (post_hubspot($firstname, $lastname, $email, $phone, $company) == 204) {
				post_dreamfactory($firstname, $lastname, $email, $phone, $company, $password);
			}
		}
	}
}

function post_dreamfactory($fn, $ln, $em, $ph, $co, $pw) {

	$str_post = "firstname=" . urlencode($fn)
	. "&lastname=" . urlencode($ln)
	. "&email=" . urlencode($em)
	. "&phone=" . urlencode($ph)
	. "&company=" . urlencode($co)
	. "&password=" . urlencode($pw);

	$endpoint = 'https://console.vz.dreamfactory.com/api/v1/ops/partner';

	$ch = @curl_init();
	@curl_setopt($ch, CURLOPT_POST, true);
	@curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
	@curl_setopt($ch, CURLOPT_URL, $endpoint);
	@curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded'
	));
	@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = @curl_exec($ch);
	$status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
	@curl_close($ch);
	return $status_code;
}

function post_hubspot($fn, $ln, $em, $ph, $co) {
	
	$hubspotutk = $_COOKIE['hubspotutk'];
	$ip_addr = $_SERVER['REMOTE_ADDR'];
	$hs_context = array(
		'hutk' => $hubspotutk,
		'ipAddress' => $ip_addr,
		'pageUrl' => 'verizon.dreamfactory.com',
		'pageName' => 'DreamFactory on Verizon Cloud'
	);
	$hs_context_json = json_encode($hs_context);

	$str_post = "firstname=" . urlencode($fn)
	. "&lastname=" . urlencode($ln)
	. "&email=" . urlencode($em)
	. "&phone=" . urlencode($ph)
	. "&company=" . urlencode($co)
	. "&mobile_lead=" . urlencode("No")
	. "&installation_source=" . urlencode("Verizon")
	. "&website_lead_source=" . urlencode("verizon.dreamfactory.com")
	. "&local_installation=" . urlencode("No")
	. "&local_installation_skipped=" . urlencode("No")
	. "&hs_context=" . urlencode($hs_context_json);

	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/247169/d48b5b8e-2274-488b-9448-156965d38048';

	$ch = @curl_init();
	@curl_setopt($ch, CURLOPT_POST, true);
	@curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
	@curl_setopt($ch, CURLOPT_URL, $endpoint);
	@curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded'
	));
	@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = @curl_exec($ch);
	$status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
	@curl_close($ch);
	return $status_code;
}

function test_input($data) {

	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

?>

<!-- start page top -->

<div style="width: 100%; height: 124px; background: black; margin: 0px;">
	<img src="/images/factory_right.png" style="float: right;">
	<img src="/images/verizon_left.png" style="float: left;">
	<p style="color: white; font-size: 4vw; line-height: 120%; text-align: center;">DreamFactory Hosted on Verizon Cloud</p>
</div>

<div class="container">
    <div class="row" style="text-align: center;">
		<div class="col-xs-6" style="display: inline-block; float: none; width: 550px; text-align: left; vertical-align: top;">

			<!-- start left col -->

			<br>
			<p>DreamFactory provides all of the RESTful web services you need to build fantastic mobile, web, and IoT applications. Sign up below for a Free Developer Environment hosted on the Verizon Cloud and get started today.</p>

			<ul>
				<li>Connect to any backend data source: SQL, NoSQL, or Files</li>
				<li>Instantly get a full palette of REST APIs with live documentation</li>
				<li>Set up users and role-based access controls for data security</li>
				<li>Client SDKs for Android, iOS, HTML5, AngularJS, jQuery, more</li>
				<li>There are no time or transaction limits on your developer account</li>
				<li>Free support for first 30 days (<a href="https://www.dreamfactory.com/developers/support">extended support plans available</a>)</li>
			</ul>
			
			<br><br>
			<img src="/images/verizon_diagram.jpg" style="display: block; margin-left: auto; margin-right: auto;">
			<br><br><br>
			
			<p>DreamFactory is a free, open source RESTful backend integration platform for mobile, web, and IoT applications. It provides RESTful web services for any data source so you can start front-end development with robust REST APIs on day one.</p>
			<p>DreamFactory provides pre-built connectors to SQL, NoSQL, file storage systems, and web services. With a few clicks, you instantly get a comprehensive palette of secure, reliable, customizable REST APIs and live API documentation.</p>
			<p>Here is a short movie that shows how to use the DreamFactory Admin Console and set up your backend platform. Many additional assets and full documentation are available in the Admin Console.</p>
			
			<!-- stop left col -->

        </div>
		<div class="col-xs-6" style="display: inline-block; float: none; width: 550px; text-align: left; vertical-align: top;">

			<!-- start right col -->

			<br>
			<p style="font-size: 24px;">Verizon Cloud Spaces Meet Your Demands</p>
			<p>​Verizon knows that no one solution fits all. That's why Verizon created a cloud that helps you do more.</p>

			<ul>
				<li>Flexible deployment model</li>
				<li>Centralized management from a single user interface</li>
				<li>Flexible managed services to match your needs</li>
			</ul>

			<p>Sign up for a free developer sandbox environment and try DreamFactory hosted on Verizon Cloud. You can move your DreamFactory instance to a new or existing Verizon Cloud account at any time for further evaluation or production.</p>
			<br><p style="font-size: 24px; font-weight: bold;">Sign up for your Free Developer<br>Environment today:</p>
			<p style="font-size: 13px;">Already registered? <a href="https://console.vz.dreamfactory.com/auth/login/">Click here to log in to the Enterprise Console.</a></p>

			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
			
				<label>First Name *</label><input type="text" name="firstname" value="<?php echo $firstname;?>">
				<br><p style="font-size: 13px; text-align: center;"><?php echo $firstNameErr;?></p>
				
				<label>Last Name *</label><input type="text" name="lastname" value="<?php echo $lastname;?>">
				<br><p style="font-size: 13px; text-align: center;"><?php echo $lastNameErr;?></p>
				
				<label>E-mail *</label><input type="text" name="email" value="<?php echo $email;?>">
				<br><p style="font-size: 13px; text-align: center;"><?php echo $emailErr;?></p>
				
				<label>Password *</label><input type="password" name="password" value="<?php echo $password;?>">
				<br><p style="font-size: 13px; text-align: center;"><?php echo $passwordErr;?></p>
				
				<label>Confirm Passwd *</label><input type="password" name="confirm" value="<?php echo $confirm;?>">
				<br><p style="font-size: 13px; text-align: center;"><?php echo $confirmErr;?></p>
				
				<label>Phone Number *</label><input type="text" name="phone" value="<?php echo $phone;?>">
				<br><p style="font-size: 13px; text-align: center;"><?php echo $phoneErr;?></p>
				
				<label>Company Name *</label><input type="text" name="company" value="<?php echo $company;?>">
				<br><p style="font-size: 13px; text-align: center;"><?php echo $companyErr;?></p>
				
				<div style="float: left;">
 					<input type="submit" class="stylebut" name="submit" value="Create Account"> 
 				</div>
				
			</form>
			
			<br><br>
			<br><br>
			<center>
			<iframe width="420" height="315" src="https://www.youtube.com/embed/8Y0uCaTw3jg" align="center" frameborder="0" allowfullscreen></iframe>
			</center>
			<br>
			
			<!-- stop right col -->

        </div>
    </div>
</div>

<br><br>
<p style="text-align: center;">© 2015 DreamFactory Software, Inc.</p>

</body>
</html>


