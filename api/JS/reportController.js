// reportController.js

let UserSalahLog = [];

const PRAYERS = ["Fajr", "Dhuhr", "Asr", "Maghrib", "Isha"];
const DAY_NAMES = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

const OK_ICON = `
<span class="ic ok">
  <svg viewBox="0 0 16 16" width="16" height="16">
    <path d="M6.2 11.6 2.7 8.1l1.1-1.1 2.4 2.4 6-6 1.1 1.1-7.1 7.2z"></path>
  </svg>
</span>`;

const BAD_ICON = `
<span class="ic bad">
  <svg viewBox="0 0 16 16" width="16" height="16">
    <path d="M4.3 3.2 3.2 4.3 6.9 8l-3.7 3.7 1.1 1.1L8 9.1l3.7 3.7 1.1-1.1L9.1 8l3.7-3.7-1.1-1.1L8 6.9 4.3 3.2z"></path>
  </svg>
</span>`;


document.addEventListener("DOMContentLoaded", () => {
  setupFilterEvents();
  GetSalahLogOfUser();
});


function GetSalahLogOfUser() {
  const xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState !== 4) return;

    if (this.status === 200) {
      const parsed = safeJsonParse(this.responseText);
      if (Array.isArray(parsed)) UserSalahLog = parsed;
      else if (parsed && Array.isArray(parsed.rows)) UserSalahLog = parsed.rows;
      else UserSalahLog = [];

      renderReports();
    } else {
      console.log("Failed to load logs:", this.responseText);
      UserSalahLog = [];
      renderReports();
    }
  };

  xhttp.open("GET", "../../api/GetUsersSalahLog.php", true);
  xhttp.send();
}

function safeJsonParse(text) {
  try { return JSON.parse(text); } catch { return null; }
}

function setupFilterEvents() {
  const selects = document.querySelectorAll(".rpt_filters select");
  const timeRangeSelect = selects[0];
  const groupBySelect = selects[1];

  if (timeRangeSelect) {
    timeRangeSelect.addEventListener("change", () => renderReports());
  }

  if (groupBySelect) {
    groupBySelect.addEventListener("change", () => {
      if (groupBySelect.value !== "By days") {
        alert('For now, only "By days" is supported.');
        groupBySelect.value = "By days";
      }
      renderReports();
    });
  }
}

function getSelectedTimeRange() {
  const selects = document.querySelectorAll(".rpt_filters select");
  const timeRangeSelect = selects[0];
  return timeRangeSelect ? timeRangeSelect.value : "This month";
}

function renderReports() {
  const timeRange = getSelectedTimeRange();
  const dateRange = getDateRange(timeRange);

  const filteredLogs = filterLogsByRange(UserSalahLog, dateRange.startUtc, dateRange.endUtc);
  const dailyList = buildDailySummaries(filteredLogs); 
  updateKpis(dailyList);
  updateBarChart(dailyList);
  updatePieChart(dailyList);
  updateTable(dailyList);
}

function todayUtc() {
  const now = new Date();
  return new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()));
}

function addDaysUtc(dateUtc, days) {
  const d = new Date(dateUtc);
  d.setUTCDate(d.getUTCDate() + days);
  return d;
}

function getDateRange(label) {
  const endUtc = todayUtc();

  if (label === "Last 7 days") {
    const startUtc = addDaysUtc(endUtc, -6);
    return { startUtc, endUtc };
  }

  if (label === "This week") {
    const day = endUtc.getUTCDay();
    const diffToMonday = (day + 6) % 7; 
    const startUtc = addDaysUtc(endUtc, -diffToMonday);
    return { startUtc, endUtc };
  }

  if (label === "Last month") {
    const y = endUtc.getUTCFullYear();
    const m = endUtc.getUTCMonth(); 
    const startUtc = new Date(Date.UTC(y, m - 1, 1));
    const endLastMonth = new Date(Date.UTC(y, m, 0));
    return { startUtc, endUtc: endLastMonth };
  }

  
  const startUtc = new Date(Date.UTC(endUtc.getUTCFullYear(), endUtc.getUTCMonth(), 1));
  return { startUtc, endUtc };
}


function getLogDateYmd(row) {

  const raw = row.prayed_date || row.prayer_date || row.date || "";
  return normalizeToYmd(raw);
}

function normalizeToYmd(dateStr) {
  const s = String(dateStr).trim();

  
  if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;


  if (/^\d{2}-\d{2}-\d{4}$/.test(s)) {
    const [dd, mm, yyyy] = s.split("-");
    return `${yyyy}-${mm}-${dd}`;
  }

  return ""; 
}

function ymdToUtcDate(ymd) {
  if (!ymd) return null;
  const [y, m, d] = ymd.split("-").map(Number);
  return new Date(Date.UTC(y, m - 1, d));
}

function filterLogsByRange(logs, startUtc, endUtc) {
  const out = [];

  for (const row of logs) {
    const ymd = getLogDateYmd(row);
    const dt = ymdToUtcDate(ymd);
    if (!dt) continue;

    if (dt >= startUtc && dt <= endUtc) {
      out.push(row);
    }
  }

  return out;
}

function normalizeStatus(value) {
  const s = String(value || "").trim().toLowerCase();

  if (s === "on time" || s === "ontime") return "On time";
  if (s === "late") return "Late";
  if (s === "missed") return "Missed";

  
  if (s === "") return "Missed";

  
  return "Missed";
}

