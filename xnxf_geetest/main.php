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

if (count($_POST) > 0) {
  CheckIsRefererValid();
  $zbp->Config('xnxf_geetest')->captchaid = trim($_POST['captchaid']);
  $zbp->Config('xnxf_geetest')->privatekey = trim($_POST['privatekey']);
  $zbp->Config('xnxf_geetest')->email = trim($_POST['email']);
  $zbp->SaveConfig('xnxf_geetest');
  $zbp->BuildTemplate();
  $zbp->SetHint('good');
  Redirect('./main.php');
}
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu">
  </div>
  <div id="divMain2">
    <form action="<?php echo BuildSafeURL("main.php?act=save"); ?>" method="post">
      <table width="100%" class="tableBorder">
        <tr>
          <th scope="col" width="20%">配置项</th>
          <th scope="col">内容</th>
        </tr>
        <tr>
          <td><label for="captchaid">极验ID</label></td>
          <td><textarea style="width: 80%;" name="captchaid" id="captchaid"><?php echo $zbp->Config('xnxf_geetest')->captchaid ?></textarea></td>
        </tr>
        <tr>
          <td><label for="privatekey">key</label></td>
          <td><textarea style="width: 80%;" name="privatekey" id="privatekey"><?php echo $zbp->Config('xnxf_geetest')->privatekey ?></textarea></td>
        </tr>
        <tr>
          <td><label for="email">邮箱必填</label></td>
          <td><input type="text" class="checkbox" name="email" id="email" value="<?php echo $zbp->Config('xnxf_geetest')->email ?>"></td>
        </tr>
        <tr>
          <td><input type="submit" value="提交" /></td>
          <td> </td>
        </tr>
      </table>
    </form>
    <p>1、注册 <a rel="nofollow" style="text-decoration: underline; color: rgb(0, 112, 192);" href="http://geetest.com/" title="极验验证" target="_blank">http://geetest.com/</a> 获取验证ID和Key写入插件配置项；</p>
    <p>2、插件会尝试自动添加验证代码（不会修改主题原始文件）；</p>
    <p>3、如果未出现验证或位置不恰当请在模板文件template/commentpost.php中加入代码<b>{if $zbp->CheckPlugin('xnxf_geetest')}{$geetest}{/if}</b></p>
    <p>4、一般在{if $option['ZC_COMMENT_VERIFY_ENABLE']}……{/if} <b>前边或后边</b></p>
    <p>5、登陆用户权限等级为5（评论者）、6（游客）时仍需要验证，，其他级别不显示验证组件；</p>
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