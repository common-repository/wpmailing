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

global $mailing_groups_desc, $mailing_info, $msg;

  if(!empty($msg))  
    print '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>'."\n";
?>
<div class="wrap">
  <h2><?php _e('User Groups', 'mailing'); ?></h2>
  <table class="widefat">
  <tbody>
    <tr class="thead">
      <th style="text-align:left; width:15%"><?php _e('Group name', 'mailing'); ?></th>
      <th style="text-align:center; width:10%"><?php _e('Contacts', 'mailing'); ?></th>
      <th style="text-align:left; width:48%"><?php _e('Group description', 'mailing'); ?></th>
      <th style="text-align:center; width:27%" colspan="3"><?php _e('Actions', 'mailing'); ?></th>
    </tr>
  <?php  
  $grps = preg_split("/\s*[,;\-]\s*/", mailing_get_groups());
  if(gettype($grps) == 'array') :
  for($i = 0; $i < count($grps); $i++) :
    if(empty($grps[$i])) continue;
  $fad = $grps[$i] == $_POST['group_name'] ? ' class="mailingfade"' : '';
  ?>
    <tr<?php if($i % 2 == 0) print ' class="alternate"'; ?>>
      <td style="text-align:left"<?php print $fad; ?>>
      <?php print $grps[$i]; ?></td>
      <td style="text-align:center"<?php print $fad; ?>><?php 
      print mailing_count_group($grps[$i]); ?></td>
      <td style="text-align:left"<?php print $fad; ?>><?php 
        if(empty($mailing_groups_desc[$grps[$i]])) {
        print '<span class="disab">'.__('Not set', 'mailing').'</span>';
        } else {
          if(strlen($mailing_groups_desc[$grps[$i]]) > 40) 
            print substr($mailing_groups_desc[$grps[$i]], 0, 37)."... (".
                  strlen($mailing_groups_desc[$grps[$i]])." ".__('characters', 'mailing').")";
          else print $mailing_groups_desc[$grps[$i]];
        }
      ?></td>
      <td style="text-align:center"<?php print $fad; ?>>
      <a href="javascript://" onclick="if(confirm('<?php 
      print __('Delete this group?', 'mailing').'\n'.
            __('OK to continue, Cancel to abort.', 'mailing'); ?>')) deleteGroup('<?php 
      print $grps[$i]; ?>')" class="delete"><?php _e('delete', 'mailing'); ?></a>
      </td>
      <td style="text-align:center"<?php print $fad; ?>>
      <a href="javascript://" onclick="editGroup('<?php 
      print $grps[$i]; ?>')" class="edit"><?php _e('Edit', 'mailing'); ?></a>
      </td>
      <td style="text-align:center"<?php print $fad; ?>>
      <a href="javascript://" onclick="emailGroup('<?php 
      print $grps[$i]; ?>')" class="edit"><?php _e('Send mail', 'mailing'); ?></a>
      </td>
    </tr>
  <?php 
  endfor;
	else :
  ?><tr><td colspan="7" class="disab"><?php _e('There are no groups yet', 'mailing'); ?></td></tr><?php 
  endif;
  ?>
  </tbody>
  </table>
<form name="mailing_form" id="mailing_form" method="post" action="<?php 
print $PHP_SELF; ?>">
<input type="hidden" name="group_name" id="group_name_fly" />
<input type="hidden" name="mailing_action" id="mailing_action_fly" />
</form>
</div>
<div class="wrap" id="updgrp" style="display:none;">
<form name="mailing_edit_form" id="mailing_edit_form" method="post" action="<?php 
print $PHP_SELF; ?>" onsubmit="if(this.group_desc.value == '<?php 
_e('Not set', 'mailing'); ?>') this.group_desc.value = '';">
  <h2><?php _e('Update group', 'mailing'); ?> :: <span id="grpname"></span></h2>
  <div class="lab"><?php _e('Name', 'mailing'); ?>:</div>
  <div class="cont">
  <input type="text" style="width:60%;" name="group_new_name" id="group_new_name" />
  </div>
  <div class="lab"><?php _e('Description', 'mailing'); ?>:</div>
  <div class="cont">
  <textarea style="width:60%; height:3em;" name="group_desc" id="group_desc"></textarea>
  </div>
  <p class="submit">
  <input type="submit" value="<?php _e('Update group', 'mailing'); ?> &raquo;" />
  </p>
<input type="hidden" name="group_name" id="group_name" />
<input type="hidden" name="mailing_action" id="mailing_action" value="update_group" />
</form>
</div>
