<?php
$originalsDir = 'zdjecia/';
$thumbnailsDir = 'zdjecia-miniaturki/';

$originals = glob($originalsDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
$thumbnails = glob($thumbnailsDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

array_multisort(array_map('filemtime', $originals), SORT_DESC, $originals);
array_multisort(array_map('filemtime', $thumbnails), SORT_DESC, $thumbnails);

$gallery = [];

foreach ($originals as $original) {
	$filename = basename($original);
	$thumbnail = findThumbnail($filename, $thumbnails);

	$date = date('d-m-Y', filemtime($original));

	if (!isset($gallery[$date])) {
		$gallery[$date] = [];
	}

	$gallery[$date][] = [
		'original' => $original,
		'thumbnail' => $thumbnail,
	];
}

function findThumbnail($filename, $thumbnails)
{
	$thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME);
	$thumbnailExtension = pathinfo($filename, PATHINFO_EXTENSION);

	foreach ($thumbnails as $thumbnail) {
		$thumbnailBase = basename($thumbnail);
		$thumbnailName = pathinfo($thumbnailBase, PATHINFO_FILENAME);

		if (strpos($thumbnailName, $thumbnailFilename) !== false && pathinfo($thumbnailBase, PATHINFO_EXTENSION) === $thumbnailExtension) {
			return $thumbnail;
		}
	}

	return null;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="robots" content="noindex">
	<meta name="robots" content="nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>🌴 Tajskie wakacje 🌴</title>
	<style>
		@import url('https://fonts.googleapis.com/css2?family=Gloria+Hallelujah&display=swap');

		body {
			background: linear-gradient(0deg, rgba(0, 198, 78, 1) 0%, rgba(255, 247, 139, 1) 45%, rgba(255, 151, 0, 1) 100%);
			background-attachment: fixed;
			background-repeat: no-repeat;
		}

		.gallery {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			justify-content: center;
			background: rgba(0, 0, 0, 0.2);
			border-radius: 16px;
			box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
			backdrop-filter: blur(5px);
			-webkit-backdrop-filter: blur(5px);
			border: 1px solid rgba(0, 0, 0, 0.82);
			padding: 10px;
			border-radius: 10px;
		}

		.full {
			position: fixed;
			padding: 10px;
			top: 0px;
			right: 0px;
			bottom: 0px;
			left: 0px;
			background: url('bg2.png') no-repeat bottom right;
			background-size: contain;
			overflow-y: scroll;
		}

		.full2 {
			position: fixed;
			padding: 10px;
			top: 0px;
			right: 0px;
			bottom: 0px;
			left: 0px;
			background: url('bg.png') no-repeat top right;
			background-size: contain;
			overflow-y: scroll;
		}

		.day-header {
			width: 100%;
			text-align: center;
			font-size: 25px;
			font-weight: bold;
			margin-top: 20px;
			font-family: "Gloria Hallelujah", cursive;
			font-style: normal;
			text-shadow:
				0 0 7px #fff,
				0 0 10px #fff,
				0 0 21px #fff;
		}

		.image-container {
			position: relative;
			overflow: hidden;
			border: 2px solid #FFF;
			border-radius: 8px;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
			cursor: pointer;
		}

		.image-container img {
			width: 100%;
			height: 100%;
			max-height: 100px;
			object-fit: cover;
			object-position: center;
			transition: transform 0.3s ease-in-out;
			transform: scale(1.1);
		}

		.image-container:hover img {
			transform: scale(1.2);
		}

		.modal {
			display: none;
			position: fixed;
			z-index: 1;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			overflow: auto;
			background-color: rgb(0, 0, 0);
			background-color: rgba(0, 0, 0, 0.9);
		}

		.modal-content {
			margin: auto;
			display: block;
			width: 100%;
			max-width: 800px;
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
		}

		.modal-content img {
			width: 100%;
			height: auto;
		}

		.close {
			position: absolute;
			top: 15px;
			right: 15px;
			color: #fff;
			font-size: 30px;
			font-weight: bold;
			cursor: pointer;
		}

		.prev,
		.next {
			position: absolute;
			top: 50%;
			width: auto;
			margin-top: -30px;
			padding: 15px;
			color: #fff;
			font-size: 30px;
			font-weight: bold;
			cursor: pointer;
			background-color: rgba(0, 0, 0, 0.8);
			user-select: none;
		}

		.next {
			right: 0;
		}

		#loadingIndicator {
			display: none;
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(255, 255, 255, 0.8);
			justify-content: center;
			z-index: 9999;
		}

		.loader {
			border: 8px solid #ff7500;
			border-top: 8px solid #ff0000;
			border-radius: 50%;
			width: 50px;
			height: 50px;
			animation: spin 1s linear infinite;
		}

		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}
	</style>
