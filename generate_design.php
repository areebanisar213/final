<?php

header("Content-Type: application/json");

require_once "config.php";


// =========================================
// 1. ONLY ACCEPT POST REQUESTS
// =========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}


// =========================================
// 2. CHECK IMAGE WAS UPLOADED
// =========================================

if (!isset($_FILES["room_image"]) || $_FILES["room_image"]["error"] !== UPLOAD_ERR_OK) {

    echo json_encode([
        "success" => false,
        "message" => "Please upload a room image."
    ]);

    exit;
}


$image = $_FILES["room_image"];


// =========================================
// 3. CHECK FILE SIZE (max 10 MB)
// =========================================

if ($image["size"] > 10 * 1024 * 1024) {

    echo json_encode([
        "success" => false,
        "message" => "Image must be less than 10 MB."
    ]);

    exit;
}


// =========================================
// 4. CHECK IMAGE TYPE
// =========================================

$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($image["tmp_name"]);

$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp"
];

if (!isset($allowedTypes[$mimeType])) {

    echo json_encode([
        "success" => false,
        "message" => "Only JPG, PNG and WEBP images are allowed."
    ]);

    exit;
}


// =========================================
// 5. GET CUSTOM PROMPT FROM USER
// =========================================

$userPrompt = trim($_POST["prompt"] ?? "");

// Base suffix appended to every prompt for consistent quality
$qualitySuffix = ", professional interior design photograph, ultra realistic, clean neat room, "
               . "sharp details, beautiful composition, high quality, 8K resolution, "
               . "architectural digest style, photorealistic";

if (!empty($userPrompt)) {
    $prompt = $userPrompt . $qualitySuffix;
} else {
    $prompt = "Beautiful modern interior design with elegant furniture, perfect lighting, neutral tones" . $qualitySuffix;
}


// =========================================
// 6. CREATE FOLDERS IF THEY DON'T EXIST
// =========================================

$uploadFolder    = __DIR__ . "/uploads/";
$generatedFolder = __DIR__ . "/generated/";

if (!is_dir($uploadFolder)) {
    mkdir($uploadFolder, 0777, true);
}

if (!is_dir($generatedFolder)) {
    mkdir($generatedFolder, 0777, true);
}


// =========================================
// 7. SAVE UPLOADED IMAGE WITH UNIQUE NAME
// =========================================

$extension        = $allowedTypes[$mimeType];
$uniqueName       = bin2hex(random_bytes(16));
$originalFileName = $uniqueName . "." . $extension;
$originalPath     = $uploadFolder . $originalFileName;

if (!move_uploaded_file($image["tmp_name"], $originalPath)) {

    echo json_encode([
        "success" => false,
        "message" => "Could not save uploaded image."
    ]);

    exit;
}


// =========================================
// 8. CHECK API KEY IS SET
// =========================================

if (empty($STABILITY_API_KEY) || $STABILITY_API_KEY === "sk-YOUR_STABILITY_KEY_HERE") {

    echo json_encode([
        "success" => false,
        "message" => "Stability AI API key is not set. Please add it in config.php. Get a free key at: https://platform.stability.ai/account/keys"
    ]);

    exit;
}


// =========================================
// 9. RESIZE IMAGE TO VALID SDXL DIMENSIONS
// =========================================
// Stability AI SDXL only accepts these exact dimension pairs:
// 1024x1024, 1152x896, 1216x832, 1344x768, 1536x640,
// 640x1536,  768x1344, 832x1216, 896x1152

$imgInfo = getimagesize($originalPath);
$origW   = $imgInfo[0];
$origH   = $imgInfo[1];

// All valid SDXL dimension pairs [width, height]
$sdxlDimensions = [
    [1024, 1024],
    [1152, 896],
    [1216, 832],
    [1344, 768],
    [1536, 640],
    [640,  1536],
    [768,  1344],
    [832,  1216],
    [896,  1152],
];

// Pick the dimension pair whose aspect ratio is closest to the original image
$origRatio  = $origW / $origH;
$bestW      = 1024;
$bestH      = 1024;
$bestDiff   = PHP_INT_MAX;

foreach ($sdxlDimensions as [$w, $h]) {
    $diff = abs(($w / $h) - $origRatio);
    if ($diff < $bestDiff) {
        $bestDiff = $diff;
        $bestW    = $w;
        $bestH    = $h;
    }
}

