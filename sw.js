// テーブルのカーソル行に色付け
$(function () {
  var tmpColor;
  var tmpIdx;
  var highLightColor = "#dff1ff";
  var tableRows = $("table tbody tr");

  tableRows.hover(
    // カーソルが乗った時の処理
    function () {
      tmpColor = $(this).find("th, td").css("background-color");
      tmpIdx = tableRows.index(this);
      tableRows.eq(tmpIdx).find("th, td").css("background", highLightColor);
    },

    // カーソルが外れた時の処理
    function () {
      tableRows.eq(tmpIdx).find("th, td").css("background", tmpColor);
    }
  );
});

// テーブルの行のクリックでチェック
$(document).ready(function () {
  $("input[type=checkbox]").click(function () {
    var t = $(this).prop;
    if (t("checked")) t("checked", "");
    else t("checked", "checked");
  });

  $("table tr").click(function () {
    var c = $(this).children("td").children("input[type=checkbox]");
    if (c.prop("checked")) c.prop("checked", "");
    else c.prop("checked", "checked");
  });
});

// デバイスを選択
function clickBtn() {
  const dlist = [];
  const device = document.device.pick;
  console.log(device);

  for (let i = 0; i < device.length; i++) {
    if (device[i].checked) {
      dlist.push('"' + device[i].value + '"');
    }
  }

  const rlist = [];
  const remote = document.remote.pick;
  console.log(remote);

  for (let i = 0; i < remote.length; i++) {
    if (remote[i].checked) {
      rlist.push('"' + remote[i].value + '"');
    }
  }

  document.getElementById("dlist").textContent = dlist;
  document.getElementById("rlist").textContent = rlist;
}

// 暗号化のためにCGIをたたく
function clickBtnEnc() {
  var password = document.getElementById("password").value;
  var token = document.getElementById("token").value;
  var dlist = document.getElementById("dlist").textContent;
  var data = dlist;

  var param = "t=" + token + "&p=" + password + "&d=" + data;

  ret = $.post(
    "https://watalab.info/sample/sw/encsw_json.cgi",
    param,
    (data, status) => {
      document.getElementById("encdata").textContent = ret.responseJSON["enc"];
    }
  );
}

// 確認のための、復号化のためにCGIをたたく
function clickBtnDec() {
  var password2 = document.getElementById("password").value;
  var encdata = document.getElementById("encdata").textContent;

  var param = "x=" + encdata + "&p=" + password2;

  ret2 = $.post(
    "https://watalab.info/sample/sw/swstatus.cgi",
    param,
    (data, status) => {
      document.getElementById("decdata").textContent = ret2.responseText;
    }
  );
}
