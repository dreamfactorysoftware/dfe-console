<?php
//******************************************************************************
//* Form logic
//******************************************************************************

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);

$firstNameErr = $emailErr = $lastNameErr = $passwordErr = $phoneErr = $companyErr = $confirmErr = null;
$firstname = $email = $lastname = $password = $confirm = $phone = $company = null;

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    if (empty($_POST['firstname'])) {
        $firstNameErr = 'First name is required';
    } else {
        $firstname = test_input($_POST['firstname']);
        if (!preg_match('/^[a-zA-Z ]*$/', $firstname)) {
            $firstNameErr = 'Only letters and white space allowed';
        }
    }

    if (empty($_POST['lastname'])) {
        $lastNameErr = 'Last name is required';
    } else {
        $lastname = test_input($_POST['lastname']);
        if (!preg_match('/^[a-zA-Z ]*$/', $lastname)) {
            $lastNameErr = 'Only letters and white space allowed';
        }
    }

    if (empty($_POST['company'])) {
        $companyErr = 'Company name is required';
    } else {
        $company = test_input($_POST['company']);
        if (!preg_match('/^[a-zA-Z ]*$/', $company)) {
            $companyErr = 'Only letters and white space allowed';
        }
    }

    if (empty($_POST['phone'])) {
        $phoneErr = 'Phone number is required';
    } else {
        $phone = test_input($_POST['phone']);
        if (!preg_match('/^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/', $phone)) {
            $phoneErr = 'Only numbers and dashes allowed';
        }
    }

    if (empty($_POST['email'])) {
        $emailErr = 'Email is required';
    } else {
        $email = test_input($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = 'Invalid email format';
        }
    }

    if (empty($_POST['password'])) {
        $passwordErr = 'Password is required';
    } else {
        $password = test_input($_POST['password']);
        if (strlen($password) < 3) {
            $passwordErr = 'Password must have at least 3 characters';
        }
    }

    if ($_POST['password'] != $_POST['confirm']) {
        $confirmErr = 'Passwords do not match';
    } else {
        $confirm = test_input($_POST['confirm']);
    }

    if ($firstNameErr == '' and $emailErr == '' and $lastNameErr == '') {
        if ($passwordErr == '' and $phoneErr == '' and $companyErr == '' and $confirmErr == '') {
            if (post_hubspot($firstname, $lastname, $email, $phone, $company) == 204) {
                post_dreamfactory($firstname, $lastname, $email, $phone, $company, $password);
            }
        }
    }
}

function post_dreamfactory($fn, $ln, $em, $ph, $co, $pw)
{
    $str_post = 'firstname=' . urlencode($fn)
        . '&lastname=' . urlencode($ln)
        . '&email=' . urlencode($em)
        . '&phone=' . urlencode($ph)
        . '&company=' . urlencode($co)
        . '&password=' . urlencode($pw);

    $endpoint = 'https://console.vz.dreamfactory.com/api/v1/ops/partner';

    $ch = @curl_init();
    @curl_setopt($ch, CURLOPT_POST, true);
    @curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
    @curl_setopt($ch, CURLOPT_URL, $endpoint);
    @curl_setopt($ch,
        CURLOPT_HTTPHEADER,
        [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = @curl_exec($ch);
    $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
    @curl_close($ch);

    return $status_code;
}

function post_hubspot($fn, $ln, $em, $ph, $co)
{
    $hubspotutk = $_COOKIE['hubspotutk'];
    $ip_addr = $_SERVER['REMOTE_ADDR'];
    $hs_context = [
        'hutk'        => $hubspotutk,
        'ipAddress'   => $ip_addr,
        'pageUrl'     => 'verizon.dreamfactory.com',
        'pageName'    => 'DreamFactory on Verizon Cloud',
        'redirectUrl' => 'https://dashboard.vz.dreamfactory.com/',
    ];
    $hs_context_json = json_encode($hs_context);

    $str_post = 'firstname=' . urlencode($fn)
        . '&lastname=' . urlencode($ln)
        . '&email=' . urlencode($em)
        . '&phone=' . urlencode($ph)
        . '&company=' . urlencode($co)
        . '&mobile_lead=' . urlencode('No')
        . '&installation_source=' . urlencode('Verizon')
        . '&website_lead_source=' . urlencode('verizon.dreamfactory.com')
        . '&local_installation=' . urlencode('No')
        . '&local_installation_skipped=' . urlencode('No')
        . '&hs_context=' . urlencode($hs_context_json);

    $endpoint = 'https://forms.hubspot.com/uploads/form/v2/247169/d48b5b8e-2274-488b-9448-156965d38048';

    $ch = @curl_init();
    @curl_setopt($ch, CURLOPT_POST, true);
    @curl_setopt($ch, CURLOPT_POSTFIELDS, $str_post);
    @curl_setopt($ch, CURLOPT_URL, $endpoint);
    @curl_setopt($ch,
        CURLOPT_HTTPHEADER,
        [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = @curl_exec($ch);
    $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
    @curl_close($ch);

    return $status_code;
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);

    return $data;
}