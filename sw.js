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

  // リクエストデータをJSON形式で構築
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

  // jQueryの$.ajaxを使用してPOSTリクエストを送信
  $.ajax({
    //fastAPI url: "http://127.0.0.1:8000/api/encode_token",
    url: "http://[::]:9000/cgi-bin/encsw_json.cgi",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: function (response) {
      // レスポンスデータを処理
      document.getElementById("encdata").textContent = response.enc;
    },
    error: function (xhr, status, error) {
      console.error("Error: " + error);
      console.error("Status: " + status);
      console.error(xhr);
    },
  });
}

// 確認のための、復号化のためにCGIをたたく
function clickBtnDec() {
  var encodeData = document.getElementById("encdata").value;
  var password = document.getElementById("password").value;
  var dlist = document.getElementById("dlist").textContent;

  // リクエストデータをJSON形式で構築
  var data = {
    x: encodeData,
    p: password,
    d: dlist,
  };

  // jQueryの$.ajaxを使用してPOSTリクエストを送信
  $.ajax({
    url: "http://127.0.0.1:8000/api/decode_token",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: function (response) {
      // レスポンスデータを処理
      document.getElementById("decdata").textContent = JSON.stringify(response);
    },
    error: function (xhr, status, error) {
      console.error("Error: " + error);
      console.error("Status: " + status);
      console.error(xhr);
    },
  });
}