$newW = $bestW;
$newH = $bestH;

// Load, resize and save image
switch ($mimeType) {
    case "image/jpeg": $srcImg = imagecreatefromjpeg($originalPath); break;
    case "image/png":  $srcImg = imagecreatefrompng($originalPath);  break;
    case "image/webp": $srcImg = imagecreatefromwebp($originalPath); break;
}

$dstImg = imagecreatetruecolor($newW, $newH);

// Fill white background (for transparency)
imagefill($dstImg, 0, 0, imagecolorallocate($dstImg, 255, 255, 255));
imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

// Save resized image as PNG for the API
$resizedPath = $uploadFolder . "resized_" . $uniqueName . ".png";
imagepng($dstImg, $resizedPath);
imagedestroy($srcImg);
imagedestroy($dstImg);


// =========================================
// 10. CALL STABILITY AI IMAGE-TO-IMAGE API
// =========================================
// Stability AI "img2img" takes your room photo + text prompt
// and generates a redesigned version.
// Free credits given on signup at https://platform.stability.ai

$ch = curl_init();

curl_setopt_array($ch, [

    CURLOPT_URL            => "https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/image-to-image",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 180,

    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $STABILITY_API_KEY,
        "Accept: application/json"
    ],

    CURLOPT_POSTFIELDS => [
        "init_image"              => new CURLFile($resizedPath, "image/png", "room.png"),
        "init_image_mode"         => "IMAGE_STRENGTH",
        "image_strength"          => 0.30,          // Lower = AI applies more redesign
        "text_prompts[0][text]"   => $prompt,
        "text_prompts[0][weight]" => 1,
        "text_prompts[1][text]"   => "blurry, low quality, cartoon, painting, drawing, messy, cluttered, ugly, distorted, deformed",
        "text_prompts[1][weight]" => -1,
        "cfg_scale"               => 8,             // Higher = follows prompt more strictly
        "samples"                 => 1,
        "steps"                   => 40             // More steps = cleaner, sharper result
    ]

]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

// Clean up resized temp file
if (file_exists($resizedPath)) {
    unlink($resizedPath);
}


// =========================================
// 11. HANDLE CURL ERROR
// =========================================

if ($response === false) {

    echo json_encode([
        "success" => false,
        "message" => "AI request failed: " . $curlError
    ]);

    exit;
}


// =========================================
// 12. PARSE THE STABILITY AI RESPONSE
// =========================================

$jsonResponse = json_decode($response, true);

if ($httpCode !== 200 || !isset($jsonResponse["artifacts"][0]["base64"])) {

    $errorMsg = "AI error (HTTP $httpCode).";

    if (isset($jsonResponse["message"])) {
        $errorMsg = $jsonResponse["message"];
    } elseif (isset($jsonResponse["name"])) {
        $errorMsg = $jsonResponse["name"] . ": " . ($jsonResponse["message"] ?? "");
    }

    echo json_encode([
        "success" => false,
        "message" => $errorMsg
    ]);

    exit;
}


// =========================================
// 13. SAVE THE GENERATED IMAGE TO DISK
// =========================================

$generatedFileName = "design_" . $uniqueName . ".png";
$generatedPath     = $generatedFolder . $generatedFileName;

$imageData = base64_decode($jsonResponse["artifacts"][0]["base64"]);

if (file_put_contents($generatedPath, $imageData) === false) {

    echo json_encode([
        "success" => false,
        "message" => "Could not save the generated image."
    ]);

    exit;
}


// =========================================
// 14. SAVE RECORD TO DATABASE
// =========================================

try {

    $stmt = $pdo->prepare("
        INSERT INTO designs (original_image, generated_image, prompt)
        VALUES (:original, :generated, :prompt)
    ");

    $stmt->execute([
        ":original"  => "uploads/" . $originalFileName,
        ":generated" => "generated/" . $generatedFileName,
        ":prompt"    => $userPrompt
    ]);

    $designId = $pdo->lastInsertId();

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Design generated but database save failed: " . $e->getMessage()
    ]);

    exit;
}


// =========================================
// 15. RETURN SUCCESS RESPONSE
// =========================================

echo json_encode([
    "success"         => true,
    "message"         => "AI design generated successfully!",
    "design_id"       => $designId,
    "original_image"  => "uploads/" . $originalFileName,
    "generated_image" => "generated/" . $generatedFileName
]);

exit;

?>