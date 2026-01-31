<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reports & Statistics</title>
  <link rel="stylesheet" href="./Reports.css?v=2" />
  <link rel="stylesheet" href="../Designs/AsideMenu.css" />
  <link rel="stylesheet" href="../Designs/Navbar.css" />
  <script src="../../api/JS/NavbarOption.js" defer></script>
  <script src="../../api/JS/reportController.js" defer></script>
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
      <a class="menu_btn " href="../Dashboard" style="text-decoration: none;">Dashboard</a>
      <a class="menu_btn " href="../PrayerTime" style="text-decoration: none;">Prayer Times</a>
      <a class="menu_btn" href="../SalahLog" style="text-decoration: none;">Salah Log</a>
      <a class="menu_btn" href="../QazaPlanner" style="text-decoration: none;">Qaza Planner</a>
      <a class="menu_btn" href="../RoutinePlanner" style="text-decoration: none;">Routine Planner</a>
      <a class="menu_btn active" href="../Reports" style="text-decoration: none;">Reports</a>
      <a class="menu_btn" href="../Knowledge" style="text-decoration: none;">Knowledge</a>
    </aside>

    <div class="rpt_page" id="reports_page">
      <section class="rpt_shell">


        <header class="rpt_headerCard">
          <h1 class="rpt_title">Reports &amp; Statistics</h1>
        </header>


        <section class="rpt_filters">
          <div class="rpt_selectBox">
            <select aria-label="Select time range">
              <option selected>This month</option>
              <option>Last month</option>
              <option>This week</option>
              <option>Last 7 days</option>
            </select>
            <span class="rpt_chev">▾</span>
          </div>

          <div class="rpt_selectBox">
            <select aria-label="Select group by">
              <option selected>By days</option>
              <option>By weeks</option>
              <option>By prayers</option>
            </select>
            <span class="rpt_chev">▾</span>
          </div>
        </section>

        <!-- KPI Cards -->
        <section class="rpt_kpiGrid">
          <article class="rpt_kpiCard">
            <div class="rpt_kpiTitle">On-time prayers<br />this month</div>
            <div class="rpt_kpiValue">85</div>
          </article>

          <article class="rpt_kpiCard">
            <div class="rpt_kpiTitle">Missed<br />prayers</div>
            <div class="rpt_kpiValue">7</div>
          </article>

          <article class="rpt_kpiCard">
            <div class="rpt_kpiTitle">Current<br />streak</div>
            <div class="rpt_kpiValue">4 days</div>
          </article>
        </section>

        <!-- Charts Row -->
        <section class="rpt_chartGrid">
          <!-- Bar chart -->
          <article class="rpt_card">
            <h2 class="rpt_cardTitle">Prayers per day</h2>

            <div class="rpt_barChart" role="img" aria-label="Bar chart prayers per day">
              <div class="rpt_bars">
                <div class="rpt_barCol"><span class="rpt_bar" style="height: 56%;"></span><span class="rpt_lbl">Sun</span></div>
                <div class="rpt_barCol"><span class="rpt_bar" style="height: 68%;"></span><span class="rpt_lbl">Mon</span></div>
                <div class="rpt_barCol"><span class="rpt_bar" style="height: 82%;"></span><span class="rpt_lbl">Tue</span></div>
                <div class="rpt_barCol"><span class="rpt_bar" style="height: 52%;"></span><span class="rpt_lbl">Wed</span></div>
                <div class="rpt_barCol"><span class="rpt_bar" style="height: 46%;"></span><span class="rpt_lbl">Thu</span></div>
                <div class="rpt_barCol"><span class="rpt_bar" style="height: 74%;"></span><span class="rpt_lbl">Fri</span></div>
              </div>
            </div>
          </article>

          <!-- Pie chart -->
          <article class="rpt_card">
            <h2 class="rpt_cardTitle">On-time vs Late vs Missed</h2>

            <div class="rpt_pieWrap">
              <svg class="rpt_pie" viewBox="0 0 36 36" aria-label="Pie chart on-time late missed" role="img">
                <path class="rpt_pieBg"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="rpt_seg rpt_seg1" stroke-dasharray="60 40"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="rpt_seg rpt_seg2" stroke-dasharray="25 75" stroke-dashoffset="-60"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="rpt_seg rpt_seg3" stroke-dasharray="15 85" stroke-dashoffset="-85"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
              </svg>

              <div class="rpt_legend">
                <div class="rpt_legItem"><span class="rpt_sw rpt_sw1"></span><span>On-time</span></div>
                <div class="rpt_legItem"><span class="rpt_sw rpt_sw2"></span><span>Late</span></div>
                <div class="rpt_legItem"><span class="rpt_sw rpt_sw3"></span><span>Missed</span></div>
              </div>
            </div>
          </article>
        </section>

        <!-- Detailed Activity -->
        <section class="rpt_card rpt_tableCard">
          <div class="rpt_tableHead">
            <h2 class="rpt_cardTitle">Detailed Activity</h2>
            <div class="rpt_tableColsTitle">
              <span>Date</span><span>Fajr</span><span>Dhuhr</span><span>Asr</span><span>Maghrib</span><span>Isha</span>
              <span class="right">Score</span>
            </div>
          </div>

          <div class="rpt_tableWrap">
            <table>
              <tbody>
                <tr>
                  <td class="date">05-12-2025</td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="score right">100%</td>
                </tr>

                <tr>
                  <td class="date">04-12-2025</td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="lateTxt">Late</td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="score right">80%</td>
                </tr>

                <tr>
                  <td class="date">03-12-2025</td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="bad"><span class="ic bad"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M4.3 3.2 3.2 4.3 6.9 8l-3.7 3.7 1.1 1.1L8 9.1l3.7 3.7 1.1-1.1L9.1 8l3.7-3.7-1.1-1.1L8 6.9 4.3 3.2z"></path>
                      </svg></span></td>
                  <td class="bad"><span class="ic bad"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M4.3 3.2 3.2 4.3 6.9 8l-3.7 3.7 1.1 1.1L8 9.1l3.7 3.7 1.1-1.1L9.1 8l3.7-3.7-1.1-1.1L8 6.9 4.3 3.2z"></path>
                      </svg></span></td>
                  <td class="bad"><span class="ic bad"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M4.3 3.2 3.2 4.3 6.9 8l-3.7 3.7 1.1 1.1L8 9.1l3.7 3.7 1.1-1.1L9.1 8l3.7-3.7-1.1-1.1L8 6.9 4.3 3.2z"></path>
                      </svg></span></td>
                  <td class="ok"><span class="ic ok"><svg viewBox="0 0 16 16" width="16" height="16">
                        <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
                      </svg></span></td>
                  <td class="score right">80%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

      </section>
    </div>
  </main>
</body>

</html>