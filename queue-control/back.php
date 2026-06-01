<?php
include '../config/db.php';

//get current serving
$current = $conn->query("
    SELECT * FROM queue 
    WHERE status='serving' 
    ORDER BY id DESC 
    LIMIT 1
")->fetch_assoc();

if (!$current) {
    echo "No current serving";
    exit;
}

$currentId = $current['id'];

//find the previous queue
$prev = $conn->query("
    SELECT * FROM queue
    WHERE id < $currentId
    ORDER BY id DESC
    LIMIT 1
")->fetch_assoc();

if ($prev) {

    //set current as waiting
    $conn->query("
        UPDATE queue 
        SET status='waiting'
        WHERE id=$currentId
    ");

   //set previous as serving
    $conn->query("
        UPDATE queue 
        SET status='serving'
        WHERE id={$prev['id']}
    ");

    echo "Back to: " . $prev['priority_number'];

} else {
    echo "No previous queue";
}
?>