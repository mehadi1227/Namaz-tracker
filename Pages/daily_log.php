<?php
date_default_timezone_set('Asia/Dhaka');

$prayers = ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
$types = ['Fard', 'Sunnah', 'Nafl'];

$selected_date = isset($_GET['log_date']) && $_GET['log_date'] !== ''
    ? $_GET['log_date']
    : date('Y-m-d');

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Daily Salah Log</title>
    <link rel="stylesheet" href="./daily_log.css">
    <link rel="stylesheet" href="./Designs/AsideMenu.css" />
    <link rel="stylesheet" href="./Designs/Navbar.css" />
    <script src="../api/JS/salaLog.js"></script>
</head>

<body>
    <header>
        <nav>
            <div class="nav_left">
                <div class="brand_icon" aria-hidden="true"></div>
                <img class="brand_logo" src="" alt="logo">
                <label>Salah Tracker</label>
            </div>

            <div class="nav_center">
                Friday, 5 December 2025
            </div>

            <div class="nav_right">
                <button class="icon_btn" type="button" aria-label="Notifications">N</button>
                <button class="avatar" type="button" aria-label="User">RA</button>
                <button class="caret_btn" type="button" onclick="toggleProfileOption()">V</button>
            </div>
        </nav>

        <div id="profile_option_container">
            <ul>
                <li>Profile</li>
                <li>Dashboard</li>
                <li>Logout</li>
            </ul>
        </div>
    </header>

    <main id="main_container">
        <aside id="dashboard_menu" class="sidebar-anim">
            <button class="menu_btn "><a href="./Dashboard.html" style="text-decoration: none;">Dashboard</a> </button>
            <button class="menu_btn "><a href="./PrayerTime.html" style="text-decoration: none;">Prayer Times</a> </button>
            <button class="menu_btn active"><a href="./daily_log.php" style="text-decoration: none;">Salah Log</a> </button>

            <button class="menu_btn" data-view="qaza">Qaza Planner</button>

            <button class="menu_btn" data-view="dashboard">Routine Planner</button>
            <button class="menu_btn" data-view="dashboard">Reports</button>
            <button class="menu_btn" data-view="dashboard">Knowledge</button>
            <button class="menu_btn" id="testbtn">Settings</button>
        </aside>

        <div class="page-wrapper">

            <!-- AJAX submit -->
            <form id="dailyLogForm" class="main-card" onsubmit="SaveToSalahLog(event)">

                <div class="header">
                    <h1>Daily Salah Log</h1>

                    <div class="date-picker">
                        <label for="log_date">Date:</label>
                        <input type="date" id="log_date" name="log_date" value="<?= htmlspecialchars($selected_date) ?>">

                        <input type="hidden" id="prayer_date" name="prayer_date" value="<?= htmlspecialchars($selected_date) ?>">
                    </div>
                </div>

                <!-- this is what your backend expects -->
                <input type="hidden" id="prayer_date" name="prayer_date" value="<?= htmlspecialchars($selected_date) ?>">

                <div class="content-grid">

                    <div class="card">
                        <h2>Log Today’s Salah</h2>
                        <p class="log-heading">Log for <?= date('d/m/Y', strtotime($selected_date)); ?></p>

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
                                            <td><?= htmlspecialchars($p) ?></td>

                                            <?php foreach ($types as $t): ?>
                                                <?php
                                                $col = $p . '_' . $t; // e.g. Fajr_Fard
                                                $options = ($t === 'Nafl')
                                                    ? [0, 2, 4, 6, 8, 12, 14, 16, 20]
                                                    : [0, 2, 3, 4];
                                                ?>
                                                <td>
                                                    <select
                                                        name="<?= htmlspecialchars($col) ?>"
                                                        class="rakat-select"
                                                        data-prayer="<?= htmlspecialchars($p) ?>"
                                                        data-type="<?= htmlspecialchars($t) ?>">
                                                        <?php foreach ($options as $opt): ?>
                                                            <option value="<?= $opt ?>"><?= $opt ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                            <?php endforeach; ?>

                                            <?php $statusCol = $p . '_Status'; ?>
                                            <td>
                                                <select
                                                    name="<?= htmlspecialchars($statusCol) ?>"
                                                    class="status-select"
                                                    data-prayer="<?= htmlspecialchars($p) ?>">
                                                    <?php foreach (['On time', 'Late', 'Missed'] as $status): ?>
                                                        <option value="<?= $status ?>"><?= $status ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                            </table>
                        </div>

                        <button id="btnSaveLog" type="submit" class="primary-btn">Save log</button>
                        <div id="save_msg" style="margin-top:10px;font-weight:700;"></div>
                    </div>

                    <div class="card">
                        <h2>Today’s Summary</h2>

                        <div class="summary-grid">
                            <div class="summary-numbers">
                                <span class="label">Total prayed wakts:</span>
                                <span class="value" id="total_prayed">0</span>
                            </div>

                            <div class="summary-numbers">
                                <span class="label">Total missed wakts:</span>
                                <span class="value" id="total_missed">0</span>
                            </div>

                            <div class="summary-chart">
                                <div class="donut" id="donutChart"
                                    style="background: conic-gradient(#079664 0deg, #e5e7eb 0);">
                                </div>
                            </div>

                            <p class="summary-text" id="summary_text">
                                Select your rakats and press “Save log”.
                            </p>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </main>
</body>

</html>