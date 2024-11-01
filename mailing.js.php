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

global $msg, $mailing_version, $mailing_wp_user;

$grps = preg_split("/\s*[,;\-]\s*/", mailing_get_groups());
$jsoptions = '';
  foreach($grps as $g) $jsoptions .= '<option value="'.$g.'">'.$g.'</option>\n';
?>
<script type="text/javascript" language="javascript1.2">
// this event needs to be the first because it rewrites the HTML removing all added behaviors
addEvent(window, 'load', stampWPMLogo); 
addEvent(window, 'load', addUserFields);
addEvent(window, 'load', addUpdateFields);
addEvent(window, 'load', addDashboardItems);
addEvent(window, 'load', aboutRedirect);
//
<?php if(get_option('mailing_float_panel') == 'on') : ?>
  if(/\bpage=_mailwrite\b/.test(location.href)) 
    addEvent(window, 'scroll', optionsAlwaysVisible);
<?php endif; ?>

var mailing_info = <?php print mailing_info2json();?>;
var groups_desc = <?php print mailing_groups2json();?>;

function optionsAlwaysVisible() {
var scrl = document.all ? document.documentElement.scrollTop : window.pageYOffset;
  if(scrl < 160) return;
document.getElementById('rightpanel').style.marginTop = (scrl - 150)+'px';
}

function addDashboardItems() {
  if(/\/wp\-admin\/?(\?.+|$|index\.php)/.test(location.href) == false) return;
var els = document.getElementsByTagName('div');
var div = null;
  for(var i = 0; i < els.length; i++) { div = els[i]; if(div.className == 'wrap') break; }
var elem = div.getElementsByTagName('p')[2];
var myp = document.createElement('p');
myp.innerHTML = '<?php _e('WPMailing plugin for WordPress', 'mailing'); ?>';
var myul = document.createElement('ul');
myul.id = 'wpmoptions';
myul.innerHTML = '<li><a href="<?php bloginfo('wpurl'); 
                 ?>/wp-admin/admin.php?page=_mailabout"><?php _e('Information and help', 'mailing'); 
								 ?></a></li>\n'+
                 '<li><a href="<?php bloginfo('wpurl'); 
                 ?>/wp-admin/admin.php?page=_mailwrite"><?php _e('Write E-mail', 'mailing'); 
								 ?></a></li>\n'+
                 '<li><a href="<?php bloginfo('wpurl'); 
                 ?>/wp-admin/users.php"><?php _e('Manage users', 'mailing'); 
								 ?></a></li>\n'+
                 '<li><a href="<?php bloginfo('wpurl'); 
                 ?>/wp-admin/admin.php?page=_mailgroups"><?php _e('Edit user groups', 'mailing'); 
								 ?></a></li>\n'+
                 '<li><a href="<?php bloginfo('wpurl'); 
                 ?>/wp-admin/admin.php?page=_mailopts"><?php _e('Update settings', 'mailing'); 
								 ?></a></li>\n';
div.insertBefore(myp, elem);
div.insertBefore(myul, elem);
}

