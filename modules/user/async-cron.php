<?php  if (!defined('_VALID_BBC')) exit('No direct script access allowed');

$fileasync   = '/opt/async/bin/manager.php';
$filecheck   = _CACHE.'async.cfg';
$fileexecute = _CACHE.'async-execute.cfg';
$filefailed  = _CACHE.'async-failed.cfg';
$num_worker  = 5; // check file /opt/async/bin/manager.php for $config['worker_num']
$notify      = '';
$data        = $db->getRow("SELECT * FROM `bbc_async` WHERE 1 ORDER BY id ASC LIMIT 1");
if (!empty($data))
{
	$checknow  = $data['id'].'-'.$data['function'];
	$checklast = file_read($filecheck);

	$last      = strtotime($data['created']);
	$threshold = strtotime('-2 minutes');
	$thresmax  = strtotime('-35 minutes');
	if ($last < $threshold)
	{
		if ($last < $thresmax)
		{
			$pending   = '?';
			$process   = '?';
			$worker    = '?';
			$notify = 'ada async yang umur lebih dari 35 menit';
		}else{
			_class('async')->fix($data['id']);
		}
		if ($notify)
		{
			_func('date');
			$url = _URL.'user/async?act=';
			$msg = '#'.@$_SERVER['HTTP_HOST'].' : '.$pending.' - '.$process.' - '.$worker
				."\nfunction: ".$data['function']
				."\ncreated: ".$data['created'].' ('.timespan(strtotime($data['created'])).')'
				."\ntotal: ".money($db->getOne("SELECT COUNT(*) FROM `bbc_async` WHERE 1"));
			$msg = array(
				'text'         => $msg."\n".$notify,
				'reply_markup' => json_encode([
							'inline_keyboard' => [
								[
									['text' => 'Async', 'callback_data' => $url.'async'],
									['text' => 'Gearman', 'callback_data' => $url.'status']
								],
								[
									['text' => 'Restart', 'callback_data' => $url.'restart'],
									['text' => 'Execute', 'url' => _URL.'user/async']
								]
							],
							'resize_keyboard' => true,
							'selective'       => true
						])
				);
			if (function_exists('tm'))
			{
				$chatID = defined('_ASYNC_CHAT') ? _ASYNC_CHAT : -345399808;
				$out = tm($msg, $chatID);
				// pr($out, $msg, __FILE__.':'.__LINE__);
			}
		}
	}else{
		user_async_cron_clean();
	}
}else{
	user_async_cron_clean();
}

function user_async_cron_clean()
{
	global $filecheck, $fileexecute;
	if (file_exists($filecheck))
	{
		@unlink($filecheck);
	}
	if (file_exists($fileexecute))
	{
		@unlink($fileexecute);
	}
}