<?php
// qaza_planner.php

date_default_timezone_set('Asia/Dhaka');

$qaza_file = __DIR__ . '/qaza_data.json';

// ---- helpers ----
function load_qaza_entries($file)
{
    if (!file_exists($file)) {
        return [];
    }
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function save_qaza_entries($file, $entries)
{
    file_put_contents($file, json_encode($entries, JSON_PRETTY_PRINT));
}

// load all Qaza entries generated from Daily Salah Log
$qaza_entries = load_qaza_entries($qaza_file);

// --- 1) Row-wise status update (only when row Apply is clicked) ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['row_action'])) {
    $rowIndex = (int)$_POST['row_action'];            // which row's Apply was clicked
    $rowStatus = $_POST['row_status'] ?? [];          // array: index => status string

    if (isset($qaza_entries[$rowIndex])) {
        $newStatus = $rowStatus[$rowIndex] ?? $qaza_entries[$rowIndex]['status'];
        $qaza_entries[$rowIndex]['status'] = ($newStatus === 'Completed') ? 'Completed' : 'Pending';
        save_qaza_entries($qaza_file, $qaza_entries);
    }
}

// --- 2) Filters (left card) ---

// default: show only Pending items
$filter_prayer = $_POST['filter_prayer'] ?? 'All';
$filter_status = $_POST['filter_status'] ?? 'Pending';

$filtered_entries = [];
foreach ($qaza_entries as $idx => $row) {
    $ok = true;

    if ($filter_prayer !== 'All' && $row['prayer'] !== $filter_prayer) {
        $ok = false;
    }
    if ($filter_status !== 'All' && $row['status'] !== $filter_status) {
        $ok = false;
    }

    if ($ok) {
        $filtered_entries[$idx] = $row; // keep original index
    }
}

// --- 3) Total pending Fard rakats ---

$total_pending_rakats = 0;
foreach ($qaza_entries as $row) {
    if (
        isset($row['type'], $row['status'], $row['rakats']) &&
        $row['type'] === 'Fard' &&
        $row['status'] === 'Pending'
    ) {
        $total_pending_rakats += (int)$row['rakats'];
    }
}

// --- 4) Plan generator (right card) ---

// from select
$finish_within = $_POST['finish_within'] ?? '30';

// if a quick button was pressed, override
if (isset($_POST['finish_quick'])) {
    $finish_within = $_POST['finish_quick']; // '30','60','custom'
}

$custom_days = isset($_POST['custom_days']) ? (int)$_POST['custom_days'] : 0;

if (in_array($finish_within, ['30', '60', '90'], true)) {
    $days = (int)$finish_within;
} elseif ($finish_within === 'custom' && $custom_days > 0) {
    $days = $custom_days;
} else {
    $days = 0;
}

$daily_rakats = 0;
$plan_text    = '';

