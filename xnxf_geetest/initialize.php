<?php
require '../../../zb_system/function/c_system_base.php';
require __DIR__ . '/lib/class.gt3.php';
require __DIR__ . '/lib/class.git4.php';

function xnxf_geetest_SendJsonHeaders()
{
  header('Content-type: application/json');
  header('Access-Control-Allow-Origin: http://localhost:3000');
}

function xnxf_geetest_OutputJson($payload)
{
  xnxf_geetest_SendJsonHeaders();
  ob_clean();
  if (is_string($payload)) {
    echo $payload;
  } else {
    echo json_encode($payload);
  }
  exit;
}

function xnxf_geetest_ResolveVersion($zbp)
{
  $version = trim((string) GetVars('ver', 'GET'));
  if ($version === '') {
    $version = trim((string) $zbp->Config('xnxf_geetest')->geetestVersion);
  }
  if ($version === '') {
    $version = '3';
  }
  return $version;
}

function xnxf_geetest_HandleV3Init($zbp)
{
  $GtSdk = new GeetestLib($zbp->Config('xnxf_geetest')->captchaid, $zbp->Config('xnxf_geetest')->privatekey);
  $param = xnxf_geetest_GetGtParam();
  $status = $GtSdk->pre_process($param, 1);
  $_SESSION['gtserver'] = $status;

  $payload = $GtSdk->get_response();
  $payload['offline'] = !$payload['success'];
  $payload['product'] = 'popup';
  xnxf_geetest_OutputJson($payload);
}

function xnxf_geetest_BuildV4InitPayload($zbp)
{
  $captchaId = trim((string) $zbp->Config('xnxf_geetest')->captchaid);
  $privateKey = trim((string) $zbp->Config('xnxf_geetest')->privatekey);

  if ($captchaId === '') {
    return array(
      'success' => 0,
      'message' => 'GT4 captcha_id is empty',
    );
  }

  $payload = array(
    'success' => 1,
    'version' => '4',
    'captchaId' => $captchaId,
    'product' => 'popup',
  );

  if ($privateKey === '') {
    $payload['warning'] = 'GT4 private key is empty';
  }

  return $payload;
}

function xnxf_geetest_HandleV4Init($zbp)
{
  $payload = xnxf_geetest_BuildV4InitPayload($zbp);
  if (!is_array($payload)) {
    xnxf_geetest_OutputJson(array(
      'success' => 0,
      'message' => 'GT4 init placeholder not implemented',
    ));
  }

  xnxf_geetest_OutputJson($payload);
}

$zbp->Load();
$zbp->StartSession();
$action = GetVars('act', 'GET');

if ($action !== 'geetest') {
  xnxf_geetest_OutputJson(array(
    'success' => 0,
    'message' => 'invalid action',
  ));
}

$version = xnxf_geetest_ResolveVersion($zbp);
if ($version === '3') {
  xnxf_geetest_HandleV3Init($zbp);
}

if ($version === '4') {
  xnxf_geetest_HandleV4Init($zbp);
}

xnxf_geetest_OutputJson(array(
  'success' => 0,
  'message' => 'unsupported geetest version',
));
?>
