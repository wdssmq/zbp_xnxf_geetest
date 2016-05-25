var gtfunc = function () {
  SetCookie("geeteston",1,1);
  SetCookie("geetestchallenge", "", 1);
  SetCookie("geetestvalidate", "", 1);
  SetCookie("geetestseccode", "", 1);
  var gtObj = new window.Geetest({
    gt: geetest_captchaid,
    challenge: geetest_challenge,
    product: "float"
  });
  gtObj.appendTo('#xnxf_geetest');
  gtObj.onSuccess(function () {
    var validate = gtObj.getValidate();
    SetCookie("geetestchallenge", validate.geetest_challenge, 1);
    SetCookie("geetestvalidate", validate.geetest_validate, 1);
    SetCookie("geetestseccode", validate.geetest_seccode, 1);
    SetCookie("geeteston",0,1);
  })
  //function(){gtObj.refresh();}
}
