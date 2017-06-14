var gtfunc = function () {
  zbp.cookie.set("geeteston",1,1);
  zbp.cookie.set("geetestchallenge", "", 1);
  zbp.cookie.set("geetestvalidate", "", 1);
  zbp.cookie.set("geetestseccode", "", 1);
  $.ajax({
    // 获取id，challenge，success（是否启用failback）
    url: zbp.options.bloghost+"zb_users/plugin/xnxf_geetest/initialize.php?act=geetest&rnd=" + (new Date()).getTime(), // 加随机数防止缓存
    type: "get",
    dataType: "json",
    success: function (data) {
      var gtObj = new window.Geetest({
        gt: data.gt,
        challenge: data.challenge,
        product: "float",
        offline: !data.success
      });
      gtObj.appendTo('#xnxf-geetest');
      gtObj.onSuccess(function () {
        var validate = gtObj.getValidate();
        zbp.cookie.set("geetestchallenge", validate.geetest_challenge, 1);
        zbp.cookie.set("geetestvalidate", validate.geetest_validate, 1);
        zbp.cookie.set("geetestseccode", validate.geetest_seccode, 1);
        zbp.cookie.set("geeteston",0,1);
      })
    }
  });
  //function(){gtObj.refresh();}
}
