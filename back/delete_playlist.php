<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit();
}

$playlist_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verifikasi apakah playlist milik pengguna
$query = "SELECT * FROM playlists WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $playlist_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  echo "Playlist not found or you do not have permission to delete it.";
  exit();
}

// Proses penghapusan
$query = "DELETE FROM playlists WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $playlist_id, $user_id);

if ($stmt->execute()) {
  header("Location:../index.php?status_dp=berhasil");
} else {
  header("Location:../index.php?status_dp=berhasil");
}
