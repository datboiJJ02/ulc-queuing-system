<?php
include '../config/db.php';

// current serving
$current = $conn->query("
    SELECT * FROM queue 
    WHERE status='serving' 
    ORDER BY id DESC 
    LIMIT 1
")->fetch_assoc();

// counts
$waiting = $conn->query("SELECT COUNT(*) as total FROM queue WHERE status='waiting'")->fetch_assoc();
$done = $conn->query("SELECT COUNT(*) as total FROM queue WHERE status='done'")->fetch_assoc();
$total = $conn->query("SELECT COUNT(*) as total FROM queue")->fetch_assoc();

// queue list
$allQueue = $conn->query("
    SELECT * FROM queue 
    ORDER BY CAST(priority_number AS UNSIGNED) ASC
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
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    background:#f4f6f9;
    color:#333;
}

.topbar{
    background:#17321A;
    color:#fff;
    padding:15px 25px;
}

.topbar h1{
    font-size:24px;
}

.container{
    max-width:1200px;
    margin:auto;
    padding:20px;
}

/* STATS */
.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:15px;
    margin-bottom:20px;
}

.card{
    background:#fff;
    border:1px solid #ddd;
    border-radius:8px;
    padding:20px;
    text-align:center;
}

.card h2{
    font-size:32px;
    margin-bottom:5px;
    color:#2563eb;
}

/* MAIN */
.main{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
}

/* NOW SERVING */
.serving{
    background:#fff;
    border:1px solid #ddd;
    border-radius:8px;
    padding:60px;
    text-align:center;
}

.serving small{
    display:block;
    font-size:18px;
    margin-bottom:10px;
    color:#666;
}

.serving h1{
    font-size:120px;
    color:#16a34a;
    font-weight:800;
}

/* ACTIONS */
.actions{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.btn{
    padding:14px;
    border:none;
    border-radius:6px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}

.next{
    background:#2563eb;
    color:white;
}

.skip{
    background:#dc2626;
    color:white;
}

.back{
    background:#f59e0b;
    color:white;
}

/* QUEUE LIST */
.queue-panel{
    background:#fff;
    border:1px solid #ddd;
    border-radius:8px;
    padding:15px;
}

.queue-panel h4{
    margin-bottom:10px;
    color:#555;
}

.queue-grid{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    max-height:300px;
    overflow-y:auto;
}

.q-item{
    min-width:60px;
    text-align:center;
    padding:10px;
    border-radius:5px;
    font-weight:600;
    border:1px solid #ddd;
}

/* STATUS COLORS */
.waiting{
    background:#f1f5f9;
    color:#333;
}

.done{
    background:#dcfce7;
    color:#166534;
    border-color:#22c55e;
}

.serving-status{
    background:#dbeafe;
    color:#1d4ed8;
    border-color:#3b82f6;
}

.skipped{
    background:#fee2e2;
    color:#b91c1c;
    border-color:#ef4444;
}

/* RESPONSIVE */
@media(max-width:900px){

    .stats{
        grid-template-columns:repeat(2,1fr);
    }

    .main{
        grid-template-columns:1fr;
    }

    .serving h1{
        font-size:80px;
    }
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

    <div class="main">

        <div class="serving">
            <small>NOW SERVING</small>
            <h1><?= $current ? $current['priority_number'] : '---' ?></h1>
        </div>

        <div class="actions">

            <button class="btn next" onclick="nextQueue()">NEXT</button>
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