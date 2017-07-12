<?php
require '../../../zb_system/function/c_system_base.php';
require_once 'function.php';
$zbp->Load();
$zbp->StartSession();
$action = GetVars('act', 'GET');
if($action == "geetest"){
    $GtSdk = new GeetestLib3($zbp->Config('xnxf_geetest')->captchaid, $zbp->Config('xnxf_geetest')->privatekey);
    $status = $GtSdk->pre_process();
    $_SESSION['gtserver'] = $status;
    echo $GtSdk->get_response_str();
}
?>