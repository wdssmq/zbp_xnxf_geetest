<?php
RegisterPlugin("xnxf_geetest", "ActivePlugin_xnxf_geetest");

function ActivePlugin_xnxf_geetest()
{
  // 接口挂载 - 模板编译处理
  Add_Filter_Plugin('Filter_Plugin_Zbp_BuildTemplate', 'xnxf_geetest_tpl');
  Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'xnxf_geetest_putcode');
  Add_Filter_Plugin('Filter_Plugin_ViewList_Template', 'xnxf_geetest_putcode');
  Add_Filter_Plugin('Filter_Plugin_ViewComments_Template', 'xnxf_geetest_putcode');
  Add_Filter_Plugin('Filter_Plugin_Cmd_Begin', 'xnxf_geetest_verify');
  // 接口挂载 - 后台登录
  Add_Filter_Plugin('Filter_Plugin_Login_Header', 'xnxf_geetest_Login');
}

// 接口函数 - 模板编译处理
function xnxf_geetest_tpl(&$templates)
{
  if (!isset($templates['comment-post'])) {
    $templates['comment-post'] = $templates['commentpost'];
  }
  // 如果模板内有 <!--xnxf_geetest-->，替换
  if (strpos($templates['comment-post'], "<!--xnxf_geetest-->") !== false) {
    $templates['comment-post'] = str_replace('<!--xnxf_geetest-->', '{if $zbp->CheckPlugin(\'xnxf_geetest\')}{$geetest}{/if}', $templates['comment-post']);
  } elseif (strpos($templates['comment-post'], "geetest") === false && mb_substr_count($templates['comment-post'], "ZC_COMMENT_VERIFY_ENABLE", "UTF-8") == 1) {
    $templates['comment-post'] = str_replace('{if $option[\'ZC_COMMENT_VERIFY_ENABLE\']}', '{if $zbp->CheckPlugin(\'xnxf_geetest\')}{$geetest}{/if}' . "\n" . '{if $option[\'ZC_COMMENT_VERIFY_ENABLE\']}', $templates['comment-post']);
  }
  if (isset($templates['commentpost'])){
    $templates['commentpost'] = $templates['comment-post'];
  }
  $templates['plugin_xnxf_geetest_capt'] = file_get_contents(xnxf_geetest_Path("tpl-capt"));
}

// 接口函数 - 后台登录
function xnxf_geetest_Login()
{
  global $zbp;
  $login = $zbp->Config('xnxf_geetest')->login;
  $optLogin = $zbp->option['ZC_LOGIN_VERIFY_ENABLE'];
  // 判断系统是否开启了登录验证，同时需要插件开启了接管
  if (!$login || !$optLogin) {
    return;
  }
  // 接管生效后，将系统登录验证关闭
  $zbp->option['ZC_LOGIN_VERIFY_ENABLE'] = false;
  // 引入插件的 CSS 和 JS
  $js = $css = "";
  xnxf_geetest_BuildInsert($js, $css);
  // 为登录页面添加验证组件
  echo <<<HTML
<script>
window.addEventListener('DOMContentLoaded', function() {
  const passwordElement = document.querySelector('dd.password');
  const validcodeElement = document.createElement('dd');
  validcodeElement.className = 'validcode';
  validcodeElement.innerHTML = '<div id="xnxf-geetest"></div>';
  passwordElement.parentNode.insertBefore(validcodeElement, passwordElement.nextSibling);
  // 为 #btnPost 添加类名
  const btnPost = document.getElementById('btnPost');
  btnPost.classList.add('geetest-btn','verify');
});
</script>
HTML;
  echo $js;
  // 验证成功后的回调
  echo <<<HTML
<script>
gtplug.login = function() {
  // 如果是由提交按钮触发的验证会有 done 标记，需要再次触发提交
  if ($(".geetest-btn.verify.done").length === 0){
    return;
  }
  const strUserName = $("#username").val().trim();
  const strPassWord = $("#password").val().trim();
  if (strUserName !== "" && strPassWord !== "") {
    $("form").attr("action", "cmd.php?act=verify");
    // 尝试触发表单提交
    $("form").submit();
  }
}
</script>
HTML;
  echo $css;
}

function xnxf_geetest_putcode(&$template)
{
  global $zbp;
  if ($zbp->user->ID > 0 && $zbp->user->Level < 5) {
    $template->SetTags('geetest', '<!--geetest-->');
    return;
  }
  $template->SetTags('geetest', '<div id="xnxf-geetest"></div>');
  $js = $css = "";
  xnxf_geetest_BuildInsert($js, $css);
  $zbp->footer .= $js;
  $zbp->header .= $css;
}

function xnxf_geetest_verify()
{
  global $zbp;
  $act = GetVars('act', 'GET');
  if (!HasNameInString('cmt|verify', $act)) {
    return;
  }
  // 如果是登录验证
  if ($act == 'verify') {
    $result = xnxf_geetest_verify_base();
    if ($result === "ok") {
      $zbp->option['ZC_LOGIN_VERIFY_ENABLE'] = false;
    }
    return;
  }
  // 评论验证
  if ($zbp->user->ID > 0 && $zbp->user->Level < 5) {
    return;
  }
  if ($zbp->Config('xnxf_geetest')->email && GetVars('email', 'POST') == null) {
    if (GetVars('format', 'POST') == 'json')
      JsonError(78, "请输入邮箱",  $_POST);
    else
      ScriptError("请输入邮箱");
  }
  $result = xnxf_geetest_verify_base();
  if ($result === "ok") {
    return;
  } else {
    if (GetVars('format', 'POST') == 'json')
      JsonError(79, $result, $_POST);
    else
      ScriptError($result);
  }
}

