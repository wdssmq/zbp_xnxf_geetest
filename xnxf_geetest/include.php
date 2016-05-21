<?php
#注册插件
RegisterPlugin("xnxf_geetest","ActivePlugin_xnxf_geetest");

function ActivePlugin_xnxf_geetest() {
  //Add_Filter_Plugin('Filter_Plugin_ViewPost_Template','geetest_putcode');
  Add_Filter_Plugin('Filter_Plugin_Zbp_MakeTemplatetags','geetest_putcode');
  Add_Filter_Plugin('Filter_Plugin_Cmd_Begin','geetest_verify');
}

function geetest_putcode(&$templatetags){
  global $zbp;
  require_once 'function.php';
  $GtSdk = new GeetestLib($zbp->Config('xnxf_geetest')->captchaid, $zbp->Config('xnxf_geetest')->privatekey);
  //session_start();
  $status = $GtSdk->pre_process();
  $_SESSION['gtserver'] = $status;
  $zbp->header .= '<script type="text/javascript">var geetest_challenge = "' . $GtSdk->challenge . '";var geetest_captchaid = "' . $zbp->Config('xnxf_geetest')->captchaid . '"</script><script src="'. $zbp->host . 'zb_users/plugin/xnxf_geetest/plugin.js" type="text/javascript"></script><script async="true" src="http://api.geetest.com/get.php?callback=gtfunc"></script>';
  $templatetags['geetest']='<div id="xnxf_geetest"></div>';
}

function geetest_verify(){
  global $zbp;
  require_once 'function.php';
  if (GetVars('act','GET')!="cmt"){return;}
  $GtSdk = new GeetestLib($zbp->Config('xnxf_geetest')->captchaid, $zbp->Config('xnxf_geetest')->privatekey);
  if ($_SESSION['gtserver'] == 1) {
    $result = $GtSdk->success_validate($_COOKIE['geetestchallenge'],$_COOKIE['geetestvalidate'],$_COOKIE['geetestseccode']);
  }else{
    $result = $GtSdk->fail_validate($_COOKIE['geetestchallenge'],$_COOKIE['geetestvalidate'],$_COOKIE['geetestseccode']);
  }
  if ($result=="ok") {
    return;
  } else {
    RespondError($result);
  }
}

function InstallPlugin_xnxf_geetest() {
  global $zbp;
  if(!$zbp->HasConfig('xnxf_geetest')){
    $zbp->Config('xnxf_geetest')->captchaid='b46d1900d0a894591916ea94ea91bd2c';
    $zbp->Config('xnxf_geetest')->privatekey='36fc3fe98530eea08dfc6ce76e3d24c4';
    $zbp->SaveConfig('xnxf_geetest');
	}
}

function UninstallPlugin_xnxf_geetest() {}