<?php  if ( ! defined('_VALID_BBC')) exit('No direct script access allowed');

function user_field($user_id = 0, $group_id = 0)
{
	global $db;
	if($user_id > 0)
	{
		$q = "SELECT group_ids FROM bbc_user WHERE id=$user_id";
		$grp = $db->getOne($q);
		if(!empty($grp))
		{
			$q = "SELECT id, is_customfield FROM bbc_user_group WHERE id IN(".fixValue($grp).") ORDER BY score DESC LIMIT 1";
			$d = $db->getRow($q);
			if(@$d['is_customfield']) $group_id = $d['id'];
		}
	}
	$q = "SELECT * FROM bbc_user_field WHERE group_id=$group_id AND active=1 ORDER BY orderby ASC";
	return $db->GetAll($q);
}
function user_field_group($group = '')
{
	global $db;
	if(!empty($group))
	{
		$group = is_array($group) ? repairImplode($group) : $group;
	}else{
		$group = repairImplode(config('rules', 'register_groups'));
	}
	$group = fixValue($group);
	$group_id = 0;
	if(!empty($group))
	{
		$q = "SELECT id, is_customfield FROM bbc_user_group WHERE id IN(".$group.") ORDER BY score DESC LIMIT 1";
		$d = $db->getRow($q);
		if($d['is_customfield']) $group_id = $d['id'];
	}
	$q = "SELECT * FROM bbc_user_field WHERE group_id=$group_id AND active=1 ORDER BY orderby";
	$output = $db->GetAll($q);
	if(!$db->Affected_rows()) $output = array();
	return $output;
}
function user_fetch($user_id)
{
	global $db;
	$q = "SELECT * FROM bbc_account WHERE user_id=".intval($user_id);
	$output = $db->getRow($q);
	$tmp = array();
	if(!empty($output['params']))
	{
		$prm = config_decode($output['params']);
		foreach(user_field($user_id) AS $dt)
		{
			$tmp[$dt['title']] = @$prm[$dt['title']];
		}
	}
	$output = array_merge($output, $tmp);
	unset($output['params']);
	return $output;
}

function user_email($params = 'none', $is_user_id = true)
{
	global $db, $user;
	$output = false;
	$params = $params == 'none' ? $user->id : $params;
	if(is_numeric($params) && $params > 0)
	{
		$id = intval($params);
		$field = $is_user_id ? 'user_id' : 'id';
		$q = "SELECT email FROM bbc_account WHERE $field=$id";
		$output = $db->getOne($q);
	}
	return $output;
}

function user_name($code = 'none', $field = 'name')
{
	global $Bbc, $db, $user;
	if(isset($Bbc->user_name[$code])) return $Bbc->user_name[$code];
	if(is_numeric($code) && $code > 0)
		$sql = 'WHERE user_id='.$code;
	elseif(is_email($code))
		$sql = "WHERE email='$code'";
	elseif($code != 'none')
		$sql = "WHERE username='$code'";
	else $sql = "WHERE user_id=".$user->id;
	$q = "SELECT {$field} FROM bbc_account $sql";
	$Bbc->user_name[$code] = $db->getOne($q);
	return $Bbc->user_name[$code];
}
/*=========================================
$params = array(
	'username'  => , // *** || OR empty for email as username...,
	'password'  => , // *** || OR empty for random password...,
	'name'      => ,
	'email'     => ,
	'params'    => , // depends on user_field();
	'group_ids' => array() || '2,1,4' || // OR empty for default group ids...
);
return user_id
 *=========================================*/
