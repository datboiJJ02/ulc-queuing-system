<?php
include '../config/db.php';

// mark current as skipped/done
$current = $conn->query("SELECT * FROM queue WHERE status='serving' LIMIT 1");
$c = $current->fetch_assoc();

if ($c) {
    $conn->query("UPDATE queue SET status='done' WHERE id=".$c['id']);
}

// move next
$next = $conn->query("
    SELECT * FROM queue 
    WHERE status='waiting' 
    ORDER BY id ASC 
    LIMIT 1
");
$n = $next->fetch_assoc();

if ($n) {
    $conn->query("UPDATE queue SET status='serving' WHERE id=".$n['id']);
    echo "Skipped. Now Serving: ".$n['priority_number'];
}
?>