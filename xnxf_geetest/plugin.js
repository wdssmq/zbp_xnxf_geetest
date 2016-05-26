var gtfunc = function () {
  SetCookie("geeteston",1,1);
  SetCookie("geetestchallenge", "", 1);
  SetCookie("geetestvalidate", "", 1);
  SetCookie("geetestseccode", "", 1);
  $.ajax({
    // 获取id，challenge，success（是否启用failback）
    url: "/zb_system/cmd.php?act=cmt&getgeetest=" + (new Date()).getTime(), // 加随机数防止缓存
    type: "get",
    dataType: "json",
    success: function (data) {
      var gtObj = new window.Geetest({
        gt: data.gt,
        challenge: data.challenge,
        product: "float",
        offline: !data.success
      });
      gtObj.appendTo('#xnxf_geetest');
      gtObj.onSuccess(function () {
        var validate = gtObj.getValidate();
        SetCookie("geetestchallenge", validate.geetest_challenge, 1);
        SetCookie("geetestvalidate", validate.geetest_validate, 1);
        SetCookie("geetestseccode", validate.geetest_seccode, 1);
        SetCookie("geeteston",0,1);
      })
    }
  });
  //function(){gtObj.refresh();}
}