if ($total_pending_rakats === 0) {
    $plan_text = "You currently have no pending Fard rakats.";
} elseif ($days > 0) {
    $daily_rakats = (int)ceil($total_pending_rakats / $days);
    $plan_text    = "Pray {$daily_rakats} extra Fard rakats every day to complete in {$days} days.";
} else {
    $plan_text = "Choose how many days you want to finish within to generate a plan.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Qaza (Missed Prayer) Planner</title>
    <link rel="stylesheet" href="qaza_planner.css">
</head>
<body>
<div class="page-wrapper">
    <form class="main-card" method="post">
        <!-- Top header -->
        <div class="header">
            <h1>Qaza (Missed Prayer) Planner</h1>
        </div>
        <p class="disclaimer">
            This tool is only for planning and tracking. Please consult scholars for rulings.
        </p>

        <!-- Two-column grid -->
        <div class="qaza-grid">
            <!-- LEFT: Pending Qaza list -->
            <div class="card">
                <h2>Pending Qaza</h2>

                <p class="small-label">Date</p>
                <div class="filters-row">
                    <select name="filter_prayer">
                        <option value="All"    <?php echo ($filter_prayer === 'All')    ? 'selected' : ''; ?>>Prayer</option>
                        <option value="Fajr"   <?php echo ($filter_prayer === 'Fajr')   ? 'selected' : ''; ?>>Fajr</option>
                        <option value="Dhuhr"  <?php echo ($filter_prayer === 'Dhuhr')  ? 'selected' : ''; ?>>Dhuhr</option>
                        <option value="Asr"    <?php echo ($filter_prayer === 'Asr')    ? 'selected' : ''; ?>>Asr</option>
                        <option value="Maghrib"<?php echo ($filter_prayer === 'Maghrib')? 'selected' : ''; ?>>Maghrib</option>
                        <option value="Isha"   <?php echo ($filter_prayer === 'Isha')   ? 'selected' : ''; ?>>Isha</option>
                    </select>

                    <select name="filter_status">
                        <option value="All"       <?php echo ($filter_status === 'All')       ? 'selected' : ''; ?>>All</option>
                        <option value="Pending"   <?php echo ($filter_status === 'Pending')   ? 'selected' : ''; ?>>Pending</option>
                        <option value="Completed" <?php echo ($filter_status === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                    </select>

                    <!-- Filter apply button: only filtering, no status update -->
                    <button type="submit" name="filter_apply" value="1" class="filter-apply-btn">
                        Apply
                    </button>
                </div>

                <div class="table-wrapper">
                    <table class="qaza-table">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Prayer</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($filtered_entries)): ?>
                            <tr>
                                <td colspan="4" class="empty-row">
                                    No records found for this filter.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filtered_entries as $idx => $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($row['date']))); ?></td>
                                    <td><?php echo htmlspecialchars($row['prayer']); ?></td>
                                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                                    <td>
                                        <div class="status-cell">
                                            <select name="row_status[<?php echo $idx; ?>]">
                                                <option value="Pending"
                                                    <?php echo ($row['status'] === 'Pending') ? 'selected' : ''; ?>>
                                                    Pending
                                                </option>
                                                <option value="Completed"
                                                    <?php echo ($row['status'] === 'Completed') ? 'selected' : ''; ?>>
                                                    Completed
                                                </option>
                                            </select>
                                            <!-- Row apply button: only this row gets updated -->
                                            <button type="submit"
                                                    name="row_action"
                                                    value="<?php echo $idx; ?>"
                                                    class="row-apply-btn">
                                                Apply
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RIGHT: Plan generator -->
            <div class="card">
                <h2>Create a Qaza Plan</h2>

                <div class="plan-top">
                    <p class="pending-text">
                        Total pending Fard rakats:
                        <span class="pending-number"><?php echo $total_pending_rakats; ?></span>
                    </p>

                    <div class="finish-row">
                        <label for="finish_within">Finish within:</label>
                        <select id="finish_within" name="finish_within">
                            <option value="30"   <?php echo ($finish_within === '30')   ? 'selected' : ''; ?>>30 days</option>
                            <option value="60"   <?php echo ($finish_within === '60')   ? 'selected' : ''; ?>>60 days</option>
                            <option value="90"   <?php echo ($finish_within === '90')   ? 'selected' : ''; ?>>90 days</option>
                            <option value="custom" <?php echo ($finish_within === 'custom') ? 'selected' : ''; ?>>Custom</option>
                        </select>
                    </div>

                    <div class="quick-row">
                        <button type="submit" name="finish_quick" value="30"
                                class="quick-btn <?php echo ($finish_within === '30') ? 'active' : ''; ?>">
                            30 days
                        </button>
                        <button type="submit" name="finish_quick" value="60"
                                class="quick-btn <?php echo ($finish_within === '60') ? 'active' : ''; ?>">
                            60 days
                        </button>
                        <button type="submit" name="finish_quick" value="custom"
                                class="quick-btn <?php echo ($finish_within === 'custom') ? 'active' : ''; ?>">
                            Custom
                        </button>
                    </div>

                    <div class="custom-days <?php echo ($finish_within === 'custom') ? 'show' : ''; ?>">
                        <label for="custom_days">Custom days:</label>
                        <input type="number" id="custom_days" name="custom_days"
                               min="1" placeholder="e.g. 45"
                               value="<?php echo ($custom_days > 0) ? $custom_days : ''; ?>">
                        <button type="submit" class="small-submit">Apply</button>
                    </div>
                </div>

                <p class="plan-main-text">
                    <?php echo htmlspecialchars($plan_text); ?>
                </p>
                <p class="plan-note">
                    You can adjust anytime.
                </p>
            </div>
        </div>

        <!-- Bottom timeline card (visual only) -->
        <div class="card timeline-card">
            <h3>Timeline</h3>
            <div class="timeline-bar">
                <div class="timeline-fill"></div>
            </div>
            <div class="timeline-times">
                <span>12 AM</span>
                <span>12 AM</span>
            </div>
            <div class="timeline-legend">
                <span class="legend-box"></span>
                <span class="legend-text">Busy time</span>
            </div>
        </div>
    </form>
</div>
</body>
</html>
