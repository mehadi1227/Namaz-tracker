<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Daily Routine Planner</title>
  <link rel="stylesheet" href="./RoutinePlanner.css?v=3" />
  <link rel="stylesheet" href="../Designs/AsideMenu.css" />
  <link rel="stylesheet" href="../Designs/Navbar.css" />
  <script src="../../api/JS/NavbarOption.js" defer></script>
  <script src="../../api/JS/routinePlanner.js" defer></script>
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
        Friday, 5 December 2025
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
      <a class="menu_btn " href="../Dashboard" style="text-decoration: none;">Dashboard</a>
      <a class="menu_btn " href="../PrayerTime" style="text-decoration: none;">Prayer Times</a>
      <a class="menu_btn" href="../SalahLog" style="text-decoration: none;">Salah Log</a>
      <a class="menu_btn" href="../QazaPlanner" style="text-decoration: none;">Qaza Planner</a>
      <a class="menu_btn active" href="../RoutinePlanner" style="text-decoration: none;">Routine Planner</a>
      <a class="menu_btn" href="../Reports" style="text-decoration: none;">Reports</a>
      <a class="menu_btn" href="../Knowledge" style="text-decoration: none;">Knowledge</a>
    </aside>

    <div class="rp_page" id="routine_planner_page">
      <section class="rp_shell">

        <header class="rp_headerCard">
          <h1 class="rp_title">Daily Routine Planner</h1>
        </header>

        <p class="rp_sub">
          Add your typical day so we can suggest prayer windows.
        </p>

        <section class="rp_grid2">
          <!-- Routine Form -->
          <div class="rp_card">
            <div class="rp_cardTop">
              <h2 class="rp_cardTitle">Routine Form</h2>
              <span class="rp_hint">Use 24h time (e.g. 13:30)</span>
            </div>

            <div class="rp_fieldGroup">
              <label class="rp_lbl">Sleep time</label>
              <div class="rp_row2">
                <input class="rp_inp" id="sleep_from" type="time" />
                <input class="rp_inp" id="sleep_to" type="time" />
              </div>
            </div>

            <div class="rp_fieldGroup">
              <label class="rp_lbl">Work / Study time</label>
              <div class="rp_row2">
                <input class="rp_inp" id="work_from" type="time" />
                <input class="rp_inp" id="work_to" type="time" />
              </div>
            </div>

            <div class="rp_fieldGroup">
              <div class="rp_blocksHeader">
                <label class="rp_lbl" style="margin:0;">Custom Blocks</label>
                <button class="rp_addBtn" id="add_block_btn" type="button" aria-label="Add block">+</button>
              </div>

              <div id="blocks_container" class="rp_blocksContainer"></div>
            </div>

            <div id="rp_error" class="rp_error" style="display:none;"></div>

            <button class="rp_primaryBtn" id="save_routine_btn" type="button">Save routine</button>
          </div>

          <!-- Suggested prayer times -->
          <div class="rp_card">
            <h2 class="rp_cardTitle">Suggested prayer times</h2>

            <div class="rp_suggestList">
              <div class="rp_sItem">
                <div class="rp_sTitle">Fajr</div>
                <div class="rp_sSub" id="sug_fajr">—</div>
              </div>

              <div class="rp_sItem">
                <div class="rp_sTitle">Dhuhr</div>
                <div class="rp_sSub" id="sug_dhuhr">—</div>
              </div>

              <div class="rp_sItem">
                <div class="rp_sTitle">Asr</div>
                <div class="rp_sSub" id="sug_asr">—</div>
              </div>

              <div class="rp_sItem">
                <div class="rp_sTitle">Maghrib</div>
                <div class="rp_sSub" id="sug_maghrib">—</div>
              </div>

              <div class="rp_sItem">
                <div class="rp_sTitle">Isha</div>
                <div class="rp_sSub" id="sug_isha">—</div>
              </div>
            </div>

            <p class="rp_note">
              Suggestion excludes your busy blocks. If no free slot is found, it will show “No free time”.
            </p>
          </div>
        </section>

        <!-- Timeline (visual only) -->
        <section class="rp_card rp_timelineCard">
          <h2 class="rp_cardTitle">Timeline</h2>
          <div class="rp_timeline">
            <div class="rp_bar">
              <span class="rp_seg rp_busy" style="left:0%; width:35%;"></span>
              <span class="rp_seg rp_busy" style="left:35%; width:18%;"></span>
              <span class="rp_seg rp_window" style="left:53%; width:47%;"></span>
            </div>

            <div class="rp_timeRow">
              <span>12 AM</span>
              <span>12 AM</span>
            </div>

            <div class="rp_legend">
              <div class="rp_legItem">
                <span class="rp_swatch rp_busy"></span><span>Busy time</span>
              </div>
              <div class="rp_legItem">
                <span class="rp_swatch rp_window"></span><span>Prayer window</span>
              </div>
            </div>
          </div>
        </section>

      </section>
    </div>
  </main>

</body>

</html>