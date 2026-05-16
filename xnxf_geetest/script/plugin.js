/* global gtplug:false */
window.addEventListener("DOMContentLoaded", function() {
  const gob_pass = zbp.cookie.get("capt_GeetPass");
  let cur_pass = false;

  const gtVersion = window.xnxf_geetest_version || "3";
  const urlRnd = new Date().getTime();
  const apiCallback = function(captchaObj) {
    // 旧版兼容？？？
    if (captchaObj.reset) {
      captchaObj.refresh = captchaObj.reset;
    }
    const gtMap = {
      v3: [
        "geetest_challenge",
        "geetest_validate",
        "geetest_seccode",
      ],
      v4: [
        "captcha_output",
        "gen_time",
        "lot_number",
        "pass_token",
      ],
    };
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
        // console.log(validate);

        if (gtVersion === "4") {
          gtMap.v4.forEach(function(key) {
            zbp.cookie.set("geetest_" + key, validate[key] || "", 1);
          });
        } else {
          gtMap.v3.forEach(function(key) {
            zbp.cookie.set(key, validate[key] || "", 1);
          });
        }
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
    zbp.plugin.on("comment.post.success", "geetest", function() {
      captchaObj.refresh();
    });
  };

  $.ajax({
    url: `${zbp.options.bloghost}zb_users/plugin/xnxf_geetest/initialize.php?act=geetest&ver=${gtVersion}&rnd=${urlRnd}`,
    dataType: "json",
    success: function(data) {
      if (gtVersion === "4") {
        window.initGeetest4(data, apiCallback);
      } else {
        window.initGeetest(data, apiCallback);
      }
    },
  });
  // function(){captchaObj.refresh();}
});
