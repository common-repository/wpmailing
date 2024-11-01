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

class mailing_email {
  var $from = "";
  var $to = "";
  var $cc = "";
  var $cco = "";
  var $subject = "";
  var $headers = "";
  var $body = "";
  var $reply_to = "";
  var $mailing_to = "";
  var $multi_to = array();
  var $attachments = array();
  var $attached = array();
  var $boundary = "";
  var $session_var = 'WPMailingAttachments';
  var $charSet = "ISO-8859-1";
  var $tmp_dir = ".";
  var $crlf = "\r\n";
  var $error = "";
	var $selected_content_id;

  function mailing_email($eml = false) {
  global $mailing_tmp_dir;
  $this->tmp_dir = $mailing_tmp_dir;
    if(!empty($_SESSION[$this->session_var])) { 
    $this->attachments = $_SESSION[$this->session_var];
      foreach($this->attachments as $att) $this->attached[] = $att['filename'];
    }
    if($eml) $this->set_fields($eml);
  }
  
  function set_fields($eml) {
  $this->boundary = $this->_boundary();
  $this->from = $eml['from'];
  $this->reply_to = empty($eml['reply_to']) ? $eml['from'] : $eml['reply_to'];
  $this->mailing_to = $eml['mailing_to'];
  $this->to = is_array($eml['to']) ? join(",", $eml['to']) : $eml['to'];
  $this->cc = is_array($eml['cc']) ? join(",", $eml['cc']) : $eml['cc'];
  $this->cco = is_array($eml['cco']) ? join(",", $eml['cco']) : $eml['cco'];
  $this->subject = $eml['subject'];
  $this->body = $eml['body'];
  ini_set('sendmail_from', $this->from);
  }
  
  function _boundary() {
  mt_srand((double)microtime()*1000000);
  $bndry = "----=WP_Mailing1.0_NextPart_";
  preg_match_all("/.{4}/", uniqid(mt_rand()), $sbo);
    for($i = 0; $i < 3; $i++) $bndry .= "_".$sbo[0][$i];
  return $bndry;
  }
  
  function _head($htm = false) {
  $head = "From: ".$this->from.$this->lf();
    if(!empty($this->reply_to)) $head .= "Reply-to: ".$this->reply_to.$this->lf();
    if(!empty($this->cc)) $head .= "Cc: ".$this->cc.$this->lf();
    if(!empty($this->cco)) $head .= "Cco: ".$this->cco.$this->lf();
  $head .= "MIME-Version: 1.0".$this->lf();
    if($htm) $head .= "Content-Type: text/html; charset=".$this->charSet.$this->lf(); 
    else $head .= "Content-Type: multipart/mixed; boundary=\"{$this->boundary}\"".$this->lf(); 
  return $head;
  }
  
  function _text_body($usr) { return strip_tags($this->translate_body($usr)); }
 
  function _body($usr, $htm = false) {
    if($htm) return $this->translate_body($usr);
  $newbound = $this->_boundary();
  $msg = "This is a multi-part message in MIME format.".$this->lf(2);
  $msg .= "--{$this->boundary}".$this->lf();
  $msg .= "Content-Type: multipart/alternative; boundary=\"$newbound\"".$this->lf(2);
  $msg .= "--$newbound".$this->lf();
  $msg .= "Content-type: text/plain; charset=".$this->charSet."; format=flowed".$this->lf();
  $msg .= "Content-Transfer-Encoding: quoted-printable".$this->lf();
  $msg .= "Content-Disposition: inline".$this->lf(2);
  $msg .= $this->_text_body($usr).$this->lf(2);
  $msg .= "--$newbound".$this->lf();
  $msg .= "Content-type: text/html; charset=".$this->charSet.$this->lf();
  $msg .= "Content-Transfer-Encoding: quoted-printable".$this->lf();
  $msg .= "Content-Disposition: inline".$this->lf(2);
  $msg .= $this->translate_body($usr).$this->lf(2);
  $msg .= "--$newbound--".$this->lf(2);
    if(count($this->attachments) > 0) {
      foreach($this->attachments as $attachment) {
        if($attachment[encoding] == "base64") 
          $attachment[data] = chunk_split(base64_encode($attachment[data]));
      $msg .= "--".$this->boundary.$this->lf();
      $msg .= "Content-type: ".$attachment[type]."; name = \"".$attachment[filename]."\"".$this->lf();
      $msg .= "Content-Transfer-Encoding: ".$attachment[encoding].$this->lf();
      $msg .= "Content-Disposition: attachment; ";
      $msg .= "filename=\"".$attachment[filename]."\"".$this->lf(2);
      $msg .= $attachment[data].$this->lf(2);
      }
    }
  $msg .= "--".$this->boundary."--".$this->lf(2);
  return $msg;
  }

