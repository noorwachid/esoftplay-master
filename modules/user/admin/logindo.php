<?php  if (!defined('_VALID_BBC')) exit('No direct script access allowed');

include 'login-type.php';
if (empty($type))
{
	include 'layout.login.php';
}else{
	$MAINURL= _URL._ADMIN;
	if (!empty($_GET['relogin']))
	{
		include 'logout.php';
		die();
	}
	if (!empty($user->id))
	{
		redirect($MAINURL);
	}
	$arr   = $sys->login($type_name);
	$email = @$arr['email'];
	$usr   = $db->getRow("SELECT * FROM `bbc_user` WHERE `username`='{$email}'");
	if(empty($usr))
	{
		$q   = "SELECT `user_id` FROM `bbc_account` WHERE `email`='{$email}'";
		$uid = intval($db->getOne($q));
		$q   = "SELECT * FROM `bbc_user` WHERE id={$uid}";
		$usr = $db->getRow($q);
	}
	if(empty($usr))
	{
		$msg = 'You are not allowed to login !';
	}else{
		_func('password');
		$output		= user_login($usr['username'], decode($usr['password']), '1');
		switch($output)
		{
			case 'allowed':
				$msg = '';
				// include 'layout.main.php';
				redirect($MAINURL);
				break;
			case 'inactive':
				$msg = "Your account has been disabled.<br />For further information, please contact administrator";
				break;
			case 'notallowed':
				$msg = "Your account is not allowed to access this section.";
				break;
			case 'none':
				$msg = "Invalid Username or Password";
				break;
		}
	}
	if (!empty($msg))
	{
		?>
		<div class="container">
			<div class="jumbotron">
				<h1><?php echo $msg; ?></h1>
				<p>Please contact administrator to get privilege to login for email <?php echo $email; ?>. Or if you want to try another shot to sign in, you may want to click the button below.</p>
				<p>
					<?php echo $sys->button($MAINURL, 'refresh', 'refresh'); ?>
					<?php echo $sys->button($MAINURL.'?relogin=1', 'relogin', 'repeat'); ?>
				</p>
			</div>
		</div>
		<?php
		$sys->set_layout('blank.php');
	}
}