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
  // チェックボックスがクリックされたときにチェック状態を反転
  $("input[type=checkbox]").click(function () {
    $(this).prop("checked", !$(this).prop("checked"));
  });

  // テーブル行がクリックされたときにその行内のチェックボックスのチェック状態を反転
  $("table tr").click(function () {
    var c = $(this).children("td").children("input[type=checkbox]");
    c.prop("checked", !c.prop("checked"));
  });
});

// デバイスを選択する関数
function clickBtn() {
  const dlist = [];
  const device = document.device.pick;

  // deviceが単一の要素か配列かを判定
  if (device.length === undefined) {
    if (device.checked) {
      dlist.push(device.value);
    }
  } else {
    // チェックされたデバイスをリストに追加
    for (let i = 0; i < device.length; i++) {
      if (device[i].checked) {
        dlist.push(device[i].value);
      }
    }
  }

  // 選択されたデバイスIDを画面に表示
  document.getElementById("dlist").textContent = dlist.join(", ");

  // 赤外線リモコンのリストも処理する場合のコード（コメントアウト）
  /*
  const rlist = [];
  const remote = document.remote.pick;

  if (remote.length === undefined) {
    if (remote.checked) {
      rlist.push(remote.value);
    }
  } else {
    for (let i = 0; i < remote.length; i++) {
      if (remote[i].checked) {
        rlist.push(remote[i].value);
      }
    }
  }

  document.getElementById("rlist").textContent = rlist.join(', ');
  */
}

// 暗号化のためにCGIをたたく
function clickBtnEnc() {
  var password = document.getElementById("password").value;
  var token = document.getElementById("token").value;
  var dlist = document.getElementById("dlist").textContent;
  var data = dlist;

  var param = "t=" + token + "&p=" + password + "&d=" + data;

  ret = $.post(
    "http://localhost:8000/encsw_json.cgi",
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

  ret2 = $.post("http://localhost:8000/swstatus.cgi", param, (data, status) => {
    document.getElementById("decdata").textContent = ret2.responseText;
  });
}