function addUserFields() {
var rad = document.getElementById('action0') || false; 
  if(/\/users\.php/.test(location.href) == false || !rad || 
     rad.tagName.toLowerCase() != 'input' || rad.type.toLowerCase() != 'radio') return;
var ul = getParentTag(rad, 'ul');
var newli = document.createElement('li');
newli.innerHTML = '<input type="radio" name="action" id="actadd" value="apply_group" '+
                  '/> '+
                  '<label for="actadd"><?php _e('Add this group to selected users', 'mailing'); 
                  ?></label>: <select name="grpadd" id="grpadd" onchange="check(\'actadd\'); '+
                  'if(this.value == \'addnew\') groupPrompt();">'+
                  '<option value=""> -- <?php _e('Groups', 'mailing'); ?> -- </option>'+
                  '<?php print $jsoptions; ?><option value="addnew">&raquo; <?php 
                  _e('new group', 'mailing'); ?></option></select> '+
                  '<span id="newgrpname" style="display:none;"'+
                  '><?php _e('Group name', 'mailing'); ?>: '+
                  '<input type="text" style="width:140px;" /></span>';
ul.appendChild(newli);
var newli = document.createElement('li');
newli.innerHTML = '<input type="radio" name="action" id="actrem" value="remove_group" /> '+
                  '<label for="actrem"><?php _e('Remove this group from selected users', 'mailing'); 
                  ?></label>: <select name="grprem" onchange="check(\'actrem\')">'+
                  '<option value=""> -- <?php _e('Groups', 'mailing'); ?> -- </option>'+
                  '<?php print $jsoptions; ?></select>';
ul.appendChild(newli);
var tb = document.getElementById('adduser').getElementsByTagName('tbody')[0];
var trs = tb.getElementsByTagName('tr'), tr = document.createElement('tr');
tb.insertBefore(tr, trs[trs.length - 1]);
var td1 = document.createElement('td'), td2 = document.createElement('td');
td1.align = 'right';
td1.innerHTML = '<strong><?php _e('Groups', 'mailing'); ?></strong>';
td2.innerHTML = '<input type="text" name="groups" id="groups" />\n';
td2.innerHTML += '<fieldset style="padding:10px 4px; border:1px solid #CCC;"><legend><?php 
                  _e('Your groups', 'mailing'); ?></legend>'+
                 '<?php $grps = mailing_make_add_links(mailing_get_groups()); 
                  if(empty($grps)) 
                    print '<span class="disab">'.__('There are no groups yet', 'mailing').'</span>'; 
                  else print $grps; ?> </fieldset>';
tr.appendChild(td1);
tr.appendChild(td2);

var frm = getParentTag(rad, 'form'); 

frm.onsubmit = function() {
var act = ['action0','action1','actadd','actrem'], checked = 0;
  for(var i = 0; i < act.length; i++) {
	var el = document.getElementById(act[i]);
	  if(el && el.checked) checked++;
	}
  if(checked == 0) {
  alert('<?php _e('Select the action you want to execute', 'mailing'); ?>');
  return false;
  }
var usrs = getUserIds();
  if(usrs.length == 0) {
  alert('<?php _e('Select at least one user to update', 'mailing'); ?>');
  return false;
  }
  if(document.getElementById('grpadd').selectedIndex == 0 && 
     document.getElementById('actadd').checked) {
  groupPrompt();
  return false;
  }
  if(document.getElementById('grprem').selectedIndex == 0 && 
     document.getElementById('actrem').checked) {
  alert('<?php _e('Select one of your groups to remove', 'mailing'); ?>');
  document.getElementById('grprem').focus();
  return false;
  }
return true;
};

addGroupsToTable();

<?php if(!empty($msg)) : ?>
var msg = document.getElementById('message') || false;
  if(msg) {
  msg.innerHTML = '<p><strong><?php print $msg; ?></strong></p>';
  return;
  }
var smenu = document.getElementById('submenu'), wrap = getNextTag(smenu);
var msg = document.createElement('div');
msg.id = 'message';
msg.style.cssText = "background-color: rgb(207, 235, 247);"
msg.className = 'updated';
msg.innerHTML = '<p><strong><?php print $msg; ?></strong></p>';
document.body.insertBefore(msg, wrap);
Fat.fade_element('message', false, 3000, "#ECF27B");
<?php endif; ?>
}

function getUserIds() {
var inps = document.getElementsByTagName('input'), ret = [];
  for(var i = 0; i < inps.length; i++)  
    if(/^user_(\d+)$/.test(inps[i].id)) 
      if(inps[i].checked) ret.push(RegExp.$1);
return ret;
}

function showGroup(grp) {
var tab = getUsersTable();
  if(!tab) return false;
	
var gcap = (document.getElementById('groupscaption') || false);
  if(gcap) gcap.parentNode.removeChild(gcap);
	
var trs = tab.getElementsByTagName('tr'), firsttr = false, firstcaps = false;
  for(var i = 0; i < trs.length; i++) {
	  if(!firsttr) firsttr = trs[i];
	  if(grp == 'SHOWALLGROUPS') {
		trs[i].style.display = '';
		continue;
		}
  var tds = trs[i].getElementsByTagName('td');
	  if(tds.length > 5) {
		var grps = tds[4].innerHTML.split(/\s*[,;\-]\s*/);
		trs[i].style.display = (!grp || inArray(grps, grp)) ? '' : 'none';
		} else if(trs[i].getElementsByTagName('th').length > 5 && !firstcaps) {
		firstcaps = true;
		} else trs[i].style.display = 'none';
	}

	if(grp != 'SHOWALLGROUPS') {
	var cap = document.createElement('tr');
	cap.id = 'groupscaption';
	var th = document.createElement('th');
	th.colspan = 7;
	th.innerHTML = '<h3><?php _e('Group', 'mailing'); ?>: '+grp+'</h3>';
	cap.appendChild(th);
	firsttr.parentNode.insertBefore(cap, firsttr);
	}
}

