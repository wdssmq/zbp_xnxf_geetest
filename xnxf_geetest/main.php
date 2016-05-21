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
	$zbp->Config('xnxf_geetest')->captchaid=$_POST['id'];
	$zbp->Config('xnxf_geetest')->privatekey=$_POST['key'];
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
    <p><b>在模板文件b_article_commentpost.html中加入<#xnxf/geetest#>以显示验证模块</b></p>
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
  </div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>