<?php
include '../config/db.php';

/* =========================
   CURRENT SERVING
========================= */
$current = $conn->query("
    SELECT * FROM queue 
    WHERE status='serving' 
    ORDER BY id DESC 
    LIMIT 1
")->fetch_assoc();

/* =========================
   COUNTS
========================= */
$waiting = $conn->query("SELECT COUNT(*) as total FROM queue WHERE status='waiting'")->fetch_assoc();
$done = $conn->query("SELECT COUNT(*) as total FROM queue WHERE status='done'")->fetch_assoc();
$total = $conn->query("SELECT COUNT(*) as total FROM queue")->fetch_assoc();

/* =========================
   QUEUE LIST (ALL NUMBERS)
========================= */
$allQueue = $conn->query("
    SELECT * FROM queue 
    ORDER BY priority_number ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Queue Control Panel</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', sans-serif;
}

body {
    background: radial-gradient(circle at top, #0f172a, #020617);
    color: #fff;
}

/* TOPBAR */
.topbar {
    background: rgba(255,255,255,0.03);
    backdrop-filter: blur(12px);
    padding: 18px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

/* STATS */
.container {
    max-width: 1200px;
    margin: auto;
    padding: 30px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 22px;
    border-radius: 18px;
}

/* MAIN */
.main {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 25px;
}

/* SERVING */
.serving {
    background: linear-gradient(135deg, #16a34a, #14532d);
    border-radius: 28px;
    padding: 70px 40px;
    text-align: center;
}

.serving h1 {
    font-size: 100px;
    font-weight: 900;
}

/* ACTIONS */
.actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.btn {
    padding: 16px;
    border: none;
    border-radius: 14px;
    font-weight: 700;
    cursor: pointer;
}

/* COLORS */
.next { background: #2563eb; color: white; }
.skip { background: #dc2626; color: white; }
.back { background: #f59e0b; color: black; }

/* =========================
   QUEUE LIST (NEW)
========================= */
.queue-panel {
    margin-top: 20px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 12px;
}

.queue-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    max-height: 260px;
    overflow-y: auto;
    padding: 10px;
}

/* DEFAULT */
.q-item {
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.06);
    transition: 0.2s;
}

.q-item:hover {
    transform: scale(1.05);
}

/* STATUS COLORS */
.done {
    background: rgba(34,197,94,0.25);
    border-color: #22c55e;
    color: #22c55e;
}

.waiting {
    background: rgba(255,255,255,0.06);
    color: #fff;
}

.serving-status {
    background: rgba(59,130,246,0.25);
    border-color: #3b82f6;
    color: #3b82f6;
}

.skipped {
    background: rgba(239,68,68,0.25);
    border-color: #ef4444;
    color: #ef4444;
}
</style>
</head>

<body>

<div class="topbar">
    <h1>QUEUE CONTROL PANEL</h1>
</div>

<div class="container">

    <!-- STATS -->
    <div class="stats">

        <div class="card">
            <h2><?= $total['total'] ?></h2>
            <p>Total Queue</p>
        </div>

        <div class="card">
            <h2><?= $waiting['total'] ?></h2>
            <p>Waiting</p>
        </div>

        <div class="card">
            <h2><?= $done['total'] ?></h2>
            <p>Completed</p>
        </div>

        <div class="card">
            <h2><?= $current ? $current['priority_number'] : '---' ?></h2>
            <p>Now Serving</p>
        </div>

    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="serving">
            <small>NOW SERVING</small>
            <h1><?= $current ? $current['priority_number'] : '---' ?></h1>
        </div>

        <div class="actions">

            <button class="btn next" onclick="nextQueue()">NEXT</button>
            <button class="btn skip">SKIP</button>
            <button class="btn back" onclick="backQueue()">BACK</button>

            <!-- QUEUE LIST -->
            <div class="queue-panel">
                <h4 style="margin-bottom:10px; opacity:0.7;">All Queue Numbers</h4>

                <div class="queue-grid">

                    <?php while($row = $allQueue->fetch_assoc()): ?>

                        <?php
                            $class = "waiting";
                            if ($row['status'] == 'done') $class = "done";
                            if ($row['status'] == 'serving') $class = "serving-status";
                            if ($row['status'] == 'skipped') $class = "skipped";
                        ?>

                        <div class="q-item <?= $class ?>">
                            <?= $row['priority_number'] ?>
                        </div>

                    <?php endwhile; ?>

                </div>
            </div>

        </div>

    </div>

</div>

<script>
function nextQueue() {
    Swal.fire({
        title: 'Proceed NEXT?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
    }).then((r) => {
        if (r.isConfirmed) {
            fetch('next.php').then(() => location.reload());
        }
    });
}

function backQueue() {
    Swal.fire({
        title: 'Go BACK?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
    }).then((r) => {
        if (r.isConfirmed) {
            fetch('back.php').then(() => location.reload());
        }
    });
}
</script>

</body>
</html>