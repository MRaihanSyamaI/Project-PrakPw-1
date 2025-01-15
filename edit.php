<?php
session_start();
include("back/db.php");

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];


// Pastikan ada ID playlist yang diterima
if (!isset($_GET['id'])) {
  die("Playlist ID tidak ditemukan!");
}

$playlist_id = $_GET['id'];

// Ambil data playlist
$query = "SELECT * FROM playlists WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $playlist_id);
$stmt->execute();
$result = $stmt->get_result();
$playlistnow = $result->fetch_assoc();

if (!$playlistnow) {
  die("Playlist tidak ditemukan!");
}

// Ambil daftar lagu dalam playlist
$song_query = "SELECT s.id, s.title, s.artist, s.cover_image 
             FROM songs s 
             JOIN playlist_songs ps ON s.id = ps.song_id 
             WHERE ps.playlist_id = ?";
$song_stmt = $conn->prepare($song_query);
$song_stmt->bind_param('i', $playlist_id);
$song_stmt->execute();
$song_result = $song_stmt->get_result();

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
      <form action="back/update_playlist.php" method="post" enctype="multipart/form-data">
        <h2>Edit Details</h2>
        <input type="hidden" name="playlist_id" value="<?php echo $playlist_id; ?>">
        <div class="modal-main">
          <div
            class="photo-container"
            onclick="document.getElementById('photoInput').click();">

            <?php
            // Mengambil cover image playlist
            $playlist_cover = $playlistnow['cover_image']; // Gambar BLOB
            $playlist_cover_base64 = base64_encode($playlist_cover);
            ?>

            <img
              id="photoPreview"
              src="data:image/jpeg;base64,<?= $playlist_cover_base64; ?>"
              alt="Choose Photo"
              class="photo-preview" />
            <span id="photoText" class="btn btn-outline-secondary ">Choose Photo</span>
            <input
              type="file"
              id="photoInput"
              accept=".jpg,.jpeg,.png"
              name="cover_image"
              onchange="previewPhoto(event)"
              hidden />
            <!-- Hidden input untuk gambar lama -->
            <input type="hidden" name="old_cover_image" value="<?= $playlist_cover_base64; ?>" />
          </div>
          <div class="modal-input">
            <input
              type="text"
              placeholder="Name"
              name="name"
              value="<?= $playlistnow['name'] ?>"
              class="form-control mb-3 input-field" />
            <textarea
              placeholder="Description"
              name="description"
              class="form-control"><?= $playlistnow['description'] ?></textarea>
          </div>

          <div class="song bg-secondary rounded-4 ms-5">
            <h3>Selected Song:</h3>
            <?php
            // Ambil semua lagu yang sudah ada dalam playlist
            $selected_songs_query = "SELECT s.id, s.title, s.artist 
                           FROM songs s
                           JOIN playlist_songs ps ON s.id = ps.song_id
                           WHERE ps.playlist_id = ?";
            $selected_songs_stmt = $conn->prepare($selected_songs_query);
            $selected_songs_stmt->bind_param('i', $playlist_id);
            $selected_songs_stmt->execute();
            $selected_songs_result = $selected_songs_stmt->get_result();

            // Ambil semua lagu yang belum ada dalam playlist
            $all_songs_query = "SELECT id, title, artist FROM songs WHERE id NOT IN (SELECT song_id FROM playlist_songs WHERE playlist_id = ?)";
            $all_songs_stmt = $conn->prepare($all_songs_query);
            $all_songs_stmt->bind_param('i', $playlist_id);
            $all_songs_stmt->execute();
            $all_songs_result = $all_songs_stmt->get_result();
            ?>

            <div>
              <table>
                <!-- Lagu yang sudah ada dalam playlist -->
                <?php while ($song = $selected_songs_result->fetch_assoc()): ?>
                  <tr>
                    <td>
                      <input type="checkbox" name="songs[]" value="<?= $song['id']; ?>" checked />
                    </td>
                    <td><?= $song['title'] . " - " . $song['artist']; ?></td>
                  </tr>
                <?php endwhile; ?>

                <!-- Lagu yang belum ada dalam playlist -->
                <?php while ($song = $all_songs_result->fetch_assoc()): ?>
                  <tr>
                    <td>
                      <input type="checkbox" name="songs[]" value="<?= $song['id']; ?>" />
                    </td>
                    <td><?= $song['title'] . " - " . $song['artist']; ?></td>
                  </tr>
                <?php endwhile; ?>
              </table>
            </div>
          </div>

        </div>
        <div class="button">
          <button type="submit" class="btn btn-light rounded-pill" name="update_playlist">
            Update Playlist
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