function getUsersTable() {
var tabs = document.getElementsByTagName('table') || false, tab = false; 
  for(var i = 0; i < tabs.length; i++) if(tabs[i].className == 'widefat') tab = tabs[i];
return tab;
}

function addGroupsToTable() {
var tab = getUsersTable();
  if(!tab) return false;
var trs = tab.getElementsByTagName('tr'), mailing_col = -1;
  for(var i = 0; i < trs.length; i++) {
  var ths = trs[i].getElementsByTagName('th');
  var tds = trs[i].getElementsByTagName('td');
    if(ths.length <= 1 && tds.length == 0) continue;
    if(ths.length == 6) {
      for(var t = 0; t <= 6; t++) {
        if(ths[t].innerHTML == '<?php _e('Website'); ?>') {
        mailing_col = t;
        var th = document.createElement('th');
        th.className = 'mailing_column';
        th.innerHTML = '<?php _e('Groups', 'mailing'); ?>';
        trs[i].insertBefore(th, ths[t]);
        break;
        }
      }
    } else {
    var ckb = trs[i].getElementsByTagName('input')[0] || false, uid = false;
      if(ckb && ckb.type == 'checkbox') uid = ckb.value;
      if(!uid) continue;
    var user = loginById(uid);
      for(var t = 0; t <= 6; t++) {
        if(t == mailing_col) {
        var td = document.createElement('td');
        td.className = 'mailing_column';
				td.innerHTML = mailing_info[user].groups;
        trs[i].insertBefore(td, tds[t]);
        }
      }
    }
  }

var div1 = document.createElement('div');
div1.className = 'tabopt';
div1.innerHTML = '<input type="checkbox" id="show_groups" onclick="showHideCol(this.checked)" '+
                 'checked="checked" /> <label for="show_groups"><?php 
                 _e('Show groups in table', 'mailing'); ?></label>';
var div2 = document.createElement('div');
div1.className = 'tabopt';
div2.innerHTML = '<input type="checkbox" id="select_all" onclick="selectAll(this)" /> '+
                 '<label for="select_all"><?php _e('Check/uncheck all boxes', 'mailing'); ?></label>';
var div3 = document.createElement('div');
div3.className = 'tabsrc';
div3.innerHTML = '<label for="this_group"><?php _e('Filter users by group', 'mailing'); ?>:</label> '+
                 '<select id="this_group"><option value="SHOWALLGROUPS"><?php 
                 _e('All groups', 'mailing'); ?></option>'+
                 '<?php print $jsoptions; ?></select> '+
                 '<input class="button" type="button" id="show_by_group" onclick="showGroup'+
                 '(this.form.this_group.value)" value="<?php _e('Show', 'mailing'); ?> &raquo;" />';
tab.parentNode.insertBefore(div1, tab);
tab.parentNode.insertBefore(div2, tab);
tab.parentNode.insertBefore(div3, div1);
}

function selectAll(ckb) {
var frm = getParentTag(ckb, 'form'), inps = frm.getElementsByTagName('input');
  for(var i = 0; i < inps.length; i++)  
    if(inps[i].type == 'checkbox' && (/user_\d/.test(inps[i].id))) inps[i].checked = ckb.checked;
}

function showHideCol(show) {
var tabs = document.getElementsByTagName('table') || false, mailing_col = -1, tab = false; 
  for(var i = 0; i < tabs.length; i++) if(tabs[i].className == 'widefat') tab = tabs[i];
  if(!tab) return false;
var thrs = tab.getElementsByTagName('*');
  for(var i = 0; i < thrs.length; i++) { 
    if(thrs[i].className == 'mailing_column') {
    thrs[i].style.display = show ? 'block' : 'none';
    }
  }
}

function loginById(id) {
  for(var i in mailing_info) if(mailing_info[i].uid == id) return i;
return '';
}

