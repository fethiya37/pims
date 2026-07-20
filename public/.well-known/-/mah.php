<?php
// Fungsi untuk menghitung jumlah baris dalam sebuah file
function getFileRowCount($filename) {
    $file = fopen($filename, "r");
    $rowCount = 0;

    while (!feof($file)) {
        fgets($file);
        $rowCount++;
    }

    fclose($file);
    return $rowCount;
}

$urls = [
    "https://gudangcheat-v3.vip/keyword/list.txt" => "pangeran",
    "https://gudangcheat-v3.vip/keyword/putih.txt" => "paduka"
];

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$fullUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

if (isset($fullUrl)) {
    $parsedUrl = parse_url($fullUrl);
    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : '';
    $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
    $baseUrl = $scheme . "://" . $host . $path;
    $urlAsli = str_replace("mah.php", "", $baseUrl);

    // Buat dua file sitemap
    $sitemapFiles = [
        "pangeran" => fopen("mapingjago1.xml", "w"),
        "paduka" => fopen("mapingjago2.xml", "w")
    ];

    foreach ($sitemapFiles as $param => $file) {
        fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
        fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);
    }

    foreach ($urls as $url => $param) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $fileContent = curl_exec($ch);
        if ($fileContent === FALSE) {
            echo "Gagal mengambil konten dari URL: " . curl_error($ch);
            continue;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'temp_file_');
        file_put_contents($tempFile, $fileContent);

        $fileLines = file($tempFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($fileLines as $index => $judul) {
            $sitemapLink = $urlAsli . '?' . $param . '=' . urlencode($judul);
            fwrite($sitemapFiles[$param], '  <url>' . PHP_EOL);
            fwrite($sitemapFiles[$param], '    <loc>' . $sitemapLink . '</loc>' . PHP_EOL);
            fwrite($sitemapFiles[$param], '  </url>' . PHP_EOL);
        }

        unlink($tempFile);
        curl_close($ch);
    }

    foreach ($sitemapFiles as $file) {
        fwrite($file, '</urlset>' . PHP_EOL);
        fclose($file);
    }

    echo "SITEMAPS CREATED!";
} else {
    echo "URL saat ini tidak didefinisikan.";
}
?>
