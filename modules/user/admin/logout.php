<?php  if (!defined('_VALID_BBC')) exit('No direct script access allowed');

include_once 'login-type.php';
user_logout($user->id);
if (empty($type))
{
	redirect(_URL._ADMIN);
}else{
	switch ($type)
	{
		case '2':
			$url = 'https://login.yahoo.com/config/login?logout=1&.done=https%3A%2F%2Fwww.yahoo.com';
			break;
		case '3':
			$url = 'https://www.facebook.com';
			break;
		case '1':
		default:
			$url = 'https://accounts.google.com/Logout?hl=en&continue='.urlencode('https://appengine.google.com/_ah/logout?continue='.urlencode(_URL._ADMIN));
			break;
	}
	redirect('https://auth.fisip.net/logout?redirect='.urlencode($url));
}
