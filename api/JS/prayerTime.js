

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
    // If you want the screenshot "—" always, just: return { text: "—", cls: "" };
    if (nowMs < startMs) return { text: "Upcoming", cls: "status_upcoming" };
    if (nowMs >= startMs && nowMs < endMs) return { text: "Now", cls: "status_now" };
    return { text: "Done", cls: "status_done" };
}

function buildScheduleRows(resp) {
    const tbody = document.getElementById("bodyOfPrayerTimeTable");
    //if (!tbody) return;

    const timings = resp.timings || {};
    const tz = resp?.meta?.timezone || resp?.user?.timezone || "";

    // Parse start times as Date objects (ISO8601 strings from your API)
    const starts = PRAYERS
        .filter(p => timings[p])
        .map(p => ({ prayer: p, start: new Date(timings[p]) }));

    // End time logic:
    // - end of each prayer = next prayer start
    // - end of Isha = Midnight if available, else next day's Fajr
    const midnight = timings["Midnight"] ? new Date(timings["Midnight"]) : null;

    const nowMs = Date.now();

    let rowsHtml = "";

    for (let i = 0; i < starts.length; i++) {
        const prayer = starts[i].prayer;
        const start = starts[i].start;

        // Compute end
        let end;
        if (i < starts.length - 1) {
            end = starts[i + 1].start;
        } else {
            // Isha
            if (midnight && midnight.getTime() > start.getTime()) {
                end = midnight;
            } else {
                // fallback to next day Fajr
                const fajr = starts.find(x => x.prayer === "Fajr");
                if (fajr) {
                    end = new Date(fajr.start.getTime());
                    end.setDate(end.getDate() + 1);
                } else {
                    // last resort: +6 hours
                    end = new Date(start.getTime() + 6 * 60 * 60 * 1000);
                }
            }
        }

        // Jamaat time (start + offset)
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
            console.log(resp);
            buildScheduleRows(resp);
        }
    };
    xhttp.open("GET", "../api/namazSchedulingEndpoint.php", true);
    xhttp.send();
}

loadTodaySchedule();

// Optional: auto-refresh the status badges every minute
// setInterval(loadTodaySchedule, 60 * 1000);
