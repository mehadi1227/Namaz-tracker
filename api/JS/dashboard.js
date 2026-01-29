

const PRAYERS = ["Fajr", "Dhuhr", "Asr", "Maghrib", "Isha"];
const RAKATS = { Fajr: 2, Dhuhr: 4, Asr: 4, Maghrib: 3, Isha: 4 };
const TOTAL_RAKATS = 17;

function AtTheBegining() {
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            const resp = JSON.parse(this.responseText);
            console.log(resp);

            // timings: {Fajr:"2026-...+06:00", ...}
            SetUpComingNamazTimes(resp);
            SetTodaysalahSummary(resp.timings);
        }
    };
    xhttp.open("GET", "../api/namazSchedulingEndpoint.php", true);
    xhttp.send();
}

AtTheBegining();

function SetUpComingNamazTimes(resp) {
    const timings = resp.timings;
    const timezone = resp?.meta?.timezone || resp?.user?.timezone || undefined;
    const locationLabel = resp?.user?.location_label || "";

    const now = new Date();

    // Build ordered list of prayer times (Date objects)
    const items = PRAYERS
        .filter(p => timings[p])
        .map(p => ({ prayer: p, when: new Date(timings[p]) }));

    // Find next prayer (first time after now)
    let next = items.find(x => x.when.getTime() > now.getTime());

    // If after Isha, next is tomorrow's Fajr (basic fallback)
    if (!next) {
        const fajr = items.find(x => x.prayer === "Fajr");
        if (fajr) {
            const tomorrowFajr = new Date(fajr.when.getTime());
            tomorrowFajr.setDate(tomorrowFajr.getDate() + 1);
            next = { prayer: "Fajr", when: tomorrowFajr };
        }
    }

    if (!next) {
        document.getElementById("next_prayer").innerHTML = `<h3>No prayer times found</h3>`;
        return;
    }

    // Format time (12-hour or 24-hour based on locale; you can force hour12:true)
    const timeFmt = new Intl.DateTimeFormat("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
        timeZone: timezone, // if tz is valid IANA, it will format in that timezone
    });

    const nextPrayerContainer = document.getElementById("next_prayer");

    nextPrayerContainer.innerHTML = `
    <h3>Next Prayer</h3>
    <h1 id="nextTitle">${next.prayer} <span id="countdown">--:--:--</span></h1>
    <h6 class="meta_row">
      <span>${next.prayer}</span>
      <span class="dot"></span>
      <span>${timeFmt.format(next.when)}</span>
      <span class="dot"></span>
      <span>Upcoming</span>
    </h6>
    <h6>${timezone}</h6>
  `;

    // Live countdown
    const cdEl = document.getElementById("countdown");
    const timer = setInterval(() => {
        const diffMs = next.when.getTime() - Date.now();
        if (diffMs <= 0) {
            clearInterval(timer);
            cdEl.textContent = "00:00:00";
            AtTheBegining();
            return;
        }
        const totalSec = Math.floor(diffMs / 1000);
        const hh = String(Math.floor(totalSec / 3600)).padStart(2, "0");
        const mm = String(Math.floor((totalSec % 3600) / 60)).padStart(2, "0");
        const ss = String(totalSec % 60).padStart(2, "0");
        cdEl.textContent = `${hh}:${mm}:${ss}`;
    }, 1000);
}



const SetTodaysalahSummary = (timings) => {
  const summaryContainer = document.getElementById("namaj_summary_container");
  const nowMs = Date.now();

  const items = PRAYERS
    .filter(p => timings[p])
    .map(p => {
      const when = new Date(timings[p]); // ISO8601 -> Date
      const isUpcoming = when.getTime() > nowMs;
      return {
        prayer: p,
        when,
        isUpcoming,
        statusText: isUpcoming ? "Upcoming" : "Done",
        statusClass: isUpcoming ? "up" : "done",
      };
    });

  const doneRakats = items
    .filter(x => !x.isUpcoming)
    .reduce((sum, x) => sum + (RAKATS[x.prayer] || 0), 0);

  // With only timing-based logic, missed is unknown => set 0 (you can change later)
  const missed = 0;

  summaryContainer.innerHTML = `
    <ul>
      ${items.map(x => `
        <li>
          <span>${x.prayer}</span>
          <span class="status ${x.statusClass}">${x.statusText}</span>
        </li>
      `).join("")}

      <li class="summary_footer">
        <span>Prayed rakats:</span><span>${doneRakats} / ${TOTAL_RAKATS}</span>
      </li>
      <li class="summary_footer">
        <span>Missed:</span><span>${missed}</span>
      </li>
    </ul>
  `;
};