function user_create($params)
{
	$params = is_array($params) ? $params : config_decode($params);
	$params = _class('params')->clean_newline($params);
	if(isset($params['params'])
		&& !empty($params['name'])
		&& isset($params['email'])
		&& is_email($params['email']))
	{
		global $db;
		/*=======================================
		 * GET USER GROUP
		/*=====================================*/
		if(!empty($params['params']))
		{
			if(!is_array($params['params']))
			{
				$r = config_decode($params['params']);
				if(!empty($r) && is_array($r))
				{
					$params['params'] = $r;
				}
			}
			if(empty($params['group_ids']))
			{
				if(!empty($params['params']['group_ids']))
				{
					$params['group_ids'] = $params['params']['group_ids'];
				}else{
					$params['group_ids'] = config('rules', 'register_groups');
				}
			}
			if(!empty($params['group_ids']))
			{
				if(!is_array($params['group_ids']))
				{
					$params['group_ids'] = repairExplode($params['group_ids']);
				}
			}else return false;
		}else return false;
		$email_to		= array();
		$def_params = array(
			'username',
			'password',
			'name',
			'email',
			'params',
			'group_ids'
		);
		$data = array('params' => '');
		foreach($def_params AS $key)
		{
			// Name
			if($key == 'name')
			{
				$data[$key] = trim($params[$key]);
			}
			// Username
			if($key == 'username')
			{
				if(isset($params[$key]) && !empty($params[$key]))
				{
					$data[$key] = strtolower($params[$key]);
				}else{
					$data[$key] = strtolower($params['email']);
				}
				$data[$key] = str_replace("'", "\'", strtolower($data[$key]));
			}
			// Password
			if($key == 'password')
			{
				if(isset($params[$key]) && !empty($params[$key])) {
					$data[$key] = $params[$key];
				}else{
					$data[$key] = preg_replace('~[^a-z0-9]~is', '', base64_encode(rand()));
				}
			}
			// Email
			if($key == 'email')
			{
				$data[$key] = strtolower($params['email']);
				$email_to[] = $data[$key];
			}
			// Params
			if($key == 'params')
			{
				$r_field = array();
				$r_param = is_array($params[$key]) ? $params[$key] : config_decode($params[$key]);
				$user_field	= user_field_group($params['group_ids']);
				foreach($user_field AS $dt)
				{
					if(isset($r_param[$dt['title']]))
					{
						$r_field[$dt['title']] = $r_param[$dt['title']];
						if(is_email($r_field[$dt['title']]))
						{
							$r_field[$dt['title']] = strtolower($r_field[$dt['title']]);
							$email_to[] = $r_field[$dt['title']];
						}
					}else{
						$r_field[$dt['title']] = @$dt['default'];
					}
				}
				$data[$key] = $r_field;
			}
			// Group_Ids
			if($key == 'group_ids')
			{
				if(empty($params[$key]) && !empty($params['params']['group_ids']))
				{
					$params[$key] = $params['params']['group_ids'];
				}
				if(!empty($params[$key]))
				{
					$data[$key] = is_array($params[$key]) ? $params[$key] : repairExplode($params[$key]);
				}else{
					$data[$key] = config('rules', 'register_groups');
				}
			}
		}
		// $db->Execute('FLUSH TABLES WITH READ LOCK');
		if (user_create_validate($data))
		{
			// $db->Execute('UNLOCK TABLES');
			@unlink(_CACHE.'user_create_validate_msg.txt');
			$q = "INSERT INTO `bbc_user` SET
				`group_ids`       = '".repairImplode(addslashes_r($data['group_ids']))."',
				`username`        = '".addslashes($data['username'])."',
				`password`        = '".encode($data['password'])."',
				`last_ip`         = '',
				`last_ip_temp`    = '',
				`last_login`      = '',
				`last_login_temp` = '',
				`exp_checked`     = '',
				`login_time`      = 0,
				`created`         = NOW(),
				`active`          = 1
				";
			if($db->Execute($q))
			{
				$user_id = $db->Insert_ID();
				$q = "INSERT INTO `bbc_account` SET
					`user_id`  = '".$user_id."',
					`username` = '".addslashes($data['username'])."',
					`name`     = '".addslashes($data['name'])."',
					`email`    = '".addslashes($data['email'])."',
					`params`   = '".config_encode($data['params'])."'
					";
				$db->Execute($q);
				user_call_func(__FUNCTION__, $user_id);
				return $user_id;
			}
		}
		$db->Execute('UNLOCK TABLES');
	}
	return 0;
}
/*
$data = array(
	'username'	=> 'username',
	'password'	=> '123456',
	'name'			=> 'Mr. Nice Guy',
	'email'			=> 'username@website.com',
	'params'		=> array(),// depends on user_field();
	'group_ids'	=> [2,1,4]
	);
return boolean;
*/
function user_create_validate($data)
{
	global $db;
	$out = true;
	@unlink(_CACHE.'user_create_validate_msg.txt');
	if ($db->getOne("SELECT 1 FROM `bbc_user` WHERE `username`='".$data['username']."'"))
	{
		user_create_validate_msg('Username has already been used!');
		$out = false;
	}
	if ($out && $db->getOne("SELECT 1 FROM `bbc_account` WHERE `email`='".$data['email']."'"))
	{
		user_create_validate_msg('Email address has already been used!');
		$out = false;
	}
	if ($out)
	{
		$out = user_call_func_validate(__FUNCTION__, $data);
	}
	return $out;
}
function user_create_validate_msg($msg='')
{
	$filename = _CACHE.'user_create_validate_msg.txt';
	if (!empty($msg))
	{
		return file_write($filename, $msg."<br />\n", 'a+');
	}else{
		$msg = file_read($filename);
		if (empty($msg))
		{
			$msg = lang('Sorry, user registration is failed');
		}
		return $msg;
	}
}
/*=========================================
 * ACCOUNT_FUNC_PRE (user_id)
 * ACCOUNT_FUNC_POST (account_id)
 *=======================================*/
