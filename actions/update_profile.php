<?php
/*
 * actions/update_profile.php
 * ------------------------------------------------------------
 * Saves edit_profile.php: phone number + profile photo.
 * Receives: phone, profile_photo (file, optional), delete_photo (checkbox)
 *
 * Upload security:
 *  - max 2MB
 *  - must really be a JPG or PNG image (checked from the file's
 *    contents with getimagesize, not from its name)
 *  - saved with a new random name (the user can't choose the name
 *    or overwrite other files)
 *  - images/profiles/.htaccess blocks PHP from running in that folder
 * ------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/auth.php';
requirePost();
checkCsrf();
$user = requireLogin();

function profileFailed($message)
{
    flash('error', $message);
    redirect('edit_profile.php');
}

// ---- Phone ----
$phone = trim(input('phone'));
if ($phone !== '' && !preg_match('/^[0-9+\- ]{7,20}$/', $phone)) {
    profileFailed('Phone number can only contain numbers, spaces, + and - (7 to 20 characters).');
}

// ---- Photo ----
$oldPhoto = $user['profile_photo'];
$newPhoto = $oldPhoto;
$file     = $_FILES['profile_photo'] ?? null;

if (input('delete_photo') === '1') {
    $newPhoto = null;
}

if (is_array($file) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
    if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $file['size'] > MAX_PHOTO_BYTES) {
        profileFailed('That photo is too big. The maximum is 2MB.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        profileFailed('The photo could not be uploaded. Please try again.');
    }

    $imageInfo  = @getimagesize($file['tmp_name']);   // false if it isn't a real image
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $mime       = $imageInfo['mime'] ?? '';
    if (!isset($extensions[$mime])) {
        profileFailed('Please upload a JPG or PNG image.');
    }

    if (!is_dir(PROFILE_PHOTO_DIR)) {
        mkdir(PROFILE_PHOTO_DIR, 0755, true);
    }
    $fileName = 'user' . $user['user_id'] . '_' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], PROFILE_PHOTO_DIR . $fileName)) {
        profileFailed('The photo could not be saved. Please try again.');
    }
    $newPhoto = $fileName;
}

$pdo->prepare('UPDATE users SET phone = ?, profile_photo = ? WHERE user_id = ?')
    ->execute([$phone, $newPhoto, $user['user_id']]);

// Remove the old photo file if it was replaced or deleted
if ($oldPhoto && $oldPhoto !== $newPhoto) {
    $oldPath = PROFILE_PHOTO_DIR . basename($oldPhoto);   // basename = can't escape the folder
    if (is_file($oldPath)) {
        unlink($oldPath);
    }
}

flash('success', 'Profile updated.');
redirect('profile.php');
