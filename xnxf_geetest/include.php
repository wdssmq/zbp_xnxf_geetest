<?php
#注册插件
RegisterPlugin("xnxf_geetest","ActivePlugin_xnxf_geetest");

function ActivePlugin_xnxf_geetest() {
  Add_Filter_Plugin('Filter_Plugin_Zbp_LoadTemplate','xnxf_geetest_begin');
}

function xnxf_geetest_begin($templates){
  if (strpos($templates['commentpost'],"geetest")!==false){
    Add_Filter_Plugin('Filter_Plugin_ViewPost_Template','xnxf_geetest_putcode');
    Add_Filter_Plugin('Filter_Plugin_Cmd_Begin','xnxf_geetest_verify');
  }
}

function xnxf_geetest_putcode(&$template){
  global $zbp;
  if ($zbp->user->Level>0&&$zbp->user->Level<5){
    $template->SetTags('geetest', '');
    return;
  }
  if ($zbp->Config('xnxf_geetest')->https==false){
    $http = "http";
  }else{
    $http = "https";
  }

  $zbp->header .= '<script src="'. $zbp->host . 'zb_users/plugin/xnxf_geetest/plugin.js" type="text/javascript"></script><script async="true" src="'.$http.'://api.geetest.com/get.php?callback=gtfunc"></script>';
  $template->SetTags('geetest', '<div id="xnxf_geetest" style="margin: 8px 0;"></div>');
}

function xnxf_geetest_verify(){
  global $zbp;
  if (GetVars('act','GET')!=="cmt"){return;}
  if ($zbp->user->ID>0&&$zbp->user->ID<5){return;}
  require_once 'function.php';
  $GtSdk = new GeetestLib($zbp->Config('xnxf_geetest')->captchaid, $zbp->Config('xnxf_geetest')->privatekey);
  session_start();
  if (GetVars('getgeetest','GET')!=""){
    $status = $GtSdk->pre_process();
    $_SESSION['gtserver'] = $status;
    echo $GtSdk->get_response_str();
    die();
  }
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
    $zbp->Config('xnxf_geetest')->https=false;
    $zbp->SaveConfig('xnxf_geetest');
	}
}

function UninstallPlugin_xnxf_geetest() {}