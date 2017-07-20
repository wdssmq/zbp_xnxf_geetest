var gtplug = {};
(function () {
  zbp.cookie.set("geeteston", 1, 1);
  zbp.cookie.set("geetestchallenge", "", 1);
  zbp.cookie.set("geetestvalidate", "", 1);
  zbp.cookie.set("geetestseccode", "", 1);
  if ($('#xnxf-geetest').length == 0)
    return;
  $.ajax({
    // 获取id，challenge，success（是否启用failback）
    url: zbp.options.bloghost + "zb_users/plugin/xnxf_geetest/initialize.php?act=geetest&rnd=" + (new Date()).getTime(), // 加随机数防止缓存
    type: "get",
    dataType: "json",
    success: function (data) {
      initGeetest({
        // 以下配置参数来自服务端 SDK
        gt: data.gt,
        challenge: data.challenge,
        offline: !data.success,
        product: "float",
        new_captcha: data.new_captcha
      }, function (captchaObj) {
        captchaObj.appendTo('#xnxf-geetest');
        if (captchaObj.reset) {
          captchaObj.refresh = captchaObj.reset;
          // gtplug.san = function(){
            // $('#xnxf-geetest').hide();
            // captchaObj.refresh();
          // };
        }
        zbp.plugin.on("comment.postsuccess", "geetest", function () {
          captchaObj.refresh();
          // $('#xnxf-geetest').show();
        });
        // captchaObj.onError();

        captchaObj.onSuccess(function () {
          var validate = captchaObj.getValidate();
          zbp.cookie.set("geetestchallenge", validate.geetest_challenge, 1);
          zbp.cookie.set("geetestvalidate", validate.geetest_validate, 1);
          zbp.cookie.set("geetestseccode", validate.geetest_seccode, 1);
          zbp.cookie.set("geeteston", 0, 1);
          for (var key in gtplug) {
            gtplug[key]();
          }
        });
      });
    }
  });
  //function(){captchaObj.refresh();}
})();
