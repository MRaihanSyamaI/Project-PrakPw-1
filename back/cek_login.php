<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $query = "SELECT * FROM user WHERE email = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
      // Menyimpan username dalam session
      $_SESSION['username'] = $row['username'];

      if ($email == "admin@gmail.com") {
        $_SESSION['role'] = "admin";
        header("Location:../admin.php");
      } else {
        $_SESSION['user_id'] = $row['id'];
        header("Location:../index.php");
        exit();
      }
    } else {
      header("Location:../login.php?pesan=gagal");
    }
  } else {
    header("Location:../login.php?pesan=tidak_ada");
  }
}