function user_delete($user_ids)
{
	if(empty($user_ids)) return false;
	$ids = is_array($user_ids) ? $user_ids : array($user_ids);
	if(count($ids) > 0)
	{
		global $db;
		$tbl_usr = 'bbc_user';
		$tbl_acc = 'bbc_account';
		$_func = array('pre' => array(), 'post' => array());
		$q = "SELECT name, account_func_pre AS pre, account_func_post AS post
					FROM bbc_module WHERE account_func_pre != '' || account_func_post != ''";
		$r = $db->getAll($q);
		foreach($r AS $dt)
		{
			if(!empty($dt['pre']))	$_func['pre'][$dt['name']]	= $dt['pre'];
			if(!empty($dt['post']))	$_func['post'][$dt['name']]	= $dt['post'];
		}
		foreach((array)$_func['pre'] AS $module => $func)
		{
			_func($module);
			if(function_exists($func))
			{
				call_user_func($func, $ids); // user_id
			}
		}
		$IDs = implode(',', $ids);
		$q="DELETE FROM `$tbl_usr` WHERE `id` IN({$IDs})";
		$db->Execute($q);
		if(count($_func['post']) > 0)
		{
			$q="SELECT id FROM `$tbl_acc` WHERE `user_id` IN({$IDs})";
			$account_ids = array_merge(array(0), $db->getCol($q));
		}
		$q="DELETE FROM `$tbl_acc` WHERE `user_id` IN({$IDs})";
		$db->Execute($q);
		foreach((array)$_func['post'] AS $module => $func)
		{
			_func($module);
			if(function_exists($func))
			{
				call_user_func($func, $account_ids); // account_id
			}
		}
		$exist = $db->getOne("SHOW TABLES LIKE 'bbc_user_push'");
		if (!empty($exist))
		{
			$db->Execute("DELETE FROM `bbc_user_push` WHERE `user_id` IN ({$IDs})");
		}
		$exist = $db->getOne("SHOW TABLES LIKE 'bbc_user_push_notif'");
		if (!empty($exist))
		{
			$db->Execute("DELETE FROM `bbc_user_push_notif` WHERE `user_id` IN ({$IDs})");
		}
		user_call_func(__FUNCTION__, $ids); // user_ids
		return true;
	}
	return false;
}

function user_reminder($email, $is_send = true)
{
	global $db, $sys;
	$q="SELECT * FROM bbc_account WHERE `email`='".strtolower($email)."'";
	$acc = $db->getRow($q);
	if(!$db->Affected_rows())
	{
		return false;
	}else{
		$q="SELECT * FROM bbc_user WHERE `id`=".intval($acc['user_id']);
		$user = $db->getRow($q);
		if(!$db->Affected_rows())
		{
			return false;
		}else{
			if(!$user['active'])
			{
				return false;
			}else{
				$user['password'] = decode($user['password']);
				$sys->module_id = $sys->get_module_id('user');
				// SET PARAMS...
				foreach($acc AS $id => $dt)
				{
					if($id != 'params')
					{
						$GLOBALS[$id] = $dt;
					}
				}
				foreach($user AS $id => $dt)
				{
					if($id != 'params')
					{
						$GLOBALS[$id] = $dt;
					}
				}
				foreach((array)config_decode($acc['params']) AS $id => $dt)
				{
					if(!preg_match('/ /', $id))
					{
						$GLOBALS[strtolower($id)] = $dt;
					}
				}
				if($is_send)
				{
					$sys->mail_send($acc['email'], 'password');
					return true;
				}else{
					$temp = $sys->mail_fetch( 'password', $sys->module_id );
					return $sys->text_replace($temp);
				}
			}
		}
	}
}

