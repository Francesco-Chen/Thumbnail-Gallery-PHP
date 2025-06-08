<!DOCTYPE html>
<html lang="en">
<head>
  <style type="text/css">
        .add-btn {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      font-size: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      cursor: pointer;
    }
    .add-btn:hover {
      background-color: #0056b3;
    }
    .delete-btn {
  position: fixed;
  bottom: 100px;
  right: 20px;
  background-color: #dc3545; /* 红色，表示删除 */
  color: white;
  border: none;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  font-size: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  cursor: pointer;
}

.delete-btn:hover {
  background-color: #b52b3a; /* 深一点的红色 */
}

  </style>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width"/>
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"
    />
</head>
<body>
<div>
    <?php

    function createThumbnail($src, $dest, $thumbHeight){
          list($width, $height, $type) = getimagesize($src);
          $aspectRatio = $width / $height;
          $thumbWidth = $thumbHeight * $aspectRatio;

          switch ($type) {
              case IMAGETYPE_JPEG:
                  $image = imagecreatefromjpeg($src);
                  break;
              case IMAGETYPE_PNG:
                  $image = imagecreatefrompng($src);
                  break;
              case IMAGETYPE_WEBP: // Support webp format
                  $image = imagecreatefromwebp($src);
                  break;
              default:
                  return false; // Unsupported image type
          }

          $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

          imagecopyresampled($thumb, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

          switch ($type) {
              case IMAGETYPE_JPEG:
                  imagejpeg($thumb, $dest);
                  break;
              case IMAGETYPE_PNG:
                  imagepng($thumb, $dest);
                  break;
              case IMAGETYPE_WEBP: // Support webp format
                  imagewebp($thumb, $dest);
                  break;
          }

          imagedestroy($thumb);
          imagedestroy($image);
          return true;
      }

    function processGallery($galleryDir, $thumbDir, $allowedExtensions)
{
    if (!file_exists($thumbDir)) {
        mkdir($thumbDir, 0777, true); // Create nested directories
    }

    $galleryFiles = scandir($galleryDir);

    // Process files first
    foreach ($galleryFiles as $file) {
        if ($file === '.' || $file === '..' || is_dir($galleryDir . DIRECTORY_SEPARATOR . $file)) {
            continue;
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), $allowedExtensions)) {
            $imageURL = $galleryDir . DIRECTORY_SEPARATOR . $file;
            $thumbURL = $thumbDir . DIRECTORY_SEPARATOR . $file;

            if (strtolower($extension) === "mp4") {
                echo '
                    <a
                        data-fancybox="gallery"
                        data-src="' . $imageURL . '"
                        data-caption="' . $file . '"
                    >
                        <img src="' . $imageURL . '" height="200" />
                    </a>
                ';
            } else {
                if (!file_exists($thumbURL)) {
                    createThumbnail($imageURL, $thumbURL, 500);
                }

                echo '
                    <a
                        data-fancybox="gallery"
                        data-src="' . $imageURL . '"
                        data-caption="' . $file . '"
                    >
                        <img src="' . $thumbURL . '" height="200" />
                    </a>
                ';
            }
        }
    }

    // Process subfolders next
    foreach ($galleryFiles as $file) {
        if ($file === '.' || $file === '..' || !is_dir($galleryDir . DIRECTORY_SEPARATOR . $file)) {
            continue;
        }

        $subDirPath = $galleryDir . DIRECTORY_SEPARATOR . $file;
        $subThumbDirPath = $thumbDir . DIRECTORY_SEPARATOR . $file;

        // Skip the "@eaDir" folders
        if ($file === '@eaDir') {
          continue;
        }

        echo '<h2>' . $file . '</h2>';
        processGallery($subDirPath, $subThumbDirPath, $allowedExtensions);
    }
}

    $galleryDir = "gallery";
    $thumbDir = "thumb";
    $allowedExtensions = array("jpg", "jpeg", "png", "gif", "webp", "mp4");

    processGallery($galleryDir, $thumbDir, $allowedExtensions);

    ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
    Fancybox.bind('[data-fancybox="gallery"]', {
        //
    });
</script>
  <!-- Add Asset Button -->
  <button class="add-btn" onclick="window.location.href='add.php'">+</button>
  <button class="delete-btn" onclick="window.location.href='delete.php'">-</button>

</body>
</html>
