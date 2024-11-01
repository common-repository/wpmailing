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

global $msg, $parent_file;

  if (!empty($msg))  
  print '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>'."\n";
?>
<div class="wrap">
<form name="mailing_opt_form" id="mailing_opt_form" action="<?php print $PHP_SELF; ?>" method="post">
	<h2><?php _e('Mailing Options', 'mailing'); ?></h2>
  <fieldset>
  <legend> <?php _e('E-mail', 'mailing'); ?>  </legend>
  <div class="lab">
    <label for="default_from"><?php _e('Default sender', 'mailing'); ?></label>:</div>
  <div class="cont"><input type="text" name="default_from" id="default_from" value="<?php
  print mailing_get_setting('mailing_default_sender', true); ?>" class="itemput" /></div>
  <div class="lab">
    <label for="default_replyto"><?php _e('Default reply address', 'mailing'); ?></label>:</div>
  <div class="cont"><input type="text" name="default_replyto" id="default_replyto" value="<?php
  print mailing_get_setting('mailing_default_replyto', true); ?>" class="itemput" /></div>
  <div class="lab">
    <label for="default_subject"><?php _e('Default subject', 'mailing'); ?></label>:</div>
  <div class="cont"><input type="text" name="default_subject" id="default_subject" value="<?php
  print mailing_get_setting('mailing_default_subject', true); ?>" class="itemput" /></div>
  <div class="lab">
    <label for="mail_encoding"><?php _e('Encoding', 'mailing'); ?></label>:</div>
  <div class="cont">
  <input type="text" name="mail_encoding" id="mail_encoding" style="width:8em;" value="<?php
  print mailing_get_setting('mailing_encoding', true); ?>" class="itemput" /><br />
  <fieldset class="bord itemput" style="margin-top:0;">
  <legend style="font-weight:normal;"><?php _e('Encoding', 'mailing'); ?></legend>
  <?php _e('Most used', 'mailing'); ?>:
  <a href="javascript://" onclick="setCharSet('UTF-8');">UTF-8</a> |
  <a href="javascript://" onclick="setCharSet('UTF-16');">UTF-16</a> |
  <a href="javascript://" onclick="setCharSet('ISO-8859-1');">ISO-8859-1</a>
  </fieldset>
  </div>
  
  </fieldset>
  <br clear="all" />
    <fieldset>
  <legend> <?php _e('Other configurations', 'mailing'); ?>
  </legend>
  <div class="lab">
    <label for="dropdown_lines"><?php _e('Add users dropdown height', 'mailing'); ?></label>:</div>
  <div class="cont"><input type="text" name="dropdown_lines" id="dropdown_lines" value="<?php
  print mailing_get_setting('mailing_dropdown_size', true); ?>" class="itemput" style="width:2em; text-align:center;" />
  <?php _e('lines', 'mailing'); ?></div>
  <div class="lab">
    <label for="dropdown_user_type"><?php _e('Show in users dropdown', 'mailing'); ?></label>:
  </div>
  <div class="cont">
  <select name="dropdown_user_type" id="dropdown_user_type">
  <option value="username"<?php $dut = get_option('mailing_dropdown_type'); 
  if($dut == 'username') print ' selected="selected"';
  ?>><?php _e('login name', 'mailing'); ?></option>
  <option value="displayname"<?php if($dut == 'displayname') print ' selected="selected"';
  ?>><?php _e('real name', 'mailing'); ?></option>
  <option value="email"<?php if($dut == 'email') print ' selected="selected"';
  ?>><?php _e('e-mail', 'mailing'); ?></option>
  </select>
  <?php _e('of the users', 'mailing'); ?>
  </div>

  <div class="lab">
    <label for="add_content_size"><?php _e('Show in add content dropdown', 'mailing'); ?></label>:</div>
  <div class="cont"><input type="text" name="add_content_size" id="add_content_size" value="<?php
  print mailing_get_setting('mailing_content_size', true); ?>" class="itemput" style="width:2em; text-align:center;" />
  <?php _e('last posts (ordered by date)', 'mailing'); ?></div>
  </fieldset>
  <div class="lab"> </div>
  <div class="cont">
	  <input type="checkbox" name="show_pages" id="show_pages" value="on"<?php 
	if(get_option('mailing_show_pages') == 'on') print ' checked="checked"'; ?> style="width:auto" />
	<label for="show_pages"><?php _e('Show pages in dropdown', 'mailing'); ?></label>
  </div>
  <div class="lab"> </div>
  <div class="cont">
	  <input type="checkbox" name="float_options_panel" id="float_options_panel" value="on"<?php 
	if(get_option('mailing_float_panel') == 'on') print ' checked="checked"'; ?> style="width:auto" />
	<label for="float_options_panel"><?php _e('Options panel always visible', 'mailing'); ?></label>
  </div>
  <div class="lab"><?php _e('When uninstalling WPMailing', 'mailing'); ?>:</div>
  <div class="cont">
	  <input type="radio" name="preserve_info" id="preserve_info0" value="on"<?php 
	if(get_option('mailing_preserve_info') == 'on') print ' checked="checked"'; ?> style="width:auto" />
	<label for="preserve_info0"><?php 
	  _e('preserve the information about my groups, in the future I can decide to install it again', 'mailing'); ?></label><br />
	  <input type="radio" name="preserve_info" id="preserve_info1" value="off"<?php 
	if(get_option('mailing_preserve_info') == 'off') print ' checked="checked"'; ?> style="width:auto" />
	<label for="preserve_info1"><?php _e('delete all stored information permanently', 'mailing'); ?></label>
  </div>
<p class="submit clb">
<input type="submit" value="<?php _e('Define settings', 'mailing'); ?> &raquo;" />
</p>
<input type="hidden" name="mailing_action" id="mailing_action" value="define_settings" />
</form>
</div>
