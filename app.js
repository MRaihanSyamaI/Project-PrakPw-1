const toggleButton = document.getElementById("toggle-btn");
const sidebar = document.getElementById("sidebar");

// Untuk Mengaplikasikan toggle
function toggleSidebar() {
  // menambahkan class .close ke id sidebar;
  // Pengaplikasian akan gagal jika ada z-index
  sidebar.classList.toggle("close");
}

// Menyiapkan value untuk memainkan musik
const progress = document.getElementById("progress");
const song = document.getElementById("song");
const ppIcon = document.getElementById("play-pause");
const curDur = document.getElementById("current-duration");
const maxDur = document.getElementById("max-duration");
const forward = document.getElementById("forward");
const backward = document.getElementById("backward");

// Untuk menyelaraskan range dengan durasi musik
song.onloadedmetadata = () => {
  // Panjang maksimal bar = durasi musik

  progress.max = song.duration;
  progress.value = song.currentTime;
  if (!isNaN(song.duration)) {
    // Pastikan duration adalah angka
    let minutes = Math.floor(song.duration / 60);
    let seconds = Math.floor(song.duration % 60);

    // Tambahkan nol di depan jika detik kurang dari 10
    seconds = seconds < 10 ? "0" + seconds : seconds;

    maxDur.innerHTML = `${minutes}:${seconds}`;
  }
  // Value dari bar tergantung lagu
  progress.value = song.currentTime;
  if (!isNaN(song.duration)) {
    // Pastikan duration adalah angka
    let minutes = Math.floor(song.currentTime / 60);
    let seconds = Math.floor(song.currentTime % 60);

    // Tambahkan nol di depan jika detik kurang dari 10
    seconds = seconds < 10 ? "0" + seconds : seconds;

    curDur.innerHTML = `${minutes}:${seconds}`;
  }
};

// Menangani kontrol play/pause
function playPause() {
  if (ppIcon.classList.contains("fa-pause")) {
    song.pause(); // MEnjeda musik
    ppIcon.classList.remove("fa-pause");
    ppIcon.classList.add("fa-play");
  } else {
    song.play(); // Memainkan musik
    ppIcon.classList.remove("fa-play");
    ppIcon.classList.add("fa-pause");
  }
}

// Untuk menyelaraskan gerakan bar range dengan durasi musik
if (song.play()) {
  setInterval(() => {
    progress.value = song.currentTime;
    if (!isNaN(song.duration)) {
      // Pastikan duration adalah angka
      let minutes = Math.floor(song.currentTime / 60);
      let seconds = Math.floor(song.currentTime % 60);

      // Tambahkan nol di depan jika detik kurang dari 10
      seconds = seconds < 10 ? "0" + seconds : seconds;

      curDur.innerHTML = `${minutes}:${seconds}`;
    }
  }, 500);
}

// Untuk menyelaraskan durasi musik dengan letak dot bar
progress.onchange = function () {
  song.play();

  song.currentTime = progress.value;
  ppIcon.classList.remove("fa-play");
  ppIcon.classList.add("fa-pause");
};

function forback() {
  if (backward.classList.contains("fa-backward")) {
    song.currentTime = Math.max(0, audioPlayer.currentTime - 10);
  } else {
    song.currentTime = Math.min(
      audioPlayer.duration,
      audioPlayer.currentTime + 10
    );
  }
  song.currentTime = progress.value;
  if (!isNaN(song.duration)) {
    // Pastikan duration adalah angka
    let minutes = Math.floor(song.currentTime / 60);
    let seconds = Math.floor(song.currentTime % 60);

    // Tambahkan nol di depan jika detik kurang dari 10
    seconds = seconds < 10 ? "0" + seconds : seconds;

    curDur.innerHTML = `${minutes}:${seconds}`;
  }
}

// Menyiapkan value untuk volume
const volumeIcon = document.getElementById("volume-icon");

// Untuk muted dan unmuted volume
function volume() {
  if (volumeIcon.classList.contains("fa-volume-high")) {
    song.muted = true;
    volumeIcon.classList.remove("fa-volume-high");
    volumeIcon.classList.add("fa-volume-xmark");
  } else {
    song.muted = false;
    volumeIcon.classList.remove("fa-volume-xmark");
    volumeIcon.classList.add("fa-volume-high");
  }
}

// Pilih semua elemen dengan kelas 'playlist'
const playlists = document.querySelectorAll(".playlist");

playlists.forEach((playlist) => {
  const contextMenu = playlist.querySelector(".right-click");

  // Event untuk menampilkan menu konteks ketika klik kanan di elemen 'playlist'
  playlist.addEventListener("contextmenu", (e) => {
    e.preventDefault();

    // Sembunyikan semua menu konteks lain terlebih dahulu
    document
      .querySelectorAll(".right-click")
      .forEach((menu) => (menu.style.visibility = "hidden"));

    // Tampilkan menu konteks yang terkait dengan elemen 'playlist' yang diklik
    contextMenu.style.visibility = "visible";
    contextMenu.style.top = `${e.pageY}px`; // Posisi Y dari kursor
    contextMenu.style.left = `${e.pageX}px`; // Posisi X dari kursor
  });
});

// Menyembunyikan menu konteks saat klik di luar elemen 'right-click'
document.addEventListener("click", () => {
  document
    .querySelectorAll(".right-click")
    .forEach((menu) => (menu.style.visibility = "hidden"));
});
