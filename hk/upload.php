<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

$pdo = Config::getInstance()->getConnection();
$auth = new Auth($pdo);
$userId = $auth->validateSession();

if (!$userId) {
    jsonResponse(false, null, 'Not authenticated');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Only POST method allowed');
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'upload':
        uploadFile($pdo, $userId);
        break;
    default:
        jsonResponse(false, null, 'Invalid action');
}

/**
 * Create image thumbnail
 */
function createThumbnail($sourcePath, $destPath, $width = 300, $height = 300) {
    if (!file_exists($sourcePath)) {
        return false;
    }
    
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return false;
    }
    
    list($origWidth, $origHeight, $type) = $imageInfo;
    
    // Calculate dimensions maintaining aspect ratio
    $ratio = $origWidth / $origHeight;
    if ($width / $height > $ratio) {
        $width = $height * $ratio;
    } else {
        $height = $width / $ratio;
    }
    
    $width = max(1, (int)$width);
    $height = max(1, (int)$height);
    
    // Create image resource
    $source = null;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            imagealphablending($source, true);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    if (!$source) {
        return false;
    }
    
    // Create thumbnail
    $thumbnail = imagecreatetruecolor($width, $height);
    
    // Preserve transparency for PNG
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefilledrectangle($thumbnail, 0, 0, $width, $height, $transparent);
    }
    
    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
    
    // Save thumbnail
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($thumbnail, $destPath, 70);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($thumbnail, $destPath, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($thumbnail, $destPath);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($thumbnail, $destPath, 70);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($thumbnail);
    
    return $result;
}

/**
 * Optimize image (reduce file size)
 */
function optimizeImage($sourcePath, $destPath, $maxWidth = 1200, $quality = 70) {
    if (!file_exists($sourcePath)) {
        return false;
    }
    
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return false;
    }
    
    list($origWidth, $origHeight, $type) = $imageInfo;
    
    // Only resize if needed
    $newWidth = $origWidth;
    $newHeight = $origHeight;
    
    if ($origWidth > $maxWidth) {
        $ratio = $origHeight / $origWidth;
        $newWidth = $maxWidth;
        $newHeight = $maxWidth * $ratio;
    }
    
    // Create image resource
    $source = null;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            imagealphablending($source, true);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    if (!$source) {
        return false;
    }
    
    $optimized = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($optimized, false);
        imagesavealpha($optimized, true);
        $transparent = imagecolorallocatealpha($optimized, 255, 255, 255, 127);
        imagefilledrectangle($optimized, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    imagecopyresampled($optimized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    
    // Save optimized image
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($optimized, $destPath, $quality);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($optimized, $destPath, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($optimized, $destPath);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($optimized, $destPath, $quality);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($optimized);
    
    return $result;
}

/**
 * Main upload function
 */
function uploadFile($pdo, $userId) {
    // Check if file exists
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(false, null, 'No file uploaded or upload error');
    }
    
    // Check receiver
    if (!isset($_POST['receiver_id']) || empty($_POST['receiver_id'])) {
        jsonResponse(false, null, 'Receiver ID is required');
    }
    
    $receiverId = (int)$_POST['receiver_id'];
    $file = $_FILES['file'];
    
    // Allowed file types
    $allowedMimeTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/quicktime',
        'application/pdf', 'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain', 'text/html'
    ];
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mp3', 'webm', 'mov', 'pdf', 'doc', 'docx', 'txt', 'html'];
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($extension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
        jsonResponse(false, null, 'File type not allowed');
    }
    
    // Max file size: 60MB
    if ($file['size'] > 60 * 1024 * 1024) {
        jsonResponse(false, null, 'File too large. Max 60MB');
    }
    
    // Create upload directory
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $uniqueId = bin2hex(random_bytes(16));
    $filename = $uniqueId . '.' . $extension;
    $filePath = 'uploads/' . $filename;
    $fullPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
        jsonResponse(false, null, 'Failed to save file');
    }
    
    // Determine file type category
    $fileType = 'document';
    $thumbnailPath = null;
    $fileWidth = null;
    $fileHeight = null;
    
    if (strpos($mimeType, 'image/') === 0) {
        $fileType = 'image';
        
        // Optimize image (reduce size)
        $optimizedPath = $uploadDir . 'opt_' . $filename;
        if (optimizeImage($fullPath, $optimizedPath, 1200, 70)) {
            unlink($fullPath);
            rename($optimizedPath, $fullPath);
        }
        
        // Get image dimensions
        $imgInfo = getimagesize($fullPath);
        if ($imgInfo) {
            $fileWidth = $imgInfo[0];
            $fileHeight = $imgInfo[1];
        }
        
        // Create thumbnail
        $thumbFilename = 'thumb_' . $filename;
        $thumbFullPath = $uploadDir . $thumbFilename;
        $thumbnailPath = 'uploads/' . $thumbFilename;
        
        if (!createThumbnail($fullPath, $thumbFullPath, 300, 300)) {
            $thumbnailPath = null;
        }
        
    } elseif (strpos($mimeType, 'video/') === 0) {
        $fileType = 'video';
    }
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Create message record
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, message_text, has_file)
            VALUES (?, ?, '', 1)
        ");
        $stmt->execute([$userId, $receiverId]);
        $messageId = $pdo->lastInsertId();
        
        // Create file record
        $stmt = $pdo->prepare("
            INSERT INTO files (
                message_id, uploader_id, file_path, thumbnail_path, 
                file_type, original_name, file_size, mime_type, 
                file_width, file_height
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $messageId,
            $userId,
            $filePath,
            $thumbnailPath,
            $fileType,
            $file['name'],
            $file['size'],
            $mimeType,
            $fileWidth,
            $fileHeight
        ]);
        
        $fileId = $pdo->lastInsertId();
        
        // Commit transaction
        $pdo->commit();
        
        // Return success response
        jsonResponse(true, [
            'message_id' => $messageId,
            'file_id' => $fileId,
            'path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'file_type' => $fileType,
            'original_name' => $file['name'],
            'mime_type' => $mimeType,
            'file_size' => $file['size'],
            'width' => $fileWidth,
            'height' => $fileHeight
        ]);
        
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        
        // Clean up files
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        if (isset($thumbFullPath) && file_exists($thumbFullPath)) {
            unlink($thumbFullPath);
        }
        
        error_log("File upload error: " . $e->getMessage());
        jsonResponse(false, null, 'Database error: ' . $e->getMessage());
    }
}
?>