/* global gtplug:false initGeetest:false */
window.addEventListener('DOMContentLoaded', function() {
  const gob_pass = zbp.cookie.get("capt_GeetPass");
  let cur_pass = false;
  // console.log(gob_pass, $("#xnxf-geetest").length);
  // if ($("#xnxf-geetest").length == 0) return;
  // console.log("geetest");
  $.ajax({
    // 获取id，challenge，success（是否启用failback）
    url:
      zbp.options.bloghost +
      "zb_users/plugin/xnxf_geetest/initialize.php?act=geetest&rnd=" +
      new Date().getTime(), // 加随机数防止缓存
    type: "get",
    dataType: "json",
    success: function(data) {
      initGeetest(
        {
          // 以下配置参数来自服务端 SDK
          gt: data.gt,
          challenge: data.challenge,
          offline: !data.success,
          product: "popup",
          new_captcha: data.new_captcha,
        },
        function(captchaObj) {
          // 旧版兼容？？？
          if (captchaObj.reset) {
            captchaObj.refresh = captchaObj.reset;
          }
          captchaObj.appendTo("#xnxf-geetest");
          captchaObj
            .onReady(function() {
              // 按钮提交事件
              $(".geetest-btn.verify").click(function() {
                if (cur_pass || gob_pass) {
                  return;
                }
                // 未完成验证直接点击按钮时添加标记并触发验证
                $(this).addClass("wait");
                $(".geetest_radar_tip_content").click();
                // captchaObj.verify(); // 触发验证
                return false;
              });
            })
            .onSuccess(function() {
              const validate = captchaObj.getValidate();
              zbp.cookie.set("geetestchallenge", validate.geetest_challenge, 1);
              zbp.cookie.set("geetestvalidate", validate.geetest_validate, 1);
              zbp.cookie.set("geetestseccode", validate.geetest_seccode, 1);
              zbp.cookie.set("geeteston", 0, 1);
              // 标记为验证通过
              cur_pass = true;
              // 如果有标记中的按钮，替换为完成状态
              if ($(".geetest-btn.verify.wait").length) {
                $(".geetest-btn.verify.wait")
                  .addClass("done")
                  .removeClass("wait");
                // 这里之前为什么要写跳转来着？
                const href = $(".geetest-btn.verify.done").attr("href") || "";
                if (href) {
                  location.href = $(".geetest-btn.verify.done").attr("href");
                }
              }
              for (let key in gtplug) {
                gtplug[key]();
              }
            })
            .onError(function() {
              // your code
            });
          zbp.plugin.on("comment.postsuccess", "geetest", function() {
            captchaObj.refresh();
          });
        },
      );
    },
  });
  // function(){captchaObj.refresh();}
});