  function attach($att) {
    if(!is_dir($this->tmp_dir) or 
       !move_uploaded_file($att['tmp_name'], $this->tmp_dir.$att['name']) or 
       !is_readable($this->tmp_dir.$att['name'])) return false;
  
  $encoding = preg_match("/^text/",$att['type']) ? "quoted-printable" : "base64";
  $contents = file_get_contents($this->tmp_dir.$att['name']);
  @unlink($this->tmp_dir.$att['name']);
        
    if(!empty($att['name']) and !empty($contents)) {
    $this->attachments[] = array("filename" => $att['name'], "type" => $att['type'],
                                 "encoding" => $encoding, "data" => $contents);
      
    $_SESSION[$this->session_var] = $this->attachments;
    $this->attached = array();
      foreach($this->attachments as $att) $this->attached[] = $att['filename'];
    return true; 
    } else {
    $this->error .= "Missing some attachment information\n";
    return false;
    }
  }
  
  function detach($arq) {
    if(count($this->attachments) == 1) { $this->empty_att_var(); return true; }
    for($i = 0; $i < count($this->attachments); $i++) {
      if($this->attachments[$i]['filename'] == trim($arq)) {
      unset($this->attachments[$i]);
        if(!isset($this->attachments[$i])) {
          if(!empty($this->attachments)) { 
          $this->attached  = array();
          $_SESSION[$this->session_var] = array();
            foreach($this->attachments as $at) {
            $_SESSION[$this->session_var][] = $at;
            $this->attached[] = $at['filename'];
            }
          }
        return true;
        }
      }
    } 
  return false;
  }
  
  function attnames() {
    if(isset($_SESSION[$this->session_var]))  
      foreach($_SESSION[$this->session_var] as $att) $this->attached[] = $att['filename'];
  return $this->attached;
  }
  
  function empty_att_var() {
  $this->attachments = array();
  $this->attached = array();
  unset($_SESSION[$this->session_var]);
  }
  
  function mailing_parse_to() {
  $users = mailing_make_info();
  $this->multi_to = array();
  $arr = preg_split("/\s*[,;\-]\s*/", $this->mailing_to);
    foreach($arr as $itm) {
      if(preg_match("/^<(group|role|level|user)\:([^>]+)>$/", $itm, $mat)) {
        if(empty($mat[1])) continue;
        if(empty($mat[2])) $mat[2] = '0';
        if(!isset($this->multi_to[$mat[1].'-'.$mat[2]]) or 
           !is_array($this->multi_to[$mat[1].'-'.$mat[2]])) 
          $this->multi_to[$mat[1].'-'.$mat[2]] = array();
        switch($mat[1]) {
          case "user":
            if(!array_key_exists($mat[2], $this->multi_to[$mat[1].'-'.$mat[2]])) 
              $this->multi_to[$mat[1].'-'.$mat[2]][$mat[2]] = $users[$mat[2]];
            break;
          case "group": case "level": case "role":
          $usrs = mailing_logins_by($mat[1], $mat[2]);
            if(is_array($usrs)) {
              foreach($usrs as $us)
                if(!array_key_exists($us, $this->multi_to[$mat[1].'-'.$mat[2]])) 
                  $this->multi_to[$mat[1].'-'.$mat[2]][$us] = $users[$us];
            }
            break;
        }
      } elseif($uarr = mailing_single_info($itm)) {
        if(!isset($this->multi_to['email']) or !is_array($this->multi_to['email'])) 
          $this->multi_to['email'] = array();
      $this->multi_to['email'][] = $uarr;
      }
    }
  return $this->multi_to;
  }
  
  function mailing_send() {
  $info = "";
  $sent = array();
  $toarr = $this->mailing_parse_to();
    foreach($toarr as $typ => $artyp) {
      foreach($artyp as $usr) {
        if(in_array($usr['email'], $sent)) {
        $info .= "<div class=\"sent-alreadysent\">[$typ: {$usr['email']}] <strong>".
                 __('skipped: already included', 'mailing')."</strong></div>\n";
        continue;
        } else $sent[] = $usr['email'];
      $this->to = empty($usr['name']) ? $usr['email'] : "\"{$usr['name']}\" <{$usr['email']}>";
      $attrue = (count($this->attachments) > 0);
        if($this->send($usr, $attrue)) 
          $info .= "<div class=\"sent-ok\">[{$typ}: {$usr['email']}] <strong>".
                   __('sent successfully', 'mailing')."</strong></div>\n";
        else $info .= "<div class=\"sent-failed\">[$typ: {$usr['email']}] <strong>".
                      __('could not be sent', 'mailing')."</strong></div>\n"; 
      }
    }
  $this->empty_att_var();
  $_POST = array();
  return $info;
  }

  function translate_body($usr) {
  return mailing_translate_body($usr, $this->selected_content_id, $this->body);
	}
  
  function lf($amount = 1) {
  $ret = "";
    for($i = 0; $i < $amount; $i++) $ret .= $this->crlf;
  return $ret;
  }
  
  function send($usr, $htm = false) {
  return @mail($this->to, $this->subject, $this->_body($usr, $hdtp), $this->_head($hdtp));
  }
}

?>