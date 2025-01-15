<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user_id = $_SESSION['user_id'];
  $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
  $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

  if (!$name || !$description) {
    die("Invalid input data.");
  }

  $selected_songs = isset($_POST['songs']) ? $_POST['songs'] : [];
  if (empty($selected_songs)) {
    die("Please select at least one song.");
  }

  $cover_image_data = null;
  if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
    $cover_image_tmp = $_FILES['cover_image']['tmp_name'];
    $cover_image_name = $_FILES['cover_image']['name'];
    $cover_extension = strtolower(pathinfo($cover_image_name, PATHINFO_EXTENSION));

    $allowed_image_types = ['jpg', 'jpeg', 'png'];
    if (in_array($cover_extension, $allowed_image_types)) {
      $cover_image_data = file_get_contents($cover_image_tmp);
    } else {
      die("Invalid image format. Only JPG, JPEG, and PNG allowed.");
    }
  }

  $query = "INSERT INTO playlists (user_id, name, description, cover_image) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param('isss', $user_id, $name, $description, $cover_image_data);

  if ($stmt->execute()) {
    $playlist_id = $conn->insert_id;

    $query = "INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    foreach ($selected_songs as $song_id) {
      $stmt->bind_param('ii', $playlist_id, $song_id);
      $stmt->execute();
    }

    header("location:../index.php?status_cp=berhasil");
  } else {
    error_log("Database error: " . $conn->error);
    header("location:../add_playlist.php?status_cp=gagal");
  }
}
