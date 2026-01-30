(() => {

  if (window.__routinePlannerLoaded) return;
  window.__routinePlannerLoaded = true;


  const ROUTINE_API_URL = "../api/routineSave.php";         
  const NAMAZ_API_URL   = "../api/namazSchedulingEndpoint.php";

  const SLOT_MINUTES = 15; 
  const STEP_MINUTES = 5;  

  let PRAYER_WINDOWS = null;  
  let SAVED_ROUTINE  = null;   

  function $(id) { return document.getElementById(id); }


  function isoToHHMM(value) {
    if (!value) return "";
    const s = String(value).trim();
    const m = /T(\d{2}):(\d{2})/.exec(s);
    if (m) return `${m[1]}:${m[2]}`;
    if (/^\d{1,2}:\d{2}$/.test(s)) return s.padStart(5, "0");
    return "";
  }

  
  function toMin(timeStr) {
    if (!timeStr) return null;
    const s = String(timeStr).trim();
    if (s === "24:00") return 1440;

    const m = /^(\d{1,2}):(\d{2})$/.exec(s);
    if (!m) return null;
    const hh = Number(m[1]), mm = Number(m[2]);
    if (hh < 0 || hh > 24 || mm < 0 || mm > 59) return null;
    if (hh === 24 && mm !== 0) return null;
    return hh * 60 + mm;
  }

  function fmt(min) {
    min = ((min % 1440) + 1440) % 1440;
    const hh24 = Math.floor(min / 60);
    const mm = min % 60;
    const ampm = hh24 >= 12 ? "PM" : "AM";
    let hh = hh24 % 12; if (hh === 0) hh = 12;
    return `${hh}:${String(mm).padStart(2, "0")} ${ampm}`;
  }

  function normalizeInterval(startMin, endMin) {
    if (startMin == null || endMin == null) return [];
    if (startMin === endMin) return [];
    if (endMin > startMin) return [[startMin, endMin]];
    return [[startMin, 1440], [0, endMin]]; 
  }

  function overlaps(aStart, aEnd, bStart, bEnd) {
    return aStart < bEnd && bStart < aEnd;
  }

  function mergeIntervals(intervals) {
    const sorted = intervals
      .map(([s, e]) => [Math.max(0, s), Math.min(1440, e)])
      .filter(([s, e]) => e > s)
      .sort((a, b) => a[0] - b[0]);

    if (!sorted.length) return [];
    const out = [sorted[0]];
    for (let i = 1; i < sorted.length; i++) {
      const [s, e] = sorted[i];
      const last = out[out.length - 1];
      if (s <= last[1]) last[1] = Math.max(last[1], e);
      else out.push([s, e]);
    }
    return out;
  }

  function isBusy(start, end, busyIntervals) {
    return busyIntervals.some(([bs, be]) => overlaps(start, end, bs, be));
  }

  function findEarliestSlot(winStart, winEnd, busyIntervals) {
    for (let t = winStart; t + SLOT_MINUTES <= winEnd; t += STEP_MINUTES) {
      if (!isBusy(t, t + SLOT_MINUTES, busyIntervals)) return t;
    }
    return null;
  }

  function setMsg(text, isError = false) {
    const box = $("rp_error");
    if (!box) return;

    box.style.display = text ? "block" : "none";
    box.textContent = text || "";
    box.style.borderColor = isError ? "#fecaca" : "#bbf7d0";
    box.style.background  = isError ? "#fff1f2" : "#f0fdf4";
    box.style.color       = isError ? "#991b1b" : "#166534";
  }

  function setRoutineMode(exists) {
    const btn = $("save_routine_btn");
    if (!btn) return;

    if (!exists) {
      btn.textContent = "Save routine";
      setMsg("Save your routine to get suggestion.", false);
      renderSuggestionsOnPage({}); 
    } else {
      btn.textContent = "Update routine";
      setMsg("", false);
    }
  }

  function renderSuggestionsOnPage(suggestions) {
    const set = (id, val) => {
      const el = $(id);
      if (el) el.textContent = val;
    };
    set("sug_fajr",    suggestions.Fajr?.text ?? "—");
    set("sug_dhuhr",   suggestions.Dhuhr?.text ?? "—");
    set("sug_asr",     suggestions.Asr?.text ?? "—");
    set("sug_maghrib", suggestions.Maghrib?.text ?? "—");
    set("sug_isha",    suggestions.Isha?.text ?? "—");
  }

  function readRoutineInputs() {
    const sleep_from = ($("sleep_from")?.value || "").trim();
    const sleep_to   = ($("sleep_to")?.value || "").trim();
    const work_from  = ($("work_from")?.value || "").trim();
    const work_to    = ($("work_to")?.value || "").trim();

    const blocks = [];
    const container = $("blocks_container");
    if (container) {
      for (const row of container.querySelectorAll(".rp_blockRow")) {
        const nameInp = row.querySelector('input[type="text"]');
        const times = row.querySelectorAll('input[type="time"]');
        const fromInp = times[0];
        const toInp   = times[1];

        blocks.push({
          name: (nameInp?.value || "").trim(),
          from: (fromInp?.value || "").trim(),
          to:   (toInp?.value || "").trim(),
        });
      }
    }

    return { sleep_from, sleep_to, work_from, work_to, blocks };
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, m => ({
      "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"
    }[m]));
  }

  function applyRoutineToForm(r) {
    if ($("sleep_from")) $("sleep_from").value = r.sleep_from || "";
    if ($("sleep_to"))   $("sleep_to").value   = r.sleep_to || "";
    if ($("work_from"))  $("work_from").value  = r.work_from || "";
    if ($("work_to"))    $("work_to").value    = r.work_to || "";

    const container = $("blocks_container");
    if (!container) return;

    container.innerHTML = "";
    (r.blocks || []).forEach(b => {
      const row = document.createElement("div");
      row.className = "rp_blockRow";
      row.innerHTML = `
        <input class="rp_inp" type="text" placeholder="Block name" value="${escapeHtml(b.name || "")}">
        <input class="rp_inp" type="time" value="${escapeHtml(b.from || "")}">
        <input class="rp_inp" type="time" value="${escapeHtml(b.to || "")}">
        <button class="rp_removeBtn" type="button">×</button>
      `;
      row.querySelector(".rp_removeBtn").addEventListener("click", () => row.remove());
      container.appendChild(row);
    });
  }

  function routineToBusy(r) {
    let busy = [];
    busy.push(...normalizeInterval(toMin(r.sleep_from), toMin(r.sleep_to)));
    busy.push(...normalizeInterval(toMin(r.work_from),  toMin(r.work_to)));
    (r.blocks || []).forEach(b => {
      busy.push(...normalizeInterval(toMin(b.from), toMin(b.to)));
    });
    return mergeIntervals(busy);
  }

  function validateRoutine(r) {
    const pairs = [
      ["Sleep", r.sleep_from, r.sleep_to],
      ["Work/Study", r.work_from, r.work_to],
    ];
    for (const [label, a, b] of pairs) {
      if ((a && !b) || (!a && b)) return `${label}: fill both From and To.`;
      if (a && toMin(a) == null) return `${label}: invalid time in From.`;
      if (b && toMin(b) == null) return `${label}: invalid time in To.`;
    }

    for (const b of (r.blocks || [])) {
      if ((b.from && !b.to) || (!b.from && b.to)) return `Block "${b.name || "Unnamed"}": fill both From and To.`;
      if (b.from && toMin(b.from) == null) return `Block "${b.name || "Unnamed"}": invalid From time.`;
      if (b.to && toMin(b.to) == null) return `Block "${b.name || "Unnamed"}": invalid To time.`;
    }

    return null;
  }

  async function loadPrayerWindows() {
    const res = await fetch(NAMAZ_API_URL, {
      method: "GET",
      headers: { "Accept": "application/json" },
      credentials: "include",
    });
    const data = await res.json().catch(() => null);

    if (!res.ok || !data || data.ok === false) {
      throw new Error(data?.error || `Prayer API failed (${res.status})`);
    }

    const t = data.timings || {};
    PRAYER_WINDOWS = {
      Fajr:    { start: isoToHHMM(t.Fajr),    end: isoToHHMM(t.Sunrise) },
      Dhuhr:   { start: isoToHHMM(t.Dhuhr),   end: isoToHHMM(t.Asr) },
      Asr:     { start: isoToHHMM(t.Asr),     end: isoToHHMM(t.Maghrib || t.Sunset) },
      Maghrib: { start: isoToHHMM(t.Maghrib), end: isoToHHMM(t.Isha) },
      Isha:    { start: isoToHHMM(t.Isha),    end: isoToHHMM(t.Midnight || "24:00") },
    };

    return data;
  }

  function buildSuggestions(routine) {
    if (!PRAYER_WINDOWS || !routine) return {};

    const busy = routineToBusy(routine);
    const suggestions = {};

    for (const [prayer, w] of Object.entries(PRAYER_WINDOWS)) {
      const ws = toMin(w.start);
      const we = toMin(w.end);

      if (ws == null || we == null) {
        suggestions[prayer] = { ok: false, text: "No timing available" };
        continue;
      }

      const slot = findEarliestSlot(ws, we, busy);
      suggestions[prayer] = slot == null
        ? { ok: false, text: `No free time between ${fmt(ws)} – ${fmt(we)}` }
        : { ok: true,  text: `Suggested at ${fmt(slot)} (within ${fmt(ws)} – ${fmt(we)})` };
    }

    return suggestions;
  }

  async function loadRoutineFromServer() {
    const res = await fetch(ROUTINE_API_URL, {
      method: "GET",
      headers: { "Accept": "application/json" },
      credentials: "include",
    });
    const data = await res.json().catch(() => null);

    if (!res.ok || !data || data.ok === false) {
      throw new Error(data?.error || `Routine load failed (${res.status})`);
    }

    if (!data.exists) return null;
    return data.routine || null;
  }

  async function saveRoutineToServer(routine) {
    const res = await fetch(ROUTINE_API_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json", "Accept": "application/json" },
      body: JSON.stringify(routine),
      credentials: "include",
    });

    const data = await res.json().catch(() => null);
    if (!res.ok || !data || data.ok === false) {
      throw new Error(data?.error || `Save failed (${res.status})`);
    }
    return data;
  }


  document.addEventListener("DOMContentLoaded", async () => {
    const addBtn = $("add_block_btn");
    if (addBtn) {
      addBtn.addEventListener("click", () => {
        const container = $("blocks_container");
        if (!container) return;

        const row = document.createElement("div");
        row.className = "rp_blockRow";
        row.innerHTML = `
          <input class="rp_inp" type="text" placeholder="Block name">
          <input class="rp_inp" type="time">
          <input class="rp_inp" type="time">
          <button class="rp_removeBtn" type="button">×</button>
        `;
        row.querySelector(".rp_removeBtn").addEventListener("click", () => row.remove());
        container.appendChild(row);
      });
    }

    const saveBtn = $("save_routine_btn");
    if (saveBtn) {
      saveBtn.addEventListener("click", async () => {
        setMsg("");

        const routine = readRoutineInputs();
        const err = validateRoutine(routine);
        if (err) {
          setMsg(err, true);
          return;
        }

        try {
          saveBtn.disabled = true;
          const oldText = saveBtn.textContent;
          saveBtn.textContent = "Saving...";

          const out = await saveRoutineToServer(routine);

          SAVED_ROUTINE = routine;
          setRoutineMode(true);

          renderSuggestionsOnPage(buildSuggestions(SAVED_ROUTINE));

          setMsg(out.message || "Routine saved", false);
          saveBtn.textContent = oldText;

        } catch (e) {
          setMsg(e.message || "Could not save routine.", true);
        } finally {
          saveBtn.disabled = false;
        }
      });
    }

    try {
      await loadPrayerWindows();
      SAVED_ROUTINE = await loadRoutineFromServer();

      if (!SAVED_ROUTINE) {
        setRoutineMode(false);
        return;
      }

      applyRoutineToForm(SAVED_ROUTINE);
      setRoutineMode(true);
      renderSuggestionsOnPage(buildSuggestions(SAVED_ROUTINE));

    } catch (e) {
      setMsg(e.message || "Initialization failed.", true);
    }
  });

})();
