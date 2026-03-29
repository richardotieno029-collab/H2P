<?php

// Run this script from CLI (php optimize_images.php) to compress and resize
// existing images that are already referenced in the database.

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/includes/image_utils.php';

$maxWidth = 1200;
$quality = 70;

function processImageRows($conn, $sql, $column) {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $path = $row[$column] ?? null;
        if (!$path) {
            continue;
        }

        // Normalize relative paths
        if (!preg_match('#^(/|[A-Za-z]:\\)#', $path)) {
            $path = __DIR__ . '/' . ltrim($path, '/');
        }

        if (!file_exists($path)) {
            continue;
        }

        if (optimizeImageFile($path, $path, $GLOBALS['maxWidth'], $GLOBALS['quality'])) {
            $count++;
            echo "Optimized: $path\n";
        }
    }

    return $count;
}

$totals = 0;

$queries = [
    [
        'sql' => 'SELECT image_path FROM houses',
        'col' => 'image_path'
    ],
    [
        'sql' => 'SELECT image_path FROM house_images',
        'col' => 'image_path'
    ],
    [
        'sql' => 'SELECT image_path FROM rooms',
        'col' => 'image_path'
    ],
    [
        'sql' => 'SELECT image_path FROM room_images',
        'col' => 'image_path'
    ],
    [
        'sql' => 'SELECT profile_image FROM landlords',
        'col' => 'profile_image'
    ],
    [
        'sql' => 'SELECT profile_image FROM students',
        'col' => 'profile_image'
    ],
];

foreach ($queries as $q) {
    echo "Processing {$q['col']}...\n";
    $totals += processImageRows($conn, $q['sql'], $q['col']);
}

echo "Done. Optimized $totals images.\n";
