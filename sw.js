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
  const managePassword = document.getElementById("managePassword").value;
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
    managePassword: managePassword,
    version: version,
    vender: vender,
  };

  $.ajax({
    url: "https://watalab.info/lab/asakura/switchbot_api.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: function (response) {
      document.getElementById("encdata").textContent = response.enc;
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
      document.getElementById("decdata").textContent = JSON.stringify(response);
    },
    error: function (xhr, status, error) {
      console.error("Error: " + error);
      console.error("Status: " + status);
      console.error(xhr);
    },
  });
}
