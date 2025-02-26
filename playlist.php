<?php
session_start();
include("back/db.php");

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php?pesan=belum_login");
  exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];


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
$no = 1;

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home</title>
  <link rel="shortcut icon" href="icons/Piringan.png" type="image/x-icon">
  <link rel="stylesheet" href="styles.css" />
  <script type="text/javascript" src="app.js" defer></script>
  <script
    src="https://kit.fontawesome.com/bb239bc160.js"
    crossorigin="anonymous"></script>
</head>

<body>
  <nav>
    <form action="search.php" method="get" class="search">
      <input
        type="search"
        name="sc"
        class="rounded-pill px-4 text-light"
        placeholder="what do you want to play?" />
      <button type="submit" class="rounded-circle">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          height="24"
          viewBox="0 -960 960 960"
          width="24"
          fill="#000000">
          <path
            d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z" />
        </svg>
      </button>
    </form>

    <div class="profile">
      <h3><a href="back/logout.php"><?= $username ?></a></h3>
    </div>
  </nav>


  <aside id="sidebar">
    <div class="element">
      <a href="index.php">
        <span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            height="40px"
            viewBox="0 -960 960 960"
            width="40px"
            fill="#000000">
            <path
              d="M200-160v-366L88-440l-48-64 440-336 160 122v-82h120v174l160 122-48 64-112-86v366H520v-240h-80v240H200Zm80-80h80v-240h240v240h80v-347L480-739 280-587v347Zm120-319h160q0-32-24-52.5T480-632q-32 0-56 20.5T400-559Zm-40 319v-240h240v240-240H360v240Z" />
          </svg>
        </span>
      </a>
      <a href="" class="logout">
        <span class="ms-3">
          <img src="icons/Piringan.png" alt="" />
        </span>
        <span class="ms-3 text-light">iMusic</span>
      </a>
    </div>

    <ul>
      <li>
        <button onclick="toggleSidebar()" id="toggle-btn">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            height="40px"
            viewBox="0 -960 960 960"
            width="40px"
            fill="#000000">
            <path
              d="M120-320v-80h280v80H120Zm0-160v-80h440v80H120Zm0-160v-80h440v80H120Zm520 480v-160H480v-80h160v-160h80v160h160v80H720v160h-80Z" />
          </svg>
          <span>Your Library</span>
        </button>

        <span class="add-btn">
          <a href="add_playlist.php?">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              height="24px"
              viewBox="0 -960 960 960"
              width="24px"
              fill="#000000">
              <path
                d="M440-280h80v-160h160v-80H520v-160h-80v160H280v80h160v160Zm40 200q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z" />
            </svg>
          </a>
        </span>
      </li>

      <?php
      // Ambil playlist milik pengguna saat ini
      $query = "SELECT * FROM playlists WHERE user_id = $user_id";
      $stmt = $conn->prepare($query);
      // $stmt->bind_param('i', $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      ?>

      <?php while ($playlist = $result->fetch_assoc()): ?>
        <?php
        $playlist_id = $playlist['id'];
        $cover_image = $playlist['cover_image']; // Ambil data cover image BLOB

        // Ubah gambar BLOB ke Base64 untuk ditampilkan
        $cover_base64 = base64_encode($cover_image);
        ?>

        <li class="playlist">
          <a href="playlist.php?id=<?= $playlist_id; ?>">
            <img src="data:image/jpeg;base64,<?= $cover_base64; ?>" alt="" />
            <span><?= $playlist['name'] ?></span>
          </a>
          <ul class="right-click">
            <li>
              <a href="back/delete_playlist.php?id=<?= $playlist_id ?>" class="sub-menu delete">
                <i class="fa-regular fa-square-minus"></i>
                Delete
              </a>
            </li>
            <li>
              <a href="edit.php?id=<?= $playlist_id ?>" class="sub-menu delete">
                <i class="fa-regular fa-pen-to-square"></i>
                Edit
              </a>
            </li>
          </ul>
        </li>

      <?php endwhile; ?>
    </ul>
  </aside>

  <main id="playlist">
    <?php
    // Mengambil cover image playlist
    $playlist_cover = $playlistnow['cover_image']; // Gambar BLOB
    $playlist_cover_base64 = base64_encode($playlist_cover);

    // Buat ngitung jumlah lagu yang ada di playlistnya
    $count = mysqli_num_rows($song_result);
    ?>

    <div class="playlist-header">
      <img src="data:image/jpeg;base64,<?= $playlist_cover_base64; ?>" alt="default" />
      <div class="text">
        <h1><?= $playlistnow['name']; ?></h1>
        <h3><?= $playlistnow['description'] ?></h3>
        <h3><?= $count ?> Songs</h3>
      </div>
    </div>


    <div class="playlist-content">
      <table>
        <tr>
          <th style="width: 3%">#</th>
          <th style="width: 50%">Title</th>
          <th>Singer</th>
        </tr>

        <?php while ($song = $song_result->fetch_assoc()): ?>
          <?php
          // Mengambil cover image lagu (BLOB)
          $song_cover = $song['cover_image']; // Gambar BLOB
          $song_cover_base64 = base64_encode($song_cover);
          ?>
          <tr>
            <th><?= $no++; ?></th>
            <td>
              <div
                class="title"
                onclick="playSong(<?= $song['id']; ?>, '<?= addslashes($song['title']); ?>', '<?= addslashes($song['artist']); ?>', '<?= base64_encode($song['cover_image']); ?>')">
                <img style="margin-right: 1rem;" src="data:image/jpeg;base64,<?= $song_cover_base64; ?>" alt="Song Cover" />
                <p><?= $song['title']; ?></p>
              </div>
            </td>
            <td><?= $song['artist'] ?></td>
          </tr>


        <?php endwhile; ?>
      </table>
    </div>
  </main>

  <footer>
    <div class="icon-song">
      <img src="icons/Kosongan.jpg" alt="Default Cover" />
      <span>
        <h3 class="penyanyi">Artist</h3>
        <h6 class="judul-musik">Song Title</h6>
      </span>
    </div>

    <div class="controls">
      <div class="icons">
        <div onclick="seekBackward()"><i class="fa-solid fa-backward"></i></div>
        <div onclick="playPause()">
          <i class="fa fa-play" id="play-pause"></i>
        </div>
        <div onclick="seekForward()"><i class="fa-solid fa-forward"></i></div>
      </div>
      <div class="slide-progress">
        <audio id="song" preload="auto" ontimeupdate="updateProgress()">
          <source id="audioSource" src="" type="audio/mpeg" />
        </audio>
        <span>
          <h6 id="current-duration">00:00</h6>
        </span>
        <input type="range" value="0" id="progress" min="0" step="0.1" onchange="setProgress(this.value)" />
        <span>
          <h6 id="max-duration">00:00</h6>
        </span>
      </div>
    </div>


    <div class="more-controls">
      <div onclick="volume()">
        <i class="fa-solid fa-volume-high" id="volume-icon"></i>
      </div>
    </div>
  </footer>


  <script>
    // Buat play lagu yang ada di display
    function playSong(songId, songTitle, songArtist, songCover) {
      const audioPlayer = document.getElementById('song');
      const audioSource = document.getElementById('audioSource');
      const songCoverImg = document.querySelector('.icon-song img');
      const songTitleElem = document.querySelector('.icon-song .judul-musik');
      const songArtistElem = document.querySelector('.icon-song .penyanyi');

      // Update source audio
      audioSource.src = `back/play_audio.php?id=${songId}`; // Pastikan path sesuai
      audioPlayer.load();
      audioPlayer.play();

      // Update cover image, title, and artist
      songCoverImg.src = `data:image/jpeg;base64,${songCover}`;
      songTitleElem.textContent = songTitle;
      songArtistElem.textContent = songArtist;
    }


    // AUDIO CONTROL UNTUK MAJU MUNDURRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
    const audioPlayer = document.getElementById('song');
    const progressBar = document.getElementById('progress');
    const currentDuration = document.getElementById('current-duration');
    const maxDuration = document.getElementById('max-duration');


    // Fungsi untuk mundur 10 detik
    function seekBackward() {
      audioPlayer.currentTime = Math.max(0, audioPlayer.currentTime - 10);
    }

    // Fungsi untuk maju 10 detik
    function seekForward() {
      audioPlayer.currentTime = Math.min(audioPlayer.duration, audioPlayer.currentTime + 10);
    }
  </script>
</body>

</html>