<?php
session_start();

// Hapus semua data sesi
session_unset();
session_destroy();

// Arahkan pengguna ke halaman login
header("Location: ../login.php?pesan=logout");
exit();
