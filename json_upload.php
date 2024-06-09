<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['jsonFile'])) {
  $file = $_FILES['jsonFile'];

  if ($file['error'] !== UPLOAD_ERR_OK) {
    echo "File upload error!";
    exit;
  }

  $fileType = mime_content_type($file['tmp_name']);
  if ($fileType !== 'application/json') {
    echo "Please upload a valid JSON file!";
    exit;
  }

  // Save the uploaded file to a temporary location
  $uploadDir = 'uploads/';
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }
  $filePath = $uploadDir . basename($file['name']);
  move_uploaded_file($file['tmp_name'], $filePath);

  // Redirect to the second page with the file path
  header('Location: show_guest_switch_bot.php?file=' . urlencode($filePath));
  exit;
} else {
  echo "Invalid request!";
  exit;
}
