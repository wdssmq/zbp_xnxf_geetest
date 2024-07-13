<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
  $zbp->ShowError(6);
  die();
}
if (!$zbp->CheckPlugin('xnxf_geetest')) {
  $zbp->ShowError(48);
  die();
}


$blogtitle = 'GeeTest';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';

$act = GetVars('act', 'GET');
$suc = GetVars('suc', 'GET');
if ($act == 'save') {
  CheckIsRefererValid();
  foreach ($_POST as $key => $val) {
    if (substr($key, 0, 5) == 'read_') {
      continue;
    }
    $zbp->Config('xnxf_geetest')->$key = trim($val);
  }
  xnxf_geetest_BuildCSS();
  $zbp->SaveConfig('xnxf_geetest');

  // 开启系统自带的验证
  $zbp->option['ZC_COMMENT_VALIDCMTKEY_ENABLE'] = $zbp->Config('xnxf_geetest')->cmtKeyOn ? true : false;
  $zbp->SaveOption();

  $zbp->BuildTemplate();
  $zbp->SetHint('good');
  Redirect('./main.php' . ($suc == null ? '' : "?act={$suc}"));
}

InstallPlugin_xnxf_geetest();

?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu">
    <a href="main.php" title="首页"><span class="m-left m-now">首页</span></a>
    <?php require "about.php"; ?>
  </div>
  <div id="divMain2">
    <form action="<?php echo BuildSafeURL("main.php?act=save"); ?>" method="post">
      <table width="100%" class="tableBorder">
        <tr>
          <th width="10%">配置项</th>
          <th>内容</th>
          <th width="25%">说明</th>
        </tr>
        <tr>
          <td><label for="captchaid">极验 ID</label></td>
          <td><?php zbpform::text("captchaid", $zbp->Config("xnxf_geetest")->captchaid, "97%"); ?></td>
          <td></td>
        </tr>
        <tr>
          <td><label for="privatekey">key</label></td>
          <td><?php zbpform::text("privatekey", $zbp->Config("xnxf_geetest")->privatekey, "97%"); ?></td>
          <td></td>
        </tr>
        <tr>
          <td><label for="">自定义 CSS</label></td>
          <td><?php zbpform::textarea("css", htmlspecialchars($zbp->Config("xnxf_geetest")->css), "97%"); ?></td>
          <td>
            <pre>#xnxf-geetest {clear: both;}</pre>
          </td>
        </tr>
        <tr>
          <td>更多选项</td>
          <td>
            <p>
              <label for="email">邮箱必填</label>
              <?php zbpform::zbradio("email", $zbp->Config("xnxf_geetest")->email); ?>
            </p>
            <p>
              <label for="login">接管登录验证</label>
              <?php zbpform::zbradio("login", $zbp->Config("xnxf_geetest")->login); ?>
            </p>
            <p>
              <label for="cmtKeyOn">验证 cmtKey</label>
              <?php zbpform::zbradio("cmtKeyOn", $zbp->Config("xnxf_geetest")->cmtKeyOn); ?>
              <?php echo $zbp->option['ZC_COMMENT_VALIDCMTKEY_ENABLE'] ? '已启用' : '未启用'; ?>
            </p>
          </td>
          <td></td>
        </tr>
        <tr>
          <td></td>
          <td colspan="2"><input type="submit" value="提交" /></td>
        </tr>
      </table>
    </form>
    <p>1、注册 <a rel="nofollow" style="text-decoration: underline; color: rgb(0, 112, 192);" href="http://geetest.com/" title="极验验证" target="_blank">http://geetest.com/</a> 获取验证 ID 和 Key 写入插件配置项；</p>
    <p>2、插件会尝试自动添加验证代码（不会修改主题原始文件）；</p>
    <p>3、如果未出现验证或位置不恰当请在模板文件 template/commentpost.php 中加入代码：<b>{if $zbp->CheckPlugin('xnxf_geetest')}{$geetest}{/if}</b></p>
    <p>4、一般在 {if $option['ZC_COMMENT_VERIFY_ENABLE']}……{/if} <b>前边或后边</b></p>
    <p>5、登陆用户权限等级为 5（评论者）、6（游客）时仍需要验证，，其他级别不显示验证组件；</p>
    <p>6、单独调用：
    <pre>if ($zbp->CheckPlugin('xnxf_geetest')) {
  $result = xnxf_geetest_verify_base();
  if ($result == "ok") {
    //验证成功;
  } else {
    ScriptError($result);
  }
}</pre>
    </p>
  </div>
  <div style="min-height: 59px;"></div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