</head>

<body>
	<div class="full">
		<div class="full2">
			<?php
			foreach ($gallery as $date => $images) {
				echo '<div class="day-header">' . $date . '</div>';
				echo '<div class="gallery" id="gallery-' . str_replace('-', '', $date) . '">';

				foreach ($images as $index => $image) {
					echo '<div class="image-container" onclick="openModal(' . $index . ', \'' . $date . '\')">';
					echo '<img src="' . $image['thumbnail'] . '" alt="Zdjęcie">';
					echo '</div>';
				}

				echo '</div>';
			}
			?>

			<div id="myModal" class="modal">
				<div class="modal-content">
					<span class="close" onclick="closeModal()">&times;</span>
					<span class="prev" onclick="plusSlides(-1)">&#10094;</span>
					<img id="modalImage" src="" alt="Zdjęcie">
					<span class="next" onclick="plusSlides(1)">&#10095;</span>
					<div id="loadingIndicator">
						<center style="display: flex;justify-content: center;align-items: center;width: 100%;height: 100%;">
							<div class="loader"></div>
						</center>
					</div>
				</div>
			</div>

			<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
			<script>
				let currentIndex = 0;
				let currentGallery = <?php echo json_encode($gallery); ?>;
				let currentGalleryDate = '';
				let isLoading = false;

				function openModal(index, date) {
					const modal = document.getElementById('myModal');
					const modalImage = document.getElementById('modalImage');

					currentIndex = index;
					currentGalleryDate = date;

					updateModalContent();

					modal.style.display = 'block';
				}

				function closeModal() {
					const modal = document.getElementById('myModal');
					modal.style.display = 'none';
				}

				function plusSlides(n) {
					currentIndex += n;
					updateModalContent();
				}

				function updateModalContent() {
					const modalImage = document.getElementById('modalImage');
					const prevButton = document.querySelector('.prev');
					const nextButton = document.querySelector('.next');
					const loadingIndicator = document.getElementById('loadingIndicator');

					isLoading = true;
					loadingIndicator.style.display = 'block';

					const img = new Image();
					img.onload = function() {
						modalImage.src = img.src;
						isLoading = false;
						loadingIndicator.style.display = 'none';
					};
					img.src = currentGallery[currentGalleryDate][currentIndex]['original'];

					prevButton.style.display = currentIndex === 0 ? 'none' : 'block';
					nextButton.style.display = currentIndex === currentGallery[currentGalleryDate].length - 1 ? 'none' : 'block';
				}

				window.onclick = function(event) {
					const modal = document.getElementById('myModal');
					if (event.target === modal) {
						closeModal();
					}
				};

				document.addEventListener('keydown', function(event) {
					if (event.key === 'ArrowLeft' && !isLoading) {
						plusSlides(-1);
					} else if (event.key === 'ArrowRight' && !isLoading) {
						plusSlides(1);
					} else if (event.key === 'Escape') {
						closeModal();
					}
				});
			</script>
		</div>
	</div>
</body>

</html>
