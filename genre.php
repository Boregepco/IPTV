<?php

$xmlFile = 'genre.xml';

// Display instruction table at the top
echo '<table border="1" style="width: 100%; margin-bottom: 3px;">';
echo '<tr><td>';
echo '<span style="font-weight: bold; font-size: 16px;">Add New Genre:</span><br>';
echo 'Enter a new genre in the "Add New Genre" form below and click "Add Genre".<br>';
echo '<span style="font-weight: bold; font-size: 16px;">Delete Genre:</span><br>';
echo 'Click "Delete" next to the genre you want to remove.<br>';
echo '<span style="font-weight: bold; font-size: 16px;">Edit Genre:</span><br>';
echo 'Enter the new value in the text box next to the genre you want to edit and click "Edit".<br>';
echo 'Connected with genre.xml.<br>';
echo '</td></tr>';
echo '</table>';

// Check if the form is submitted for adding a new genre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_genre'])) {
    $newGenre = $_POST['new_genre'];

    // Load the XML file
    $xml = simplexml_load_file($xmlFile);

    // Check if the genre already exists
    if (genreExists($xml, $newGenre)) {
        echo '<p style="color: red;">Genre already exists. Please choose a different genre.</p>';
    } else {
        // Find the maximum ID in the XML to generate a new ID
        $maxId = 0;
        foreach ($xml->item as $item) {
            $maxId = max($maxId, (int)$item->ID);
        }

        // Add a new item with the specified Genre and a new ID
        $newItem = $xml->addChild('item');
        $newItem->addChild('ID', $maxId + 1);
        $newItem->addChild('Genre', $newGenre);

        // Save the updated XML
        file_put_contents($xmlFile, $xml->asXML());

        // Display success message
        echo '<p style="color: green;">Genre added successfully.</p>';

        // Redirect to a different page to prevent form resubmission
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Function to check if a genre already exists in the XML
function genreExists($xml, $newGenre) {
    foreach ($xml->item as $item) {
        if (strtolower($item->Genre) === strtolower($newGenre)) {
            return true;
        }
    }
    return false;
}

// Check if the form is submitted and a row ID is provided for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];

    // Load the XML file
    $xml = simplexml_load_file($xmlFile);

    // Find and remove the item with the specified ID
    foreach ($xml->xpath("//item[ID=$deleteId]") as $item) {
        unset($item[0]);
    }

    // Save the updated XML
    file_put_contents($xmlFile, $xml->asXML());

    // Redirect to a different page to prevent form resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Check if the form is submitted for editing a genre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id']) && isset($_POST['new_value'])) {
    $editId = $_POST['edit_id'];
    $newValue = $_POST['new_value'];

    // Load the XML file
    $xml = simplexml_load_file($xmlFile);

    // Check if the new value already exists
    if (genreExists($xml, $newValue)) {
        echo '<p style="color: red;">Genre already exists. Please choose a different value.</p>';
    } else {
        // Find the item with the specified ID and update the value
        foreach ($xml->xpath("//item[ID=$editId]") as $item) {
            $item->Genre = $newValue;
        }

        // Save the updated XML
        file_put_contents($xmlFile, $xml->asXML());

        // Display success message
        echo '<p style="color: green;">Genre updated successfully.</p>';

        // Redirect to a different page to prevent form resubmission
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Load and display the XML content
$xml = simplexml_load_file($xmlFile);

// Display the table
echo '<table border="1">';
echo '<tr><th><span style="font-weight: bold; font-size: larger;">Genre</span></th><th>Action</th><th>Edit</th></tr>';

foreach ($xml->item as $item) {
    echo '<tr>';
    echo '<td><span style="font-weight: bold; font-size: larger;">' . $item->Genre . '</span></td>';
    echo '<td>';
    echo '<form method="post">';
    echo '<input type="hidden" name="delete_id" value="' . $item->ID . '">';
    echo '<input type="submit" value="Delete">';
    echo '</form>';
    echo '</td>';
    echo '<td>';
    echo '<form method="post">';
    echo '<input type="hidden" name="edit_id" value="' . $item->ID . '">';
    echo '<input type="text" name="new_value" placeholder="New Value" required>';
    echo '<input type="submit" value="Edit">';
    echo '</form>';
    echo '</td>';
    echo '</tr>';
}

echo '</table>';

// Add a space between the table and the "Add New Genre" form
echo '<br>';

// Add a form for adding a new genre
echo '<form method="post">';
echo '<label for="new_genre">Add New Genre:</label>';
echo '<input type="text" id="new_genre" name="new_genre" required>';
echo '<input type="submit" name="add_genre" value="Add Genre">';
echo '</form>';

?>
