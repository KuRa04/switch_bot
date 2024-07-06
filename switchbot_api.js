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

    document.getElementById("getDeviceListLoading").innerHTML = "";
    document.getElementById("deviceListContainer").innerHTML = table;
  } catch (error) {
    console.error("Error: " + error);
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

  // 必要な入力フィールドのリスト
  const inputs = [
    { id: "description", name: "説明" },
    { id: "startTime", name: "開始日" },
    { id: "endTime", name: "終了日" },
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
  const deviceList = deviceArray;

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
      const result = JSON.stringify(response.data, null, 2);
      document.getElementById("decodeData").textContent = result;
    })
    .catch(function (error) {
      console.error("Error: " + error);
    });
}

function jsonDownload() {
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
  a.download = "data.json";
  a.click();
  window.URL.revokeObjectURL(url);
}

async function getAllowDeviceStatus(token, secretKey, deviceList) {
  const loadingElement = document.getElementById("get-status-loading");
  loadingElement.innerHTML = "デバイスを取得中...";
  const data = {
    token: token,
    secretKey: secretKey,
    deviceList: deviceList,
  };

  try {
    const response = await axios({
      method: "post",
      url: "https://watalab.info/lab/asakura/api/get_allow_device_list_status.php",
      data: JSON.stringify(data),
      headers: {
        "Content-Type": "application/json",
      },
    });
    loadingElement.innerHTML = "";
    return response.data;
  } catch (error) {
    console.error("Error: " + error);
  }
}

function setDeviceCommand(token, secretKey, deviceId, func) {
  const data = {
    token: token,
    secretKey: secretKey,
    deviceId: deviceId,
    commands: {
      command: func,
      parameter: "default",
      commandType: "command",
    },
  };

  axios({
    method: "post",
    url: "https://watalab.info/lab/asakura/api/set_allow_device_command.php",
    data: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then(function (response) {
      console.log(response.data);
    })
    .catch(function (error) {
      console.error("Error: " + error);
    });
}

async function printAllowDeviceTable(jsonData) {
  if (jsonData) {
    const allowDeviceStatus = await getAllowDeviceStatus(
      jsonData["token"],
      jsonData["secretKey"],
      jsonData["deviceList"]
    );

    let tableHtml =
      '<table class="device-table"><tr><th>Device ID</th><th>Device Name</th><th>Status</th><th>Command</th></tr>';

    jsonData["deviceList"].forEach((device) => {
      tableHtml += "<tr>";
      tableHtml += `<td>${device["deviceId"]}</td>`;
      tableHtml += `<td><a href='allow_device_detail.php?t=${encodeURIComponent(
        jsonData["token"]
      )}s=${encodeURIComponent(jsonData["secretKey"])}&d=${encodeURIComponent(
        device["deviceId"]
      )}'>${device["deviceName"]}</a></td>`;
      tableHtml += "<td>";
      if (device["status"]) {
        allowDeviceStatus.forEach(function (allowDevice) {
          if (typeof allowDevice.body === "object") {
            if (allowDevice.body.deviceId === device["deviceId"]) {
              Object.entries(allowDevice.body.status).map(([key, value]) => {
                tableHtml += `<p id='allowStatus${device["deviceId"]}'>${key}: ${value}</p>`;
                ``;
              });
            }
          }
        });
      }
      tableHtml += "</td>";
      tableHtml += "<td>";
      if (device["commands"]) {
        Object.keys(device["commands"]).forEach((key) => {
          if (device["commands"][key]) {
            tableHtml += `<button id='${device["deviceId"]}-${key}' class='button-command' value='${key}' onClick="setDeviceCommand('${jsonData["token"]}', '${jsonData["secretKey"]}','${device["deviceId"]}', '${key}')">${key}</button><br>`;
          }
        });
      }
      tableHtml += "</td>";
      tableHtml += "</tr>";
    });

    tableHtml += "</table>";

    document.getElementById("deviceListContainer").innerHTML = tableHtml;
  }
}
