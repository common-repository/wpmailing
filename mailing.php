<?php

/* ---------------------------------- *
 * WPMailing plugin for WordPress 2.x *
 * ---------------------------------- *
 * Start date: 2007-07-07             *
 * Licence: GPL                       *
 * ---------------------------------- */

/*
Plugin Name: WPMailing
Plugin URI: http://cauguanabara.jsbrasil.com/blog/wp-plugins/wpmailing/
Description: Create groups to organize your subscribed users and send them e-mails based on this groups or using user levels, capabilities or usernames. 
Version: 1.0
Author: Cau Guanabara
Author URI: http://cauguanabara.jsbrasil.com/
*/

$mailing_version = 1;
$mailing_stability_status = 'beta';
$mailing_uptodate = '2007-08-23';
$mailing_dir_name = preg_replace("/^.+(\\\\|\/)/", "", dirname(__FILE__));
$mailing_base_url = get_bloginfo('wpurl').'/'.PLUGINDIR.'/'.$mailing_dir_name.'/';
$mailing_base_dir = ABSPATH.PLUGINDIR.'/'.$mailing_dir_name.'/';
$mailing_tmp_dir = $mailing_base_dir.'tmp/';
$mailing_user_groups = array();
$mailing_groups_desc = array();
$mailing_info = array();
$mailing_wp_user = '';
$mailing_obj = NULL;
$mailing_lost_attachments = false;
$mailing_sent_info = '';
$mailing_unavailable = (!function_exists('mail'));
// support for editor replacement plugins
$xinha4wp_loaded = false;        // Xinha4WP
$deans_fckeditor_loaded = false; // Dean's FCKEditor

add_action('plugins_loaded', 'mailing_check_plugins');
add_action('init', 'mailing_init');
add_action('admin_head', 'mailing_add_includes');
add_action('admin_menu', 'mailing_add_pages');
add_action('user_register', 'mailing_set_user_groups');
add_action('profile_update', 'mailing_set_user_groups');
add_action('delete_user', 'mailing_unset_user_groups');
add_action('activate_'.$mailing_dir_name.'/mailing.php', 'mailing_install');
add_action('deactivate_'.$mailing_dir_name.'/mailing.php', 'mailing_uninstall');

load_plugin_textdomain('mailing', 'wp-content/plugins/'.$mailing_dir_name);


function mailing_init() { 
global $mailing_obj, $mailing_lost_attachments, $mailing_base_dir;
  if(strstr($_SERVER['REQUEST_URI'], 'page=_mailwrite')) {
  session_start();
	  if(user_can_richedit()) {
		wp_enqueue_script('tiny_mce');
		wp_enqueue_script('wp_tiny_mce');
		}
    if(!class_exists('mailing_email')) 
      include_once $mailing_base_dir.'mailing.cls.php';
  $mailing_obj = new mailing_email();
    if(count($mailing_obj->attachments) > 0 and !isset($_POST['mailing_action']))
      $mailing_lost_attachments = true;
  }
mailing_onload();
mailing_check_post();
}

function mailing_check_plugins() {
global $xinha4wp_loaded, $deans_fckeditor_loaded;
$current_plugins = get_option('active_plugins');
  if(is_array($current_plugins)) {
    foreach ($current_plugins as $plugin) {
      if(preg_match("/xinha4wp/i", $plugin) and get_option('wpx_posts_enabled')) 
        $xinha4wp_loaded = true;
      if(preg_match("/deans_fckeditor/i", $plugin)) $deans_fckeditor_loaded = true;
    }
  }
}

