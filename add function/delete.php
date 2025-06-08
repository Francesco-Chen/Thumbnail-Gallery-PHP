<!DOCTYPE html>
<html lang="en">
<head>
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
        mkdir($thumbDir, 0777, true);
    }

    $galleryFiles = scandir($galleryDir);

    foreach ($galleryFiles as $file) {
        if ($file === '.' || $file === '..' || is_dir($galleryDir . DIRECTORY_SEPARATOR . $file)) {
            continue;
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), $allowedExtensions)) {
            $imageURL = $galleryDir . DIRECTORY_SEPARATOR . $file;
            $thumbURL = $thumbDir . DIRECTORY_SEPARATOR . $file;

            echo '<div style="display:inline-block; text-align:center; margin:10px;">';

            if (strtolower($extension) === "mp4") {
                echo '
                    <a data-fancybox="gallery" data-src="' . $imageURL . '" data-caption="' . $file . '">
                        <img src="' . $imageURL . '" height="200" />
                    </a>
                ';
            } else {
                if (!file_exists($thumbURL)) {
                    createThumbnail($imageURL, $thumbURL, 500);
                }

                echo '
                    <a data-fancybox="gallery" data-src="' . $imageURL . '" data-caption="' . $file . '">
                        <img src="' . $thumbURL . '" height="200" />
                    </a>
                ';
            }

            // 删除按钮
            echo '
                <form action="deleteImage.php" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this file?\');">
                    <input type="hidden" name="file" value="' . htmlspecialchars($imageURL) . '">
                    <button type="submit" style="margin-top:5px; background-color:red; color:white; border:none; padding:5px; cursor:pointer;">
                        Delete
                    </button>
                </form>
            ';

            echo '</div>';
        }
    }

    foreach ($galleryFiles as $file) {
        if ($file === '.' || $file === '..' || !is_dir($galleryDir . DIRECTORY_SEPARATOR . $file)) {
            continue;
        }

        $subDirPath = $galleryDir . DIRECTORY_SEPARATOR . $file;
        $subThumbDirPath = $thumbDir . DIRECTORY_SEPARATOR . $file;

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
</body>
</html>
