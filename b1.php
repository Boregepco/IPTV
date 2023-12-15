<?php
// Initialize variables
$xml_url = "";
$display_name = "";
$cut_length = 0; // Start from 0 characters to cut
$cut_from_right = false; // Default to not cut from the right
$dataTableDisplayed = false;
$channelCount = 0;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the XML URL is provided in the form
    if (isset($_POST['xml_url']) && !empty($_POST['xml_url'])) {
        $xml_url = $_POST['xml_url']; // Get the XML URL from the form

        // Get the display-name from the form if provided
        $display_name = isset($_POST['display_name']) ? $_POST['display_name'] : '';

        // Sanitize the display name to handle special characters
        $display_name = htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8');

        // Get the number of characters to cut from the form
        $cut_length = isset($_POST['cut_length']) ? (int)$_POST['cut_length'] : $cut_length;

        // Check if the checkbox for cutting from right is checked
        $cut_from_right = isset($_POST['cut_from_right']);

        // Fetch the XML content
        $xml_content = file_get_contents($xml_url);
        if ($xml_content !== false) {
            $xml = simplexml_load_string($xml_content); // Load the XML content
            $dataTableDisplayed = true; // Set flag to true if the data table is displayed
            $channelCount = count($xml->channel); // Count the number of channels
        } else {
            echo "Failed to load the XML content from the provided URL.";
        }
    }
}

// Function to export data to XML file
function exportToXML($xmlData, $filename) {
    if ($xmlData !== null) {
        $xmlData->asXML($filename);
        echo "Data exported successfully to $filename.";
    } else {
        echo "Error: Unable to export data. XML data is missing.";
    }
}

// Check if the export button is clicked
if (isset($_POST['export'])) {
    // Create a new SimpleXMLElement for exporting modified data
    $exportXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><exported_data></exported_data>');

    foreach ($xml->channel as $counter => $channel) {
        // Apply the same modifications made for display
        $display_name_to_export = $cut_from_right
            ? substr($channel->{'display-name'}, 0, -$cut_length)
            : substr($channel->{'display-name'}, $cut_length);

        // Create a new channel element in the exported XML
        $exportedChannel = $exportXml->addChild('channel');
        $exportedChannel->addAttribute('id', $channel->attributes()->id);
        $exportedChannel->{'display-name'} = $display_name_to_export;

        // Add additional columns to the exported XML
        $exportedChannel->addChild('logo-file', "{$channel->attributes()->id}.png");
        $exportedChannel->addChild('logo-url', "http://localhost/web/IPTV/Logo/{$channel->attributes()->id}.png");
    }

    // Get the user-specified filename from the input field
    $export_filename = isset($_POST['export_filename']) ? $_POST['export_filename'] : 'exported_data';

    // Add ".xml" to the filename
    $export_filename .= '.xml';

    // Export the modified data with the specified filename
    exportToXML($exportXml, $export_filename);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XML Parser</title>
    <style>
        .centered {
            margin-left: auto;
            margin-right: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .submit-row {
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- New table with one row -->
<table class="centered" border="1">
    <tr>
        <td colspan="4" style="text-align: center; font-weight: bold;">New Table Instructions</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center;">В XML URL: е път до локален файл или URL до EPG.xml файл.</td>
    </tr>
    <tr>
    <td colspan="4" style="text-align: center;">В Display Name: от EPG.xml &lt;display-name lang="bg"&gt;.</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center;">В Number of Characters to Cut: Брой символи ако искаме да отрежем.</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center;">Определя отпред или отзад режем.</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center;">Бутон Submit за да изпълни.</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center;">Ако искаме да запишем е полето за име на файл и Бутон Export.</td>
    </tr>
</table>


    <!-- Display the form table -->
    <form method="post" action="">
        <table class="centered" border="1">
            <tr>
                <td>XML URL:</td>
                <td><input type="text" id="xml_url" name="xml_url" value="<?php echo $xml_url; ?>" required></td>
                <td>Display Name:</td>
                <td><input type="text" id="display_name" name="display_name" value="<?php echo $display_name; ?>"></td>
            </tr>
            <tr>
                <td>Number of Characters to Cut:</td>
                <td><input type="number" id="cut_length" name="cut_length" value="<?php echo $cut_length; ?>" min="0" required></td>
                <td>Cut from Right:</td>
                <td><input type="checkbox" id="cut_from_right" name="cut_from_right" <?php echo $cut_from_right ? 'checked' : ''; ?>></td>
            </tr>
            <tr>
                <!-- Display the channel count on the left side of the submit button -->
                <td colspan="3" style="text-align: left;">Number of Channels: <?php echo $channelCount; ?></td>
                <td colspan="1" style="text-align: center;">
                    <input type="submit" value="Submit" name="submit">
                    
                    <!-- Add an input field for the XML file name -->
                    <input type="text" id="export_filename" name="export_filename" placeholder="Enter XML file name (without extension)">
                    
                    <input type="submit" value="Export" name="export">
                </td>
            </tr>
        </table>
    </form>

    <!-- Data Table -->
    <?php if ($dataTableDisplayed): ?>
        <table border='1' class="centered">
            <tr>
                <th>Logo</th>
                <th>Channel ID</th>
                <th>Display Name</th>
                <th>Logo File</th>
                <th>Logo URL</th>
            </tr>
            <?php
            foreach ($xml->channel as $channel):
                // Cut the specified number of characters from the display name
                $display_name_to_show = $cut_from_right
                    ? substr($channel->{'display-name'}, 0, -$cut_length)
                    : substr($channel->{'display-name'}, $cut_length);
                ?>
                <tr>
                    <td><img src='<?php echo "http://localhost/web/IPTV/Logo/{$channel->attributes()->id}.png"; ?>' alt='Logo' style='width:100%;'></td>
                    <td><?php echo $channel->attributes()->id; ?></td>
                    <td><?php echo $display_name_to_show; ?></td>
                    <td><?php echo "{$channel->attributes()->id}.png"; ?></td>
                    <td><?php echo "http://localhost/web/IPTV/Logo/{$channel->attributes()->id}.png"; ?></td>
                </tr>
                <?php
            endforeach;
            ?>
        </table>
    <?php endif; ?>
</body>
</html>
