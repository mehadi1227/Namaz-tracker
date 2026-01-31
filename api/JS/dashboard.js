

const PRAYERS = ["Fajr", "Dhuhr", "Asr", "Maghrib", "Isha"];
const RAKATS = { Fajr: 2, Dhuhr: 4, Asr: 4, Maghrib: 3, Isha: 4 };
const TOTAL_RAKATS = 17;

function AtTheBegining() {
  const xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      const resp = JSON.parse(this.responseText);

      
      SetUpComingNamazTimes(resp);
      GetTodaysLog(resp)
      // SetTodaysalahSummary(resp.timings);
    }
  };
  xhttp.open("GET", "../../api/namazSchedulingEndpoint.php", true);
  xhttp.send();
}

AtTheBegining();

function GetTodaysLog(apiValues) {
  const dateVal = new Date().toISOString().split('T')[0];

  if (!dateVal) {
    return;
  }

  const xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState !== 4) return;

    if (this.status === 200) {
      const userData = JSON.parse(this.responseText);
      SetTodaysalahSummary(apiValues.timings, userData);
    } else {
      // alert("Error retrieving Salah log: " + this.responseText);
      SetTodaysalahSummary(apiValues.timings, null);
    }
  };

  xhttp.open("POST", `../api/retriveFromSalahLog.php`, true);
  xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhttp.send(`prayer_date=${encodeURIComponent(dateVal)}`);
}

