// テーブルのカーソル行に色付け
$(function () {
  var tmpColor;
  var tmpIdx;
  var highLightColor = "#dff1ff";
  var tableRows = $("table tbody tr");

  tableRows.hover(
    function () {
      tmpColor = $(this).find("th, td").css("background-color");
      tmpIdx = tableRows.index(this);
      tableRows.eq(tmpIdx).find("th, td").css("background", highLightColor);
    },
    function () {
      tableRows.eq(tmpIdx).find("th, td").css("background", tmpColor);
    }
  );
});

// テーブルの行のクリックでチェック
$(document).ready(function () {
  $("input[type=checkbox]").click(function () {
    $(this).prop("checked", !$(this).prop("checked"));
  });

  $("table tr").click(function () {
    var c = $(this).children("td").children("input[type=checkbox]");
    c.prop("checked", !c.prop("checked"));
  });
});

// デバイスを選択する関数
function clickBtn() {
  const dlist = [];
  const device = document.device.pick;

  if (device.length === undefined) {
    if (device.checked) {
      dlist.push(device.value);
    }
  } else {
    for (let i = 0; i < device.length; i++) {
      if (device[i].checked) {
        dlist.push(device[i].value);
      }
    }
  }

  document.getElementById("dlist").textContent = dlist.join(", ");
}

// 暗号化のためにPHPをたたく
function clickBtnEnc() {
  const password = document.getElementById("password").value;
  const token = document.getElementById("token").value;
  const secret = document.getElementById("secret").value;
  const description = document.getElementById("description").value;
  const startTime = document.getElementById("startTime").value;
  const endTime = document.getElementById("endTime").value;
  const version = document.getElementById("version").value;
  const vender = document.getElementById("vender").value;
  const dlist = document.getElementById("dlist").textContent;

  var data = {
    t: token,
    p: password,
    d: dlist,
    s: secret,
    desc: description,
    st: startTime,
    et: endTime,
    version: version,
    vender: vender,
  };

  $.ajax({
    url: "https://watalab.info/lab/asakura/switchbot_api.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: function (response) {
      // switchbot_apiで作成したencを取得して、encdataに代入。
      //guest_login.htmlのURLを表示するpタグを作成。queryパラメータを含めたURLを表示。
      document.getElementById("encdata").textContent = response.enc;
      document.getElementById("guest_login_page_url").textContent =
        response.guest_login_page_url;
    },
    error: function (xhr, status, error) {
      console.error("Error: " + error);
      console.error("Status: " + status);
      console.error(xhr);
    },
  });
}

// 確認のための、復号化のためにPHPをたたく
function clickBtnDec() {
  var encodeData = document.getElementById("encdata").value;
  var password = document.getElementById("password").value;
  var dlist = document.getElementById("dlist").textContent;

  var data = {
    x: encodeData,
    p: password,
    d: dlist,
  };

  $.ajax({
    url: "https://watalab.info/lab/asakura/switchbot_api.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: function (response) {
      const responseJson = JSON.stringify(response);
      document.getElementById("decdata").textContent = JSON.stringify(response);
    },
    error: function (xhr, status, error) {
      console.error("Error: " + error);
      console.error("Status: " + status);
      console.error(xhr);
    },
  });
}

function jsonDownload() {
  const json = document.getElementById("decdata").textContent;
  const blob = new Blob([json], { type: "application/json" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "data.json";
  a.click();
  window.URL.revokeObjectURL(url);
}
