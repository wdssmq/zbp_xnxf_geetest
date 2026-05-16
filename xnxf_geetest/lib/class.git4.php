<?php

/**
 * Geetest 4.0 secondary verification helper.
 *
 * Reference: official GT4 demo flow
 */
class GeetestLib4
{
  const API_SERVER = 'http://gcaptcha4.geetest.com';

  public static $connectTimeout = 2;
  public static $socketTimeout = 5;

  private $captchaId;
  private $captchaKey;
  private $apiServer;
  private $lastResponse = '';
  private $lastHttpCode = 0;

  public function __construct($captchaId, $captchaKey, $apiServer = self::API_SERVER)
  {
    $this->captchaId = trim((string) $captchaId);
    $this->captchaKey = trim((string) $captchaKey);
    $this->apiServer = rtrim(trim((string) $apiServer), '/');
  }

  public function get_last_response()
  {
    return $this->lastResponse;
  }

  public function get_last_http_code()
  {
    return $this->lastHttpCode;
  }

  public function validate($lotNumber, $captchaOutput, $passToken, $genTime)
  {
    if ($this->captchaId === '' || $this->captchaKey === '') {
      return array(
        'result' => 'fail',
        'reason' => 'captcha_id or captcha_key is empty',
      );
    }

    $lotNumber = trim((string) $lotNumber);
    $captchaOutput = trim((string) $captchaOutput);
    $passToken = trim((string) $passToken);
    $genTime = trim((string) $genTime);
    if ($lotNumber === '' || $captchaOutput === '' || $passToken === '' || $genTime === '') {
      return array(
        'result' => 'fail',
        'reason' => 'missing required parameters',
      );
    }

    $signToken = hash_hmac('sha256', $lotNumber, $this->captchaKey);
    $query = array(
      'lot_number' => $lotNumber,
      'captcha_output' => $captchaOutput,
      'pass_token' => $passToken,
      'gen_time' => $genTime,
      'sign_token' => $signToken,
    );
    $url = sprintf('%s/validate?captcha_id=%s', $this->apiServer, urlencode($this->captchaId));
    $response = $this->post_request($url, $query);
    if ($response === false || $response === '') {
      return array(
        'result' => 'fail',
        'reason' => 'request geetest api fail',
      );
    }

    $json = json_decode($response, true);
    if (!is_array($json)) {
      return array(
        'result' => 'fail',
        'reason' => 'invalid geetest api response',
      );
    }
    if (!isset($json['result'])) {
      $json['result'] = 'fail';
    }
    if (!isset($json['reason'])) {
      $json['reason'] = '';
    }
    return $json;
  }

  public function validate_from_array($data)
  {
    if (!is_array($data)) {
      $data = array();
    }
    return $this->validate(
      isset($data['lot_number']) ? $data['lot_number'] : '',
      isset($data['captcha_output']) ? $data['captcha_output'] : '',
      isset($data['pass_token']) ? $data['pass_token'] : '',
      isset($data['gen_time']) ? $data['gen_time'] : ''
    );
  }

  private function post_request($url, $postData)
  {
    $data = http_build_query($postData);
    $this->lastHttpCode = 0;
    $this->lastResponse = '';

    if (function_exists('curl_exec')) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout);
      curl_setopt($ch, CURLOPT_TIMEOUT, self::$socketTimeout);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      $response = curl_exec($ch);
      $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      $this->lastHttpCode = $httpCode;
      $this->lastResponse = $response === false ? '' : $response;
      if ($response === false || $httpCode !== 200) {
        return false;
      }
      return $response;
    }

    $options = array(
      'http' => array(
        'method' => 'POST',
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'content' => $data,
        'timeout' => self::$connectTimeout + self::$socketTimeout,
      ),
    );
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    $httpCode = 0;
    if (isset($http_response_header[0])) {
      if (preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
        $httpCode = (int) $matches[1];
      }
    }
    $this->lastHttpCode = $httpCode;
    $this->lastResponse = $result === false ? '' : $result;
    if ($result === false || $httpCode !== 200) {
      return false;
    }
    return $result;
  }
}
