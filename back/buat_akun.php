<?php
include 'db.php'; // Koneksi database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'];  // Mengambil username
  $email = $_POST['email'];  // Mengambil email
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Mengambil password dan mengenkripsi

  // Mengecek apakah email sudah terdaftar
  $syntax = "SELECT email FROM user WHERE email = ?";
  $stmt2 = $conn->prepare($syntax);
  $stmt2->bind_param('s', $email);
  $stmt2->execute();
  $stmt2->store_result();

  if ($stmt2->num_rows > 0) {
    // Jika email sudah ada
    header("location:../register.php?pesan=ada");
  } else {
    // Jika email belum terdaftar, insert data ke database
    $query = "INSERT INTO user (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sss', $username, $email, $password);

    if ($stmt->execute()) {
      header("location:../login.php");
    } else {
      header("location:../register.php?pesan=gagal");
    }
  }
}