function addUpdateFields() {
var frm = document.getElementById('your-profile') || false; 
  if(/\/(profile|user\-edit)\.php/.test(location.href) == false || frm.name != 'profile') return;
var fset = document.createElement('fieldset');
fset.style.cssText = 'width:auto; float:none; margin-right:5%;';
var lgnd = document.createElement('legend');
lgnd.innerHTML = 'Mailing';
fset.appendChild(lgnd);
fset.innerHTML += '<p style="clear:both"><?php 
                  _e('To create a new group, just type the new group name in the field bellow.', 
                     'mailing'); ?></p>\n'+
                  '<p><label for="groups"><?php 
                  print __('Tag this user with the following groups', 'mailing').' ('.
                        __('separated by commas', 'mailing').')'; 
                  ?></label><br />\n'+
                  '<input type="text" name="groups" id="groups" size="16" value="<?php 
                  print mailing_get_groups($mailing_wp_user); ?>" />\n'+
                  '<fieldset style="width:auto; float:none; padding:1%; margin:0;"><legend '+
                  'style="font-size:1em;"><?php _e('Your groups', 'mailing'); ?></legend>\n'+
                  '<?php $grps = mailing_make_add_links(mailing_get_groups()); 
                  if(empty($grps)) 
                    print '<span class="disab">'.__('There are no groups yet', 'mailing').'</span>'; 
                  else print $grps; ?></fieldset></p>';
var ps = frm.getElementsByTagName('fieldset'), elem;
  for(var i =  0; i < ps.length; i++) elem = getNextTag(ps[i]);
frm.insertBefore(fset, elem);
addEvent(frm, 'submit', function() { document.profile.user_login.disabled = false; return true; });
}

function applyUsersTo() { 
var sel = document.getElementById('mailing_users');
  for(var i = 0; i < sel.options.length; i++) {
    if(sel.options[i].selected) applyToField(sel.options[i].value, 'mailing_to', 'user'); 
  }
}

function addGroup(grp) { applyToField(grp, 'groups'); }

function applyGroupTo(grp) { applyToField(grp, 'mailing_to', 'group'); }

function applyRoleTo(grp) { applyToField(grp, 'mailing_to', 'role'); }

function applyLevelTo(grp) { applyToField(grp, 'mailing_to', 'level'); }

function applyToField(grp, eid, typ) {
var el = document.getElementById(eid), val = typ ? '<'+typ+':'+grp+'>' : grp;
  if(el.value.indexOf(val) >= 0) {
  var arr = el.value.split(/\s*[,;\-]\s*/), arr2 = [];
    for(var i = 0; i < arr.length; i++) if(arr[i] != val) arr2.push(arr[i]);
  el.value = arr2.join(', ');
  } else if(el.value.indexOf(val) < 0) {
  el.value += ', '+val;
  el.value = el.value.replace(/^\s*[,;]\s*/, '');
  }
}

function editGroup(grp) { 
document.getElementById('grpname').innerHTML = grp;
var el = document.getElementById('updgrp');
el.style.display = 'block';
el.scrollIntoView();
var frm = document.getElementById('mailing_edit_form');
frm.group_new_name.value = grp;
frm.group_name.value = grp;
frm.group_desc.value = groups_desc[grp] || '<?php _e('Not set', 'mailing'); ?>';
frm.group_desc.focus();
frm.group_desc.select();
}

function deleteGroup(grp) {
var frm = document.getElementById('mailing_form');
frm.elements.mailing_action.value = 'delete_group';
frm.group_name.value = grp;
frm.submit();
}

function emailGroup(grp) {
location.href = '<?php bloginfo('wpurl'); ?>/wp-admin/post-new.php?page=_mailwrite&group='+grp;
}

function lostAtt(disc) {
var frm = document.getElementById('mailing_write_form');
frm.mailing_sendmessage.value = disc ? 'discard_att' : 'continue';
frm.submit();
}

function setCharSet(val) {
document.getElementById('mail_encoding').value = val;
}

function check(id) {
  try { document.getElementById(id).checked = true; } catch(e) {}
}

function groupPrompt() {
var promp = document.getElementById('newgrpname');
var txt = promp.getElementsByTagName('input')[0];
promp.style.display = 'block';
  txt.onblur = function() {
  getParentTag(this, 'span').style.display = 'none';
    if(/^[a-zA-Z0-9\s]+$/.test(this.value)) {
    var sel = document.getElementById('grpadd');
    addOptionAt(sel, this.value, this.value, sel.options.length - 1);
    sel.selectedIndex = sel.options.length - 2;
    } else document.getElementById('grpadd').selectedIndex = 0;
  };
txt.focus();
}