function buildDailySummaries(rows) {
  
  const mapByDate = {};

  for (const row of rows) {
    const ymd = getLogDateYmd(row);
    if (!ymd) continue;

    const statuses = {};
    let onTimeCount = 0;
    let lateCount = 0;
    let missedCount = 0;

    for (const prayer of PRAYERS) {
      const key = prayer + "_Status";
      const st = normalizeStatus(row[key]);
      statuses[prayer] = st;

      if (st === "On time") onTimeCount++;
      else if (st === "Late") lateCount++;
      else missedCount++;
    }

    const scorePercent = Math.round((onTimeCount / 5) * 100);

    mapByDate[ymd] = {
      dateYmd: ymd,
      statuses,
      onTime: onTimeCount,
      late: lateCount,
      missed: missedCount,
      score: scorePercent
    };
  }

  const list = Object.values(mapByDate);

 
  list.sort((a, b) => a.dateYmd.localeCompare(b.dateYmd));

  return list;
}


function updateKpis(dailyList) {
  const kpiValues = document.querySelectorAll(".rpt_kpiGrid .rpt_kpiValue");
  if (!kpiValues || kpiValues.length < 3) return;

  let totalOnTime = 0;
  let totalMissed = 0;

  for (const day of dailyList) {
    totalOnTime += day.onTime;
    totalMissed += day.missed;
  }

  const streakDays = calculateCurrentStreak(dailyList);
  kpiValues[0].textContent = String(totalOnTime);
  kpiValues[1].textContent = String(totalMissed);
  kpiValues[2].textContent = `${streakDays} days`;
}


function calculateCurrentStreak(dailyList) {
  if (dailyList.length === 0) return 0;


  let streak = 0;
  for (let i = dailyList.length - 1; i >= 0; i--) {
    if (dailyList[i].missed === 0) streak++;
    else break;
  }
  return streak;
}


function updateBarChart(dailyList) {
  const barsWrap = document.querySelector(".rpt_bars");
  if (!barsWrap) return;

  if (dailyList.length === 0) {
    barsWrap.innerHTML = "";
    return;
  }

  const last7 = dailyList.slice(Math.max(0, dailyList.length - 7));

  let html = "";
  for (const day of last7) {
    const dt = ymdToUtcDate(day.dateYmd);
    const dayName = dt ? DAY_NAMES[dt.getUTCDay()] : "—";

    html += `
      <div class="rpt_barCol">
        <span class="rpt_bar" style="height: ${(day.score)+1}px;"></span>
        <span class="rpt_lbl">${dayName}</span>
      </div>`;
  }

  barsWrap.innerHTML = html;
}


function updatePieChart(dailyList) {
  const seg1 = document.querySelector(".rpt_seg1");
  const seg2 = document.querySelector(".rpt_seg2");
  const seg3 = document.querySelector(".rpt_seg3");
  if (!seg1 || !seg2 || !seg3) return;

  let onTime = 0, late = 0, missed = 0;
  for (const day of dailyList) {
    onTime += day.onTime;
    late   += day.late;
    missed += day.missed;
  }

  const total = onTime + late + missed;
  if (total === 0) {
    seg1.setAttribute("stroke-dasharray", "0 100");
    seg2.setAttribute("stroke-dasharray", "0 100");
    seg3.setAttribute("stroke-dasharray", "0 100");
    seg2.setAttribute("stroke-dashoffset", "0");
    seg3.setAttribute("stroke-dashoffset", "0");
    return;
  }


  const onPct = Math.round((onTime / total) * 100);
  const latePct = Math.round((late / total) * 100);
  let missedPct = 100 - onPct - latePct;

  if (missedPct < 0) missedPct = 0;

  seg1.setAttribute("stroke-dasharray", `${onPct} ${100 - onPct}`);
  seg2.setAttribute("stroke-dasharray", `${latePct} ${100 - latePct}`);
  seg2.setAttribute("stroke-dashoffset", `-${onPct}`);
  seg3.setAttribute("stroke-dasharray", `${missedPct} ${100 - missedPct}`);
  seg3.setAttribute("stroke-dashoffset", `-${onPct + latePct}`);
}


function updateTable(dailyList) {
  const tbody = document.querySelector(".rpt_tableWrap tbody");
  if (!tbody) return;

  if (dailyList.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td class="date">No data</td>
        <td colspan="6">No salah logs found for this range.</td>
      </tr>`;
    return;
  }

  const copy = [...dailyList].sort((a, b) => b.dateYmd.localeCompare(a.dateYmd));

  let html = "";
  for (const day of copy) {
    const dateShow = formatDateDdMmYyyy(day.dateYmd);

    html += `<tr>
      <td class="date">${dateShow}</td>
      ${renderStatusCell(day.statuses.Fajr)}
      ${renderStatusCell(day.statuses.Dhuhr)}
      ${renderStatusCell(day.statuses.Asr)}
      ${renderStatusCell(day.statuses.Maghrib)}
      ${renderStatusCell(day.statuses.Isha)}
      <td class="score right">${day.score}%</td>
    </tr>`;
  }

  tbody.innerHTML = html;
}

function renderStatusCell(status) {
  if (status === "On time") {
    return `<td class="ok">${OK_ICON}</td>`;
  }
  if (status === "Missed") {
    return `<td class="bad">${BAD_ICON}</td>`;
  }
  return `<td class="lateTxt">Late</td>`;
}

function formatDateDdMmYyyy(ymd) {
  if (!ymd) return "";
  const [y, m, d] = ymd.split("-");
  return `${d}-${m}-${y}`;
}