function user_login($username, $password, $is_admin = 0, $rememberme = 0)
{
	global $db, $_CONFIG;
	$_SESSION[bbcAuth] = array();
	unset($_SESSION[bbcAuth]);
	$output   = 'none';
	$is_admin = intval($is_admin);
	$fail_txt = !empty($_SERVER['REMOTE_ADDR']) ? _CACHE.'failed_login/'.$_SERVER['REMOTE_ADDR'].'.txt' : '';
	$fails_no = 0;
	if (!empty($fail_txt))
	{
		$fails_no = intval(file_read($fail_txt));
	}
	if ($fails_no >= 3)
	{
		return $output;
	}
	$username = str_replace("'", "\'", $username);
	$q        = "SELECT * FROM `bbc_user` WHERE `username`='{$username}'";
	$dt       = $db->getRow($q);
	if(!empty($dt))
	{
		// LOGIN CORRECT
		if($password == decode($dt['password']))
		{
			$q = "SELECT `is_admin` FROM `bbc_user_group` WHERE `id` IN(".fixValue($dt['group_ids']).")";
			$dt['is_admin'] = array_unique($db->getCol($q));
			if(!$dt['active'])
			{
				$output = 'inactive';
			}else
			if(!in_array($is_admin, $dt['is_admin']))
			{
				$output = 'notallowed';
			}else{
				$output = 'allowed';
				if (!empty($fail_txt) && file_exists($fail_txt))
				{
					@unlink($fail_txt);
				}
				// fix Variable RememberMe...
				if(!$is_admin && $rememberme)
				{
					_func('cookie');
					cookie_set(md5(_SALT), encode($dt['id']), strtotime('+1 YEAR'));
				}
				// UPDATE USER DATA
				$idle_duration = $is_admin ? config('logged', 'duration_admin'): config('logged', 'duration');
				$idle_period	 = $is_admin ? config('logged', 'period_admin') : config('logged', 'period_admin');
				$q="UPDATE `bbc_user` SET
					`last_ip_temp`    = `last_ip`,
					`last_ip`         = '".$_SERVER['REMOTE_ADDR']."',
					`last_login_temp` = `last_login`,
					`last_login`      = NOW(),
					`login_time`      = (`login_time`+1),
					`exp_checked`     = DATE_ADD(NOW(), INTERVAL +".intval($idle_duration)." ".$idle_period.")
						WHERE `id` = ".$dt['id'];
				$db->Execute($q);

				// FETCH USER DATA
				$q = "SELECT id, username, last_ip_temp AS last_ip,
							DATE_FORMAT(last_login_temp, '%b %D %Y') AS lastLogDate,
							DATE_FORMAT(last_login_temp, '%T') AS lastLogTime,
							login_time, '1' AS is_login, group_ids AS group_id
							FROM bbc_user WHERE id = ".$dt['id'];
				$user = $db->getRow($q);
				// FETCH PARTICULAR
				$q = "SELECT id AS account_id, name, email, image, params FROM bbc_account WHERE user_id=".$dt['id'];
				$acc = $db->getRow($q);
				$acc['params'] = config_decode(@$acc['params']);
				$user = array_merge($user, $acc);

				// NEW FETCH ALL MENUS AND CPANELS
				$q = "SELECT id, menus, cpanels FROM bbc_user_group WHERE id IN(".fixValue($user['group_id']).") AND is_admin=$is_admin";
				$r = $db->getAll($q);
				$user['group_ids']= $user['menu_ids']	= $user['cpanel_ids']= array();
				foreach((array)$r AS $d)
				{
					$user['group_ids'][] = $d['id'];
					$user['menu_ids']    = array_merge($user['menu_ids'], repairExplode($d['menus']));
					$user['cpanel_ids']  = array_merge($user['cpanel_ids'], repairExplode($d['cpanels']));
				}
				if(in_array('all', $user['menu_ids'])) $user['menu_ids'] = array('all');
				else $user['menu_ids'] = array_unique($user['menu_ids']);
				if($is_admin)
				{
					if(in_array('all', $user['cpanel_ids'])) $user['cpanel_ids'] = array('all');
					else $user['cpanel_ids'] = array_unique($user['cpanel_ids']);
				}else $user['cpanel_ids'] = array();

				$_SESSION[bbcAuth] = $user;
				$GLOBALS['user']   = new stdClass();
				foreach((array)@$_SESSION[bbcAuth] AS $id => $value)
				{
					if($id!='')$GLOBALS['user']->$id = $value;
				}
				user_call_func(__FUNCTION__, $user['id']); // user_id
			}
		}
	}
	if ($output!='allowed')
	{
		file_write($fail_txt, ++$fails_no);
	}
	return $output;
}

function user_logout($user_id, $is_admin = 0)
{
	global $db, $user;
	_func('cookie');
	cookie_delete(md5(_SALT));
	$q = "UPDATE bbc_user SET exp_checked='0000-00-00 00:00:00' WHERE id=".intval($user_id);
	$db->Execute($q);
	if($user_id == $user->id)
	{
		unset($_SESSION[bbcAuth]);
		user_call_func(__FUNCTION__, $user_id); // user_id
	}else{
		unset($_SESSION[bbcAuth]);
	}
}

