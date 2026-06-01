<?php
include 'config/db.php';

/* =========================
   CHECK DB CONNECTION
========================= */
if (!$conn) {
    die("Database connection failed");
}

/* =========================
   GET CURRENT SERVING
========================= */
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

/* =========================
   GET NEXT QUEUE
========================= */
$nextResult = $conn->query("
    SELECT * FROM queue 
    WHERE status='waiting' 
    ORDER BY priority_number ASC 
    LIMIT 6
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Queue Display</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

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

.container {
    width: 95%;
    max-width: 1300px;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

/* LEFT */
.card {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 25px;
    padding: 70px 40px;
    text-align: center;
    backdrop-filter: blur(14px);
    box-shadow: 0 25px 60px rgba(0,0,0,0.35);
    position: relative;
    overflow: hidden;
}

.card h3 {
    letter-spacing: 4px;
    font-size: 18px;
    opacity: 0.85;
}

/* =========================
   NOW SERVING NUMBER (ENHANCED)
========================= */
.number {
    font-size: 180px;
    font-weight: 800;
    margin: 10px 0;
    text-shadow: 0 10px 40px rgba(0,0,0,0.4);

    /* NEW EFFECTS */
    animation: pulseNumber 1.8s infinite ease-in-out;
    position: relative;
}

/* glow layer */
.number::before {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(circle, rgba(255,255,255,0.25), transparent 60%);
    filter: blur(20px);
    z-index: -1;
    animation: glowMove 3s infinite linear;
}

/* breathing animation */
@keyframes pulseNumber {
    0% { transform: scale(1); text-shadow: 0 10px 40px rgba(0,0,0,0.4); }
    50% { transform: scale(1.05); text-shadow: 0 15px 60px rgba(0,0,0,0.6); }
    100% { transform: scale(1); text-shadow: 0 10px 40px rgba(0,0,0,0.4); }
}

/* moving glow */
@keyframes glowMove {
    0% { transform: translateY(-10px); opacity: 0.4; }
    50% { transform: translateY(10px); opacity: 0.7; }
    100% { transform: translateY(-10px); opacity: 0.4; }
}

.card p {
    opacity: 0.85;
    font-size: 16px;
}

/* RIGHT */
.right {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.right h3 {
    margin-bottom: 10px;
    letter-spacing: 4px;
    font-size: 16px;
    opacity: 0.9;
}

/* UP NEXT */
.queue-item {
    background: rgba(255,255,255,0.10);
    border: 1px solid rgba(255,255,255,0.15);
    padding: 16px;
    border-radius: 14px;
    text-align: center;
    font-size: 22px;
    font-weight: 600;
    transition: 0.25s ease;
    animation: fadeUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(10px);
}

.queue-item:nth-child(1) { animation-delay: 0.05s; }
.queue-item:nth-child(2) { animation-delay: 0.10s; }
.queue-item:nth-child(3) { animation-delay: 0.15s; }
.queue-item:nth-child(4) { animation-delay: 0.20s; }
.queue-item:nth-child(5) { animation-delay: 0.25s; }
.queue-item:nth-child(6) { animation-delay: 0.30s; }

.queue-item:hover {
    transform: scale(1.06);
    background: rgba(255,255,255,0.18);
    box-shadow: 0 0 25px rgba(255,255,255,0.25);
}

.queue-item:first-child {
    border: 2px solid rgba(255,255,255,0.4);
    background: rgba(255,255,255,0.16);
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 900px) {
    .container {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .number {
        font-size: 120px;
    }
}
</style>
</head>

<body onclick="speechSynthesis.resume()">

<div class="container">

    <!-- LEFT -->
    <div class="card">

        <h3>NOW SERVING</h3>

        <div class="number">
            <?= $current ? $current['priority_number'] : '---' ?>
        </div>

        <p>Please proceed to the receiving counter.</p>

    </div>

    <!-- RIGHT -->
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