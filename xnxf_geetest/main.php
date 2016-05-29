<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action='root';
if (!$zbp->CheckRights($action)) {$zbp->ShowError(6);die();}
if (!$zbp->CheckPlugin('xnxf_geetest')) {$zbp->ShowError(48);die();}

$blogtitle='xnxf_geetest';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';

if(count($_POST)>0){
	$zbp->Config('xnxf_geetest')->captchaid=trim($_POST['id']);
	$zbp->Config('xnxf_geetest')->privatekey=trim($_POST['key']);
	$zbp->SaveConfig('xnxf_geetest');
  // $zbp -> BuildTemplate();
	$zbp->SetHint('good');
	Redirect('./main.php');
}
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle;?></div>
  <div class="SubMenu">
  </div>
  <div id="divMain2">
    <form action="main.php?act=save" method="post">
      <table border="1" width="100%" class="tableBorder">
        <tr>
          <th scope="col" width="20%">配置项</th>
          <th scope="col">内容</th>
        </tr>
        <tr>
          <td><label for="id">极验ID</label></td>
          <td><textarea style="width: 80%;" name="id" id="id"><?php echo $zbp->Config('xnxf_geetest')->captchaid ?></textarea></td>
        </tr>
        <tr>
          <td><label for="key">key</label></td>
          <td><textarea style="width: 80%;" name="key" id="key"><?php echo $zbp->Config('xnxf_geetest')->privatekey ?></textarea></td>
        </tr>
        <tr>
          <td><input type="submit" value="提交" /></td><td> </td>
        </tr>
      </table>
    </form>
    <p>1、在模板文件commentpost.php中加入<b>{$geetest}</b>以显示验证模块--例：{if $user.ID>0}…{else}…{if $option['ZC_COMMENT_VERIFY_ENABLE']}…{/if}{/if}<b>{$geetest}</b>--</p>
    <p>2、注册 <a rel="nofollow" style="text-decoration: underline; color: rgb(0, 112, 192);" href="http://geetest.com/" title="极验验证" target="_blank">http://geetest.com/</a> 获取验证ID和Key写入插件配置项；</p>
    <p>3、登陆用户权限等级为5（评论者）、6（游客）时仍需要验证，，其他级别不显示验证组件；</p>

  </div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>