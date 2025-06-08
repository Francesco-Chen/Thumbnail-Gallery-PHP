<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photosDir = 'gallery';

    // Ensure the photos directory exists
    if (!is_dir($photosDir)) {
        mkdir($photosDir, 0755, true);
    }

    // Function to sanitize and generate a unique filename
    function getUniqueFilename($dir, $originalName) {
        $pathinfo = pathinfo($originalName);

        // Clean the filename: keep only letters, numbers, dash, underscore and dot
        $basename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $pathinfo['filename']);
        $extension = isset($pathinfo['extension']) ? '.' . strtolower($pathinfo['extension']) : '';

        $newName = $basename . $extension;
        $counter = 1;

        while (file_exists($dir . DIRECTORY_SEPARATOR . $newName)) {
            $newName = $basename . '-' . $counter . $extension;
            $counter++;
        }
        return $newName;
    }

    // Handle multiple photo uploads
    if (!empty($_FILES['photos']['name'][0])) {
        foreach ($_FILES['photos']['tmp_name'] as $index => $photoTmpPath) {
            if ($_FILES['photos']['error'][$index] === UPLOAD_ERR_OK) {
                $originalName = $_FILES['photos']['name'][$index];
                $safeName = getUniqueFilename($photosDir, $originalName);
                $photoDestPath = $photosDir . DIRECTORY_SEPARATOR . $safeName;

                // Correct image orientation and compress
                $image = imagecreatefromstring(file_get_contents($photoTmpPath));
                if ($image) {
                    $exif = @exif_read_data($photoTmpPath);
                    if (!empty($exif['Orientation'])) {
                        switch ($exif['Orientation']) {
                            case 3:
                                $image = imagerotate($image, 180, 0);
                                break;
                            case 6:
                                $image = imagerotate($image, -90, 0);
                                break;
                            case 8:
                                $image = imagerotate($image, 90, 0);
                                break;
                        }
                    }

                    imagejpeg($image, $photoDestPath, 75); // Compress to 75% quality
                    imagedestroy($image);
                    echo "<p style='color: green;'>Upload successful: $safeName</p>";
                } else {
                    echo "<p style='color: red;'>Invalid image file.</p>";
                }
            } else {
                echo "<p style='color: red;'>File upload error.</p>";
            }
        }
        header('Location: .');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Images</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        .container {
            max-width: 400px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #4CAF50;
        }

        .drop-zone {
            border: 2px dashed #4CAF50;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            background-color: #fafafa;
        }

        .drop-zone.highlight {
            background-color: #e8f5e9;
        }

        .preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .preview img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[type="file"] {
            display: none;
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Images</h1>
        <form method="POST" action="" enctype="multipart/form-data" id="upload-form">
            <div class="drop-zone" id="drop-zone">Drag & Drop Images Here</div>
            <input type="file" id="photos" name="photos[]" accept="image/*" multiple>
            <div class="preview" id="preview"></div>
            <button type="submit">Upload</button>
        </form>
    </div>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('photos');
        const preview = document.getElementById('preview');
        const uploadForm = document.getElementById('upload-form');
        let filesArray = [];

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('highlight');
        });

        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('highlight'));

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('highlight');
            addFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => addFiles(e.target.files));

        function addFiles(files) {
            for (let file of files) {
                if (!filesArray.includes(file)) {
                    filesArray.push(file);
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    preview.appendChild(img);
                }
            }
            updateFileInput();
        }

        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            filesArray.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }
    </script>
</body>
</html>