function SetUpComingNamazTimes(resp) {
  const timings = resp.timings;
  const timezone = resp?.meta?.timezone || resp?.user?.timezone || undefined;
  const locationLabel = resp?.user?.location_label || "";

  const now = new Date();


  const items = PRAYERS
    .filter(p => timings[p])
    .map(p => ({ prayer: p, when: new Date(timings[p]) }));


  let next = items.find(x => x.when.getTime() > now.getTime());


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


  const timeFmt = new Intl.DateTimeFormat("en-US", {
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
    timeZone: timezone,
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

  // live countdown
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

function DidUserMissedNamaz(prayerName, userData) {
  if (!userData || typeof userData !== 'object') {
    return "Missed";
  }
  const statusKey = `${prayerName}_Status`;
  if (userData[statusKey] === 'Missed') {
    return "Missed";
  } else {
    return "Done"
  }
}

function HowManyRaktDidUserPrayed(prayerArray, userData) {
  if (!userData || typeof userData !== 'object') {
    return 0;
  }
  let prayedRakat = 0;
  prayerArray.forEach(p => {
    const statusFardKey = `${p.prayer}_Fard`;
    const statusSunnahKey = `${p.prayer}_Sunnah`;

    if (userData[statusFardKey] !== 0) {
      prayedRakat += userData[statusFardKey];
    }
    if (userData[statusSunnahKey] !== 0) {
      prayedRakat += userData[statusSunnahKey];
    }
  })

  return prayedRakat;
}

const SetTodaysalahSummary = (timings, userData) => {
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
        statusText: isUpcoming ? "Upcoming" : DidUserMissedNamaz(p, userData),
        statusClass: isUpcoming ? "up" : "done",
      };
    });

  const doneRakats = items.filter(x => !x.isUpcoming);
  const doneRakatsCount = HowManyRaktDidUserPrayed(doneRakats, userData);


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
        <span>Prayed rakats:</span><span>${doneRakatsCount} / ${TOTAL_RAKATS}</span>
      </li>
      <li class="summary_footer">
        <span>Missed:</span><span>${missed}</span>
      </li>
    </ul>
  `;
};


function RetriveWeeklyActivities() {
  const xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState !== 4) return;
    if (this.status === 200) {
      const resp = JSON.parse(this.responseText);
      console.log("Weekly Activities:", resp);
      ShowWeeklyActivities(resp.activities, resp.weekStart, resp.weekEnd);
    } else {
      console.log("Error retrieving Weekly Activities: " + this.responseText);
    }
  };
  xhttp.open("POST", `../../api/getWeeklyactivities.php`, true);
  xhttp.send();

}
RetriveWeeklyActivities()

function ShowWeeklyActivities(Activities, weekStart, weekEnd) {
  const prayed_five_wakt_this_week = document.getElementById('prayed_five_wakt_this_week');
  const streak_count_id = document.getElementById('streak_count_id');
  const fajr_prayer_span_badge = document.getElementById('fajr_prayer_span_badge');
  const full_week_complete_badge = document.getElementById('full_week_complete_badge');
  const miniChart = document.getElementById('miniChart');

  const datesInWeek = getDatesBetweenInclusive(weekStart, weekEnd);
  const { NumOfDayPrayed, FajrPrayed, progressbar } = UserPrayedFiveWaktThisWeek(Activities, datesInWeek);
  prayed_five_wakt_this_week.innerText = `You prayed all 5 prayers on ${NumOfDayPrayed} days this week.`;
  streak_count_id.innerText = `${NumOfDayPrayed} days`;
  fajr_prayer_span_badge.innerText = `Fajr Prayed ${FajrPrayed} days`;
  if (NumOfDayPrayed === 7) {
    full_week_complete_badge.style.color = 'blue';
  } else {
    full_week_complete_badge.style.color = 'none';
    full_week_complete_badge.textContent = 'Week in Progress';
  }

  if (progressbar.length !== 0) {
    for (let i = 0; i < 7; i++) {
      miniChart.innerHTML += `<span style="height: ${progressbar[i] || 0}px; background-color: ${progressbar[i]==1 ? 'red' : 'green'};"></span>`;
    }

  }
}

function UserPrayedFiveWaktThisWeek(Activities, datesInWeek) {
  if (!Array.isArray(Activities) || Activities.length === 0) {
    return { NumOfDayPrayed: 0, FajrPrayed: 0, progressbar: new Array(7).fill(0) };
  }
  let NumOfDayPrayed = 0;
  let FajrPrayed = 0;
  let progressbar = new Array(7).fill(1);
  Activities.forEach(activity => {
    // console.log(activity);
    if (activity.Asr_Fard !== 0 &&
      activity.Dhuhr_Fard !== 0 &&
      activity.Fajr_Fard !== 0 &&
      activity.Isha_Fard !== 0 &&
      activity.Maghrib_Fard !== 0) {
      NumOfDayPrayed += 1;
      console.log("Weekly Prayed Days:", NumOfDayPrayed);
    }

    let index = datesInWeek.indexOf(activity.prayer_date);
    let progressbarheight = 0;
    if (activity.Fajr_Fard !== 0) {
      FajrPrayed += 1;
      progressbarheight += 8.4;
    }
    if (activity.Dhuhr_Fard !== 0) {
      progressbarheight += 8.4;
    }
    if (activity.Asr_Fard !== 0) {
      progressbarheight += 8.4;
    }
    if (activity.Isha_Fard !== 0) {
      progressbarheight += 8.4;
    }
    if (activity.Maghrib_Fard !== 0) {
      progressbarheight += 8.4;
    }
    progressbar[index] = progressbarheight;

  });

  return { NumOfDayPrayed, FajrPrayed, progressbar };
}

//ChatGPT dise jani na kemne kaaj kore ei part
function parseYMDToUTC(ymd) {
  const [y, m, d] = ymd.split("-").map(Number);
  return new Date(Date.UTC(y, m - 1, d)); // month is 0-based
}

function getDatesBetweenInclusive(weekStart, weekEnd) {
  let start = parseYMDToUTC(weekStart);
  let end = parseYMDToUTC(weekEnd);

  // If reversed, swap
  if (start > end) [start, end] = [end, start];

  const dates = [];
  for (let cur = new Date(start); cur <= end; cur.setUTCDate(cur.getUTCDate() + 1)) {
    dates.push(cur.toISOString().slice(0, 10)); // "YYYY-MM-DD"
  }
  return dates;
}

