<?php
//注册插件
RegisterPlugin("xnxf_geetest", "ActivePlugin_xnxf_geetest");
function ActivePlugin_xnxf_geetest()
{
  Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'geetest_putcode');
  Add_Filter_Plugin('Filter_Plugin_ViewList_Template', 'geetest_putcode');
  Add_Filter_Plugin('Filter_Plugin_ViewComments_Template', 'geetest_putcode');
  Add_Filter_Plugin('Filter_Plugin_Cmd_Begin', 'geetest_verify');
  Add_Filter_Plugin('Filter_Plugin_Zbp_BuildTemplate', 'geetest_begin');
}
function geetest_begin(&$templates)
{
  if (strpos($templates['commentpost'], "geetest") === false && mb_substr_count($templates['commentpost'], "ZC_COMMENT_VERIFY_ENABLE") == 1) {
    $templates['commentpost'] = str_replace('{if $option[\'ZC_COMMENT_VERIFY_ENABLE\']}', '{if $zbp->CheckPlugin(\'xnxf_geetest\')}{$geetest}{/if}' . "\n" . '{if $option[\'ZC_COMMENT_VERIFY_ENABLE\']}', $templates['commentpost']);
  }
}
function geetest_putcode(&$template)
{
  global $zbp;
  if ($zbp->user->ID > 0 && $zbp->user->Level < 5) {
    $template->SetTags('geetest', '');
    return;
  }
  echo __LINE__ . ":<br>\n";
  var_dump(1);
  echo "<br><br>\n\n";
  //die();
  // $template->SetTags('geetest', '<div id="xnxf-geetest" style="margin: 8px 0;"></div>');
  $template->SetTags('geetest', '<div id="xnxf-geetest"></div>');
  $zbp->footer .= '<script src="' . geetest_Path("gt.js", "host") . '"></script><!--gtplug--><script src="' . geetest_Path("plu.js", "host") . '"></script>';
}
function geetest_verify()
{
  global $zbp;
  if (GetVars('act', 'GET') !== "cmt") {
    return;
  }
  if ($zbp->user->ID > 0 && $zbp->user->Level < 5) {
    return;
  }
  if ($zbp->Config('xnxf_geetest')->email && GetVars('email', 'POST') == null) {
    if (GetVars('format', 'POST') == 'json')
      JsonError(78, "请输入邮箱",  $_POST);
    else
      ScriptError("请输入邮箱");
  }
  $result = geetest_verify_base();
  if ($result == "ok") {
    return;
  } else {
    if (GetVars('format', 'POST') == 'json')
      JsonError(79, $result, $_POST);
    else
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
function geetest_Path($file, $t = "path")
{
  global $zbp;
  $result = $zbp->$t . "zb_users/plugin/xnxf_geetest/";
  if ($t == "host")
    $hash = "?v=20180906213539";
  else
    $hash = "";
  switch ($file) {
    case "gt.js":
      return $result . "gt.js{$hash}";
      break;
    case "plu.js":
      return $result . "plugin.js{$hash}";
      break;
    case "Main":
      return $result . "main.php";
      break;
    default:
      return $result;
  }
}
function InstallPlugin_xnxf_geetest()
{
  global $zbp;
  if (!$zbp->HasConfig('xnxf_geetest')) {
    $zbp->Config('xnxf_geetest')->captchaid = 'b46d1900d0a894591916ea94ea91bd2c';
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
