<?php  if (!defined('_VALID_BBC')) exit('No direct script access allowed');

// CATEGORY PARENT
$q = "SELECT `id`, `par_id`, `title` FROM `bbc_content_cat` AS c
LEFT JOIN `bbc_content_cat_text` AS t ON (t.`cat_id`=c.`id` AND t.`lang_id`=".lang_id().")
WHERE `type_id`=$type_id ORDER BY `par_id`, `title`";

$r_cat    = array_path($db->getAll($q), 0, '>', '', '--');
$cat_size = count($r_cat);
if ($cat_size > 10)
{
	$cat_size = 10;
}
$r_group    = $db->getAll("SELECT `id`, `name` FROM `bbc_user_group` WHERE `is_admin`=0 ORDER BY `score`, `name`, `id` ASC");
$group_size = count($r_group);
if ($group_size > 10)
{
	$group_size = 10;
}

$data['protect']    = (empty($data['privilege']) || $data['privilege'] == ',all,') ? '0' : '1';
$data['privilege']  = empty($data['privilege']) ? array() : repairExplode($data['privilege']);
$data['image_size'] = '';
if (is_file($Content->img_path.'p_'.$data['image']))
{
	$r = @getimagesize($Content->img_path.'p_'.$data['image']);
	if (!empty($r))
	{
		$data['image_size'] = ' ('.$r[0].'x'.$r[1].' pixel)';
	}
}

/* START IMAGES CONFIG */
$tmp_dir = date('YmdHis');
$path    = 'images/modules/content/'.$content_id.'/';
$temp    = 'content/'.$tmp_dir;
$params  = array(
	'path' => array(
		'folder' => $path,
		'tmp'    => $temp,
		),
	'ext'       => _class('images')->valid,
	'resize'    => get_config('content','manage', 'image_size'),
	'thumbnail' => array(
		'size'   => $data['config']['thumbsize'],
		'prefix' => 'thumb_',
		'is_dir' => 0,
		),
	'folder' => _ROOT.$path,
	'expire' => strtotime('+3 HOUR')
	);
if (config('manage', 'image_watermark') == '1')
{
	$params['watermark'] = array(
		'wm_overlay_path' => _ROOT.dirname($path).'/'.config('manage', 'image_watermark_file'),
		'wm_position'     => config('manage', 'image_watermark_position')
		);
}
$imgs = (encode(json_encode($params)));
$temp = _URL.'images/tmp/'.$temp.'/';
$exts = array();
$rext = content_ext();
foreach ($rext as $val => $r1)
{
	foreach ($r1 as $key)
	{
		$exts[$key] = $val;
	}
}
/* HAPUS CONTENT CACHE YG LEBIH DARI 3 JAM */
_func('path');
$dir_del = _CACHE.'content/';
$r = path_list($dir_del);
if (!empty($r))
{
	sort($r);
	$timelast = date('YmdHis', strtotime('-3 HOUR'));
	$lastdir  = $r[count($r)-1];
	if ($timelast > $lastdir)
	{
		path_delete($dir_del);
	}
}else{
	path_delete($dir_del);
}
include_once _ROOT.'modules/content/constants.php';
?>
<script type="text/javascript">
	var _VIDEO_EMBED = '<?php echo _VIDEO_EMBED; ?>';
	var _VIDEO_IMAGE = '<?php echo _VIDEO_IMAGE; ?>';
	var _AUDIO_EMBED = '<?php echo _AUDIO_EMBED; ?>';
	var _AUDIO_IMAGE = '<?php echo _AUDIO_IMAGE; ?>';
	var _EXTENSIONS = <?php echo json_encode($exts); ?>;
	var content_id = <?php echo $content_id; ?>;
