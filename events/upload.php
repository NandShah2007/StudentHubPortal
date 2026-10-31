<?php
// Poster upload with validation.
// Returns [saved_file_name_or_null, error_message_or_null]
function upload_poster($file)
{
    // No file chosen: the poster is optional.
    if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return [null, 'File must be 2 MB or less.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload failed. Please try again.'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return [null, 'Upload failed.'];
    }

    // 1. Size check (2 MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return [null, 'File must be 2 MB or less.'];
    }

    // 2. Check the REAL file type (not just the name the user typed)
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return [null, 'Only JPG, PNG and WEBP images are allowed.'];
    }

    // 3. It must really be an image
    if (@getimagesize($file['tmp_name']) === false) {
        return [null, 'This file is not a valid image.'];
    }

    // 4. Random file name + extension chosen by US (never the user's file name)
    $newName = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $folder = __DIR__ . '/uploads/';
    if (!is_dir($folder)) {
        return [null, 'Upload folder is missing.'];
    }
    if (!move_uploaded_file($file['tmp_name'], $folder . $newName)) {
        return [null, 'Could not save the file. Check folder permissions.'];
    }
    return [$newName, null];
}

/** Delete an old poster file safely (file name must look like our generated names). */
function delete_poster($name)
{
    if ($name && preg_match('/^[a-f0-9]{24}\.(jpg|png|webp)$/', $name)) {
        $path = __DIR__ . '/uploads/' . $name;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