function mailing_handle_email() {
global $mailing_obj, $msg, $mailing_tmp_dir, $mailing_sent_info; 
 // if($mailing_obj == NULL) $mailing_obj = new mailing_email();

$sendok = ($_POST['mailing_sendmessage'] == 'true'); 
$dontsend = false;
$msg = '';
$eml = array("from" => empty($_POST['mailing_from']) ? 
                         mailing_get_setting('mailing_default_sender') : $_POST['mailing_from'], 
             "replyto" => empty($_POST['default_replyto']) ? 
                            mailing_get_setting('mailing_default_replyto') : 
                              $_POST['default_replyto'], 
             "mailing_to" => empty($_POST['mailing_to']) ? '' : $_POST['mailing_to'], "to" => '', 
             "cc" => empty($_POST['mailing_cc']) ? '' : $_POST['mailing_cc'], 
             "cco" => empty($_POST['mailing_cco']) ? '' : $_POST['mailing_cco'], 
             "subject" => empty($_POST['mailing_subject']) ? 
                            mailing_get_setting('mailing_default_subject') : 
                              $_POST['mailing_subject'], 
             "body" => empty($_POST['mailing_content']) ? '' : $_POST['mailing_content']);
$mailing_obj->set_fields($eml);
$mailing_obj->selected_content_id = $_POST['selected_post'];
$_attnames = $mailing_obj->attnames();
  if($sendok) {
    if(empty($mailing_obj->from) || empty($mailing_obj->mailing_to) || 
       empty($mailing_obj->subject) || empty($mailing_obj->body)) {
    $msg = __('The fields From, To, Subject and Body are required.', 'mailing');
    $dontsend = true;
    }
    if(!$dontsend) {
    $mailing_sent_info = $mailing_obj->mailing_send();
    } elseif(empty($msg)) 
      $msg = __('Your e-mail could not be sent, an error has occurred.', 'mailing');
  } else {
    if($_POST['mailing_sendmessage'] == 'false') {
      // attach file
      if(!empty($_FILES['mailing_attfile']) and empty($_POST['mailing_attachment'])) {  
        if(!in_array($_FILES['mailing_attfile']['name'], $_attnames)) {
          if(!$mailing_obj->attach($_FILES['mailing_attfile']))
            $msg = __('The file could not be added, an error has occurred.', 'mailing');
        } 
      }
      // detach file
      if(!empty($_POST['mailing_attachment'])) { 
        if(in_array($_POST['mailing_attachment'], $_attnames)) {
          if(!$mailing_obj->detach($_POST['mailing_attachment']))
            $msg = __('The file could not be removed, an error has occurred.', 'mailing');
        }
      }
    } elseif($_POST['mailing_sendmessage'] == 'discard_att') {
    $mailing_obj->empty_att_var();
    $msg = '';
    } elseif($_POST['mailing_sendmessage'] == 'continue') {
    $msg = '';
    }
  }
}

function mailing_check_post() { 
global $msg, $mailing_obj;
array_walk(&$_POST, 'mailing_clear_empty');
  if(empty($_POST['mailing_action']) and (empty($_POST['action']) 
     or !preg_match("/^(apply|remove)_group$/", $_POST['action']))) return;

$act = !empty($_POST['action']) ? $_POST['action'] : $_POST['mailing_action'];
  switch($act) {
  case 'define_settings':
	update_option('mailing_float_panel', empty($_POST['float_options_panel']) ? 'off' : 'on');
	update_option('mailing_show_pages', empty($_POST['show_pages']) ? 'off' : 'on');
	update_option('mailing_preserve_info', $_POST['preserve_info']);
    if(!empty($_POST['default_from'])) 
			update_option('mailing_default_sender', $_POST['default_from']);
    if(!empty($_POST['default_subject'])) 
			update_option('mailing_default_subject', $_POST['default_subject']);
    if(!empty($_POST['dropdown_lines'])) 
			update_option('mailing_dropdown_size', $_POST['dropdown_lines']);
    if(!empty($_POST['dropdown_user_type'])) 
			update_option('mailing_dropdown_type', $_POST['dropdown_user_type']);
    if(!empty($_POST['default_replyto'])) 
			update_option('mailing_default_replyto', $_POST['default_replyto']);
    if(!empty($_POST['mail_encoding'])) 
			update_option('mailing_encoding', $_POST['mail_encoding']);
    if(!empty($_POST['add_content_size'])) 
			update_option('mailing_content_size', $_POST['add_content_size']);
	$msg = __('Settings successfully defined', 'mailing');
    break;
  case 'apply_group': 
  $grp = $_POST['grpadd'];
  case 'remove_group': 
    if(!isset($grp)) $grp = $_POST['grprem']; 
  $msg = mailing_add_remove_group($act, $grp, $_POST['users']);
    break;
  case 'update_group':
  mailing_set_group_desc();
  $msg = __('Group information successfully updated', 'mailing');
    break;
  case 'delete_group':
  mailing_delete_group();
  $msg = __('Group successfully removed', 'mailing');
    break;
  case 'send_email':
  mailing_handle_email();
    break;
  }
}

function mailing_onload() { 
global $userdata, $mailing_wp_user;
mailing_get_option();
mailing_make_info();
  if(!empty($_GET['user_id'])) {
	$mailing_wp_user = mailing_login_by_id($_GET['user_id']);
  } else if(function_exists('get_currentuserinfo')) {
	get_currentuserinfo();
	$mailing_wp_user = $userdata->user_login;
	}
}

