<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>

  <link rel="stylesheet" href="./dashboard.css?v=1" />
  <link rel="stylesheet" href="../Designs/AsideMenu.css" />
  <link rel="stylesheet" href="../Designs/Navbar.css" />

  <script src="../../api/JS/dashboard.js"></script>
  <script src="../../api/JS/NavbarOption.js" defer></script>
</head>

<body>
  <header>
    <nav>
      <div class="nav_left">
        <div class="brand_icon" aria-hidden="true"></div>
        <img class="brand_logo" src="" alt="logo">
        <label>Salah Tracker</label>
      </div>

      <div class="nav_center" id="nav_center">
      </div>

      <div class="nav_right">
        <button class="avatar" type="button" aria-label="User" onclick="toggleProfileOption()">RA</button>
      </div>
    </nav>

    <div id="profile_option_container">
      <ul>
        <li onclick="window.location.href = '../../Profile'">Profile</li>
        <li onclick="LogOut()">Logout</li>
      </ul>
    </div>
  </header>

  <main id="main_container">
    <aside id="dashboard_menu" class="sidebar-anim">
      <a class="menu_btn active" href="../Dashboard" style="text-decoration: none;">Dashboard</a>
      <a class="menu_btn " href="../PrayerTime" style="text-decoration: none;">Prayer Times</a>
      <a class="menu_btn" href="../SalahLog" style="text-decoration: none;">Salah Log</a>
      <a class="menu_btn" href="../QazaPlanner" style="text-decoration: none;">Qaza Planner</a>
      <a class="menu_btn" href="../RoutinePlanner" style="text-decoration: none;">Routine Planner</a>
      <a class="menu_btn" href="../Reports" style="text-decoration: none;">Reports</a>
      <a class="menu_btn" href="../Knowledge" style="text-decoration: none;">Knowledge</a>
    </aside>

    <section id="content_section">
      <div id="dashboard_content">
        <div id="next_prayer" class="iteam_containers card-anim d1">
        </div>

        <div id="summary_container" class="iteam_containers card-anim d2">
          <h3>Today's Salah Summary</h3>
          <div id="namaj_summary_container" class="summary_list">

            </ul>
          </div>
        </div>

        <div id="quick_action_container" class="iteam_containers card-anim d3">
          <h3>Quick actions</h3>
          <div class="chip_grid">
            <button class="chip_btn">Log 3 prayer</button>
            <button class="chip_btn">Update routine</button>
            <button class="chip_btn">View Qaza list</button>
            <button class="chip_btn">Open reports</button>
          </div>
        </div>

        <div id="weekly_container" class="iteam_containers card-anim d4">
          <h3>Weekly Progress</h3>
          <div class="miniChart" aria-label="Weekly chart" id="miniChart">

          </div>
          <p id="prayed_five_wakt_this_week"></p>
        </div>

        <div id="streak_container" class="iteam_containers card-anim d5">
          <h3>Streak & Badges</h3>
          <div class="streak_center">
            <h6>Current streak:</h6>
            <h1 id="streak_count_id">4 days</h1>
          </div>

          <div class="badgeRow">
            <span class="badgeBtn" id="fajr_prayer_span_badge">Fajr early x days</span>
            <span class="badgeBtn" id="full_week_complete_badge">Full week complete</span>
          </div>
        </div>
      </div>


    </section>
  </main>

  <script>



  </script>
</body>

</html>