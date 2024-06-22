function getDeviceList() {
  const token = document.getElementById("token").value;
  const secret_key = document.getElementById("secret_key").value;

  const data = {
    token: token,
    secret_key: secret_key,
  };

  document.getElementById("deviceListContainer").innerHTML = "Loading...";

  axios({
    url: "https://watalab.info/lab/asakura/api/get_device_list.php",
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    data: JSON.stringify(data),
  })
    .then(function (response) {
      const result = JSON.stringify(response.data);
      console.log(result);

      let table =
        '<table border="1"><tr><th>Device ID</th><th>Device Name</th><th>Device Type</th><th>Commands</th><th>Status</th></tr>';

      response.data.body.deviceList.forEach((device) => {
        let statusHtml = "";
        if (device.status) {
          device.status.forEach((status) => {
            statusHtml += `<input type="checkbox" onclick="onClickCheckbox(this)" value="${device.deviceId}/${device.deviceType}/${device.deviceName}/status/${status.key}"> ${status.key}<br>`;
          });
        }

        let commandsHtml = "";
        if (device.commands) {
          device.commands.forEach((command) => {
            commandsHtml += `<input type="checkbox" onclick="onClickCheckbox(this)" value="${device.deviceId}/${device.deviceType}/${device.deviceName}/command/${command.command}"> ${command.command}<br>`;
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

      document.getElementById("deviceListContainer").innerHTML = table;
    })
    .catch(function (error) {
      console.error("Error: ", error);
      // エラー発生時もLoadingを消す
      document.getElementById("deviceListContainer").innerHTML =
        "An error occurred";
    });
}

let deviceArray = [];
function onClickCheckbox(checkbox) {
  const value = checkbox.value;
  const [deviceId, deviceType, deviceName, type, allowObj] = value.split("/");

  let deviceEntry = deviceArray.find((entry) => entry.deviceId === deviceId);

  if (!deviceEntry) {
    deviceEntry = {
      deviceId: deviceId,
      deviceType: deviceType,
      deviceName: deviceName,
      commands: {},
      status: {},
    };
    deviceArray.push(deviceEntry);
  }

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

function validateInputs() {
  let isValid = true;
  let errorMessage = "";

  // 必要な入力フィールドのリスト
  const inputs = [
    { id: "description", name: "説明" },
    { id: "startTime", name: "開始日" },
    { id: "endTime", name: "終了日" },
    { id: "password", name: "パスワード" },
  ];

  // 各入力フィールドをチェック
  inputs.forEach((input) => {
    const value = document.getElementById(input.id).value;
    if (!value) {
      isValid = false;
      errorMessage += `${input.name}が入力されていません。<br>`;
    }
  });

  // エラーメッセージを表示またはクリア
  document.getElementById("errorMessages").innerHTML = errorMessage;

  return isValid;
}

function clickBtnEnc() {
  if (!validateInputs()) {
    return;
  }

  const token = document.getElementById("token").value;
  const password = document.getElementById("password").value;
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

  axios({
    method: "post",
    url: "https://watalab.info/lab/asakura/api/encrypt.php",
    data: JSON.stringify(data),
    headers: { "Content-Type": "application/json" },
  })
    .then(function (response) {
      document.getElementById("encdata").textContent = response.data.enc;
      document.getElementById("guest_login_page_url").textContent =
        response.data.guest_login_page_url;
    })
    .catch(function (error) {
      console.error("Error: " + error);
    });
}

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

  axios({
    method: "post",
    url: "https://watalab.info/lab/asakura/api/decrypt.php",
    data: JSON.stringify(data),
    headers: { "Content-Type": "application/json" },
  })
    .then(function (response) {
      document.getElementById("decdata").textContent = response.data;
    })
    .catch(function (error) {
      console.error("Error: " + error);
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
