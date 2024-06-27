function getDeviceList(token, secretKey) {
  const data = {
    token: token,
    secretKey: secretKey,
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
        '<table border="1"><tr><th>Device ID</th><th>Device Name</th><th>Device Type</th><th>Status</th><th>Commands</th></tr>';

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
      document.getElementById("encodeData").textContent =
        response.data.encodeData;
    })
    .catch(function (error) {
      console.error("Error: " + error);
    });
}

function clickBtnDec() {
  const encodeData = document.getElementById("encodeData").value;
  const password = document.getElementById("password").value;

  const data = {
    encodeData: encodeData,
    password: password,
  };

  axios({
    method: "post",
    url: "https://watalab.info/lab/asakura/api/decrypt.php",
    data: JSON.stringify(data),
    headers: { "Content-Type": "application/json" },
  })
    .then(function (response) {
      document.getElementById("decodeData").textContent = response.data;
    })
    .catch(function (error) {
      console.error("Error: " + error);
    });
}

function jsonDownload() {
  const authGuestToken = document.getElementById("encodeData").textContent;
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
