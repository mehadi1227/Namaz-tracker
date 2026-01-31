<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Prayer Times</title>

  <link rel="stylesheet" href="./PrayerTime.css" />
  <link rel="stylesheet" href="../Designs/AsideMenu.css" />
  <link rel="stylesheet" href="../Designs/Navbar.css" />
  <script src="../../api/JS/prayerTime.js" defer></script>
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
      <a class="menu_btn active" href="../PrayerTime" style="text-decoration: none;">Prayer Times</a>
      <a class="menu_btn" href="../SalahLog" style="text-decoration: none;">Salah Log</a>
      <a class="menu_btn" href="../QazaPlanner" style="text-decoration: none;">Qaza Planner</a>
      <a class="menu_btn" href="../RoutinePlanner" style="text-decoration: none;">Routine Planner</a>
      <a class="menu_btn" href="../Reports" style="text-decoration: none;">Reports</a>
      <a class="menu_btn" href="../Knowledge" style="text-decoration: none;">Knowledge</a>
    </aside>

    <div class="page" id="prayer_time_page">
      <section class="card">
        <div class="topbar">
          <h1 class="title">Prayer Times</h1>

          <div class="dateSelect">
            <select aria-label="Select day" id="daySelect"> 
              <option selected>Today</option>
              <option>Tomorrow</option>
            </select>
            <span class="chev">▾</span>
          </div>
        </div>

        <div class="divider"></div>

        <div class="filters">

          <div class="selectBox">
            <select aria-label="Select method" id="methodSelect">
              <option selected>Hanafi</option>
              <option>Shafi</option>
              <option>Maliki</option>
              <option>Hanbali</option>
            </select>
            <span class="chev">▾</span>
          </div>

          <button class="btn" type="button" id="btnUseLocation" onclick="useMyLocation()">Use my location</button>
        </div>

        <section class="tableCard">
          <div class="tableHead">
            <h2>Today’s Prayer Schedule</h2>
          </div>

          <div class="tableWrap">
            <table border="1">
              <thead>
                <tr>
                  <th>Prayer</th>
                  <th>Start Time</th>
                  <th>End Time</th>
                  <th>Jamaat Time</th>
                  <th class="status">Status</th>
                </tr>
              </thead>

              <tbody id="bodyOfPrayerTimeTable">

              </tbody>
            </table>
          </div>

          <p class="note"><i><b>
                Times are approximate. Please verify with your local mosque if needed.</b></i>
          </p>
        </section>
      </section>
    </div>
  </main>
</body>

</html>