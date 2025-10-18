<?php
$dir = isset($_GET['dir']) ? 'files/' . $_GET['dir'] : 'files/';
$searchQuery = isset($_GET['search']) ? strtolower($_GET['search']) : '';

// Determine parent directory for "Go Up" button
$parentDir = dirname($dir);
if ($parentDir === 'files') {
    $parentDir = ''; // Prevents navigating above root folder
}

// Delete file functionality
if (isset($_GET['delete'])) {
    $fileToDelete = $dir . '/' . basename($_GET['delete']);
    if (file_exists($fileToDelete)) {
        unlink($fileToDelete);
    }
}

// Upload file functionality
if (isset($_FILES['file'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], $dir . '/' . basename($_FILES['file']['name']));
}

// Create new folder functionality
if (isset($_POST['new_folder']) && !empty($_POST['new_folder'])) {
    $newFolderName = basename($_POST['new_folder']); // Sanitize folder name
    $newFolderPath = $dir . '/' . $newFolderName;
    if (!file_exists($newFolderPath)) {
        mkdir($newFolderPath, 0777, true);
    }
}


// Function to recursively search files and folders
function searchFiles($folder, $query) {
    $results = [];
    foreach (scandir($folder) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $folder . '/' . $item;
        if (stripos($item, $query) !== false) {
            $results[] = $path;
        }
        if (is_dir($path)) {
            $results = array_merge($results, searchFiles($path, $query));
        }
    }
    return $results;
}

$files = array_diff(scandir($dir), array('.', '..'));
$searchResults = $searchQuery ? searchFiles('files', $searchQuery) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File Manager</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; text-align: center; }
        h2 { font-size: 24px; }
        form { margin-bottom: 20px; }
        input[type="file"], input[type="text"], input[type="submit"] {
            width: 80%; padding: 10px; margin: 5px; font-size: 16px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #f4f4f4; }
        a { text-decoration: none; color: blue; font-size: 18px; display: block; padding: 10px; }
        .folder { font-weight: bold; }
        .button {
            display: inline-block;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            font-size: 16px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        @media (max-width: 600px) {
            th, td { font-size: 14px; padding: 8px; }
            input[type="file"], input[type="text"], input[type="submit"] { font-size: 14px; width: 100%; }
        }
    </style>
</head>
<body>
    <h2>File Manager - <?php echo htmlspecialchars($dir); ?></h2>

    <?php if ($dir !== 'files/'): ?>
        <a href="?dir=<?php echo urlencode(substr($parentDir, 6)); ?>" class="button">⬆ Go Up</a>
        <a href="index_menu.php" class="button">MENU</a>
    <?php endif; ?>

    <form action="" method="get">
        <input type="hidden" name="dir" value="<?php echo isset($_GET['dir']) ? htmlspecialchars($_GET['dir']) : ''; ?>">
        <input type="text" name="search" placeholder="Search files or folders" value="<?php echo htmlspecialchars($searchQuery); ?>">
        <input type="submit" value="Search">
    </form>

    <form action="?dir=<?php echo urlencode(isset($_GET['dir']) ? $_GET['dir'] : ''); ?>" method="post" enctype="multipart/form-data">
        <input type="file" name="file">
        <input type="submit" value="Upload">
    </form>

	<form action="?dir=<?php echo urlencode(isset($_GET['dir']) ? $_GET['dir'] : ''); ?>" method="post">
		<input type="text" name="new_folder" placeholder="New folder name">
		<input type="submit" value="Create Folder">
	</form>

    <?php if ($searchQuery): ?>
        <h3>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
        <table>
            <tr><th>Found Items</th></tr>
            <?php foreach ($searchResults as $result): ?>
                <tr>
                    <td>
                        <?php if (is_dir($result)): ?>
                            <a class="folder" href="?dir=<?php echo urlencode(substr($result, 6)); ?>"><?php echo substr($result, 6); ?></a>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($result); ?>" target="_blank"><?php echo substr($result, 6); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <table>
            <tr>
                <th>File Name</th>
                <th>Action</th>
            </tr>
            <?php foreach ($files as $file): ?>
                <tr>
                    <td>
                        <?php if (is_dir($dir . '/' . $file)): ?>
                            <a class="folder" href="?dir=<?php echo urlencode(isset($_GET['dir']) ? $_GET['dir'] . '/' . $file : $file); ?>"><?php echo $file; ?></a>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($dir . '/' . $file); ?>" target="_blank"><?php echo $file; ?></a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?dir=<?php echo urlencode(isset($_GET['dir']) ? $_GET['dir'] : ''); ?>&delete=<?php echo urlencode($file); ?>" onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