function mailing_install() {
global $mailing_tmp_dir;

  if(!is_dir($mailing_tmp_dir))                 mkdir($mailing_tmp_dir, 0777);
  if(!get_option('mailing_information'))        add_option('mailing_information', array());
  if(!get_option('mailing_groups_description')) add_option('mailing_groups_description', array());
  if(!get_option('mailing_default_sender'))     add_option('mailing_default_sender', '');
  if(!get_option('mailing_default_replyto'))    add_option('mailing_default_replyto', '');
  if(!get_option('mailing_default_subject'))    add_option('mailing_default_subject', '');
  if(!get_option('mailing_dropdown_size'))      add_option('mailing_dropdown_size', 10);
  if(!get_option('mailing_dropdown_type'))      add_option('mailing_dropdown_type', 'email');
  if(!get_option('mailing_encoding'))           add_option('mailing_encoding', 'ISO-8859-1');
  if(!get_option('mailing_content_size'))       add_option('mailing_content_size', 5);
  if(!get_option('mailing_float_panel'))        add_option('mailing_float_panel', 'off');
  if(!get_option('mailing_show_pages'))         add_option('mailing_show_pages', 'on');
  if(!get_option('mailing_preserve_info'))      add_option('mailing_preserve_info', 'on');
}

function mailing_uninstall() {
  if(get_option('mailing_preserve_info') == 'on') return false;
	
delete_option('mailing_preserve_info');
delete_option('mailing_information');
delete_option('mailing_groups_description');
delete_option('mailing_default_sender');
delete_option('mailing_default_replyto');
delete_option('mailing_default_subject');
delete_option('mailing_dropdown_size');
delete_option('mailing_dropdown_type');
delete_option('mailing_encoding');
delete_option('mailing_content_size');
delete_option('mailing_float_panel');
delete_option('mailing_show_pages');
}

function mailing_get_setting($name, $notify = false) {
$ret = get_option($name);
return empty($ret) ? ($notify ? __('Not set', 'mailing') : '') : $ret;
}

function mailing_get_option() {
global $mailing_user_groups, $mailing_groups_desc;
$mailing_user_groups = get_option('mailing_information');
$mailing_groups_desc = get_option('mailing_groups_description');
}

function mailing_update_option() {
global $mailing_user_groups, $mailing_groups_desc;
update_option('mailing_information', $mailing_user_groups);
update_option('mailing_groups_description', $mailing_groups_desc);
}

function mailing_make_info() {
global $mailing_info, $wpdb, $table_prefix;
$wpdb->query('SELECT * FROM '.$table_prefix.'users');
$wpres = $wpdb->get_results();
  foreach($wpres as $ind => $obj) {
  $uarr = get_userdata($obj->ID); 
  $mailing_info[$uarr->user_login] = array(
    "uid" => $uarr->ID,
    "name" => $uarr->user_nicename,
    "displayname" => $uarr->display_name, 
    "username" => $uarr->user_login,
    "email" => $uarr->user_email,
    "website" => $uarr->user_url,
    "level" => $uarr->user_level,
    "groups" => mailing_get_groups($uarr->user_login),
    "capabilities" => join(', ', array_keys($uarr->wp_capabilities)) );
  }
return $mailing_info;
}

function mailing_single_info($mai) {
$test = preg_match("/^((.+)\s)?<?(([^\s]+)@[^\s]+\.[a-z]{2,5})>?$/", 
                   preg_replace("/[\'\"]/", "", stripslashes($mai)), $matc);
  if(!$test) return false;
$ret = array( "name" => empty($matc[2]) ? $matc[4] : $matc[2],
              "username" => $matc[4], "email" => $matc[3],
              "uid" => '', "website" => '', "level" => '',
              "groups" => '', "capabilities" => '' );
return $ret;
}

function mailing_set_publication($p) {
  if(empty($_GET['id_to_load']) or $_GET['id_to_load'] != $p->ID) return;
global $mailing_loaded_post;
print_r($p);
}

function mailing_set_user_groups($uname = '', $groups = '') {
global $mailing_user_groups;
  if(empty($uname) or gettype($uname) == 'integer') $uname = $_REQUEST['user_login'];
  if(empty($groups)) $groups = $_REQUEST['groups'];
  if(gettype($groups) == 'array') $groups = join(",", $groups);
  if(empty($uname) || empty($groups)) return;

mailing_get_option();
$mailing_user_groups[$uname] = $groups;
mailing_update_option();
}

function mailing_unset_user_groups($uname = '') {
global $mailing_user_groups;
  if(empty($uname)) $uname = $_POST['user_login'];
mailing_get_option();
  if(isset($mailing_user_groups[$uname])) unset($mailing_user_groups[$uname]);
mailing_update_option();
}

