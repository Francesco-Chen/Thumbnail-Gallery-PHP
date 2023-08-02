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

    function createThumbnail($src, $dest, $thumbHeight) {
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

    $galleryDir = "gallery/";
    $thumbDir = "thumb/";
    $allowedExtensions = array("jpg", "jpeg", "png", "gif", "webp"); // Remove "mp4" from allowed extensions

    // Check and create the "thumb" directory if it doesn't exist
    if (!file_exists($thumbDir)) {
        mkdir($thumbDir);
    }

    $galleryFiles = scandir($galleryDir);

    foreach ($galleryFiles as $file) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), $allowedExtensions)) {
            $imageURL = $galleryDir . $file;
            $thumbURL = $thumbDir . $file;

            if (!file_exists($thumbURL)) {
                createThumbnail($imageURL, $thumbURL, 200); // The thumbnail will have a height of 200 pixels.
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
