<?php
session_start();
include("back/db.php");

// Pastikan pengguna adalah admin
if ($_SESSION['role'] != 'admin') {
  header("Location: login.php?pesan=belum_login");
  exit();
}

// Menangani upload file
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Ambil data dari form
  $title = $_POST['title'];
  $artist = $_POST['artist'];

  // Upload Lagu (file .mp3)
  $audio_file = $_FILES['audio_file'];
  $audio_tmp_name = $audio_file['tmp_name'];
  $audio_name = $audio_file['name'];
  $audio_extension = pathinfo($audio_name, PATHINFO_EXTENSION);

  // Upload Cover Image (file .jpg, .png)
  $cover_image = $_FILES['cover_image'];
  $cover_tmp_name = $cover_image['tmp_name'];
  $cover_name = $cover_image['name'];
  $cover_extension = pathinfo($cover_name, PATHINFO_EXTENSION);

  // Validasi file
  $allowed_audio_types = ['mp3'];
  $allowed_image_types = ['jpg', 'jpeg', 'png'];

  if (in_array($audio_extension, $allowed_audio_types) && in_array($cover_extension, $allowed_image_types)) {
    // Baca file dan simpan sebagai binary data
    $audio_data = file_get_contents($audio_tmp_name);
    $cover_data = file_get_contents($cover_tmp_name);

    // Simpan lagu dan cover image ke database
    $query = "INSERT INTO songs (title, artist, audio_file, cover_image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $title, $artist, $audio_data, $cover_data);

    if (!$stmt) {
      die("Query preparation failed: " . $conn->error);
    }

    if ($stmt->execute()) {
      header("location:admin.php?status_a=berhasil");
    } else {
      header("location:admin.php?status_a=berhasil");
    }
  } else {
    echo "Format file tidak valid! Pastikan file audio .mp3 dan cover image .jpg/.png!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document</title>
  <link rel="shortcut icon" href="icons/Piringan.png" type="image/x-icon">
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous" />
  <link rel="stylesheet" href="style_edit.css" />
</head>

<body>
  <div class="modal">
    <div class="modal-main d-flex">
      <!-- Tambahkan enctype untuk upload file -->
      <form method="post" enctype="multipart/form-data">
        <h2>Add Song</h2>
        <div class="modal-main">
          <div
            class="photo-container"
            onclick="document.getElementById('photoInput').click();">

            <img
              id="photoPreview"
              src="icons/Kosongan.jpg"
              alt="Choose Photo"
              class="photo-preview" />
            <span id="photoText" class="btn btn-outline-secondary">Choose Photo</span>
            <input
              type="file"
              id="photoInput"
              accept=".jpg,.jpeg,.png"
              name="cover_image"
              onchange="previewPhoto(event)"
              hidden />
          </div>

          <div class="modal-input">
            <input
              type="text"
              placeholder="Title"
              name="title"
              class="form-control mb-3 input-field"
              required />

            <input
              type="text"
              placeholder="Artist"
              name="artist"
              class="form-control mb-3 input-field"
              required />

            <label>Upload Lagu</label>
            <input
              type="file"
              name="audio_file"
              class="form-control mb-3 input-field"
              accept=".mp3"
              required />
          </div>
        </div>

        <div class="text-center button btn-admin">
          <button type="submit" class="btn btn-light rounded-pill" name="add_playlist">
            Add Song
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Preview image function
    function previewPhoto(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('photoPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    }
  </script>
</body>

</html>