<?php
// daily_salah_log.php

date_default_timezone_set('Asia/Dhaka'); // change if needed

$prayers = ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];

$types = [
    'fard'   => 'Fard',
    'sunnah' => 'Sunnah',
    'nafl'   => 'Nafl'
];

// EXPECTED rakats = real planned rakats (used for "missed" & chart)
$expected = [
    'Fajr'    => ['fard' => 2, 'sunnah' => 2, 'nafl' => 0],
    'Dhuhr'   => ['fard' => 4, 'sunnah' => 4, 'nafl' => 0],
    'Asr'     => ['fard' => 4, 'sunnah' => 0, 'nafl' => 0],
    'Maghrib' => ['fard' => 3, 'sunnah' => 3, 'nafl' => 0],
    'Isha'    => ['fard' => 4, 'sunnah' => 4, 'nafl' => 0],
];

$selected_date = isset($_POST['log_date']) && $_POST['log_date'] !== ''
    ? $_POST['log_date']
    : date('Y-m-d');

$values         = [];
$statuses       = [];
$total_prayed   = 0;
$total_expected = 0;
$total_missed   = 0;
$missed_details = [];

// read submitted values or use DEFAULT 0 in the form
foreach ($prayers as $p) {
    $values[$p] = [];
    foreach ($types as $key => $label) {
        $fieldName = strtolower($p) . '_' . $key;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $v = isset($_POST[$fieldName]) ? (int)$_POST[$fieldName] : 0;
        } else {
            // first load -> show 0 in all selects
            $v = 0;
        }

        $values[$p][$key] = $v;
        $total_prayed += $v;

        // use EXPECTED rakats to compute missed + chart
        $planned = $expected[$p][$key];
        $total_expected += $planned;

        if ($planned > $v) {
            $missed = $planned - $v;
            $total_missed += $missed;
            if ($missed > 0) {
                $missed_details[] = $p . ' ' . $label;
            }
        }
    }

    $statusField = strtolower($p) . '_status';
    $statuses[$p] = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[$statusField]))
        ? $_POST[$statusField]
        : 'On time';
}

// donut angle
if ($total_expected > 0) {
    $prayed_fraction = max(0, min(1, $total_prayed / $total_expected));
    $donut_deg = round(360 * $prayed_fraction);
} else {
    // no planned rakats set -> keep donut grey
    $donut_deg = 0;
}

// summary text
if ($total_expected == 0 && $total_prayed == 0) {
    $summary_text = "You haven’t set any planned rakats for today yet.";
} elseif ($total_missed === 0) {
    $summary_text = "Great job! You didn't miss any planned rakats today.";
} else {
    $unique_missed = array_unique($missed_details);
    if (count($unique_missed) === 1) {
        $summary_text = "You missed only " . $unique_missed[0] . " today.";
    } else {
        $list = implode(', ', array_slice($unique_missed, 0, 3));
        if (count($unique_missed) > 3) {
            $list .= ', and others';
        }
        $summary_text = "You missed: " . $list . ".";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Salah Log</title>
    <link rel="stylesheet" href="daily_log.css">
</head>
<body>
<div class="page-wrapper">
    <form class="main-card" method="post">
        <div class="header">
            <h1>Daily Salah Log</h1>
            <div class="date-picker">
                <label for="log_date">Date:</label>
                <input type="date" id="log_date" name="log_date"
                       value="<?php echo htmlspecialchars($selected_date); ?>">
            </div>
        </div>

        <div class="content-grid">
            <!-- Left: Log form -->
            <div class="card">
                <h2>Log Today's Salah</h2>
                <p class="log-heading">
                    Log for <?php echo date('d/m/Y', strtotime($selected_date)); ?>
                </p>

                <div class="table-wrapper">
                    <table class="salah-table">
                        <thead>
                        <tr>
                            <th>Prayer</th>
                            <th>Fard</th>
                            <th>Sunnah</th>
                            <th>Nafl</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($prayers as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p); ?></td>

                                <?php foreach ($types as $key => $label): ?>
                                    <?php
                                    $fieldName = strtolower($p) . '_' . $key;
                                    $current   = $values[$p][$key];

                                    // options per type
                                    if ($key === 'nafl') {
                                        $options = [0, 2, 4, 6, 8, 12, 14, 16, 20];
                                    } else { // fard & sunnah
                                        $options = [0, 2, 3, 4];
                                    }
                                    ?>
                                    <td>
                                        <select name="<?php echo htmlspecialchars($fieldName); ?>">
                                            <?php foreach ($options as $opt): ?>
                                                <option value="<?php echo $opt; ?>"
                                                    <?php echo ($current == $opt) ? 'selected' : ''; ?>>
                                                    <?php echo $opt; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                <?php endforeach; ?>

                                <?php
                                $statusField   = strtolower($p) . '_status';
                                $currentStatus = $statuses[$p];
                                ?>
                                <td>
                                    <select name="<?php echo htmlspecialchars($statusField); ?>">
                                        <?php foreach (['On time', 'Late', 'Missed'] as $status): ?>
                                            <option value="<?php echo $status; ?>"
                                                <?php echo ($currentStatus === $status) ? 'selected' : ''; ?>>
                                                <?php echo $status; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="primary-btn">Save log</button>
            </div>

            <!-- Right: Summary -->
            <div class="card">
                <h2>Today’s Summary</h2>

                <div class="summary-grid">
                    <div class="summary-numbers">
                        <span class="label">Total prayed rakats:</span>
                        <span class="value"><?php echo $total_prayed; ?></span>
                    </div>
                    <div class="summary-numbers">
                        <span class="label">Total missed rakats:</span>
                        <span class="value"><?php echo $total_missed; ?></span>
                    </div>

                    <div class="summary-chart">
                        <div class="donut"
                             style="background: conic-gradient(#079664 <?php echo $donut_deg; ?>deg, #e5e7eb 0);">
                        </div>
                    </div>

                    <p class="summary-text">
                        <?php echo htmlspecialchars($summary_text); ?>
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
</body>
</html>
