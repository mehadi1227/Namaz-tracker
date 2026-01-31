

const PRAYERS = ["Fajr", "Dhuhr", "Asr", "Maghrib", "Isha"];


const JAMAAT_OFFSET_MIN = {
    Fajr: 30,
    Dhuhr: 25,
    Asr: 15,
    Maghrib: 5,
    Isha: 90
};

function fmtTime(dateObj, tz) {
    return new Intl.DateTimeFormat("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
        timeZone: tz || undefined
    }).format(dateObj);
}

function getStatus(nowMs, startMs, endMs) {

    if (nowMs < startMs) return { text: "Upcoming", cls: "status_upcoming" };
    if (nowMs >= startMs && nowMs < endMs) return { text: "Now", cls: "status_now" };
    return { text: "Done", cls: "status_done" };
}

function buildScheduleRows(resp) {
    const tbody = document.getElementById("bodyOfPrayerTimeTable");

    const timings = resp.timings || {};
    const tz = resp?.meta?.timezone || resp?.user?.timezone || "";


    const starts = PRAYERS
        .filter(p => timings[p])
        .map(p => ({ prayer: p, start: new Date(timings[p]) }));


    const midnight = timings["Midnight"] ? new Date(timings["Midnight"]) : null;

    const nowMs = Date.now();

    let rowsHtml = "";

    for (let i = 0; i < starts.length; i++) {
        const prayer = starts[i].prayer;
        const start = starts[i].start;


        let end;
        if (i < starts.length - 1) {
            end = starts[i + 1].start;
        } else {

            if (midnight && midnight.getTime() > start.getTime()) {
                end = midnight;
            } else {

                const fajr = starts.find(x => x.prayer === "Fajr");
                if (fajr) {
                    end = new Date(fajr.start.getTime());
                    end.setDate(end.getDate() + 1);
                } else {

                    end = new Date(start.getTime() + 6 * 60 * 60 * 1000);
                }
            }
        }


        const offsetMin = JAMAAT_OFFSET_MIN[prayer] ?? 0;
        const jamaat = new Date(start.getTime() + offsetMin * 60 * 1000);

        const status = getStatus(nowMs, start.getTime(), end.getTime());

        rowsHtml += `<tr>
            <td>${prayer}</td>
            <td>${fmtTime(start, tz)}</td>
            <td>${fmtTime(end, tz)}</td>
            <td>${fmtTime(jamaat, tz)}</td>
            <td class="status">
                ${status.text}
            </td>
        </tr>`;

    }

    tbody.innerHTML = rowsHtml;
}

function loadTodaySchedule() {
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            const resp = JSON.parse(this.responseText);
            // console.log(resp);
            buildScheduleRows(resp);
        }
    };
    xhttp.open("GET", "../../api/namazSchedulingEndpoint.php", true);
    xhttp.send();
}

loadTodaySchedule();

// document.getElementById("btnUseLocation").addEventListener("click", useMyLocation);
// daySelect
// methodSelect
// btnUseLocation

function useMyLocation() {
  const btn = document.getElementById("btnUseLocation");
  const daySelect = document.getElementById("daySelect");
  const methodSelect = document.getElementById("methodSelect"); // (your UI says Hanafi/Shafi/Maliki/Hanbali)

  if (!navigator.geolocation) {
    alert("Geolocation is not supported by this browser.");
    return;
  }

  const btnOldText = btn ? btn.textContent : "";


  if (btn) {
    btn.textContent = "Requesting location permission...";
    btn.disabled = true;
  }
  if (daySelect) daySelect.disabled = true;
  if (methodSelect) methodSelect.disabled = true;

 
  const d = new Date();
  const pick = (daySelect?.value || "Today").toLowerCase();
  if (pick === "tomorrow") d.setDate(d.getDate() + 1);


  const pad = (n) => String(n).padStart(2, "0");
  const dateParam = `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${d.getFullYear()}`;

  const tz = (Intl.DateTimeFormat().resolvedOptions().timeZone || "").trim();

  const madhab = (methodSelect?.value || "Shafi").toLowerCase();
  const school = madhab === "hanafi" ? 1 : 0;

  navigator.geolocation.getCurrentPosition(
    async (pos) => {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;

      try {
        const params = new URLSearchParams({
          lat: String(lat),
          lng: String(lng),
          date: dateParam,
          school: String(school),
        });

        if (tz) params.set("tz", tz);

        const url = `../../api/namazSchedulingEndpoint.php?${params.toString()}`;

        const res = await fetch(url, { headers: { Accept: "application/json" } });
        const data = await res.json().catch(() => null);

        if (!res.ok || !data || data.ok === false) {
          throw new Error(data?.error || `Request failed (${res.status})`);
        }


        if (typeof SetUpComingNamazTimes === "function") SetUpComingNamazTimes(data.timings);

        // console.log("Prayer response:", data);
        buildScheduleRows(data);
      } catch (e) {
        console.error(e);
        alert(e?.message || "Could not load prayer times.");
      } finally {
        if (btn) {
          btn.textContent = btnOldText || "Use my location";
          btn.disabled = false;
        }
        if (daySelect) daySelect.disabled = false;
        if (methodSelect) methodSelect.disabled = false;
      }
    },
    (err) => {
      if (btn) {
        btn.textContent = btnOldText || "Use my location";
        btn.disabled = false;
      }
      if (daySelect) daySelect.disabled = false;
      if (methodSelect) methodSelect.disabled = false;

      alert(err?.message || "Could not get location.");
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
  );
}

