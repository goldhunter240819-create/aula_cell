<?php require 'koneksi.php'; \ = mysqli_query(\, 'SELECT foto_profil FROM users'); if(!\) echo mysqli_error(\); else echo 'OK'; ?>
