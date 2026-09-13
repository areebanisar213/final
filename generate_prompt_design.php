<?php

header("Content-Type: application/json");

require_once "config.php";


// ======================================
// 1. ONLY POST REQUEST
// ======================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}


// ======================================
// 2. CHECK API KEY
// ======================================

if (
    empty($STABILITY_API_KEY) ||
    $STABILITY_API_KEY === "YOUR_STABILITY_API_KEY"
) {

    echo json_encode([
        "success" => false,
        "message" => "Stability AI API key is missing."
    ]);

    exit;
}


// ======================================
// 3. GET PROMPT
// ======================================

$userPrompt = trim($_POST["prompt"] ?? "");


if (empty($userPrompt)) {

    echo json_encode([
        "success" => false,
        "message" => "Please enter a design prompt."
    ]);

    exit;
}


// ======================================
// 4. ADD INTERIOR DESIGN INSTRUCTIONS
// ======================================

$finalPrompt =

    $userPrompt .

    ", professional interior design, "
    . "beautiful furniture, realistic room layout, "
    . "modern interior, realistic materials, "
    . "natural lighting, elegant decoration, "
    . "photorealistic, high quality, "
    . "professional interior photography";


// ======================================
// 5. CREATE GENERATED FOLDER
// ======================================

$generatedFolder = __DIR__ . "/generated/";


if (!is_dir($generatedFolder)) {

    mkdir(
        $generatedFolder,
        0777,
        true
    );

}


// ======================================
// 6. STABILITY AI API
// ======================================

$apiUrl =
    "https://api.stability.ai/v2beta/stable-image/generate/core";


// ======================================
// 7. CURL REQUEST
// ======================================

$curl = curl_init();


$postFields = [

    "prompt" => $finalPrompt,

    "aspect_ratio" => "1:1",

    "output_format" => "png",

    "negative_prompt" =>
        "blurry, distorted, ugly, low quality, "
        . "cartoon, drawing, deformed furniture, "
        . "messy room, unrealistic objects",

    "style_preset" => "photographic"

];


curl_setopt_array(

    $curl,

    [

        CURLOPT_URL => $apiUrl,

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_POST => true,

        CURLOPT_TIMEOUT => 180,

        CURLOPT_HTTPHEADER => [

            "Authorization: Bearer "
            . $STABILITY_API_KEY,

            "Accept: image/*"

        ],

        CURLOPT_POSTFIELDS => $postFields

    ]

);


// ======================================
// 8. SEND REQUEST
// ======================================

$response = curl_exec($curl);


$httpCode =
    curl_getinfo(
        $curl,
        CURLINFO_HTTP_CODE
    );


$curlError =
    curl_error($curl);


curl_close($curl);


// ======================================
// 9. CURL ERROR
// ======================================

if ($response === false) {

    echo json_encode([

        "success" => false,

        "message" =>
            "AI connection failed: "
            . $curlError

    ]);

    exit;
}


// ======================================
// 10. API ERROR
// ======================================

if ($httpCode !== 200) {

    $errorData =
        json_decode(
            $response,
            true
        );


    $errorMessage =
        "AI generation failed. HTTP "
        . $httpCode;


    if (
        is_array($errorData) &&
        isset($errorData["errors"])
    ) {

        $errorMessage =
            implode(
                " | ",
                $errorData["errors"]
            );

    }


    echo json_encode([

        "success" => false,

        "message" => $errorMessage,

        "http_code" => $httpCode

    ]);

    exit;
}


// ======================================
// 11. CREATE UNIQUE FILE NAME
// ======================================

$uniqueName =
    bin2hex(
        random_bytes(16)
    );


$generatedFileName =
    "design_"
    . $uniqueName
    . ".png";


$generatedPath =
    $generatedFolder
    . $generatedFileName;


// ======================================
// 12. SAVE AI IMAGE
// ======================================

if (
    file_put_contents(
        $generatedPath,
        $response
    ) === false
) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Could not save generated design."

    ]);

    exit;
}


// ======================================
// 13. SAVE TO DATABASE
// ======================================

try {

    $stmt = $pdo->prepare("

        INSERT INTO designs
        (
            original_image,
            generated_image,
            prompt
        )

        VALUES
        (
            NULL,
            :generated,
            :prompt
        )

    ");


    $stmt->execute([

        ":generated" =>
            "generated/"
            . $generatedFileName,

        ":prompt" =>
            $userPrompt

    ]);


    $designId =
        $pdo->lastInsertId();


} catch (PDOException $e) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Design generated but database save failed.",

        "database_error" =>
            $e->getMessage()

    ]);

    exit;
}


// ======================================
// 14. SUCCESS RESPONSE
// ======================================

echo json_encode([

    "success" => true,

    "message" =>
        "AI design generated successfully.",

    "design_id" =>
        $designId,

    "prompt" =>
        $userPrompt,

    "generated_image" =>
        "generated/"
        . $generatedFileName

]);


exit;

?>