<?php  if (!defined('_VALID_BBC')) exit('No direct script access allowed');

link_js(tpl('posted_form.js'));
_lib('pea', 'bbc_content');
if ($id > 0)
{
	$title .= ' <a href="'.content_link($id, $data['text'][lang_id()]['title']).'" target="external" class="pull-right">preview '.icon('fa-external-link').'</a>';
}
?>
<script type="text/javascript">
	var _VIDEO_EMBED = '<?php echo _VIDEO_EMBED; ?>';
	var _VIDEO_IMAGE = '<?php echo _VIDEO_IMAGE; ?>';
	var _AUDIO_EMBED = '<?php echo _AUDIO_EMBED; ?>';
	var _AUDIO_IMAGE = '<?php echo _AUDIO_IMAGE; ?>';
	var _EXTENSIONS = <?php echo json_encode($exts); ?>;
	var content_id = <?php echo $content_id; ?>;
	var lang_ids	=	[<?php echo implode(',', array_keys($r_lang));?>];
	var lang_id		= '<?php echo lang_id();?>';
</script>
<style type="text/css">
.modal-title {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
</style>
<form method="post" action="" enctype="multipart/form-data" role="form" id="content_form">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php echo $title; ?></h3>
		</div>
		<div class="panel-body">
			<?php
			if (!empty($manage['is_nested']))
			{
				link_js(_PEA_URL.'includes/FormTags.js', false);
				$token = array(
					'table'  => 'bbc_content_text',
					'field'  => 'title',
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
			}
			$tabs_text = array();
			$is_multi = count($r_lang) > 1 ? true : false;
			foreach($r_lang AS $lang_id => $dt)
			{
				$t_title   = $is_multi ? $dt['title'] : lang('Title');
				$t_intro   = $is_multi ? $dt['title'] : lang('Intro');
				$t_content = $is_multi ? $dt['title'] : lang('Content');
				ob_start();
				?>
				<div class="form-group">
					<label><?php echo lang('Title'); ?></label>
					<input name="title[<?php echo $lang_id;?>]" type="text" value="<?php echo htmlentities(@$data['text'][$lang_id]['title']);?>" id="title_<?php echo $lang_id;?>" class="form-control" title="<?php echo $t_title;?>" placeholder="<?php echo $t_title;?>">
				</div>
				<div class="form-group">
					<label><?php echo lang('Intro'); ?></label>
					<textarea name="intro[<?php echo $lang_id; ?>]" id="text_intro_<?php echo $lang_id;?>" class="form-control"><?php echo @htmlentities($data['text'][$lang_id]['intro']); ?></textarea>
				</div>
				<div class="form-group">
					<label><?php echo lang('Content'); ?></label>
					<?php echo editor_html('content['.$lang_id.']', @$data['text'][$lang_id]['content'], array('Height'=>'500px')); ?>
				</div>
				<?php
				$tabs_text[$dt['title']] = ob_get_contents();
				ob_end_clean();
			}
			echo tabs($tabs_text, 0);
			?>
			<div class="form-group">
				<input type="hidden" name="tmp_dir" value="<?php echo $tmp_dir; ?>" />
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
						$images = json_decode($data['images'], 1);
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
					if (is_file(_ROOT.$path.$data['file']))
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
						$file_name = '<a href="'._URL.'posted_downloader.htm?id='.$content_id.'&act=file">'.$file_name.'</a>';
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
			      <input type="text" id="file_type1" class="form-control" name="file_url" placeholder="Insert Download URL" />
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
										            <a href="<?php echo _URL; ?>posted_downloader.htm/<?php echo $content_id; ?>" class="btn btn-default btn-secondary">
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
						        <h4 class="modal-title" id="img_popup_label"><?php echo $data['image']; ?></h4>
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
					<div id="image_input">
						<input type="hidden" name="image_text" id="image_input_text" />
						<input type="file"
							id="image_input_file"
							name="image"
							class="form-control"
							placeholder="Upload file..."
							data-path="<?php echo $temp; ?>"
							data-params="<?php echo $imgs; ?>" />
					</div>
					<textarea class="form-control" name="caption" placeholder="image caption..." rows="1"><?php echo @$data['caption']; ?></textarea>
				</div>
			</div>
			<div class="form-group checkbox">
				<label><input type="checkbox" name="is_popimage" value="1" <?php echo is_checked($data['is_popimage']);?>> Show image in detail view</label>
			</div>
			<?php
			if($conf['show_cat'])
			{
				_func('array');
				if (!empty($conf['cat_option']))
				{
					$conf['cat_option'] = implode(', ', $conf['cat_option']);
				}else{
					$conf['cat_option'] = "''";
				}
				$add_sql = $conf['show_cat']==2 ? " AND c.id IN (".$conf['cat_option'].")" : "";
				$q = "SELECT c.id, c.par_id, t.title FROM bbc_content_cat AS c
					LEFT JOIN bbc_content_cat_text AS t ON (t.cat_id=c.id AND t.lang_id=".lang_id().")
					WHERE c.type_id={$type_id}{$add_sql} ORDER BY c.par_id, t.title ASC";
				$r_cat    = array_path($db->getAll($q), 0, '>', '', '--');
				$cat_size = count($r_cat);
				if ($cat_size > 10)
				{
					$cat_size = 10;
				}
				?>
			<div class="form-group">
				<label><?php echo lang('Categories'); ?></label>
				<div class="input-group">
					<select name="cat_ids[]" multiple="multiple" id="cat_ids_form" size="<?php echo $cat_size; ?>" class="form-control">
						<?php echo createOption($r_cat, @$data['cat_ids']); ?>
					</select>
					<div class="input-group-addon">
						<input onclick="var v=$(this).parent().prev().get(0);for(i=0; i &lt; v.options.length; i++)v.options[i].selected=this.checked;" type="checkbox" />
					</div>
				</div>
				<div class="help-block">
					<?php echo lang('hold ctrl/cmd and click for mutiple categories'); ?>
				</div>
			</div>
				<?php
			}
			if (empty($manage['is_nested']))
			{
				$r_ids = !empty($content_id) ? $db->getAssoc("SELECT `id`, `related_id` FROM `bbc_content_related` WHERE `content_id`={$content_id}") : array();
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
				?>
				<div class="form-group">
					<label><?php echo lang('Related Content'); ?></label>
					<div class="input-group">
						<input type="text" name="content_related" id="related_content" value="<?php echo implode(',', $r_ids);	?>" class="form-control"
						placeholder="<?php echo lang('Insert Content IDs separate by comma or space'); ?>" />
						<div class="input-group-addon" id="related_icon" style="cursor: pointer;">
							<?php echo icon('play') ?>
						</div>
					</div>
					<div class="help-block"><?php echo lang('This is where you can insert ids of related contents and separate them by space or comma'); ?></div>
					<div id="related_preview"></div>
					<input type="hidden" name="par_id" value="<?php echo $data['par_id'];?>">
				</div>
				<?php
			}
			?>
		</div>
		<div class="panel-footer">
			<button type="submit" name="submit_update" value="<?php echo lang('Save'); ?>" class="btn btn-primary btn-sm">
				<span class="glyphicon glyphicon-floppy-disk"></span>
				<?php echo lang('Save'); ?>
			</button>
			<?php
			if(@$data['id'] > 0 && $conf['delete'])
			{
				?>
				<button type="submit" name="submit_delete" value="<?php echo lang('Delete Content'); ?>" class="btn btn-warning btn-sm"
					onclick='if (confirm("<?php echo lang('entry delete sure');?>")) { return true; } else { return false; }'>
					<span class="glyphicon glyphicon-floppy-disk"></span>
					<?php echo lang('Delete Content'); ?>
				</button>
				<?php
			}
			?>
		</div>
	</div>
</form>