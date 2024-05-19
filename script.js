async function getDevicelist(token) {
  const url = "https://api.switch-bot.com/v1.0/devices";
  const headers = new Headers({
    Authorization: token,
    "Content-Type": "application/json; charset=utf8",
  });

  try {
    const response = await fetch(url, {
      method: "GET",
      headers: headers,
    });
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching data: ", error);
    return null;
  }
}

// URLからクエリパラメータを取得し、`token`を抽出するコードをここに追加
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const token = params.get("token");

  if (token && token !== "") {
    getDevicelist(token)
      .then((data) => {
        console.log(data); // 本番環境では、ここでデータを適切に表示する
      })
      .catch((error) => {
        console.error("デバイスリストの取得に失敗しました: ", error);
      });
  } else {
    console.error("tokenが設定されていません");
  }
});
