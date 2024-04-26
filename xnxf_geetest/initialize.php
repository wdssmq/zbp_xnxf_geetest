<?php
require '../../../zb_system/function/c_system_base.php';
require __DIR__ . '/lib/class.gt.php';
$zbp->Load();
$zbp->StartSession();
$action = GetVars('act', 'GET');
if ($action == "geetest") {
  $GtSdk = new GeetestLib($zbp->Config('xnxf_geetest')->captchaid, $zbp->Config('xnxf_geetest')->privatekey);
  $param = xnxf_geetest_GetGtParam();

  $status = $GtSdk->pre_process($param, 1);
  $_SESSION['gtserver'] = $status;
  header('Content-type: application/json');
  ob_clean();
  echo $GtSdk->get_response_str();
}
?>