function xnxf_geetest_verify_base($code = "", $app = "capt", $exp = 300)
{
  global $zbp;
  if ($zbp->user->ID > 0 && $zbp->user->Level < 5) {
    return "ok";
  }
  $cookieName = "{$app}_GeetPass";
  if (!empty($code) && $code == GetVars($cookieName, "COOKIE")) {
    return "ok";
  }
  $zbp->StartSession();
  require __DIR__ . '/lib/class.gt.php';
  $GtSdk = new GeetestLib($zbp->Config('xnxf_geetest')->captchaid, $zbp->Config('xnxf_geetest')->privatekey);
  $param = xnxf_geetest_GetGtParam();
  if (isset($_SESSION['gtserver']) && $_SESSION['gtserver'] == 1) {
    $result = $GtSdk->success_validate(GetVars('geetestchallenge', "COOKIE"), GetVars('geetestvalidate', "COOKIE"), GetVars('geetestseccode', "COOKIE"), $param);
  } else {
    $result = $GtSdk->fail_validate(GetVars('geetestchallenge', "COOKIE"), GetVars('geetestvalidate',  "COOKIE"), GetVars('geetestseccode', "COOKIE"));
  }
  if ($result === "ok" && !empty($code)) {
    setcookie($cookieName, $code, time() + $exp, $zbp->cookiespath);
  } else {
    setcookie($cookieName, $code, time() - 1, $zbp->cookiespath);
  }
  if ($result === 1) {
    return "ok";
  } else {
    return "验证失败";
  }
}

function xnxf_geetest_GetGtParam()
{
  global $zbp;
  return array(
    'user_id' => $zbp->user->ID,
    'client_type' => 'web',
    'ip_address' => GetGuestIP(),
  );
}

// 获取接口版本，默认为 3.0
function xnxf_geetest_GetVersion()
{
  global $zbp;
  $version = trim((string) $zbp->Config('xnxf_geetest')->geetestVersion);
  if ($version !== '4') {
    return '3';
  }
  return '4';
}

// 构建用于插入 CSS 和 JS 的 HTML 代码
function xnxf_geetest_BuildInsert(&$js, &$css)
{
  $js .= "<script src=\"" . xnxf_geetest_Path("gt.js", "host") . "\"></script>\n<!--gtplug-->\n<script defer src=\"" . xnxf_geetest_Path("plu.js", "host") . "\"></script>\n";
  $css .= "<link rel=\"stylesheet\" href=\"" . xnxf_geetest_Path("bin-css", "host") . "\">\n";
}

function xnxf_geetest_BuildCSS()
{
  global $zbp;
  $dir = xnxf_geetest_Path("bin");
  if (!is_dir($dir)) {
    @mkdir($dir, 0755);
    #code
  }
  $file = xnxf_geetest_Path("bin-css");
  $css = $zbp->Config('xnxf_geetest')->css;
  file_put_contents($file, $css);
}

function xnxf_geetest_Path($file, $t = "path")
{
  global $zbp;
  $result = $zbp->$t . "zb_users/plugin/xnxf_geetest/";
  if ($t == "host")
    $hash = "?v=20240713103";
  else
    $hash = "";
  switch ($file) {
    case "tpl-capt":
      return $result . "tpl/capt.php";
      break;
    case "gt.js":
      return $result . "script/gt.js{$hash}";
      break;
    case "plu.js":
      return $result . "script/plugin.js{$hash}";
      break;
    case "bin":
      return $result . "bin/";
      break;
    case "bin-css":
      return $result . "bin/style.css{$hash}";
      break;
    case "Main":
      return $result . "main.php";
      break;
    default:
      return $result;
  }
}

function xnxf_geetest_ShowCapt($title = "", $note = "")
{
  global $zbp;
  if (xnxf_geetest_verify_base() === "ok") {
    return "pass";
  }
  $zbp->template->SetTemplate('plugin_xnxf_geetest_capt');
  $zbp->template->SetTags('title', $title);
  foreach ($GLOBALS['hooks']['Filter_Plugin_ViewList_Template'] as $fpname => &$fpsignal) {
    $fpreturn = $fpname($zbp->template);
    if ($fpsignal == PLUGIN_EXITSIGNAL_RETURN) {
      $fpsignal = PLUGIN_EXITSIGNAL_NONE;
      return $fpreturn;
    }
  }
  $zbp->template->Display();
  die();
}

function InstallPlugin_xnxf_geetest()
{
  global $zbp;
  if (!$zbp->HasConfig('xnxf_geetest')) {
    $zbp->Config('xnxf_geetest')->captchaid = 'b46d1900d0a894591916ea94ea91bd2c';
    $zbp->Config('xnxf_geetest')->privatekey = '36fc3fe98530eea08dfc6ce76e3d24c4';
    $zbp->SaveConfig('xnxf_geetest');
  }
  $zbp->SetHint('good', '请确认已在模板中加入 {$geetest} 或 &lt;!--xnxf_geetest--&gt;');
  $zbp->BuildTemplate();
}

function UninstallPlugin_xnxf_geetest()
{
  global $zbp;
  $zbp->SetHint('bad', '请移除模板中的 {$geetest} 标签');
}
