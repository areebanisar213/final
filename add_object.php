<?php

require_once "config.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);

    exit;
}

$imagePath = $_POST["image_path"] ?? "";
$object = $_POST["object"] ?? "";

if (empty($imagePath) || empty($object)) {

    echo json_encode([
        "success" => false,
        "message" => "Image path and object are required"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Check image
|--------------------------------------------------------------------------
*/

if (!file_exists($imagePath)) {

    echo json_encode([
        "success" => false,
        "message" => "Image not found"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Prompt for AI
|--------------------------------------------------------------------------
*/

$prompt = "Edit this interior room design.
Keep the existing room architecture and furniture.
Add a {$object} to the room.
Place it naturally in a suitable location.
Make it realistic and match the existing interior style.
Do not remove the existing furniture.";

/*
|--------------------------------------------------------------------------
| OpenAI API
|--------------------------------------------------------------------------
*/

$apiUrl = "https://api.openai.com/v1/images/edits";

$cfile = new CURLFile(
    $imagePath,
    mime_content_type($imagePath),
    basename($imagePath)
);

$postData = [
    "model" => "gpt-image-2",
    "image[]" => $cfile,
    "prompt" => $prompt,
    "size" => "1024x1024"
];

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $OPENAI_API_KEY
]);

$response = curl_exec($ch);

if ($response === false) {

    echo json_encode([
        "success" => false,
        "message" => curl_error($ch)
    ]);

    curl_close($ch);
    exit;
}

curl_close($ch);

/*
|--------------------------------------------------------------------------
| Decode response
|--------------------------------------------------------------------------
*/

$result = json_decode($response, true);

if (isset($result["error"])) {

    echo json_encode([
        "success" => false,
        "message" => $result["error"]["message"]
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Save generated image
|--------------------------------------------------------------------------
*/

if (!isset($result["data"][0]["b64_json"])) {

    echo json_encode([
        "success" => false,
        "message" => "AI did not return an image"
    ]);

    exit;
}

$imageData = base64_decode(
    $result["data"][0]["b64_json"]
);

$fileName = "object_" . time() . ".png";

$outputPath = "generated/" . $fileName;

file_put_contents(
    $outputPath,
    $imageData
);

/*
|--------------------------------------------------------------------------
| Return result
|--------------------------------------------------------------------------
*/

echo json_encode([
    "success" => true,
    "image" => $outputPath
]);

?>