function mailing_set_group_desc($gname = '', $ngname = '', $gdesc = '') {
global $mailing_groups_desc;
mailing_get_option();
  if(empty($gname)) $gname = $_REQUEST['group_name'];
  if(empty($ngname)) $ngname = $_REQUEST['group_new_name'];
  if(empty($gdesc)) $gdesc = $_REQUEST['group_desc'];
  if($gname != $ngname) {
  unset($mailing_groups_desc[$gname]);
  mailing_update_group_name($gname, $ngname);
  $gname = $ngname;
  }
$mailing_groups_desc[$gname] = $gdesc;
mailing_update_option();
mailing_make_info();
}

function mailing_get_group_desc($gname) {
global $mailing_user_groups;
  if(isset($mailing_groups_desc[$gname])) return $mailing_groups_desc[$gname];
  else return '';
}

function mailing_update_group_name($gname, $ngname) {
global $mailing_user_groups;
mailing_get_option();
  foreach($mailing_user_groups as $usr => $grps) { 
    $mailing_user_groups[$usr] = preg_replace("/\b".$gname."\b/", $ngname, $grps);
  }
mailing_update_option();
}

function mailing_get_groups($login = false) { 
global $mailing_user_groups;
  if($login) { 
    if(isset($mailing_user_groups[$login])) 
      return $mailing_user_groups[$login];
  return '';
  }
$ret = array();
  foreach($mailing_user_groups as $grps) {
  $grar = preg_split("/\s*[,;\-]\s*/", $grps);
    foreach($grar as $gr) if(!empty($gr) and !in_array($gr, $ret)) $ret[] = $gr;
  }
return join(", ", $ret);
}

function mailing_delete_group($gname = '', $users = false) {
global $mailing_user_groups, $mailing_groups_desc;
$gname = empty($gname) ? $_REQUEST['group_name'] : $gname;
  if(empty($gname)) return;
mailing_get_option();
  foreach($mailing_user_groups as $usr => $grps) { 
  $grar = mailing_split_groups($grps);
    if((is_array($users) and !in_array($usr, $users)) || 
       !in_array($gname, $grar)) continue;
  $ret = array();
    foreach($grar as $gr) if(!empty($gr) and $gr != $gname) $ret[] = $gr;
    $mailing_user_groups[$usr] = join(", ", $ret);
  }
  if(mailing_count_group($gname) == 0) {
  $ret = array();
    foreach($mailing_groups_desc as $grp => $desc) if($grp != $gname) $ret[$grp] = $desc;
  }
$mailing_groups_desc = $ret;
mailing_update_option();
mailing_make_info();
}

function mailing_apply_group($gname = '', $users = false) {
global $mailing_user_groups, $mailing_groups_desc;
$gname = empty($gname) ? $_REQUEST['group_name'] : $gname;
  if(empty($gname)) return;
mailing_get_option();
  foreach($users as $un)
    if(!isset($mailing_user_groups[$un])) $mailing_user_groups[$un] = '';
  foreach($mailing_user_groups as $usr => $grps) { 
  $grar = mailing_split_groups($grps);
    if((is_array($users) and !in_array($usr, $users)) || 
       in_array($gname, $grar)) continue;
  $grar[] = $gname;
  $mailing_user_groups[$usr] = join(", ", $grar);
  }
mailing_update_option();
mailing_make_info();
}

function mailing_split_groups($str) {
$ret = array();
$arr = array_unique(preg_split("/\s*[,;\-]\s*/", $str));
  foreach($arr as $i) if(!empty($i)) $ret[] = $i;
return $ret;
}

function mailing_add_remove_group($act, $grp, $usr) { 
$users = mailing_login_by_id($usr);
  switch($act) {
    case "apply_group":
    mailing_apply_group($grp, $users);
    return __('The group was added', 'mailing');
      break;
    case "remove_group":
    mailing_delete_group($grp, $users);
    return __('The group was removed', 'mailing');
      break;
  }
}

function mailing_info2json() {
global $mailing_info;
$jsar = array();
  foreach($mailing_info as $login => $root) {
  $jsar2 = array();
  $str = "'$login': { ";
    foreach($root as $prop => $val) {
    $jsar2[] = "'$prop': '$val'";
    }
  $str .= join(", ", $jsar2)." }";
  $jsar[] = $str;
  }
return "{ ".join(", ", $jsar)." }";
}

function mailing_groups2json() {
global $mailing_groups_desc;
$jsar = array();
  foreach($mailing_groups_desc as $grp => $desc) $jsar[] = "'$grp': '$desc'";
return "{ ".join(", ", $jsar)." }";
}

