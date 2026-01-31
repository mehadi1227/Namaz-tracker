<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Qaza Planner</title>


  <link rel="stylesheet" href="./qaza_planner.css?v=1" />
  <link rel="stylesheet" href="../Designs/AsideMenu.css" />
  <link rel="stylesheet" href="../Designs/Navbar.css" />

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
      <a class="menu_btn" href="../Dashboard" style="text-decoration: none;">Dashboard</a>
      <a class="menu_btn " href="../PrayerTime" style="text-decoration: none;">Prayer Times</a>
      <a class="menu_btn" href="../SalahLog" style="text-decoration: none;">Salah Log</a>
      <a class="menu_btn active" href="../QazaPlanner" style="text-decoration: none;">Qaza Planner</a>
      <a class="menu_btn" href="../RoutinePlanner" style="text-decoration: none;">Routine Planner</a>
      <a class="menu_btn" href="../Reports" style="text-decoration: none;">Reports</a>
      <a class="menu_btn" href="../Knowledge" style="text-decoration: none;">Knowledge</a>
    </aside>
    <div>
        <div class="page-wrapper">
            <div class="main-card">
                <div class="header" style="text-align: center; padding: 60px 20px;">
                    <h1 style="font-size: 32px; margin-bottom: 16px;">🚧 Under Construction</h1>
                    <p style="font-size: 18px; color: var(--muted); margin: 0;">This page is currently being developed and will be available soon.</p>
                    <p style="font-size: 14px; color: var(--muted); margin-top: 24px;">Thank you for your patience!</p>
                </div>
            </div>
        </div>
    </div>
  </main>
</body>
</html>