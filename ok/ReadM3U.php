<?php

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["m3uFile"])) {
    // Check if the file is uploaded successfully
    if ($_FILES["m3uFile"]["error"] == 0) {
        $uploadedFilePath = $_FILES["m3uFile"]["tmp_name"];

        // Process the uploaded file
        $m3uContent = file_get_contents($uploadedFilePath);
        $lines = explode(PHP_EOL, $m3uContent);

        $channels = [];

        foreach ($lines as $line) {
            if (strpos($line, '#EXTINF:') === 0) {
                $displayName = '';
                if (strpos($line, ',') !== false) {
                    $displayName = trim(substr($line, strpos($line, ',') + 1));
                }

                preg_match('/tvg-id="([^"]+)"/', $line, $tvgIdMatches);
                preg_match('/tvg-name="([^"]+)"/', $line, $tvgNameMatches);

                $tvgId = isset($tvgIdMatches[1]) ? $tvgIdMatches[1] : '';
                $tvgName = isset($tvgNameMatches[1]) ? $tvgNameMatches[1] : '';

                if (empty($tvgId)) {
                    $tvgId = $tvgName;
                } elseif (empty($tvgName)) {
                    $tvgName = $tvgId;
                }

                $channels[] = [
                    'displayName' => $displayName,
                    'url' => '',
                    'tvgId' => $tvgId,
                    'tvgName' => $tvgName,
                ];
            } elseif (strpos($line, 'http') === 0) {
                $channels[count($channels) - 1]['url'] = $line;
            }
        }

        // Display the "Upload M3U File" section
        echo '<h2>Upload M3U File</h2>';
        echo '<form action="" method="post" enctype="multipart/form-data">';
        echo '<input type="file" name="m3uFile" accept=".m3u">';
        echo '<button type="submit">Upload</button>';
        echo '</form>';

        // Display the number of records on the screen
        echo '<p>Number of records on the screen: ' . count($channels) . '</p>';

        // Display the data table
        echo '<table border="1">';
        echo '<tr><th>tvg-id</th><th>tvg-name</th><th>Display Name</th><th>Channel URL</th></tr>';

        foreach ($channels as $channel) {
            echo '<tr>';
            echo '<td>' . $channel['tvgId'] . '</td>';
            echo '<td>' . $channel['tvgName'] . '</td>';
            echo '<td>' . $channel['displayName'] . '</td>';
            echo '<td>' . $channel['url'] . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo "Error uploading the file.";
    }
} else {
    // Display the "Upload M3U File" section for the initial load
    echo '<h2>Upload M3U File</h2>';
    echo '<form action="" method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="m3uFile" accept=".m3u">';
    echo '<button type="submit">Upload</button>';
    echo '</form>';
}

?> 