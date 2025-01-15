<?php
include 'db.php';

if (isset($_GET['id'])) {
  $song_id = $_GET['id'];

  // Ambil file audio dari database
  $query = "SELECT audio_file FROM songs WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param('i', $song_id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($audio_file);
  $stmt->fetch();

  if ($audio_file) {
    // Set header untuk tipe konten audio dan memungkinkan seeking
    // Memberitahukan browser bahwa file yang dikirim adalah file audio MP3.
    header("Content-Type: audio/mpeg"); // Menentukan tipe konten audio MP3
    // Memungkinkan browser untuk meminta bagian tertentu dari file (seperti saat pengguna menggeser slider durasi).
    header("Accept-Ranges: bytes");    // Memungkinkan penggunaan range untuk seeking
    // Menginformasikan browser tentang panjang file audio, ini penting untuk memastikan browser dapat mengunduhnya dengan benar.
    header("Content-Length: " . strlen($audio_file)); // Menyatakan panjang konten audio

    // Jika ada header Range (permintaan untuk bagian tertentu dari file), tangani itu
    if (isset($_SERVER['HTTP_RANGE'])) {
      $range = $_SERVER['HTTP_RANGE'];
      list(, $range) = explode('=', $range, 2);
      list($start, $end) = explode('-', $range);
      $start = intval($start);
      $end = $end ? intval($end) : strlen($audio_file) - 1;
      $length = $end - $start + 1;

      header("Content-Range: bytes $start-$end/" . strlen($audio_file));
      header("Content-Length: $length");
      header("HTTP/1.1 206 Partial Content");

      // Kirimkan sebagian file audio
      echo substr($audio_file, $start, $length);
      exit();
    }

    // Jika tidak ada permintaan range, kirimkan seluruh file
    echo $audio_file;
    exit();
  } else {
    header("HTTP/1.0 404 Not Found");
    exit();
  }
}

/* Penjelasan:
1. Header Content-Type: audio/mpeg:
    Memberitahukan browser bahwa file yang dikirim adalah file audio MP3.

2. Header Accept-Ranges: bytes:
    Memungkinkan browser untuk meminta bagian tertentu dari file (seperti saat pengguna menggeser slider durasi).

3. Header Content-Length:
    Menginformasikan browser tentang panjang file audio, ini penting untuk memastikan browser dapat mengunduhnya dengan benar.

4. Mendukung Range Requests:
  Jika browser mengirimkan permintaan dengan header Range (misalnya, untuk melompat ke detik tertentu dalam lagu), server akan mengirimkan bagian file yang diminta, bukan seluruh file.
  Ini memungkinkan fitur seeking (memindahkan slider durasi) untuk bekerja dengan benar.

5. Jika Tidak Ada Range:
    Jika permintaan tidak memiliki header Range, maka file audio akan dikirimkan sepenuhnya.

Penting:
Range Requests: Jika file audio cukup besar dan Anda ingin mendukung fitur seperti fast-forward atau seek (menggeser slider durasi), menggunakan range request adalah cara terbaik untuk mengelola pemutaran audio.

Pastikan Anda menguji penggunaan Developer Tools di browser untuk memeriksa apakah file audio dimuat dengan benar dan apakah durasi serta seeking berfungsi sebagaimana mestinya.

Dengan menambahkan header ini, browser akan dapat mengenali file audio yang dikirim dan mendukung fungsi seperti seek atau scrolling pada slider durasi. */
