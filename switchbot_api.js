async function getDeviceList(token, secretKey) {
  const data = {
    token: token,
    secretKey: secretKey,
  };

  document.getElementById("getDeviceListLoading").innerHTML =
    "デバイスを取得中...";

  try {
    const response = await axios({
      url: "https://watalab.info/lab/asakura/api/get_device_list.php",
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      data: JSON.stringify(data),
    });

    const result = JSON.stringify(response.data);
    console.log(result);

    let table =
      '<table class="device-table"><tr><th>Device ID</th><th>Device Name</th><th>Device Type</th><th>Status</th><th>Command</th></tr>';

    response.data.body.deviceList.forEach((device) => {
      let statusHtml = "";
      if (device.status) {
        device.status.forEach((status) => {
          statusHtml += `<input type="checkbox" onclick="onClickCheckbox(this)" onchange="clearAuthGuestToken()" value="${device.deviceId}/${device.deviceType}/${device.deviceName}/status/${status.key}"> ${status.key}<br>`;
        });
      }

      let commandsHtml = "";
      if (device.commands) {
        device.commands.forEach((command) => {
          commandsHtml += `<input type="checkbox" onclick="onClickCheckbox(this)" onchange="clearAuthGuestToken()" value="${device.deviceId}/${device.deviceType}/${device.deviceName}/command/${command.command}"> ${command.command}<br>`;
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

    document.getElementById("getDeviceListLoading").innerHTML = "";
    document.getElementById("deviceListContainer").innerHTML = table;
  } catch (error) {
    console.error("Error: " + error);
  }
}

function clearAuthGuestToken() {
  if (document.getElementById("authGuestToken").textContent) {
    document.getElementById("authGuestToken").textContent = "";
    document.getElementById("encryptMessages").innerHTML =
      "データが変更されました。再度「暗号化」ボタンを押し、データを暗号化してください。";
    document.getElementById("decode-button").disabled = true;
    document.getElementById("json-download-button").disabled = true;
  }
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

  // TODO: 開始日と終了日のバリデーションを教授に確認、要素として必要かどうか
  const inputs = [
    { id: "description", name: "説明" },
    // { id: "startTime", name: "開始日" },
    // { id: "endTime", name: "終了日" },
    { id: "password", name: "パスワード" },
  ];

  const checkboxes = document.querySelectorAll('input[type="checkbox"]');
  const isChecked = Array.from(checkboxes).some((checkbox) => checkbox.checked);

  if (!isChecked) {
    isValid = false;
    errorMessage +=
      "少なくとも一つのcommand、またはstatusを選択してください。<br>";
  }

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
  const secretKey = document.getElementById("secretKey").value;
  const password = document.getElementById("password").value;
  const description = document.getElementById("description").value;
  const startTime = document.getElementById("startTime").value;
  const endTime = document.getElementById("endTime").value;
  const version = document.getElementById("version").value;
  const vendor = document.getElementById("vendor").value;
  const deviceList = deviceArray; //checkboxで選択された内容を取得

  const data = {
    token: token,
    password: password,
    secretKey: secretKey,
    description: description,
    startTime: startTime,
    endTime: endTime,
    version: version,
    vendor: vendor,
    deviceList: deviceList,
  };

  axios({
    method: "post",
    url: "https://watalab.info/lab/asakura/api/encrypt.php",
    data: JSON.stringify(data),
    headers: { "Content-Type": "application/json" },
  })
    .then(function (response) {
      document.getElementById("authGuestToken").textContent =
        response.data.authGuestToken;

      document.getElementById("decode-button").disabled = false;
      document.getElementById("json-download-button").disabled = false;
      document.getElementById("encryptMessages").innerHTML = "";
    })
    .catch(function (error) {
      console.error("Error: " + error);
    });
}

function clickBtnDec() {
  const authGuestToken = document.getElementById("authGuestToken").value;
  const password = document.getElementById("password").value;

  const data = {
    authGuestToken: authGuestToken,
    password: password,
  };

  axios({
    method: "post",
    url: "https://watalab.info/lab/asakura/api/decrypt.php",
    data: JSON.stringify(data),
    headers: { "Content-Type": "application/json" },
  })
    .then(function (response) {
      //アロー関数に修正
      const result = JSON.stringify(response.data, null, 2);
      document.getElementById("decodeData").textContent = result;
    })
    .catch(function (error) {
      console.error("Error: " + error);
    });
}

function jsonDownload() {
  //downloadJsonFile
  const authGuestToken = document.getElementById("authGuestToken").textContent;
  const password = document.getElementById("password").value;

  const guestLoginInfo = {
    authGuestToken: authGuestToken,
    password: password,
  };

  const guestLoginJson = JSON.stringify(guestLoginInfo);

  const blob = new Blob([guestLoginJson], { type: "application/json" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "data.json"; //明示的にファイル名を指定
  a.click();
  window.URL.revokeObjectURL(url);
}

/**
 * デバイス毎のステータスを取得する
 * @param {*} token
 * @param {*} secretKey
 * @param {*} deviceList
 * @returns
 */
async function getStatus(authGuestToken, password, deviceId) {
  const loadingElement = document.getElementById(`${deviceId}-button`);
  loadingElement.innerHTML = "ステータスを取得中...";
  const data = {
    authGuestToken,
    password,
    deviceId,
  };

  try {
    const response = await axios({
      method: "post",
      url: "https://watalab.info/lab/asakura/api/get_allow_device_status.php",
      data: JSON.stringify(data),
      headers: {
        "Content-Type": "application/json",
      },
    });
    Object.keys(response.data.body.status).forEach((key) => {
      const pTagId = `allowStatus${deviceId}${key}`;
      const pTag = document.getElementById(`${pTagId}`);
      if (pTag) {
        pTag.innerHTML = `${key}: ${response.data.body.status[key]}`;
      }
    });
    loadingElement.innerHTML = "ステータスを更新";
    return response.data;
  } catch (error) {
    console.error("Error: " + error);
  }
}

/**
 * 暗号化されたtokenをもとにデバイスのコマンドを叩く
 * @param {*} token
 * @param {*} secretKey
 * @param {*} deviceId
 * @param {*} command
 */
async function operateSwitch(authGuestToken, password, deviceId, command) {
  const data = {
    authGuestToken,
    password,
    deviceId,
    commands: {
      command: command,
      parameter: "default",
      commandType: "command",
    },
  };

  try {
    const pTagId = `allowStatus${deviceId}power`;
    const pTag = document.getElementById(`${pTagId}`);
    pTag.innerHTML = "power: 通信中...";
    const response = await axios({
      method: "post",
      url: "https://watalab.info/lab/asakura/api/operate_command.php",
      data: JSON.stringify(data),
      headers: {
        "Content-Type": "application/json",
      },
    });
    if (response.data.statusCode === 100) {
      pTag.innerHTML = `power: ${response.data.power}`;
    } else {
      pTag.innerHTML = `power: ${response.data.message}`;
    }
  } catch (error) {
    console.error("Error: " + error);
  }
}
