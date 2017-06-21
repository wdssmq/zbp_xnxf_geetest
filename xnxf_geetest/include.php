<?php
//注册插件
RegisterPlugin("xnxf_geetest", "ActivePlugin_xnxf_geetest");
function ActivePlugin_xnxf_geetest()
{
  Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'geetest_putcode');
  Add_Filter_Plugin('Filter_Plugin_ViewList_Template', 'geetest_putcode');
  Add_Filter_Plugin('Filter_Plugin_Cmd_Begin', 'geetest_verify');
  Add_Filter_Plugin('Filter_Plugin_Zbp_BuildTemplate', 'geetest_begin');
}
function geetest_begin(&$templates)
{
  if (strpos($templates['commentpost'], "geetest") === false) {
    $templates['commentpost'] = str_replace('{if $option[\'ZC_COMMENT_VERIFY_ENABLE\']}', '{if $zbp->CheckPlugin(\'xnxf_geetest\')}{$geetest}{/if}'."\n".'{if $option[\'ZC_COMMENT_VERIFY_ENABLE\']}',$templates['commentpost']);
    file_put_contents(dirname(__file__) . "jald.txt", $templates['commentpost']);
  }
}
function geetest_putcode(&$template)
{
  global $zbp;
  if ($zbp->user->Level > 0 && $zbp->user->Level < 5) {
    $template->SetTags('geetest', '');
    return;
  }
  $template->SetTags('geetest', '<div id="xnxf-geetest" style="margin: 8px 0;"></div>');
  $zbp->footer .= '<script src="' . $zbp->host . 'zb_users/plugin/xnxf_geetest/plugin.js" type="text/javascript"></script><!--gtplug--><script async="true" src="//api.geetest.com/get.php?callback=gtfunc"></script>';
}
function geetest_verify()
{
  global $zbp;
  if (GetVars('act', 'GET') !== "cmt") {
    return;
  }
  if ($zbp->user->ID > 0 && $zbp->user->ID < 5) {
    return;
  }
  $result = geetest_verify_base();
  if ($result == "ok") {
    return;
  } else {
    ScriptError($result);
  }
}
function geetest_verify_base()
{
  global $zbp;
  $zbp->StartSession();
  require_once 'function.php';
  $GtSdk = new GeetestLib($zbp->Config('xnxf_geetest')->captchaid, $zbp->Config('xnxf_geetest')->privatekey);
  if ($_SESSION['gtserver'] == 1) {
    $result = $GtSdk->success_validate($_COOKIE['geetestchallenge'], $_COOKIE['geetestvalidate'], $_COOKIE['geetestseccode']);
  } else {
    $result = $GtSdk->fail_validate($_COOKIE['geetestchallenge'], $_COOKIE['geetestvalidate'], $_COOKIE['geetestseccode']);
  }
  return $result;
}
function InstallPlugin_xnxf_geetest()
{
  global $zbp;
  if (!$zbp->HasConfig('xnxf_geetest')) {
    $zbp->Config('xnxf_geetest')->captchaid  = 'b46d1900d0a894591916ea94ea91bd2c';
    $zbp->Config('xnxf_geetest')->privatekey = '36fc3fe98530eea08dfc6ce76e3d24c4';
    $zbp->SaveConfig('xnxf_geetest');
  }
  $zbp->SetHint('good', '请确认已在模板中加入{$geetest}');
}
function UninstallPlugin_xnxf_geetest()
{
  global $zbp;
  $zbp->SetHint('bad', '请移除模板中的{$geetest}标签');
}