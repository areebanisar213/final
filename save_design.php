<?php

header("Content-Type: application/json");

require_once "config.php";


// =========================================
// SAVE DESIGN — marks a design as "saved"
// =========================================
// Called when user clicks the Save button.
// We update the is_saved column to 1 in DB.
// =========================================


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);

    exit;
}


// Get the design ID sent from the frontend
$designId = intval($_POST["design_id"] ?? 0);


if ($designId <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Design ID is missing."
    ]);

    exit;
}


// =========================================
// UPDATE DATABASE — set is_saved = 1
// =========================================

try {

    $stmt = $pdo->prepare("
        UPDATE designs
        SET is_saved = 1
        WHERE id = :id
    ");

    $stmt->execute([":id" => $designId]);

    echo json_encode([
        "success" => true,
        "message" => "Design saved successfully!"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Could not save design: " . $e->getMessage()
    ]);

}

exit;

?>