<?php
if (isset($_POST)) {
    $zdj = $_POST['zdjecia'];
    $min = $_POST['miniatury'];
    $title = $_POST['tytul'];

    if (!mkdir($zdj, 0777, true)) {
        $error = 'error with creating photos dir';
    }
    if (!mkdir($min, 0777, true)) {
        $error = 'error with creating miniatures dir';
    }
}
