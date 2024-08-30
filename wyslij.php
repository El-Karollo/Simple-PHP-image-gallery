<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_GET['clear'] == 'clear') {
	$_SESSION['info'] = '';
	header('Location: wyslij.php');
	die;
}

$upload_dir = 'zdjecia/';
$thumbnail_dir = 'zdjecia-miniaturki/';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
	$files = $_FILES['file'];

	$info = '';
	$info .= '<b>' . date('Y-m-d H:i:s') . '</b><br>';

	foreach ($files['name'] as $key => $name) {
		if ($files['error'][$key] === UPLOAD_ERR_OK) {
			if ($files['size'][$key] <= 20 * 1024 * 1024) {
				$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
				$file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

				if (in_array($file_extension, $allowed_extensions)) {
					$unique_filename = uniqid('img_') . '.' . $file_extension;
					$target_path = $upload_dir . $unique_filename;

					if (move_uploaded_file($files['tmp_name'][$key], $target_path)) {
						generateThumbnail($target_path, $thumbnail_dir . $unique_filename, 150);

						$info .= '<span style="color: #12ce12;"><b>Plik ' . $name . ' został pomyślnie przesłany.</b></span><br>';
					} else {
						$info .= '<span style="color: #ff0015;"><b>Wystąpił problem podczas przenoszenia pliku ' . $name . '.</b></span><br>';
					}
				} else {
					$info .= '<span style="color: #ff0015;"><b>Niedozwolone rozszerzenie pliku ' . $name . '. Akceptowane formaty to: ' . implode(', ', $allowed_extensions) . '</b></span><br>';
				}
			} else {
				$info .= '<span style="color: #ff0015;"><b>Plik ' . $name . ' przekracza maksymalny rozmiar 20 MB.</b></span><br>';
			}
		} else {
			$info .= '<span style="color: #ff0015;"><b>Wystąpił błąd podczas przesyłania pliku ' . $name . '. Spróbuj ponownie.</b></span><br>';
		}
	}
	$_SESSION['info'] = $info;
}

function generateThumbnail($sourcePath, $destinationPath, $thumbnailSize)
{
	list($width, $height) = getimagesize($sourcePath);

	$aspectRatio = $width / $height;

	$newWidth = $thumbnailSize;
	$newHeight = $thumbnailSize / $aspectRatio;

	$thumbnail = imagecreatetruecolor($newWidth, $newHeight);
	$sourceImage = loadImage($sourcePath);

	imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

	saveImage($thumbnail, $destinationPath);
}

function loadImage($path)
{
	$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

	switch ($extension) {
		case 'jpg':
		case 'jpeg':
			return imagecreatefromjpeg($path);
		case 'png':
			return imagecreatefrompng($path);
		case 'gif':
			return imagecreatefromgif($path);
		default:
			return false;
	}
}

function saveImage($image, $path)
{
	$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

	switch ($extension) {
		case 'jpg':
		case 'jpeg':
			imagejpeg($image, $path);
			break;
		case 'png':
			imagepng($image, $path);
			break;
		case 'gif':
			imagegif($image, $path);
			break;
	}

	imagedestroy($image);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Formularz przesyłania obrazków</title>
	<style>
		input[type=file] {
			width: 88%;
			max-width: 100%;
			color: #444;
			padding: 5px;
			background: #fff;
			border-radius: 10px;
			border: 1px solid #555;
			margin: 10px 0;
		}

		input[type=file]::file-selector-button {
			margin-right: 20px;
			border: none;
			background: #084cdf;
			padding: 10px 20px;
			border-radius: 10px;
			color: #fff;
			cursor: pointer;
			transition: background .2s ease-in-out;
		}

		input[type=file]::file-selector-button:hover {
			background: #0d45a5;
		}

		.button {
			display: inline-block;
			padding: 15px 25px;
			font-size: 24px;
			cursor: pointer;
			text-align: center;
			text-decoration: none;
			outline: none;
			color: #fff;
			background-color: #0b50bf;
			border: none;
			border-radius: 15px;
			box-shadow: 0 9px #999;
		}

		.button:hover {
			background-color: #003296
		}

		.button:active {
			background-color: #003296;
			box-shadow: 0 5px #666;
			transform: translateY(4px);
		}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

</head>

<body>
	<p><?php echo $_SESSION['info']; ?></p>
	<br>
	<div style="padding: 25px; border: 2px dotted black; width:85%; margin: auto auto;">
		<center>

			<div id="progress-bar" style="display: none; margin-bottom: 10px;">
				<progress id="upload-progress" value="0" max="100" style="width: 50%;"></progress>
				<span id="progress-label">0%</span>
			</div>

			<form id="upload-form" action="" method="post" enctype="multipart/form-data">
				<label>Wybierz obraz:<br></label>
				<input name="file[]" type="file" multiple="multiple" class="multi" accept="image/jpeg, image/jpg, image/png, image/gif">
				<br>
				<input class="button" type="submit" value="Prześlij">
			</form>
		</center>
	</div>

	<script>
		$(document).ready(function() {
			$('#upload-form').submit(function(e) {
				e.preventDefault();

				var formData = new FormData(this);

				$('#progress-bar').show();

				$.ajax({
					url: 'wyslij.php',
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					xhr: function() {
						var xhr = new window.XMLHttpRequest();
						xhr.upload.addEventListener('progress', function(e) {
							if (e.lengthComputable) {
								var percent = Math.round((e.loaded / e.total) * 100);
								$('#upload-progress').val(percent);
								$('#progress-label').text(percent + '%');
							}
						});
						return xhr;
					},
					success: function(response) {
						$('#progress-bar').hide();
						location.reload();
					},
					error: function(xhr, status, error) {
						console.error(xhr.responseText);
					}
				});
			});
		});
	</script>

	<br><br>
	<center><button class="button" onclick="window.location.href = 'wyslij.php?clear=clear';" style="font-size: 15px!important;">Wyczyść informacje o wysłanych plikach</button></center>

</body>

</html>