function stampWPMLogo() {
var ok = false;
  if(document.getElementById('newcontent') || false) 
		ok = (/plugin\-editor\.php/.test(location.href) && 
					(/WPMailing/.test(document.getElementById('newcontent').value)));
	else ok = /(page=_mail(opts|write|groups|about)|(profile|user(s|\-edit))\.php)/.test(location.href);
  if(!ok) return;

var divs = document.getElementsByTagName('div');
  for(var i = 0; i < divs.length; i++) {
	  if(divs[i].className == 'wrap') {
		var logo = document.createElement('a');
		logo.href = '<?php bloginfo('wpurl'); ?>/wp-admin/plugins.php?page=_mailabout';
		logo.innerHTML = '<img id="wpmlogo" src="<?php print $mailing_base_url; ?>images/logo.gif'+
												'" alt="WPMailing" title="<?php _e('About WPMailing', 'mailing'); ?>'+
												' <?php print $mailing_version; ?>" />';
		divs[i].insertBefore(logo, divs[i].firstChild);
	  break;
		}
	}
}

var _alertCcCcoMsg = false;
function alertCcCco() {
  if(_alertCcCcoMsg) return true;
	else _alertCcCcoMsg = true;
return confirm('<?php _e('If Cc or Cco fields are not empty, the recipients that you put there will receive copies of all messages sent', 'mailing'); ?> \n(<?php 
								_e('the system sends individual messages to each recipient included in To field',
								'mailing'); ?>)! \n<?php _e('Do you wish to continue?', 'mailing'); ?>');
}

function loadToMail(id) {
location.href = '<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=_mailwrite&id_to_load='+id;
}

var panels = {'wpm_about': '<?php _e('WPMailing', 'mailing'); ?>', 
              'wpm_help': '<?php _e('Help', 'mailing'); ?>', 
              'wpm_faq': '<?php _e('FAQ', 'mailing'); ?>', 
              'wpm_donate': '<?php _e('Donate', 'mailing'); ?>'};

function openPanel(pid) {
location.hash = 'panel='+pid.replace(/wpm_/, '');
  for(var i in panels) document.getElementById(i).style.display = 'none';
document.getElementById(pid).style.display = '';
var as = document.getElementById('hmenu').getElementsByTagName('a');
  for(var i = 0; i < as.length; i++) {
	as[i].className = '';
    if(as[i].innerHTML == panels[pid]) as[i].className = 'sel';
	}
}

function aboutRedirect() {
  if(/#panel=([a-z]+)/.test(location.href)) {
	  try { openPanel('wpm_'+RegExp.$1); }
		catch(e) { openPanel('wpm_about'); }
	}
}

// support functions

function addEvent(el, name, func) {
  if(document.all) el.attachEvent("on"+name, func);
  else el.addEventListener(name, func, true);
}

function getNextTag(elem) {
var nex = elem.nextSibling;
    while((nex || false) && nex.nodeType != 1) nex = nex.nextSibling;
return nex;
}

function showHide(elid, show) {
var el = document.getElementById(elid) || false;
  if(!el) return;
var display = typeof(show) == 'undefined' ? 
                (el.style.display == 'none' ? 'block' : 'none') : (show ? 'block' : 'none');
el.style.display = display;
  if(show) {
	el.scrollIntoView();
	Fat.fade_element(elid, 30, 3000, "#FF6600");
	}
}

function getParentTag(elem, tag) {
var el = elem, tag = tag.toLowerCase();
  while((el || false)) {
  el = el.parentNode;
    if((el || false) && el.nodeType == 1 && el.tagName.toLowerCase() == tag) break;
  }
return el;
}

function addOption(selec, val, tex) {
var sel = typeof(selec) == 'string' ? document.getElementById(selec) : selec,
    opt = document.createElement('option');
opt.value = val;
opt.text = tex ? tex : val;

  try { sel.add(opt, null); } // NS FF
  catch(e) { sel.add(opt); } // IE
}

function addOptionAt(selec, val, tex, ind) {
ind = /^\d+$/.test(ind) ? Number(ind) : 0;
var opts = [];
  for(var i = 0; i < selec.options.length; i++) {
    if(i == ind) opts.push({'val': val, 'tex': tex});
  opts.push({'val': selec.options[i].value, 'tex': selec.options[i].innerHTML});
  }
selec.innerHTML = '';
  for(var i = 0; i < opts.length; i++) addOption(selec, opts[i].val, opts[i].tex);
}

function inArray(arr, val, icompare) {
  for(var i = 0; i < arr.length; i++) {
    if(typeof(icompare) == 'function') return icompare(arr[i], val);
    else if(icompare && (arr[i].toLowerCase() == val.toLowerCase())) return true;
    else if(arr[i] == val) return true;
  }
return false;
}

</script>