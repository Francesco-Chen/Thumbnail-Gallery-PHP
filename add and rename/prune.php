<?php
function deleteOrphanedFilesAndFolders($galleryDir, $thumbDir) {
    $galleryFiles = getFilesRecursive($galleryDir);
    $thumbFiles = getFilesRecursive($thumbDir);

    // Delete orphaned thumbnails
    foreach ($thumbFiles as $thumbFile) {
        $galleryFile = str_replace($thumbDir, $galleryDir, $thumbFile);
        if (!in_array($galleryFile, $galleryFiles)) {
            unlink($thumbFile);
            echo "Deleted orphaned thumbnail: $thumbFile\n <br>";
        }
    }

    // Delete empty folders in thumb directory
    deleteEmptyFolders($thumbDir);
}

function getFilesRecursive($dir) {
    $files = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
        RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
    );

    foreach ($iterator as $path => $info) {
        if ($info->isFile()) {
            $files[] = $path;
        }
    }

    return $files;
}

function deleteEmptyFolders($dir) {
    foreach (glob($dir . '/*') as $folder) {
        if (is_dir($folder)) {
            deleteEmptyFolders($folder);
            if (count(glob($folder . '/*')) === 0) {
                rmdir($folder);
                echo "Deleted empty folder: $folder\n <br>";
            }
        }
    }
}

$galleryDir = 'gallery';
$thumbDir = 'thumb';

deleteOrphanedFilesAndFolders($galleryDir, $thumbDir);
?>