</script>
<style type="text/css">
.modal-title {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
</style>
<div class="form-group">
	<input type="hidden" name="tmp_dir" value="<?php echo $tmp_dir; ?>" />
	<div class="input-group">
		<select name="cat_ids[]" multiple="multiple" id="cat_ids" size="<?php echo $cat_size; ?>" class="form-control">
			<?php echo createOption($r_cat, $data['cat_ids']);?>
		</select>
		<div class="input-group-addon">
			<input onclick="var v=$(this).parent().prev().get(0);for(i=0; i < v.options.length; i++)v.options[i].selected=this.checked;" type="checkbox">
		</div>
	</div>
	<div class="help-block">press Ctrl / CMD + click to select multiple category</div>
</div>
<div class="form-group">
	<div class="btn-group btn-group-justified" data-toggle="buttons" style="width: 100%;">
	  <label class="btn btn-default btn-secondary">
	    <input type="radio" name="kind_id" value="0" <?php echo is_checked($data['kind_id'], '0'); ?> class="content_kind" autocomplete="off" title="Content article"> <?php echo icon('fa-newspaper-o'); ?>
	  </label>
	  <label class="btn btn-default btn-secondary">
	    <input type="radio" name="kind_id" value="1" <?php echo is_checked($data['kind_id'], '1'); ?> class="content_kind" autocomplete="off" title="Gallery multiple images"> <?php echo icon('fa-file-picture-o'); ?>
	  </label>
	  <label class="btn btn-default btn-secondary">
	    <input type="radio" name="kind_id" value="2" <?php echo is_checked($data['kind_id'], '2'); ?> class="content_kind" autocomplete="off" title="Upload file for download"> <?php echo icon('fa-upload'); ?>
	  </label>
	  <label class="btn btn-default btn-secondary">
	    <input type="radio" name="kind_id" value="3" <?php echo is_checked($data['kind_id'], '3'); ?> class="content_kind" autocomplete="off" title="Video from youtube.com"> <?php echo icon('fa-file-video-o'); ?>
	  </label>
	  <label class="btn btn-default btn-secondary">
	    <input type="radio" name="kind_id" value="4" <?php echo is_checked($data['kind_id'], '4'); ?> class="content_kind" autocomplete="off" title="Audio from soundcloud.com"> <?php echo icon('fa-file-audio-o'); ?>
	  </label>
	</div>
</div>
<div id="content_kind1" class="content_kind_block">
	<div class="file-uploader"
		title="Add or Drop multiple images here"
		placeholder="Add or Drop multiple images here"
		data-name="images"
		data-path="<?php echo $temp; ?>"
		data-params="<?php echo $imgs; ?>"
		data-title data-description>
		<noscript> <p>Please enable JavaScript to use file uploader.</p> </noscript>
		<div class="hidden">
			<?php
			link_js(_PEA_URL.'includes/FormMultifile.js');
			link_css(_PEA_URL.'includes/FormMultifile.css');
			$images = config_decode($data['images'], 1);
			foreach ((array)$images as $image)
			{
				echo '<img src="'._URL.$path.$image['image'].'" data-title="'.htmlentities($image['title']).'" data-description="'.htmlentities($image['description']).'" />';
			}
			?>
		</div>
	</div>
</div>
<div id="content_kind2" class="content_kind_block">
	<?php
	$tmp = $params;
	unset($tmp['resize'], $tmp['thumbnail']);
	$tmp['ext'] = array_keys($exts);
	$file_style = ' style="display: none;"';
	$file_name  = '';
	if (!empty($data['file']))
	{
		if (_class('images')->exists(_ROOT.$path.$data['file']))
		{
			$file_style = '';
			$file_name  = icon('fa-file-o').' '.$data['file'];
			if (preg_match('~\.([a-z]+)$~', $data['file'], $m))
			{
				if (!empty($exts[$m[1]]))
				{
					$file_name  = icon('fa-file-'.$exts[$m[1]].'-o').' '.$data['file'];
				}
			}
			$file_name .= ' ('.file_size(_ROOT.$path.$data['file']).')';
			$file_name = '<a href="index.php?mod=content.content_downloader&id='.$content_id.'&act=file">'.$file_name.'</a>';
		}
	}
	?>
	<div class="form-group">
		<label>Upload file for download</label><br />
		<div id="file_current">
			<h4<?php echo $file_style; ?>><?php echo $file_name; ?></h4>
			<div class="progress" style="display: none;">
				<div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
					<span class="sr-only"><span class="val">40</span>% Complete (success)</span>
				</div>
			</div>
			<input type="hidden" name="file" id="file_text" value="<?php echo @$data['file']; ?>" />
		</div>
		<div class="input-group">
      <div class="input-group-btn">
      	<input type="hidden" value="<?php echo $data['file_type']; ?>" id="file_type_hidden" name="file_type" />
        <button type="button" value="<?php echo $data['file_type']; ?>" id="file_type" class="btn btn-default btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Type <span class="caret"></span></button>
        <ul class="dropdown-menu dropdown-menu-left">
          <li><a href="#0"<?php echo is_checked($data['file_type'], '0') ?>>Upload</a></li>
          <li><a href="#1"<?php echo is_checked($data['file_type'], '1') ?>>URL</a></li>
        </ul>
      </div>
      <input type="file" id="file_type0" class="form-control" name="file" data-path="<?php echo $temp; ?>" data-params="<?php echo (encode(json_encode($tmp))); ?>" placeholder="Upload file for download" />
      <input type="text" id="file_type1" class="form-control" name="file_url" value="<?php echo @$data['file_url']; ?>" placeholder="Insert Download URL" />
      <div class="input-group-btn">
      	<input type="hidden" value="<?php echo @$data['file_format']; ?>" name="file_format" />
        <button type="button" value="<?php echo @$data['file_format']; ?>" class="btn btn-default btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Extension <span class="caret"></span></button>
        <ul class="dropdown-menu dropdown-menu-right">
        	<?php
        	$formats = array_keys($rext);
        	foreach ($formats as $format)
        	{
        		$title = $format;
        		$icon  = 'fa-file';
        		$act   = $format == @$data['file_format'] ? ' checked' : '';
        		if (!empty($format))
        		{
        			$icon .= '-';
        		}else{
        			$title = 'format';
        		}
        		echo '<li><a href="#'.$format.'"'.$act.'>'.icon($icon.$format.'-o').' '.$title.'</a></li>';
        	}
        	?>
        </ul>
      </div>
    </div>
    <div class="form-group checkbox">
    	<label><input type="checkbox" name="file_register" id="file_register" value="1"<?php echo is_checked(@$data['file_register']); ?> /> User must register to download</label>
    	<?php
    	if (!empty($file_name))
    	{
    		?>
	    	<a href="#" class="pull-right" data-toggle="modal" data-target="#downloader"><?php echo icon('fa-users') ?> view report</a>
				<div class="modal fade" id="downloader" tabindex="-1" role="dialog" aria-labelledby="downloader_label" aria-hidden="true">
				  <div class="modal-dialog modal-lg">
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				        <h4 class="modal-title" id="downloader_label"><?php echo $file_name; ?></h4>
				      </div>
				      <div class="modal-body">
				      	<?php
				      	_func('date');
				      	$location = array('local file', 'tirth party file');
				      	echo table(
				      		array(
				      			'Format'        => $data['file_format'],
				      			'Location'      => $location[$data['file_type']],
				      			'Uploaded'      => content_date($data['created']),
				      			'Downloaded'    => items($data['file_hit'], 'time'),
				      			'Last Download' => timespan($data['file_hit_time']),
				      			)
				      		);
				      	if (!empty($data['file_register']) && $data['file_hit'] > 1)
				      	{
				      		?>
							    <table class="table table-striped table-bordered table-hover table-responsive">
							      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Time</th></tr></thead>
							      <tbody class="registrantlist"></tbody>
							      <tfoot>
							        <tr>
							          <td colspan="5">
							            <a href="#" class="btn btn-default btn-secondary onload">
							              <span class="glyphicon glyphicon-chevron-down"></span>
							              Load more
							            </a>
							            <a href="index.php?mod=content.content_downloader&id=<?php echo $content_id; ?>" class="btn btn-default btn-secondary">
							              <?php echo icon('fa-file-excel-o'); ?>
							              Download Registrant
							            </a>
							          </td>
							        </tr>
							      </tfoot>
							    </table>
				      		<?php
				      	}
				      	?>
				      </div>
				    </div>
				  </div>
				</div>
    		<?php
    	}
    	?>
    </div>
	</div>
</div>
<div id="content_kind3" class="content_kind_block">
	<div class="form-group">
		<label>Insert Youtube URL / Youtube Code</label>
		<div class="input-group">
			<input type="text" name="video" id="video" value="<?php echo @$data['video'];	?>" class="form-control"
			placeholder="Insert URL from youtube.com or Youtube Code" />
			<div id="video_icon" class="input-group-addon" style="cursor: pointer;" title="Play Video">
				<?php echo icon('play', 'Play Video') ?>
			</div>
		</div>
		<div class="help-block">Click enter after <a href="#" data-toggle="modal" data-target="#video_url">inserting your URL</a> from <a href="http://youtube.com/" target="_blank">youtube.com</a></div>
		<div class="modal fade" id="video_url" tabindex="-1" role="dialog" aria-labelledby="video_url_label" aria-hidden="true">
		  <div class="modal-dialog modal-lg">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		        <h4 class="modal-title" id="video_url_label">How to get youtube.com's code</h4>
		      </div>
		      <div class="modal-body">
		      	<h3 class="text-danger">Open your video in youtube and then :</h3>
		      	<img src="http://fisip.net/images/howto_video.png" class="img img-responsive img-fluid" />
		      </div>
		    </div>
		  </div>
		</div>
		<div class="modal fade" id="video_play" tabindex="-1" role="dialog" aria-labelledby="video_play_label" aria-hidden="true">
		  <div class="modal-dialog modal-lg">
		    <div class="modal-content">
		      <div class="modal-body"></div>
		    </div>
		  </div>
		</div>
	</div>
</div>
<div id="content_kind4" class="content_kind_block">
	<div class="form-group">
		<label>Insert SoundCloud Code</label>
		<div class="input-group">
			<input type="text" name="audio" id="audio" value="<?php echo @$data['audio'];	?>" class="form-control"
			placeholder="Insert your code from soundcloud.com" />
			<div class="input-group-addon" id="audio_icon" style="cursor: pointer;" title="Play Sound">
				<?php echo icon('play', 'Play Sound') ?>
			</div>
		</div>
		<div class="help-block">Upload your audio file to <a href="http://soundcloud.com/" target="_blank">soundcloud.com</a> copy the code (<a href="#" data-toggle="modal" data-target="#audio_code">code example</a>) place into the input text above and then press enter</div>
		<div class="modal fade" id="audio_code" tabindex="-1" role="dialog" aria-labelledby="audio_code_label" aria-hidden="true">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		        <h4 class="modal-title" id="audio_code_label">How to get soundcloud.com's code</h4>
		      </div>
		      <div class="modal-body">
		      	<h3 class="text-danger">After click share button, go to "Embed" tab</h3>
		      	<img src="http://fisip.net/images/howto_audio.png" class="img img-responsive img-fluid" />
		      </div>
		    </div>
		  </div>
		</div>
		<div class="modal fade" id="audio_play" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Play soundcloud.com Audio</h4>
					</div>
					<div class="modal-body" id="audio_play_body">
						<iframe width="100%" height="450" scrolling="no" frameborder="no" data-src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/{audio_code}&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true"></iframe>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="content_kind0" class="content_kind_block">
	<?php
	$src = content_src($data['image'], false, true);
	if (empty($src))
	{
		$src = _URL.'images/modules/content/none.gif';
	}else{
		if(!empty($_POST['submit_update']))
		{
			$src .= '?t='.time();
		}
	}
	?>
	<div class="form-group">
		<div id="image_thumbnail">
			<center>
				<a href="#" data-toggle="modal" data-target="#img_popup">
					<img src="<?php echo $src; ?>" class="img img-thumbnail" />
				</a>
			</center>
			<div class="modal fade" id="img_popup" tabindex="-1" role="dialog" aria-labelledby="img_popup_label" aria-hidden="true">
			  <div class="modal-dialog">
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			        <h4 class="modal-title" id="img_popup_label"><?php echo $data['image'].$data['image_size']; ?></h4>
			      </div>
			      <div class="modal-body">
			      	<center><img src="<?php echo $src; ?>" class="img img-responsive img-fluid" /></center>
			      	<br />
			      </div>
			    </div>
			  </div>
			</div>
		</div>
		<div id="image_loading" style="display: none;">
			<div class="progress">
				<div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
					<span class="sr-only"><span class="val">40</span>% Complete (success)</span>
				</div>
			</div>
		</div>
		<div class="input-group" id="image_input">
			<input type="hidden" name="image_text" id="image_input_text" />
			<input type="file"
				id="image_input_file"
				name="image"
				class="form-control"
				placeholder="Upload file..."
				data-path="<?php echo $temp; ?>"
				data-params="<?php echo $imgs; ?>" />
			<span class="input-group-btn" id="image_input_browse">
				<button class="btn btn-default btn-secondary" type="button">Browse server!</button>
			</span>
		</div>
		<textarea class="form-control" name="caption" placeholder="image caption..." rows="1"><?php echo @$data['caption']; ?></textarea>
	</div>
</div>
<div class="form-group checkbox">
	<label><input type="checkbox" name="is_popimage" value="1" <?php echo is_checked($data['is_popimage']);?>> Show image in detail view</label>
</div>
<?php
if(!config('frontpage','auto'))
{
	?>
	<div class="form-group checkbox">
		<label><input type="checkbox" name="is_front" value="1" <?php echo is_checked($data['is_front']);?>> Show in frontpage</label>
	</div>
	<?php
}
if (config('manage','webtype') == '1' && empty($data['prune']))
{
	$r = $db->getAll("SELECT * FROM bbc_content_schedule WHERE content_id={$content_id} ORDER BY action_time ASC");
	$c = !empty($r) ? '' : ' style="display: none;"';
	link_js(_URL.'templates/admin/bootstrap/js/datetimepicker.js');
	link_css(_URL.'templates/admin/bootstrap/css/datetimepicker.css', false);
	?>
	<div class="form-group checkbox publish_checkbox">
		<label><input type="checkbox" name="publish" value="1" <?php echo is_checked($data['publish']);?>> Publish</label>
		<a href="#" id="schedule" class="pull-right"><span class="glyphicon glyphicon-calendar"></span> Add Schedule</a>
	</div>
	<div class="form-group publish_schedule"<?php echo $c; ?>>
		<?php
		foreach ($r as $s)
		{
			switch ($s['action'])
			{
				case '1':
					$txt = 'Publish';
					break;
				case '2':
					$txt = 'Unpublish';
					break;
				case '3':
					$txt = 'Delete';
					break;
				default:
					$txt = 'Action';
					break;
			}
			?>
			<div class="input-group">
	      <div class="input-group-btn">
	        <button type="button" class="btn btn-default btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $txt; ?> <span class="caret"></span></button>
	        <input type="hidden" name="schedule[action][]" value="<?php echo $s['action']; ?>"/>
	        <ul class="dropdown-menu">
	          <li><a href="#" rel="1" onclick="return schedule_action(this);">Publish</a></li>
	          <li><a href="#" rel="2" onclick="return schedule_action(this);">Unpublish</a></li>
	          <li><a href="#" rel="3" onclick="return schedule_action(this);">Delete</a></li>
	        </ul>
	      </div>
	      <input type="datetime" name="schedule[action_time][]" value="<?php echo $s['action_time']; ?>" class="form-control" placeholder="Schedule time" />
	      <span class="input-group-addon">
	      	<a  href="#" onclick="return schedule_del(this);"><span class="glyphicon glyphicon-trash"></span></a>
	      </span>
	      <input type="hidden" name="schedule[id][]" value="<?php echo $s['id']; ?>" />
	    </div>
			<?php
		}
		?>
	</div>
	<script type="text/template" id="schedule_tpl">
		<div class="input-group">
      <div class="input-group-btn">
        <button type="button" class="btn btn-default btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action <span class="caret"></span></button>
        <input type="hidden" name="schedule[action][]" value="0"/>
        <ul class="dropdown-menu">
          <li><a href="#" rel="1" onclick="return schedule_action(this);">Publish</a></li>
          <li><a href="#" rel="2" onclick="return schedule_action(this);">Unpublish</a></li>
          <li><a href="#" rel="3" onclick="return schedule_action(this);">Delete</a></li>
        </ul>
      </div>
      <input type="datetime" name="schedule[action_time][]" class="form-control" placeholder="Schedule time" />
      <span class="input-group-addon">
      	<a  href="#" onclick="return schedule_del(this);"><span class="glyphicon glyphicon-trash"></span></a>
      </span>
      <input type="hidden" name="schedule[id][]" value="0" />
    </div>
	</script>
	<?php
}else{
	if (empty($data['prune']))
	{
		?>
		<div class="form-group checkbox">
			<label><input type="checkbox" name="publish" value="1" <?php echo is_checked($data['publish']);?> /> Publish</label>
		</div>
		<?php
	}else{
		?>
		<div class="form-group">
			<label>Archive content is always published</label>
			<div class="input-group">
				<a href="index.php?mod=content.delete_pruned&id=<?php echo $content_id; ?>&return=<?php echo @urlencode($_GET['return']); ?>" id="delete_archive">click here</a> to delete this content due to unpublishable
				<input type="hidden" name="publish" value="1" />
			</div>
		</div>
		<?php
	}
}
?>
<div class="form-group checkbox">
	<label><input type="checkbox" name="protect" id="protect" value="1" <?php echo is_checked($data['protect']);?> /> Protect this content from non login users</label>
</div>
<div class="form-group" id="privilege_input">
	<div class="input-group">
		<select name="privilege[]" multiple="multiple" id="privilege" size="<?php echo $group_size; ?>" class="form-control">
			<?php echo createOption($r_group, $data['privilege']);?>
		</select>
		<div class="input-group-addon">
			<input onclick="var v=$(this).parent().prev().get(0);for(i=0; i < v.options.length; i++)v.options[i].selected=this.checked;" type="checkbox">
		</div>
	</div>
	<div class="help-block">select which <a href="index.php?mod=_cpanel.group" rel="admin_link">user groups</a> are allowed to open this content, press Ctrl / CMD + click to select multiple user groups</div>
</div>
<?php
$c = config('manage');
if (!empty($c['is_nested']))
{
	link_js(_PEA_URL.'includes/FormTags.js', false);
	$token = array(
		'table'  => 'bbc_content_text',
		'field'  => 'CONCAT(title, " (", content_id, ")")',
		'id'     => 'content_id',
		'format' => 'CONCAT(title, " (", content_id, ")")',
		'sql'    => 'lang_id='.lang_id(),
		'expire' => $params['expire'],
		);
	?>
	<div class="form-group">
		<label>Parent Content</label>
		<input type="text" name="par_id" rel="ac" value="<?php echo $data['par_id'];?>" class="form-control"
		placeholder="Insert title for parent content" data-token="<?php echo encode(json_encode($token)); ?>" />
		<div class="help-block">Please select which content you want to use as its parent content</div>
	</div>
	<?php
}else{
	link_js(_PEA_URL.'includes/FormTags.js', false);
	$r_ids = ($form_act == 'edit') ? $db->getAssoc("SELECT `id`, `related_id` FROM `bbc_content_related` WHERE `content_id`={$content_id}") : array();
	$r     = array_values($r_ids);
	if ($r!=array_unique($r))
	{
		$new_r = array();
		foreach ($r_ids as $i => $j)
		{
			if (!in_array($j, $new_r))
			{
				$new_r[] = $j;
			}else{
				$db->Execute("DELETE FROM `bbc_content_related` WHERE `id`={$i}");
			}
		}
		$r_ids = $new_r;
	}
	$max_content = intval(config('rules', 'content_max'));
	$tot_content = $db->getOne("SELECT COUNT(1) FROM `bbc_content` WHERE 1")+1;
	if ($tot_content > $max_content)
	{
		?>
		<div class="form-group">
			<label>Related Content</label>
			<div class="input-group">
				<input type="text" name="content_related" id="related_content" value="<?php echo implode(',', $r_ids);	?>" class="form-control"
				placeholder="Insert Content IDs separate by comma or space" />
				<div class="input-group-addon" id="related_icon" style="cursor: pointer;">
					<?php echo icon('play') ?>
				</div>
			</div>
			<div class="help-block">This is where you can insert ids of related contents and separate them by space or comma.</div>
			<div id="related_preview"></div>
			<input type="hidden" name="par_id" value="<?php echo $data['par_id'];?>">
		</div>
		<?php
	}else{
		$elink = 'index.php?mod=content.content_edit&id=';
		$token = encode(
			json_encode(
				array(
					'table'  => 'bbc_content_text',
					'field'  => 'title',
					'id'     => 'content_id',
					'format' => 'CONCAT(title, " (", content_id, ")")',
					'sql'    => 'content_id!='.$content_id,
					'expire' => $params['expire']
					)
				)
			);
		?>
		<div class="form-group">
			<label>Related Content</label>
			<div class="form-control tags">
				<span>
					<?php
					if (!empty($content_id))
					{
						$r = array();
						if (!empty($r_ids))
						{
							$r = $db->getAssoc("SELECT `content_id`, `title` FROM `bbc_content_text` WHERE `content_id` IN (".implode(',', $r_ids).")");
						}
						foreach ($r_ids as $i)
						{
							?>
							<span>
								<span class="glyphicon glyphicon-remove-circle"></span>
								<a href="<?php echo $elink.$i; ?>"><?php echo $r[$i]; ?> (<?php echo $i; ?>)</a>
								<input type="hidden" name="content_related[]" value="<?php echo $i; ?>" />
							</span>
							<?php
						}
					}
					?>
				</span>
				<span data-token="<?php echo $token; ?>" data-href="<?php echo $elink; ?>" name="content_related" contenteditable></span>
			</div>
			<div class="help-block">Insert content title which is related to this current content, you can press Enter or Tab to add the content into this field</div>
			<input type="hidden" name="par_id" value="<?php echo $data['par_id'];?>">
		</div>
		<?php
	}
}
if (config('manage','webtype') == '1')
{
	$token = encode(json_encode(array('table'=>'bbc_content_tag', 'expire'=>$params['expire'])));
	$elink = 'index.php?mod=content.tag_detail&id=';
	link_js(_PEA_URL.'includes/FormTags.js', false);
	?>
	<div class="form-group">
		<label>Content Tags</label>
		<div class="form-control tags">
			<span>
				<?php
				if (!empty($content_id))
				{
					$q = "SELECT t.`id`, t.`title` FROM `bbc_content_tag_list` AS l
					LEFT JOIN `bbc_content_tag` AS t ON (l.`tag_id`=t.`id`)
					WHERE l.`content_id`=".$content_id;
					$r = $db->getAll($q);
					foreach ($r as $t)
					{
						?>
						<span>
							<span class="glyphicon glyphicon-remove-circle"></span>
							<a href="<?php echo $elink.$t['id']; ?>"><?php echo $t['title']; ?></a>
							<input type="hidden" name="tags_ids[]" value="<?php echo $t['id']; ?>" />
						</span>
						<?php
					}
				}
				?>
			</span>
			<span data-token="<?php echo $token; ?>" data-isallowednew="1" data-href="<?php echo $elink; ?>" name="tags_ids" contenteditable></span>
		</div>
		<div class="help-block">Content Tags will be displayed on related content if exists, and it will be displayed after current related content (field above)</div>
	</div>
	<div class="form-group">
		<label>Author Name</label>
		<input type="text" class="form-control" name="created_by_alias" value="<?php echo $data['created_by_alias']; ?>" />
	</div>
	<?php
}
if($form_act == 'edit')
{
	?>
	<table class="table table-striped table-bordered">
		<tbody>
			<tr>
				<td colspan=2>
					<?php
					$r2 = explode(',', $data['rating']);
					$ratt_tot = array_sum($r2);
					if($ratt_tot)
					{
						$text = array('Poor', 'Not bad', 'Good', 'Great', 'Awesome');
						$rat  = array();
						for($i=0;$i < 5;$i++)
						{
							if(isset($r2[$i]) && is_numeric($r2[$i]))
							{
								$d = $r2[$i] * 100 / $ratt_tot;
								$rat[$i] = round($d, 2);
							}else $rat[$i] = 0;
						}
					?>
						<table class="table table-bordered">
							<tr style="height:100px;">
								<td style="vertical-align: bottom;"><div style="background-color: #354;height: <?php echo $rat[0];?>px;" title="<?php echo $rat[0];?> %"></div></td>
								<td style="vertical-align: bottom;"><div style="background-color: #354;height: <?php echo $rat[1];?>px;" title="<?php echo $rat[1];?> %"></div></td>
								<td style="vertical-align: bottom;"><div style="background-color: #354;height: <?php echo $rat[2];?>px;" title="<?php echo $rat[2];?> %"></div></td>
								<td style="vertical-align: bottom;"><div style="background-color: #354;height: <?php echo $rat[3];?>px;" title="<?php echo $rat[3];?> %"></div></td>
								<td style="vertical-align: bottom;"><div style="background-color: #354;height: <?php echo $rat[4];?>px;" title="<?php echo $rat[4];?> %"></div></td>
							</tr>
							<tr>
								<td><?php echo $text[0];?></td>
								<td><?php echo $text[1];?></td>
								<td><?php echo $text[2];?></td>
								<td><?php echo $text[3];?></td>
								<td><?php echo $text[4];?></td>
							</tr>
							<tr>
								<td colspan=5>
									<?php echo rating($data['rating']); ?>
								</td>
							</tr>
						</table>
					<?php
					}else{
						echo '<center>No Vote...</center>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td>Hits</td>
				<td><?php echo items($data['hits'], 'time');?> [ <?php echo date('M, dS Y', strtotime($data['last_hits']));?>]</td>
			</tr>
			<tr>
				<td>Revised</td>
				<td><?php echo items($data['revised'], 'time');?></td>
			</tr>
			<tr>
				<td>Created</td>
				<td><?php echo date('M, dS Y', strtotime($data['created']));?> by <?php echo user_name($data['created_by']);?></td>
			</tr>
			<tr>
				<td>Modified</td>
				<td><?php echo date('M, dS Y', @strtotime($data['modified']));?> by <?php echo user_name($data['modified_by']);?></td>
			</tr>
			<tr>
				<td>Link</td>
				<td><a href="<?php echo _URL.'id.htm/'.$content_id; ?>" target="external">index.php?mod=content.detail&id=<?php echo $content_id;?> <?php echo icon('fa-external-link'); ?></a></td>
			</tr>
		</tbody>
	</table>
	<?php
}