function user_auto_login()
{
	global $db;
	_func('cookie');
	$code = cookie_fetch(md5(_SALT));
	if(!empty($code))
	{
		$user_id = intval(decode($code));
		if($user_id > 0)
		{
			$q      = "SELECT * FROM `bbc_user` WHERE `id`={$user_id}";
			$usr    = $db->getRow($q);
			$output = user_login($usr['username'], $usr['password'], 0, 1);
			if($output != 'allowed')
			{
				cookie_delete(md5(_SALT));
			}
		}
	}
	$output = (isset($output) && $output == 'allowed') ? true : false;
	return $output;
}
/*
fetch all available modules based on filename
*/
function user_modules($file='_function')
{
	global $Bbc;
	_ext($file);
	$id = menu_save('module_'.$file);
	if (empty($Bbc->$id))
	{
		$path = _ROOT.'modules/';
		$all  = _func('path', 'list', $path);
		$mods = array();
		foreach ($all as $mod)
		{
			if (file_exists($path.$mod.'/'.$file))
			{
				include_once $path.$mod.'/'.$file;
				$mods[] = $mod;
			}
		}
		$Bbc->$id = $mods;
	}
	return $Bbc->$id;
}
/*
execute all functions with 'function_name' AS postfix in all modules
user_call_func('function_name'[, $arg1[, $arg2]]);
*/
function user_call_func()
{
	$path = _ROOT.'modules/';
	$mods = user_modules();
	$args = func_get_args();
	$func = array_shift($args);
	foreach ($mods as $mod)
	{
		if (function_exists($mod.'_'.$func))
		{
			call_user_func_array($mod.'_'.$func, $args);
		}
	}
}
/*
execute all functions with 'function_name' AS postfix in all modules
this is for validation, and the looping will stop when the return is FALSE
boolean user_call_func_validate('function_name'[, $arg1[, $arg2]]);
*/
function user_call_func_validate()
{
	$path = _ROOT.'modules/';
	$mods = user_modules();
	$args = func_get_args();
	$func = array_shift($args);
	$out  = true;
	foreach ($mods as $mod)
	{
		if (function_exists($mod.'_'.$func))
		{
			$out = call_user_func_array($mod.'_'.$func, $args);
			if (!$out)
			{
				break;
			}
		}
	}
	return $out;
}
function user_auth($data = array())
{
	global $user;
	if (!empty($data) && is_array($data))
	{
		$_SESSION['user_auth'] = $data;
		redirect();
	}else
	if (!empty($_SESSION['user_auth']))
	{
		return $_SESSION['user_auth'];
	}else
	if (!empty($user->image))
	{
		$out = array(
			'id'    => $user->id,
			'name'  => $user->name,
			'email' => $user->email,
			'image' => $user->image
			);
		$_SESSION['user_auth'] = $out;
		return $out;
	}else
	if (@$_GET['mod']!='user.auth')
	{
		$add_url = '';
		if (!empty($data) && is_string($data))
		{
			$add_url = 'msg='.urlencode(lang($data)).'&';
		}
		redirect('user/auth?'.$add_url.'return='.urlencode(seo_url()));
	}
}
function user_location($click_ids, $id_area, $alert = '')
{
	if (is_array($click_ids))
	{
		$click_ids = implode(', ', $click_ids);
	}
	if (empty($alert))
	{
		$alert = lang('masukkan nama wilayah terlebih dahulu');
	}
	ob_start();
	$click_ids = addslashes($click_ids);
	?>
	<script type="text/javascript">
		_Bbc(function($){
			window.LatLng = {};
			var x = $("<?php echo $click_ids; ?>");
			x.each(function(){
				if (["input", "textarea"].indexOf($(this).prop("tagName").toLowerCase()) > -1) {
					window.LatLng = $(this);
				}
			}).on("focus, click", function(a){
				a.preventDefault();
				var b = $("<?php echo $id_area; ?>");
				var c = window.LatLng;
				if (c.val() == "" && $(b).val()=="") {
					alert("<?php echo $alert; ?>");
					b.focus();
				}else{
					var d = window.open(_URL+"user/location?id=" + encodeURIComponent(b.val()) + "&latlong=" + encodeURIComponent(c.val()), "maps", "width=800, height=600, align=top, scrollbars=yes, status=no, resizable=yes");
					d.focus()
				}
			});
		});
		function setPosition(a, b) {
			LatLng.val(a + "," + b);
			return false
		};
	</script>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}