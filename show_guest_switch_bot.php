<?php
if (!isset($_GET['file'])) {
  echo "No file specified!";
  exit;
}

$filePath = urldecode($_GET['file']);
if (!file_exists($filePath)) {
  echo "File not found!";
  exit;
}

$jsonData = file_get_contents($filePath);
$data = json_decode($jsonData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
  echo "Invalid JSON file!";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Display JSON Data</title>
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 8px;
    }

    th {
      background-color: #f2f2f2;
    }
  </style>
</head>

<body>
  <h1>JSON Data</h1>
  <table id="jsonTable">
    <thead>
      <tr>
        <th>Key</th>
        <th>Value</th>
      </tr>
    </thead>
    <tbody>
      <!-- JSONデータがここに表示されます -->
    </tbody>
  </table>
  <script>
    const data = <?php echo json_encode($data); ?>;

    function buildTable(data, tableId) {
      const table = document.getElementById(tableId);
      const tbody = table.querySelector('tbody');
      tbody.innerHTML = ''; // 既存の行をクリア

      for (const key in data) {
        if (data.hasOwnProperty(key)) {
          const row = document.createElement('tr');
          const keyCell = document.createElement('td');
          const valueCell = document.createElement('td');

          keyCell.textContent = key;
          valueCell.textContent = (typeof data[key] === 'object') ? JSON.stringify(data[key]) : data[key];

          row.appendChild(keyCell);
          row.appendChild(valueCell);
          tbody.appendChild(row);
        }
      }
    }

    document.addEventListener("DOMContentLoaded", function() {
      buildTable(data, 'jsonTable');
    });
  </script>
</body>

</html>