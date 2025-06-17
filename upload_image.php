<?php
session_start();
include 'config.php'; // Include your database connection

$imageData = null;  // To store the image data
$imagePath = null;  // To store the image file path
$message = "";  // To store any messages like success or error

// Handle the image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    // Get the image file details
    $imageName = $_FILES['image']['name'];
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageSize = $_FILES['image']['size'];
    $imageError = $_FILES['image']['error'];

    // Set the target directory to store the image
    $targetDir = "uploads/";  // Folder 'uploads' should exist in your project directory
    $targetFile = $targetDir . basename($imageName);
    
    // Check if there were any errors during the upload
    if ($imageError === 0) {
        // Move the uploaded file to the target directory
        if (move_uploaded_file($imageTmpName, $targetFile)) {
            // Save the file path in the database
            $query = "INSERT INTO images (image_name, image_path) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $imageName, $targetFile);
            $stmt->execute();
            $message = "Image uploaded successfully!";
        } else {
            $message = "Failed to upload the image.";
        }
    } else {
        $message = "Error during file upload.";
    }
}

// Handle image deletion
if (isset($_GET['delete'])) {
    $imageId = $_GET['delete'];
    // Fetch the image file path from the database
    $query = "SELECT image_path FROM images WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    
    // Check if the file exists before deleting it
    if (file_exists($imagePath)) {
        // Try to delete the image from the uploads folder
        if (unlink($imagePath)) {
            // After deleting the image, remove the record from the database
            $deleteQuery = "DELETE FROM images WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $imageId);
            $deleteStmt->execute();
            $message = "Image deleted successfully!";
        } else {
            $message = "Error deleting the image.";
        }
    } else {
        $message = "File does not exist.";
    }
}

// Fetch the latest uploaded image from the database
$query = "SELECT id, image_name, image_path FROM images ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);

$image = null;
if ($result->num_rows > 0) {
    $image = $result->fetch_assoc();
    $imageData = $image['image_name'];
    $imagePath = $image['image_path'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .upload-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .upload-container h2 {
            margin-bottom: 20px;
        }

        .upload-container form {
            display: flex;
            flex-direction: column;
        }

        .upload-container input[type="file"] {
            margin-bottom: 10px;
        }

        .upload-container button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .upload-container button:hover {
            background-color: #218838;
        }

        .image-preview {
            margin-top: 20px;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            margin-bottom: 10px;
        }

        .delete-button {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-button:hover {
            background-color: #c82333;
        }

        /* Modal Popup Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
        }

        .modal-content button {
            padding: 10px;
            margin: 5px;
        }

        .cancel-button {
            background-color: #ddd;
        }

        .cancel-button:hover {
            background-color: #bbb;
        }

        .message {
            padding: 10px;
            background-color: #f1f1f1;
            margin-top: 20px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

    <div class="upload-container">
        <h2>Upload Image</h2>
        
        <!-- Display messages like success or error -->
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'success') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Image Upload Form -->
        <form action="upload_image.php" method="POST" enctype="multipart/form-data">
            <label for="image">Select image:</label>
            <input type="file" name="image" id="image" required>
            <button type="submit" name="upload">Upload</button>
        </form>

        <!-- Display Uploaded Image -->
        <?php if ($image): ?>
            <div class="image-preview">
                <h3>Uploaded Image</h3>
                <img src="<?php echo $image['image_path']; ?>" alt="Uploaded Image">
                <br>
                <button class="delete-button" onclick="confirmDelete(<?php echo $image['id']; ?>)">Delete Image</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to delete this image?</p>
            <button id="confirmDeleteButton" class="delete-button">Yes, Delete</button>
            <button class="cancel-button" onclick="closeModal()">Cancel</button>
        </div>
    </div>

    <script>
        function confirmDelete(imageId) {
            // Show the modal for confirmation
            document.getElementById("deleteModal").style.display = "block";
            
            // Set the delete button to redirect with the delete image ID
            document.getElementById("confirmDeleteButton").onclick = function() {
                window.location.href = "upload_image.php?delete=" + imageId;
            };
        }

        function closeModal() {
            document.getElementById("deleteModal").style.display = "none";
        }

        // Close the modal if the user clicks anywhere outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById("deleteModal")) {
                closeModal();
            }
        };
    </script>

</body>
</html>
