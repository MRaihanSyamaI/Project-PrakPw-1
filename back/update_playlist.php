<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user_id = $_SESSION['user_id'];
  $playlist_id = isset($_POST['playlist_id']) ? $_POST['playlist_id'] : null; // Pastikan ID ada jika mengedit playlist
  $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
  $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

  if (!$name || !$description) {
    die("Invalid input data.");
  }

  $selected_songs = isset($_POST['songs']) ? $_POST['songs'] : [];
  if (empty($selected_songs)) {
    die("Please select at least one song.");
  }

  // Proses cover image, jika ada
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
  } else {
    // Jika tidak ada gambar baru, gunakan gambar lama
    $cover_image_data = base64_decode($_POST['old_cover_image']);
  }

  // Menambahkan playlist baru atau memperbarui jika ID sudah ada
  if ($playlist_id) {
    // Update playlist
    $query = "UPDATE playlists SET name = ?, description = ?, cover_image = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssii', $name, $description, $cover_image_data, $playlist_id, $user_id);
    $stmt->execute();
  } else {
    // Insert playlist baru
    $query = "INSERT INTO playlists (user_id, name, description, cover_image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('isss', $user_id, $name, $description, $cover_image_data);
    if ($stmt->execute()) {
      $playlist_id = $conn->insert_id; // Ambil ID playlist yang baru ditambahkan
    } else {
      die("Failed to create playlist.");
    }
  }

  // Mengambil semua lagu yang sudah ada dalam playlist
  $existing_songs_query = "SELECT song_id FROM playlist_songs WHERE playlist_id = ?";
  $existing_songs_stmt = $conn->prepare($existing_songs_query);
  $existing_songs_stmt->bind_param('i', $playlist_id);
  $existing_songs_stmt->execute();
  $existing_songs_result = $existing_songs_stmt->get_result();
  $existing_songs = [];
  while ($row = $existing_songs_result->fetch_assoc()) {
    $existing_songs[] = $row['song_id'];
  }

  // Lagu yang sudah ada dalam playlist yang tidak dipilih lagi
  $songs_to_remove = array_diff($existing_songs, $selected_songs);

  // Hapus lagu yang tidak dipilih
  if (!empty($songs_to_remove)) {
    $remove_query = "DELETE FROM playlist_songs WHERE playlist_id = ? AND song_id IN (" . implode(',', array_fill(0, count($songs_to_remove), '?')) . ")";
    $remove_stmt = $conn->prepare($remove_query);

    // Membuat array untuk parameter bind
    $params = array_merge([$playlist_id], $songs_to_remove);
    $types = str_repeat('i', count($params)); // menentukan tipe data yang digunakan ('i' untuk integer)

    // Bind parameter menggunakan call_user_func_array untuk menghindari error "Cannot use positional argument after argument unpacking"
    call_user_func_array([$remove_stmt, 'bind_param'], array_merge([$types], $params));

    $remove_stmt->execute();
  }


  // Menambahkan lagu ke playlist (hanya yang dipilih)
  $query = "INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)";
  $stmt = $conn->prepare($query);
  foreach ($selected_songs as $song_id) {
    // Hanya tambahkan jika lagu belum ada dalam playlist
    if (!in_array($song_id, $existing_songs)) {
      $stmt->bind_param('ii', $playlist_id, $song_id);
      $stmt->execute();
    }
  }

  // Redirect setelah berhasil
  header("Location: ../index.php?status_cp=berhasil");
  exit();
}
