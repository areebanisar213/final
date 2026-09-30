<?php 
 
include "dp.php"; 
 
$sql = "SELECT * FROM rooms"; 
$result = mysqli_query($conn, $sql); 
 
?> 
 
<!DOCTYPE html> 
<html lang="en"> 
 
<head> 
 
    <meta charset="UTF-8"> 
 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
 
    <title>Select Room Type</title> 
 
    <style> 
 
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f7f7fb; 
            margin: 0; 
            padding: 40px; 
        } 
 
        .container { 
            width: 90%; 
            max-width: 1100px; 
            margin: auto; 
        } 
 
        h1 { 
            text-align: center; 
            color: #222; 
        } 
 
        h1 span { 
            color: #6655e8; 
        } 
 
        .subtitle { 
            text-align: center; 
            color: #777; 
            margin-bottom: 40px; 
        } 
 
        .rooms { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 25px; 
        } 
 
        .room { 
            background: white; 
            padding: 20px; 
            border-radius: 15px; 
            box-shadow: 0 3px 10px rgba(0,0,0,0.1); 
        } 
 
        .room h2 { 
            margin-bottom: 10px; 
        } 
 
        .room p { 
            color: #777; 
            min-height: 45px; 
        } 
 
        .btn { 
            display: block; 
            text-align: center; 
            text-decoration: none; 
            background: #6655e8; 
            color: white; 
            padding: 12px; 
            border-radius: 8px; 
            margin-top: 15px; 
        } 
 
        .btn:hover { 
            background: #5142c5; 
        } 
 
        @media(max-width: 800px) { 
            .rooms { 
                grid-template-columns: 1fr; 
            } 
        } 
 
    </style> 
 
</head> 
 
<body> 
 
<div class="container"> 
 
    <h1>Select <span>Room Type</span></h1> 
 
    <p class="subtitle"> 
        Choose the room you want to design 
    </p> 
 
 
    <div class="rooms"> 
 
        <?php 
 
        while ($row = mysqli_fetch_assoc($result)) { 
 
        ?> 
 
            <div class="room"> 
 
                <h2> 
                    <?php echo $row['room_name']; ?> 
                </h2> 
 
                <p> 
                    <?php echo $row['description']; ?> 
                </p> 
 
                <a 
                    href="view_design.php?id=<?php echo $row['id']; ?>" 
                    class="btn" 
                > 
                    Select Room 
                </a> 
 
            </div> 
 
        <?php 
 
        } 
 
        ?> 
 
    </div> 
 
</div> 
 
</body> 
 
</html>