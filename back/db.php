<?php
// Konfigurasi koneksi ke database
$host = 'localhost'; // Nama host, biasanya 'localhost'
$username = 'root';  // Username database
$password = '';      // Password database
$database = 'imusic3'; // Nama database

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

// Aktifkan error reporting untuk debugging (opsional)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
