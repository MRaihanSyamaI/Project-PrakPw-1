<?php
session_start();
include("back/db.php");

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php?pesan=belum_login");
  exit();
}

// Ambil semua lagu yang tersedia di database
$query = "SELECT * FROM songs";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Playlist</title>
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
      <form action="back/create_playlist.php" method="post" enctype="multipart/form-data">
        <h2>Add Playlist</h2>
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
              value="icons/kosongan.jpg"
              onchange="previewPhoto(event)"
              hidden />
          </div>

          <div class="modal-input">
            <input
              type="text"
              placeholder="Name"
              name="name"
              class="form-control mb-3 input-field"
              required />

            <textarea
              placeholder="Description"
              name="description"
              class="form-control"
              required></textarea>
          </div>

          <div class="song bg-secondary rounded-4 ms-5">
            <h3>Selected Songs:</h3>

            <table>
              <?php while ($song = $result->fetch_assoc()): ?>
                <tr>
                  <td><input type="checkbox" name="songs[]" value="<?= $song['id']; ?>" /></td>
                  <td><?= $song['title'] . " - " . $song['artist']; ?></td>
                </tr>
              <?php endwhile; ?>
            </table>

          </div>
        </div>
        <div class="button">
          <button type="submit" class="btn btn-light rounded-pill" name="add_playlist">
            Add Playlist
          </button>

          <a href="index.php" class="btn btn-danger ms-3 rounded-pill" style="width: 200px;" role="button">Back</a>
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