<?php
include 'config/db.php';

if (!$conn) {
    die("Database connection failed");
}

$currentResult = $conn->query("
    SELECT * FROM queue 
    WHERE status='serving' 
    ORDER BY id DESC 
    LIMIT 1
");

$current = null;
if ($currentResult && $currentResult->num_rows > 0) {
    $current = $currentResult->fetch_assoc();
}

$nextResult = $conn->query("
    SELECT * FROM queue 
    WHERE status='waiting' 
    ORDER BY CAST(priority_number AS UNSIGNED) ASC 
    LIMIT 6
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Queue Display</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: Inter, sans-serif;
    background: radial-gradient(circle at top, #1f7a4a, #0f3d27);
    color: white;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* main container */
.container {
    width: 98%;
    max-width: 1600px;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
}

/* left */
.card {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 30px;
    padding: 100px 60px;
    text-align: center;
    backdrop-filter: blur(14px);
    box-shadow: 0 30px 80px rgba(0,0,0,0.45);
}

.card h3 {
    letter-spacing: 6px;
    font-size: 28px;
    opacity: 0.9;
}

/* big number */
.number {
    font-size: 260px;
    font-weight: 900;
    margin: 20px 0;
    text-shadow: 0 15px 60px rgba(0,0,0,0.6);
    animation: pulseNumber 1.8s infinite ease-in-out;
    position: relative;
}

@keyframes pulseNumber {
    0% { transform: scale(1); }
    50% { transform: scale(1.07); }
    100% { transform: scale(1); }
}

/* right side */
.right {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.right h3 {
    margin-bottom: 15px;
    letter-spacing: 6px;
    font-size: 22px;
    opacity: 0.9;
}


.queue-item {
    background: rgba(255,255,255,0.10);
    border: 1px solid rgba(255,255,255,0.15);
    padding: 22px;
    border-radius: 16px;
    text-align: center;
    font-size: 28px;
    font-weight: 700;

    opacity: 0;
    transform: translateY(25px) scale(0.95);
    animation: slideUp 0.6s ease forwards;

    transition: 0.25s ease;
    position: relative;
    overflow: hidden;
}

/* stagger animation */
.queue-item:nth-child(1) { animation-delay: 0.05s; }
.queue-item:nth-child(2) { animation-delay: 0.10s; }
.queue-item:nth-child(3) { animation-delay: 0.15s; }
.queue-item:nth-child(4) { animation-delay: 0.20s; }
.queue-item:nth-child(5) { animation-delay: 0.25s; }
.queue-item:nth-child(6) { animation-delay: 0.30s; }

/* slide in effect */
@keyframes slideUp {
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* hover glow */
.queue-item:hover {
    transform: scale(1.08);
    background: rgba(255,255,255,0.22);
    box-shadow: 0 0 25px rgba(255,255,255,0.25);
}

/* next in line (first item)*/
.queue-item:first-child {
    border: 2px solid rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.18);
    animation: pulseGlow 2s infinite;
}

/* glowing pulse for next queue */
@keyframes pulseGlow {
    0% { box-shadow: 0 0 10px rgba(255,255,255,0.15); }
    50% { box-shadow: 0 0 30px rgba(255,255,255,0.45); }
    100% { box-shadow: 0 0 10px rgba(255,255,255,0.15); }
}

/* subtle floating movement */
.queue-item:first-child {
    animation: pulseGlow 2s infinite, floatMove 3s ease-in-out infinite;
}

@keyframes floatMove {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-4px); }
    100% { transform: translateY(0px); }
}

/* MOBILE */
@media (max-width: 900px) {
    .container {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .number {
        font-size: 160px;
    }

    .card {
        padding: 60px 30px;
    }
}
</style>
</head>

<body onclick="speechSynthesis.resume()">

<div class="container">

    <!-- left -->
    <div class="card">

        <h3>NOW SERVING</h3>

        <div class="number">
            <?= $current ? $current['priority_number'] : '---' ?>
        </div>

        <p>Please proceed to the receiving counter.</p>

    </div>

    <!-- right -->
    <div class="right">

        <h3>UP NEXT</h3>

        <?php if ($nextResult && $nextResult->num_rows > 0): ?>
            <?php while($row = $nextResult->fetch_assoc()): ?>
                <div class="queue-item">
                    #<?= $row['priority_number'] ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="queue-item">No waiting queue</div>
        <?php endif; ?>

    </div>

</div>

<script>
let lastNumber = "<?= $current ? $current['priority_number'] : '' ?>";

function speak(text) {
    let msg = new SpeechSynthesisUtterance(text);
    msg.lang = "en-US";
    window.speechSynthesis.speak(msg);
}

setInterval(() => {

    fetch(window.location.href)
        .then(res => res.text())
        .then(html => {

            let doc = new DOMParser().parseFromString(html, "text/html");
            let newNumber = doc.querySelector(".number").innerText.trim();

            if (newNumber !== "---" && newNumber !== lastNumber) {
                lastNumber = newNumber;
                speak("Now serving number " + newNumber);
            }

            document.body.innerHTML = doc.body.innerHTML;

        });

}, 3000);
</script>

</body>
</html>