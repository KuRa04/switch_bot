// コマンドを保持する配列
let deviceArray = [];

function onCheckboxClick(checkbox) {
  // チェックボックスの値を取得
  const value = checkbox.value;

  // デバイスID、タイプ、コマンドを取得
  const [deviceId, deviceType, deviceName, type, allowObj] = value.split("/");

  // デバイスIDを持つエントリを探す
  let deviceEntry = deviceArray.find((entry) => entry.deviceId === deviceId);

  if (!deviceEntry) {
    // デバイスIDが存在しない場合、新しいエントリを作成
    deviceEntry = {
      deviceId: deviceId,
      deviceType: deviceType,
      deviceName: deviceName,
      commands: {},
      status: {},
    };
    deviceArray.push(deviceEntry);
  }

  // コマンドの状態を更新
  if (checkbox.checked) {
    if (type === "status") {
      deviceEntry.status[allowObj] = true;
    } else {
      deviceEntry.commands[allowObj] = true;
    }
  } else {
    if (type === "status") {
      deviceEntry.status[allowObj] = false;
    } else {
      deviceEntry.commands[allowObj] = false;
    }
  }
  console.log(deviceArray);
}

function getDeviceList() {
  const token = document.getElementById("token").value;
  const secret_key = document.getElementById("secret_key").value;

  const data = {
    token: token,
    secret_key: secret_key,
  };

  let result = {};

  try {
    $.ajax({
      url: "https://watalab.info/lab/asakura/api/get_device_list.php",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(data),
      success: function (response) {
        result = JSON.stringify(response); // 最初のリクエストの結果をresultに格納
        console.log(result);

        // Table要素を生成
        let table =
          '<table border="1"><tr><th>Device ID</th><th>Device Name</th><th>Device Type</th><th>Commands</th><th>Status</th></tr>';

        // responseからデバイスリストを取得し、Tableの行を生成
        response.body.deviceList.forEach((device) => {
          let statusHtml = "";
          if (device.status) {
            device.status.forEach((status) => {
              statusHtml += `<input type="checkbox" onclick="onCheckboxClick(this)" value="${device.deviceId}/${device.deviceType}/${device.deviceName}/status/${status.key}"> ${status.key}<br>`;
            });
          }

          let commandsHtml = "";
          if (device.commands) {
            device.commands.forEach((command) => {
              commandsHtml += `<input type="checkbox" onclick="onCheckboxClick(this)" value="${device.deviceId}/${device.deviceType}/${device.deviceName}/command/${command.command}"> ${command.command}<br>`;
            });
          }
          table += `<tr>
            <td>${device.deviceId}</td>
            <td>${device.deviceName}</td>
            <td>${device.deviceType}</td>
            <td>${statusHtml}</td>
            <td>${commandsHtml}</td>
          </tr>`;
        });

        table += "</table>";

        // 生成したTableをHTMLに展開
        document.getElementById("deviceListContainer").innerHTML = table;
      },
      error: function (xhr, status, error) {
        console.error("Error: " + error);
        console.error("Status: " + status);
        console.error(xhr);
      },
    });
  } catch (error) {
    console.log("error: ", error);
  }
}

// デバイスを選択する関数
function clickBtn() {
  const dlist = [];
  //formの中のdeviceの中のpickを取得
  const device = document.device.pick;
  console.log(document.device);

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
  const secret = document.getElementById("secret_key").value;
  const description = document.getElementById("description").value;
  const startTime = document.getElementById("startTime").value;
  const endTime = document.getElementById("endTime").value;
  const version = document.getElementById("version").value;
  const vender = document.getElementById("vender").value;
  const deviceList = deviceArray;

  var data = {
    t: token,
    p: password,
    s: secret,
    description: description,
    st: startTime,
    et: endTime,
    version: version,
    vender: vender,
    deviceList: deviceList,
  };

  $.ajax({
    url: "https://watalab.info/lab/asakura/api/encrypt.php",
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
  const encodeData = document.getElementById("encdata").value;
  const password = document.getElementById("password").value;
  const url = document.getElementById("guest_login_page_url").value;
  const match = url.match(/mp=(.*)/);
  const managePassword = match ? match[1] : "";

  var data = {
    x: encodeData,
    p: password,
    mp: managePassword,
  };

  $.ajax({
    url: "https://watalab.info/lab/asakura/api/decrypt.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: function (response) {
      document.getElementById("decdata").textContent = response;
    },
    error: function (xhr, status, error) {
      console.error("Error: " + error);
      console.error("Status: " + status);
      console.error(xhr);
    },
  });
}

function jsonDownload() {
  const auth_guest_token = document.getElementById("encdata").textContent;
  const guest_login_page_url = document.getElementById(
    "guest_login_page_url"
  ).textContent;
  const password = document.getElementById("password").value;

  const guest_login_info = {
    token: auth_guest_token,
    guest_login_page_url: guest_login_page_url,
    password: password,
  };

  const guest_login_json = JSON.stringify(guest_login_info);

  const blob = new Blob([guest_login_json], { type: "application/json" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "data.json";
  a.click();
  window.URL.revokeObjectURL(url);
}