function mailing_count_group($grp = '') {
global $mailing_info;
$ret = array();
  foreach($mailing_info as $usr) {
    if(empty($usr['groups'])) continue;
  $grar = preg_split("/\s*[,;\-]\s*/", $usr['groups']);
    foreach($grar as $gr) {
      if(array_key_exists($gr, $ret)) $ret[$gr]++;
      else $ret[$gr] = 1;
    }
  }
return empty($grp) ? $ret : (isset($ret[$grp]) ? $ret[$grp] : 0);
}

function mailing_login_by_id($id) {
global $mailing_info; 
  if(gettype($id) == 'array') {
  $ret = array();
    foreach($id as $uid) if(preg_match("/^\d+$/", $uid)) $ret[] = mailing_login_by_id($uid);
  return $ret;
  } 
  foreach($mailing_info as $usr) if($usr['uid'] == (int)$id) return $usr['username'];
return __('Unknown user', 'mailing');
}

function mailing_logins_by($typ, $val) {
global $mailing_info;
$ret = array();
  foreach($mailing_info as $usr) {
    if($typ == "group" or $typ == "role") {
    $iid = $typ == "group" ? "groups" : "capabilities";
      if(strstr($usr[$iid], $val)) $ret[] = $usr['username'];
    } elseif($typ == "user" or $typ == "level") {
      if($usr[$typ] == $val) $ret[] = $usr['username'];
    }
  }
return $ret;
}

function mailing_translate_body($usr, $contid, $body) {
	foreach($usr as $prop => $val) $body = preg_replace("/%".$prop."\b/i", $val, $body);
$pst = get_post($contid);
$pst->author_name = mailing_login_by_id($pst->post_author);
	foreach($pst as $prop => $val) $body = preg_replace("/%".$prop."\b/i", $val, $body);
return $body;
}

function mailing_make_add_links($str, $sep = ', ') {
global $mailing_groups_desc;
$arr = preg_split("/\s*[,;\-]\s*/", $str);
$ret = array();
  foreach($arr as $e) {
  $tit = empty($mailing_groups_desc[$e]) ? '' : ' title="'.$mailing_groups_desc[$e].'"';
	$ret[] = "<a href=\"javascript://\"{$tit} onclick=\"addGroup(\\'$e\\'); return false;\">$e</a>";
	}
return join($sep, $ret);
}

function mailing_make_to_links($func, $str, $sep = ', ') {
global $mailing_groups_desc;
$arr = preg_split("/\s*[,;\-]\s*/", $str);
$ret = array();
  foreach($arr as $e) {
	  if($func == 'applyGroupTo')  
      $tit = empty($mailing_groups_desc[$e]) ? '' : ' title="'.$mailing_groups_desc[$e].'"';
	  else $tit = '';
  $ret[] = '<a href="javascript://"'.$tit.' onclick="'.$func.'(\''.$e.'\'); return false;">'.$e.'</a>';
	}
return join($sep, $ret);
}

function mailing_to($tag, $cont) { return "<$tag:$cont>"; }

function mailing_add_includes(){
$ok = "/(page=_mail(opts|write|groups|about)|(profile|user(s|\-edit))\.php".
      "|\/wp\-admin\/?(\?.+|$|index\.php))/";
  if(preg_match($ok, $_SERVER['REQUEST_URI'])) {
	global $mailing_base_url;
	print "<link rel=\"stylesheet\" href=\"{$mailing_base_url}mailing.css\" type=\"text/css\" />\n";
	include_once "mailing.js.php";
	}
}

function mailing_add_pages() {
add_submenu_page('post.php', __('Write e-mail', 'mailing'), __('Write e-mail', 'mailing'), 8, 
                 '_mailwrite', 'mailing_write_page'); 
add_submenu_page('edit.php', __('Manage Groups', 'mailing'), __('Mailing Groups', 'mailing'), 8, 
                 '_mailgroups', 'mailing_groups_page'); 
add_submenu_page('options-general.php', __('Mailing Options', 'mailing'), __('E-mail', 'mailing'), 
                 8, '_mailopts', 'mailing_options_page'); 
add_submenu_page('plugins.php', __('WPMailing', 'mailing'), __('WPMailing', 'mailing'), 
                 8, '_mailabout', 'mailing_about_page'); 
}

function mailing_about_page() { include "about.php"; }

function mailing_write_page() { include "write.php"; }

function mailing_groups_page() { include "groups.php"; }

function mailing_options_page() { include "settings.php"; }

function mailing_clear_empty($it) { if($it == __('Not set', 'mailing')) return $it = ''; }

?>