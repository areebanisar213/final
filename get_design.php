<?php

header("Content-Type: application/json");

require_once "config.php";


// =========================================
// GET DESIGN — fetch a design from DB
// =========================================
// Called when we need to retrieve a design
// record from the database by its ID.
// =========================================


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);

    exit;
}


// Get the design ID
$designId = intval($_POST["design_id"] ?? 0);


if ($designId <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Design ID is missing."
    ]);

    exit;
}


// =========================================
// QUERY DATABASE — get the design record
// =========================================

try {

    $stmt = $pdo->prepare("
        SELECT id, original_image, generated_image, prompt, is_saved, created_at
        FROM designs
        WHERE id = :id
    ");

    $stmt->execute([":id" => $designId]);

<?php

$GEMINI_API_KEY = "YOUR_GEMINI_API_KEY_HERE";

?>
    $design = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$design) {

        echo json_encode([
            "success" => false,
            "message" => "Design not found."
        ]);

        exit;
    }


    echo json_encode([
        "success" => true,
        "design"  => $design
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);

}

exit;

?>