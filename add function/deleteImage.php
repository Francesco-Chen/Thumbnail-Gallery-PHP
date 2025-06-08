<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["file"])) {
    $file = $_POST["file"];

    // 获取 gallery 和 thumb 目录的真实路径
    $galleryDir = realpath("gallery");
    $thumbDir = realpath("thumb");
    $realFile = realpath($file);

    if ($realFile && strpos($realFile, $galleryDir) === 0) {
        // 计算缩略图路径
        $thumbFile = str_replace($galleryDir, $thumbDir, $realFile);

        // 删除原图
        if (file_exists($realFile)) {
            unlink($realFile);
            echo "Original file deleted successfully.<br>";
        } else {
            echo "Original file not found.<br>";
        }

        // 删除缩略图
        if (file_exists($thumbFile)) {
            unlink($thumbFile);
            echo "Thumbnail deleted successfully.<br>";
        } else {
            echo "Thumbnail not found.<br>";
        }
    } else {
        echo "Invalid file path.";
    }
} else {
    echo "Invalid request.";
}
header('Location: delete.php');
?>
