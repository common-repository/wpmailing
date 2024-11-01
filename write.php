<?php 

/**
 * WPMailing plugin for WordPress 2.x
 * ----------------------------------
 * This file is part of WPMailing 1.0
 * ----------------------------------
 * Author: Cau Guanabara
 * Website: http://cauguanabara.jsbrasil.com
 * ----------------------------------
 */

global $msg, $mailing_info, $mailing_obj, $mailing_lost_attachments, $mailing_unavailable, 
       $mailing_sent_info, $xinha4wp_loaded, $deans_fckeditor_loaded, $deans_fckeditor; 

  if($mailing_unavailable) 
    $msg = __('The PHP function to send e-mails is disabled in this server.', 'mailing');

  if($mailing_lost_attachments) {
    $msg = __('There are some lost files, attached to an e-mail that was not sent.', 'mailing');
    $msg .= "<br />\n".__('Files', 'mailing').': '.join(", ", $mailing_obj->attached);
    $msg .= ' [<a href="javascript://" onclick="lostAtt()">'.
            __('Use it', 'mailing').'</a>] [<a href="javascript://" onclick="lostAtt(true)">'.
            __('Discard', 'mailing').'</a>]';
  }
  if(!empty($msg))  
    print '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>'."\n";
?>
  
<?php if(!empty($mailing_sent_info)) : ?>
  <div class="wrap">
  <h2><?php _e('Mailing sending information', 'mailing'); ?></h2>
  <div><?php print $mailing_sent_info; ?></div>
  </div>
<?php endif; ?>
  
