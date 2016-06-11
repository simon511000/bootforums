<?php
/*
Submit.php - The brains of the forum, the "API" so to speak. Enjoy.
Script Created by Mitchell Urgero
Date: Sometime in 2016 ;)
Website: https://urgero.org
E-Mail: info@urgero.org

Script is distributed with Open Source Licenses, do what you want with it. ;)
"I wrote this because I saw that there are not that many databaseless Forums for PHP. It needed to be done. I think it works great, looks good, and is VERY mobile friendly. I just hope at least one other person
finds this PHP script as useful as I do."

*/
session_start();
require("db.php");
require("config.php");
$type = "";
if($_POST['type']){
	$type = $_POST['type'];
}
if($_GET['type']){
	$type = $_GET['type'];
}


switch($type){
	
	case "reply":
		if(!$_SESSION['username']){ die("You must be logged in to do anything on these forums."); }
		if(clean($_POST['post-id']) == "" || clean($_POST['post-id']) == "-" || !isNotEmpty(clean($_POST['post-id']))){
			die("Invalid name detected, please try again!");
		}
		addPost($_POST['post-id'], $_POST['text'], $_SESSION['username']);
		header("Location: ./post.php?page=last&type=view&post=".clean($_POST['post-id']));
		die();
		break;
	case "new":
		if(!$_SESSION['username']){ die("You must be logged in to do anything on these forums."); }
		if(clean($_POST['post-id']) == "" || clean($_POST['post-id']) == "-" || !isNotEmpty(clean($_POST['post-id']))){
			die("Invalid name detected, please try again!");
		}
		if(!$config['allowNewThreads'] && !in_array($_SESSION['username'], $config['admins'])){
			header("Location: ./");
			die();
			break;
		}
		addPost($_POST['post-id'], $_POST['text'], $_SESSION['username']);
		header("Location: ./post.php?type=view&post=".clean($_POST['post-id']));
		die();
		break;
	case "reg":
		if($_POST['cap'] != $_SESSION['captcha']['code']){
			header("Location: ./register.php?msg=Captcha invalid!"); die();
		}
		if($config['registration'] == false){
			header("Location: ./register.php?msg=Registration is disabled! You cannot register, even when you want to be sneaky."); die();
		}
		$u = clean($_POST['user']);
		if(strlen($u) > 12){
			$u = substr($u, 0, 12);
		}
		$msg = adduser($u, $_POST['pass']);
		if(!$msg){ header("Location: ./register.php?msg=Registration failed!"); die(); }
		header("Location: ./login.php?msg=Login to continue registration, ".$u);
		die();
		break;
	case "login":
		if($_POST['cap'] != $_SESSION['captcha']['code'] && $config['captchaLoginForce'] === true){
			header("Location: ./login.php?msg=Captcha invalid!"); die();
		}
		$msg = auth($_POST['user'], $_POST['pass']);
		if(!$msg){
			header("Location: ./login.php?msg=Incorrect username or password");
			die();
		}
		$_SESSION['username'] = $_POST['user'];
		header("Location: ./");
		die();
	case "edit":
		if(!$_SESSION['username']){ die("You must be logged in to do anything on these forums."); }
		if(clean($_POST['post-id']) == "" || clean($_POST['post-id']) == "-" || !isNotEmpty(clean($_POST['post-id']))){
			die("Invalid name detected, please try again!");
		}
		update($_POST['post-id'], $_SESSION['username'], $_POST['time'], $_POST['text'], $_POST['reply_num']);
		header("Location: ./post.php?page=last&type=view&post=".clean($_POST['post-id']));
		die();
		break;
	case "lock":
		if(!$_SESSION['username']){ die("You must be logged in to do anything on these forums."); }
		lock($_GET['post'], $_SESSION['username']);
		header("Location: ./post.php?page=last&type=view&post=".clean($_GET['post']));
		die();
		break;
	case "unlock":
		if(!$_SESSION['username']){ die("You must be logged in to do anything on these forums."); }
		unlock($_GET['post'], $_SESSION['username']);
		header("Location: ./post.php?page=last&type=view&post=".clean($_GET['post']));
		die();
		break;
	case "delete":
		if(!$_SESSION['username']){ die("You must be logged in to do anything on these forums."); }
		deletePost($_GET['post'], $_SESSION['username']);
		header("Location: ./index.php");
		die();
		break;
	case "passwd":
		if(!$_SESSION['username']){ die("You must be logged in to do anything on these forums."); }
		$msg = changePasswd($_SESSION['username'], $_POST['currPass'], $_POST['pass1'], $_POST['pass2']);
		if($msg == false){
			$msg = "There was an error updating the password, please try again.";
		}
		if($msg === true){
			$msg = "Password has changed successfully!";
		}
		header("Location: ./change.php?msg=$msg");
}
function isNotEmpty($input) 
{
    $strTemp = $input;
    $strTemp = trim($strTemp);

    if($strTemp !== '') //Also tried this "if(strlen($strTemp) > 0)"
    {
         return true;
    }

    return false;
}

?>