<form name="mailing_write_form" id="mailing_write_form" action="<?php print $PHP_SELF; ?>" method="post" enctype="multipart/form-data">
  <div class="wrap">
 <h2><?php _e('Write e-mail', 'mailing'); ?></h2>
  <div id="rightpanel">
  <h4><?php _e('Options', 'mailing'); ?></h4>
  <input type="checkbox" id="mailing_addcontent" onclick="
	showHide('mailing_blog_contents', this.checked)" />
  <label for="mailing_addcontent"><?php _e('Select active content', 'mailing'); ?> </label><br />

  <input type="checkbox" onclick="showHide('mailing_att_fields', this.checked)"<?php 
  if(count($mailing_obj->attached) > 0 and !$mailing_lost_attachments) 
    print ' checked="checked" disabled="disabled"'; 
  ?> id="mailing_att_area" />
  <label for="mailing_att_area"><?php _e('Attach files', 'mailing'); ?> </label><br />

  <input type="checkbox" id="mailing_allfields" onclick="
		if(this.checked && !alertCcCco()) { this.checked = false; return false; }
	showHide('mailing_hidden_fields', this.checked)" />
  <label for="mailing_allfields"><?php _e('Show all fields', 'mailing'); ?> </label>

  <p><h4><?php _e('Your contacts', 'mailing'); ?></h4>
  <select name="mailing_users" id="mailing_users" multiple="multiple" size="<?php 
  print mailing_get_setting('mailing_dropdown_size'); 
  ?>" style="width:99%">
  <?php
  $utyp = mailing_get_setting('mailing_dropdown_type');
    foreach($mailing_info as $usr) 
      print '<option ondblclick="applyUsersTo()" value="'.$usr['username'].'">'.$usr[$utyp].'</option>';
  ?></p>
  </select><br />
  <button onclick="applyUsersTo()" style="width:99%; margin-top:3px;" class="button" type="button"><?php 
  _e('Add/remove selected', 'mailing'); ?></button>
  </div>
	<div id="leftpanel">
		
		<div id="mailing_blog_contents" style="display:none;">
		<h4><?php _e('Change active content', 'mailing'); ?></h4>
			<div class="labfull">
				<label for="selected_post"><?php _e('Your publications', 'mailing'); ?></label>:</div>
			<div class="contfull">
			<select class="inp" name="selected_post" id="selected_post" onchange="
			var s = this.options[this.selectedIndex].innerHTML;
			document.getElementById('loaded_content').innerHTML = s;">
			<optgroup label="<?php _e('Posts', 'mailing'); ?>">
			<?php
			$ddlimit = get_option('mailing_content_size');
			$pst = get_posts('numberposts='.$ddlimit);
			$firstpost = false;
			  foreach($pst as $post) {
				  if(!$firstpost) $firstpost = $post->post_title;
				?>
				<option value="<?php print $post->ID; ?>"><?php print $post->post_title; ?></option>
				<?php
				}
			?>
			</optgroup>
			
			<?php
			  if(get_option('mailing_show_pages') == 'on') {
				?><optgroup label="<?php _e('Pages', 'mailing'); ?>"><?php
					$pgs = get_pages();
						foreach($pgs as $page) {
						?>
						<option value="<?php print $page->ID; ?>"><?php print $page->post_title; ?></option>
						<?php
						}
				?></optgroup><?php
				}
			?>
			
			</select>
			</div>
			<div class="labfull"><?php _e('Selected', 'mailing'); ?>: <span id="loaded_content"><?php 
			                                        print $firstpost; ?></span></div>
    <div class="labfull clb" style="border-bottom:1px solid #CCC;"></div>
		</div>

    <div id="mailing_att_fields"<?php 
    if(count($mailing_obj->attached) < 1 or $mailing_lost_attachments) 
      print ' style="display:none;"'; ?>>
    <h4><?php _e('Attachments', 'mailing'); ?></h4>
      <div class="labfull">
      <label for="mailing_attfile"><?php _e('Attach file', 'mailing'); ?></label>:</div>
      <div class="contfull">
      <input type="file" style="width:75%" name="mailing_attfile" id="mailing_attfile" />
      <input type="button" style="width:20%" value="<?php _e('Upload file', 'mailing'); 
      ?>" class="button" onclick="this.form.mailing_sendmessage.value = 'false'; 
      this.form.submit();" />
      </div>
      <fieldset>
      <legend><?php _e('Attached files', 'mailing'); ?></legend>
      <?php 
      if(count($mailing_obj->attached) < 1 or $mailing_lost_attachments) {
      print  '<span class="disab">'.__('There are no files attached to this message', 
                                        'mailing').'</span>';
      } else {
      $attlinks = '';
        foreach($mailing_obj->attached as $i => $name) {
        print '<a href="javascript://" onclick="if(!confirm(\''.
              __('Remove this file?', 'mailing').'\')) return false; '.
              'document.mailing_write_form.'.
              'mailing_sendmessage.value=\'false\'; '.
              'document.mailing_write_form.mailing_attachment.value=\''.$name.
              '\'; document.mailing_write_form.submit()" title="'.
              __('Click to remove this file', 'mailing').'">'.$name.'</a>';
        if($i < count($mailing_obj->attached) - 1) print " | ";
        }
      }
      ?>
      </fieldset>
    <div class="labfull" style="border-bottom:1px solid #CCC;"></div>
    </div>
		
    <div class="labfull">
    <label for="mailing_from"><?php _e('From', 'mailing'); ?></label>:</div>
    <div class="contfull">
    <input type="text" class="inp" name="mailing_from" id="mailing_from" value="<?php 
      if(!empty($_POST['mailing_from'])) print $_POST['mailing_from'];
      else print mailing_get_setting('mailing_default_sender'); ?>" />
    </div>
    <div class="labfull"><label for="mailing_to"><?php _e('To', 'mailing'); ?></label>:</div>
    <div class="contfull">
    <textarea class="inp" name="mailing_to" id="mailing_to" style="height:3em; font-size:16px;"><?php 
    $toarr = array();
      if(!empty($_GET['group'])) $toarr[] = mailing_to('group', $_GET['group']); 
      if(!empty($_GET['role'])) $toarr[] = mailing_to('role', $_GET['role']); 
      if(!empty($_GET['level'])) $toarr[] = mailing_to('level', $_GET['level']); 
      
      if(!empty($_POST['mailing_to'])) print $_POST['mailing_to'];
      elseif(count($toarr) > 0) print join(", ", $toarr);
    ?></textarea>
    </div>
    <div class="contfull">
    <fieldset style="padding:4px;" class="optsfld">
    <legend><?php _e('Options', 'mailing'); ?></legend>
    <div><?php _e('Your groups', 'mailing'); ?>: 
    <?php $grps = mailing_make_to_links('applyGroupTo', mailing_get_groups(), ' | '); 
          if(empty($grps)) 
            print '<span class="disab">'.__('There are no groups yet', 'mailing').'</span>'; 
          else print "$grps"; ?></div>
    <div><?php _e('User roles', 'mailing'); ?>: 
      <?php $rls = "administrator, editor, author, contributor, subscriber";
            print mailing_make_to_links('applyRoleTo', $rls, ' | '); ?></div>
    <div><?php _e('User levels', 'mailing'); ?>:  
      <?php $lvs = "0,1,2,3,4,5,6,7,8,9,10";
            print mailing_make_to_links('applyLevelTo', $lvs, ' | '); ?></div>
    </fieldset>
    </div>
    <div id="mailing_hidden_fields" style="display:none">
    <div class="labfull">
      <label for="mailing_cc"><?php _e('Cc', 'mailing'); ?></label>:</div>
    <div class="contfull">
    <input type="text" class="inp" name="mailing_cc" id="mailing_cc" value="<?php 
    if(!empty($_POST['mailing_cc'])) print $_POST['mailing_cc'];
    ?>" />
    </div>
    <div class="labfull">
      <label for="mailing_cco"><?php _e('Cco', 'mailing'); ?></label>:</div>
    <div class="contfull">
    <input type="text" class="inp" name="mailing_cco" id="mailing_cco" value="<?php
    if(!empty($_POST['mailing_cco'])) print $_POST['mailing_cco'];
    ?>" />
    </div>
    <div class="labfull">
      <label for="mailing_replyto"><?php _e('Reply to', 'mailing'); ?></label>:</div>
    <div class="contfull">
    <input type="text" class="inp" name="mailing_replyto" id="mailing_replyto" value="<?php
    if(!empty($_POST['mailing_replyto'])) print $_POST['mailing_replyto'];
    else print mailing_get_setting('mailing_default_replyto');
    ?>" />
    </div>
    </div>
    <div class="labfull">
		  <label for="mailing_subject"><?php _e('Subject', 'mailing'); ?></label>:</div>
    <div class="contfull">
    <input type="text" class="inp" name="mailing_subject" id="mailing_subject" value="<?php 
      if(!empty($_POST['mailing_subject'])) print $_POST['mailing_subject'];
      else print mailing_get_setting('mailing_default_subject'); ?>" />
    </div>
		
    <div class="labfull">
		  <label for="mailing_content" id="contenttitle"><?php _e('Message', 'mailing'); ?>:</label></div>
    <div class="contfull">
    <?php 
    $bcont = empty($_POST['mailing_content']) ? '' : $_POST['mailing_content'];    
      if($xinha4wp_loaded) {
      $style = '#quicktags { display:none; }';
      $jsdata = get_option('wpx_static_js_posts');
        if($jsdata) wp_xinha_generate_xinha_js($jsdata, $style, 'mailing_content');
      ?><textarea name="mailing_content" id="mailing_content"><?php print $bcont; ?></textarea><?php
      } else if($deans_fckeditor_loaded) {
      ?><textarea name="mailing_content" id="content"><?php print $bcont; ?></textarea><?php
      $deans_fckeditor->load_fckeditor();
      } else { the_editor($bcont, 'mailing_content', 'contenttitle'); }
    ?>
    </div>
    <div class="butts submit">
    <input type="submit" id="sbutt" accesskey="s" value="<?php _e('Send e-mail', 'mailing'); ?> &raquo;" />
    </div>
  </div>  
<div class="clb"></div>
</div>
<input type="hidden" name="mailing_attachment">
<input type="hidden" name="mailing_sendmessage" value="true">
<input type="hidden" name="mailing_action" value="send_email" />
</form>
<?php if($mailing_unavailable) : ?>
<script type="text/javascript">
var e = document.getElementById('sbutt');
e.value = '<?php _e('Sending is disabled', 'mailing'); ?>';
e.disabled = true;
</script>
<?php endif; ?>
