/* =========================================================
   MATTA — Dashboard Versi 3
   Memakai helper dari app.js (window.MATTA): t, tf, toast, confirm,
   openModal, closeModal, validateForm, openDetail, nowWIB, pad, store, getLang.
   ========================================================= */
(function () {
  "use strict";
  document.addEventListener("DOMContentLoaded", () => {
    const root = document.querySelector("[data-page='dashboard-v3']");
    if (!root || !window.MATTA) return;
    const M = window.MATTA, T = M.t, TF = M.tf, pad = M.pad, store = M.store;
    const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const esc = (v) => String(v ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
    const DATA = JSON.parse($("#v3-data").textContent);
    const lang = () => M.getLang();
    const now = () => M.nowWIB();

    const DAYS = { en: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"], id: ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"] };
    const DSHORT = { en: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"], id: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"] };
    const MONTHS = { en: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"], id: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"] };
    const GREET = { en: ["Good Morning", "Good Afternoon", "Good Evening"], id: ["Selamat Pagi", "Selamat Siang", "Selamat Malam"] };

    /* ---------------- Absensi (aturan sama dengan Versi 1/2) ---------------- */
    const RULE = { start: 8 * 60 + 30, tolerance: 9 * 60, duration: 9 * 60 };
    const key = () => { const n = now(); return "matta.att." + n.getFullYear() + pad(n.getMonth() + 1) + pad(n.getDate()); };
    let att = store.get(key(), null) || { in: null, out: null, early: false };
    const hm = (t) => { const d = new Date(t); return pad(d.getHours()) + ":" + pad(d.getMinutes()); };
    const minOf = (t) => { const d = new Date(t); return d.getHours() * 60 + d.getMinutes(); };
    const dur = (ms) => { const m = Math.max(0, Math.floor(ms / 60000)); return Math.floor(m / 60) + T("j") + " " + pad(m % 60) + T("m"); };
    const hms = (ms) => { const s = Math.max(0, Math.floor(ms / 1000)); return pad(Math.floor(s / 3600)) + ":" + pad(Math.floor(s / 60) % 60) + ":" + pad(s % 60); };
    const outAt = () => att.in + RULE.duration * 60000;
    const status = () => {
      const m = minOf(att.in);
      if (m <= RULE.start) return { cls: "badge-green", text: "ON-TIME", k: "ok" };
      if (m <= RULE.tolerance) return { cls: "badge-amber", text: T("TOLERANSI"), k: "tol" };
      return { cls: "badge-red", text: "LATE", k: "late" };
    };
    const ICO = {
      in: '<svg class="icon icon-md" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>',
      out: '<svg class="icon icon-md" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>',
      lock: '<svg class="icon icon-md" viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
      done: '<svg class="icon icon-md" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
      chev: '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>',
      check: '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
      x: '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
      arrow: '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>'
    };
    const card = $(".v3-att", root), cta = $("[data-v3-cta]"), early = $("[data-v3-early]");

    function renderHead() {
      const n = now(), h = n.getHours(), L = lang();
      $("[data-v3-greeting]").textContent = GREET[L][h < 12 ? 0 : h < 18 ? 1 : 2];
      $("[data-v3-date]").textContent = DAYS[L][n.getDay()] + ", " + n.getDate() + " " + MONTHS[L][n.getMonth()] + " " + n.getFullYear();
      $("[data-v3-time]").textContent = pad(h) + ":" + pad(n.getMinutes());
    }

    function renderAttend() {
      const t = now().getTime();
      const title = $("[data-v3-status-title]"), sub = $("[data-v3-status-sub]"), badge = $("[data-v3-badge]"), lbl = $("[data-v3-cta-label]");
      card.classList.remove("st-pending", "st-in", "st-done", "st-late", "st-tol");
      early.hidden = true;
      let state;
      if (!att.in) {
        state = "in";
        card.classList.add("st-pending");
        title.textContent = T("Belum Absen Masuk");
        badge.className = "badge badge-gray"; badge.textContent = T("BELUM");
        sub.hidden = false; sub.innerHTML = T("Masuk") + " <b>08:30</b>";
        cta.className = "btn btn-lg v3-cta in"; cta.disabled = false;
        cta.firstElementChild.outerHTML = ICO.in; lbl.textContent = T("Clock In");
      } else {
        const st = status();
        badge.className = "badge " + st.cls; badge.textContent = st.text;
        if (st.k === "late") card.classList.add("st-late");
        if (st.k === "tol") card.classList.add("st-tol");
        if (att.out) {
          state = "done";
          card.classList.add("st-done");
          title.textContent = hm(att.in) + " – " + hm(att.out);
          sub.hidden = true;
          cta.className = "btn btn-lg v3-cta done"; cta.disabled = true;
          cta.firstElementChild.outerHTML = ICO.done; lbl.textContent = T("Selesai hari ini");
        } else {
          card.classList.add("st-in");
          title.textContent = T("Masuk") + " " + hm(att.in);
          const allowed = outAt();
          sub.hidden = true;
          if (t >= allowed) {
            state = "out";
            cta.className = "btn btn-lg v3-cta out"; cta.disabled = false;
            cta.firstElementChild.outerHTML = ICO.out; lbl.textContent = T("Clock Out");
          } else {
            state = "locked";
            cta.className = "btn btn-lg v3-cta locked"; cta.disabled = true;
            cta.firstElementChild.outerHTML = ICO.lock; lbl.textContent = T("Clock Out") + " " + hm(allowed);
            early.hidden = false;
          }
        }
      }
      cta.dataset.state = state;
      // jam kerja
      const end = att.out || (att.in ? t : 0), ms = att.in ? Math.max(0, end - att.in) : 0, target = RULE.duration * 60000;
      $("[data-v3-worked]").textContent = hms(ms);
      const bar = $("[data-v3-worked-bar]");
      bar.style.width = Math.min(100, (ms / target) * 100) + "%";
      bar.classList.toggle("done", ms >= target);
      const ws = $("[data-v3-worked-sub]");
      ws.hidden = ms < target;
      ws.innerHTML = ms >= target ? '<span class="ok">✓ ' + T("Target 9 jam tercapai") + "</span>" : "";
      renderTimeline(t);
    }

    function renderTimeline(t) {
      const box = $("[data-v3-timeline]");
      const nowM = minOf(t), inM = att.in ? minOf(att.in) : null, outAllowM = att.in ? minOf(outAt()) : RULE.start + RULE.duration;
      const outM = att.out ? minOf(att.out) : null;
      let rs = Math.min(8 * 60, inM ?? 8 * 60, nowM) - 30, re = Math.max(18 * 60, outAllowM, outM ?? 0, nowM) + 30;
      rs = Math.max(0, Math.floor(rs / 60) * 60); re = Math.min(24 * 60, Math.ceil(re / 60) * 60);
      const pos = (m) => ((Math.min(re, Math.max(rs, m)) - rs) / (re - rs)) * 100;
      const L = (m) => pad(Math.floor(m / 60) % 24) + ":" + pad(m % 60);
      let ticks = "";
      // skala jam dihilangkan agar ringkas
      let fill = "";
      if (inM !== null) {
        const endM = outM ?? nowM;
        fill = '<span class="tl-fill' + (outM !== null ? " done" : "") + '" style="left:' + pos(inM) + "%;width:" + (pos(endM) - pos(inM)) + '%"></span>';
      }
      const flag = (m, cls, label) => '<span class="tl-flag ' + cls + '" style="left:' + pos(m) + '%"><i></i><em>' + label + "</em></span>";
      const dot = (m, cls, label) => '<span class="tl-dot ' + cls + '" style="left:' + pos(m) + '%" title="' + esc(label) + '"><i></i></span>';
      box.innerHTML =
        '<div class="tl-track">' + '<span class="tl-zone" style="left:' + pos(RULE.start) + "%;width:" + (pos(RULE.tolerance) - pos(RULE.start)) + '%"></span>' + fill +
        flag(RULE.start, "start", "08:30") + flag(RULE.tolerance, "tol", "09:00") +
        flag(outAllowM, "end", L(outAllowM)) +
        (inM !== null ? dot(inM, "in " + (inM > RULE.tolerance ? "late" : inM > RULE.start ? "tol" : "ok"), T("Masuk") + " " + L(inM)) : "") +
        (outM !== null ? dot(outM, "out", T("Pulang") + " " + L(outM)) : dot(nowM, "now", T("Sekarang"))) +
        "</div>";
    }

    cta.addEventListener("click", () => {
      const st = cta.dataset.state;
      if (st === "in") {
        att = { in: now().getTime(), out: null, early: false }; store.set(key(), att); renderAttend(); renderCal(); renderStats();
        const s = status();
        M.toast(T("Absen masuk tercatat pukul") + " " + hm(att.in) + " WIB" + (s.k === "late" ? " — " + T("terlambat, melewati batas toleransi 09:00") : s.k === "tol" ? " (" + T("masih dalam toleransi") + ")" : "") + ". " + T("Clock Out mulai") + " " + hm(outAt()) + " WIB.", s.k === "late" ? "error" : "success");
      } else if (st === "out") {
        M.confirm(T("Absen pulang sekarang?"), T("Jam pulang akan dicatat pukul") + " " + hm(now().getTime()) + " WIB.", T("Clock Out")).then((ok) => {
          if (!ok) return; att.out = now().getTime(); store.set(key(), att); renderAttend(); M.toast(T("Absen pulang tercatat. Sampai jumpa besok!"));
        });
      }
    });

    /* ---------------- Kalender + ringkasan ---------------- */
    const ABS = (M.attendanceRule && M.attendanceRule.absences) || [];
    const today0 = (() => { const n = now(); return new Date(n.getFullYear(), n.getMonth(), n.getDate()); })();
    let view = new Date(today0.getFullYear(), today0.getMonth(), 1);
    const iso = (d) => d.getFullYear() + "-" + pad(d.getMonth() + 1) + "-" + pad(d.getDate());
    function dayInfo(d) {
      const k = iso(d), dow = d.getDay();
      if (DATA.holidays[k]) return { s: "holiday", note: DATA.holidays[k] };
      if (dow === 0 || dow === 6) return { s: "weekend" };
      if (ABS.includes(k)) return { s: "absent" };
      if (DATA.calendar[k]) return DATA.calendar[k];
      if (k === iso(today0)) return att.in ? { s: minOf(att.in) > RULE.start ? "late" : "present", in: hm(att.in), out: att.out ? hm(att.out) : null, today: true } : { s: "today" };
      return { s: d < today0 ? "nodata" : "future" };
    }
    function renderCal() {
      const y = view.getFullYear(), m = view.getMonth(), L = lang();
      $("[data-v3-cal-title]").textContent = MONTHS[L][m] + " " + y;
      const first = new Date(y, m, 1).getDay(), days = new Date(y, m + 1, 0).getDate(), prev = new Date(y, m, 0).getDate();
      let html = "";
      for (let i = first - 1; i >= 0; i--) html += '<div class="cal-day other">' + (prev - i) + "</div>";
      const sum = { present: 0, late: 0, absent: 0, leave: 0, work: 0 };
      for (let d = 1; d <= days; d++) {
        const date = new Date(y, m, d), info = dayInfo(date), cls = ["cal-day", "v3d", "s-" + info.s];
        if (iso(date) === iso(today0)) cls.push("today");
        if (date < today0 && info.s !== "weekend" && info.s !== "holiday") sum.work++;
        if (date <= today0) {
          if (info.s === "present") sum.present++;
          if (info.s === "late") { sum.present++; sum.late++; }
          if (info.s === "absent") sum.absent++;
          if (info.s === "leave" || info.s === "sick") sum.leave++;
        }
        const clickable = !["weekend", "future"].includes(info.s);
        html += (clickable ? '<button type="button"' : "<div") + ' class="' + cls.join(" ") + '" data-date="' + iso(date) + '">' + d + (clickable ? "</button>" : "</div>");
      }
      const cells = first + days;
      for (let i = 1; cells + i - 1 < Math.ceil(cells / 7) * 7; i++) html += '<div class="cal-day other">' + i + "</div>";
      $("[data-v3-cal-grid]").innerHTML = html;
      $("[data-v3-cal-pop]").hidden = true;
      const presentNoToday = sum.present - (att.in && view.getMonth() === today0.getMonth() ? 1 : 0);
      $("[data-v3-cal-sum]").innerHTML =
        '<span class="cs present"><b>' + sum.present + "</b>" + T("Hadir") + "</span>" +
        '<span class="cs late"><b>' + sum.late + "</b>" + T("Toleransi") + "</span>" +
        '<span class="cs absent"><b>' + sum.absent + "</b>" + T("Tanpa Ket.") + "</span>" +
        '<span class="cs leave"><b>' + sum.leave + "</b>" + T("Cuti/Sakit") + "</span>";
      if (view.getMonth() === today0.getMonth() && view.getFullYear() === today0.getFullYear()) {
        CAL_SUM = { present: presentNoToday, leave: sum.leave, work: sum.work };
      }
    }
    let CAL_SUM = { present: 0, leave: 0, work: 0 };
    $("[data-v3-cal-prev]").addEventListener("click", () => { view.setMonth(view.getMonth() - 1); renderCal(); });
    $("[data-v3-cal-next]").addEventListener("click", () => { view.setMonth(view.getMonth() + 1); renderCal(); });
    const pop = $("[data-v3-cal-pop]");
    $("[data-v3-cal-grid]").addEventListener("click", (e) => {
      const b = e.target.closest("button[data-date]");
      if (!b) return;
      if (pop.dataset.for === b.dataset.date && !pop.hidden) { pop.hidden = true; return; }
      const [y, mo, d] = b.dataset.date.split("-").map(Number), date = new Date(y, mo - 1, d), info = dayInfo(date), L = lang();
      const head = DSHORT[L][date.getDay()] + ", " + d + " " + MONTHS[L][mo - 1].slice(0, 3) + " " + y;
      const label = { present: T("Hadir"), late: T("Toleransi"), absent: T("Tanpa Keterangan"), leave: T("Cuti"), sick: T("Izin Sakit"), holiday: T("Libur"), today: T("Hari Ini"), nodata: T("Tidak ada data") }[info.s] || "";
      let body = "";
      if (info.in) {
        const [ih, im] = info.in.split(":").map(Number);
        const outTxt = info.out || "—";
        let total = "";
        if (info.out) { const [oh, om] = info.out.split(":").map(Number); total = dur(((oh * 60 + om) - (ih * 60 + im)) * 60000); }
        body = '<div class="pop-row"><span>' + T("Masuk") + "</span><b>" + info.in + '</b></div><div class="pop-row"><span>' + T("Pulang") + "</span><b>" + outTxt + "</b></div>" + (total ? '<div class="pop-row"><span>' + T("Total") + "</span><b>" + total + "</b></div>" : "");
      } else if (info.s === "absent") {
        body = '<p class="pop-note">' + T("Belum ada keterangan untuk tanggal ini.") + '</p><a class="btn btn-danger btn-xs" href="form-permit.html">' + T("Lengkapi Keterangan") + "</a>";
      } else if (info.note) {
        body = '<p class="pop-note">' + esc(T(info.note)) + "</p>";
      } else if (info.s === "today") {
        body = '<p class="pop-note">' + T("Anda belum Clock In hari ini.") + "</p>";
      }
      pop.innerHTML = '<div class="pop-head"><b>' + head + '</b><span class="pop-tag s-' + info.s + '">' + label + "</span></div>" + body;
      pop.dataset.for = b.dataset.date;
      pop.hidden = false;
      const pr = pop.offsetParent.getBoundingClientRect(), br = b.getBoundingClientRect();
      pop.style.left = Math.min(Math.max(br.left - pr.left + br.width / 2 - 110, 0), pr.width - 220) + "px";
      pop.style.top = (br.bottom - pr.top + 6) + "px";
    });
    document.addEventListener("click", (e) => { if (!e.target.closest("[data-v3-cal-pop], [data-v3-cal-grid]")) pop.hidden = true; });

    /* ---------------- Pengajuan & persetujuan ---------------- */
    const MODS = DATA.modules, ICONS = DATA.icons;
    const extraReq = () => store.get("matta.v3.req", []);
    const decided = () => store.get("matta.v3.decided", {});
    const allReq = () => extraReq().concat(DATA.requests);
    const STATUS = {
      waiting: ["Menunggu", "badge-orange"], process: ["Diproses", "badge-blue"], approved: ["Disetujui", "badge-green"], rejected: ["Ditolak", "badge-red"]
    };
    let tab = "mine", filter = "all";
    // tanggal ISO (YYYY-MM-DD) → "15 Okt 2026" / "15 Oct 2026"
    const fmtDate = (v) => { const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(v || ""); return m ? (+m[3]) + " " + MONTHS[lang()][+m[2] - 1].slice(0, 3) + " " + m[1] : v; };
    const tNote = (s) => String(s).replace(/(\d) jam\b/g, "$1 " + T("hrs")).replace(/(\d) hari\b/g, "$1 " + T("hari"));
    const ico = (mod) => '<span class="v3-row-ico" style="--ic:' + MODS[mod].ic + ";--ib:" + MODS[mod].ib + '">' + ICONS[mod] + "</span>";

    function reqDetail(r, approver) {
      const st = r.status || "waiting";
      const done = st === "approved" || st === "rejected";
      return {
        eyebrow: MODS[r.mod].name, title: r.id, module: r.mod, img: "assets/img/modules/" + r.mod + ".png",
        badges: [[STATUS[st][0], { waiting: "orange", process: "blue", approved: "green", rejected: "red" }[st]]],
        person: approver ? { name: r.name, nip: r.nip, role: r.role } : { name: "M. Daffa Dzaky Pratama", nip: "P0826155", role: "Frontend Developer" },
        sections: [{ h: "Detail Pengajuan", rows: [["ID Formulir", r.id], ["Jenis", r.title], ["Keterangan", tNote(r.note)], ["Tanggal", fmtDate(r.date)]] }],
        timeline: [
          { t: "Diajukan", by: approver ? r.name : "M. Daffa Dzaky Pratama", at: fmtDate(r.date), state: "done" },
          { t: "Persetujuan Atasan", by: approver ? "M. Daffa Dzaky Pratama" : (r.approver || "Muhammad Rizky"), at: st === "waiting" ? "" : fmtDate(r.date), state: st === "waiting" ? "current" : "done" },
          { t: "Verifikasi Human Capital", by: "Dewi Kaniawati", at: "", state: done ? "done" : st === "process" ? "current" : "todo" }
        ]
      };
    }

    function renderList() {
      const list = $("[data-v3-list]");
      $("[data-v3-filters]").hidden = tab !== "mine";
      const dec = decided();
      const pendingApprovals = DATA.approvals.filter((a) => !dec[a.id]);
      $("[data-v3-approve-count]").textContent = pendingApprovals.length;
      $("[data-v3-approve-count]").hidden = !pendingApprovals.length;
      let html = "";
      if (tab === "mine") {
        const rows = allReq().filter((r) => filter === "all" || (filter === "open" ? ["waiting", "process"].includes(r.status) : ["approved", "rejected"].includes(r.status)));
        html = rows.map((r, i) =>
          '<li style="--i:' + i + '"><button type="button" class="v3-row" data-req="' + esc(r.id) + '">' + ico(r.mod) +
          '<span class="v3-row-main"><b>' + esc(T(r.title)) + ' <span class="v3-row-note">· ' + esc(tNote(T(r.note))) + "</span></b><span>" + esc(r.id) + " · " + esc(fmtDate(r.date)) + "</span></span>" +
          (["waiting", "process"].includes(r.status) ? '<span class="v3-row-flow">' + esc(T(r.stage)) + "</span>" : "") +
          '<span class="badge badge-pill ' + STATUS[r.status][1] + '">' + T(STATUS[r.status][0]) + "</span>" + ICO.chev + "</button></li>").join("");
        if (!rows.length) html = '<li class="v3-empty">' + T("Tidak ada pengajuan.") + "</li>";
      } else {
        html = DATA.approvals.map((a, i) => {
          const d = dec[a.id];
          return '<li style="--i:' + i + '"><div class="v3-row approve' + (d ? " decided" : "") + '" data-appr="' + esc(a.id) + '" tabindex="0">' + ico(a.mod) +
            '<span class="v3-row-main"><b>' + esc(a.name) + ' <span class="v3-row-note">· ' + esc(T(a.title)) + "</span></b><span>" + esc(tNote(T(a.note))) + " · " + esc(fmtDate(a.date)) + "</span></span>" +
            (d ? '<span class="badge badge-pill ' + (d === "approved" ? "badge-green" : "badge-red") + '">' + T(d === "approved" ? "Disetujui" : "Ditolak") + "</span>"
               : '<span class="v3-appr-actions"><button type="button" class="btn btn-xs btn-outline v3-reject" data-decide="rejected">' + ICO.x + T("Tolak") + '</button><button type="button" class="btn btn-xs v3-approve" data-decide="approved">' + ICO.check + T("Setujui") + "</button></span>") +
            "</div></li>";
        }).join("");
      }
      list.innerHTML = html;
    }
    $("[data-v3-tabs]").addEventListener("click", (e) => {
      const b = e.target.closest("[data-v3-tab]"); if (!b) return;
      tab = b.dataset.v3Tab;
      $$("[data-v3-tab]").forEach((x) => x.setAttribute("aria-selected", String(x === b)));
      renderList();
    });
    $("[data-v3-filters]").addEventListener("click", (e) => {
      const b = e.target.closest("[data-v3-filter]"); if (!b) return;
      filter = b.dataset.v3Filter;
      $$("[data-v3-filter]").forEach((x) => x.setAttribute("aria-pressed", String(x === b)));
      renderList();
    });
    $("[data-v3-list]").addEventListener("click", (e) => {
      const dec = e.target.closest("[data-decide]");
      if (dec) {
        const row = dec.closest("[data-appr]"), a = DATA.approvals.find((x) => x.id === row.dataset.appr), ok = dec.dataset.decide === "approved";
        M.confirm(ok ? T("Setujui pengajuan ini?") : T("Tolak pengajuan ini?"), a.name + " · " + T(a.title) + " · " + tNote(a.note), ok ? T("Setujui") : T("Tolak"), !ok).then((yes) => {
          if (!yes) return;
          const d = decided(); d[a.id] = dec.dataset.decide; store.set("matta.v3.decided", d);
          renderList();
          M.toast((ok ? T("Pengajuan disetujui") : T("Pengajuan ditolak")) + ": " + a.name + " · " + T(a.title), ok ? "success" : "error");
        });
        return;
      }
      const r = e.target.closest("[data-req]");
      if (r) { const req = allReq().find((x) => x.id === r.dataset.req); req && M.openDetail(reqDetail(req, false)); return; }
      const ap = e.target.closest("[data-appr]");
      if (ap) { const a = DATA.approvals.find((x) => x.id === ap.dataset.appr); a && M.openDetail(reqDetail(Object.assign({ status: decided()[a.id] || "waiting" }, a), true)); }
    });
    $("[data-v3-list]").addEventListener("keydown", (e) => { if ((e.key === "Enter" || e.key === " ") && e.target.matches("[data-appr]")) { e.preventDefault(); e.target.click(); } });

    /* ---------------- Statistik ---------------- */
    function renderStats() {
      const pend = allReq().filter((r) => ["waiting", "process"].includes(r.status));
      $('[data-v3-stat="pending"]').textContent = pend.length;
      const byMod = {};
      pend.forEach((r) => { byMod[r.mod] = (byMod[r.mod] || 0) + 1; });
      $('[data-v3-stat-sub="pending"]').textContent = pend.length ? Object.keys(byMod).map((k) => byMod[k] + " " + T(MODS[k].short).toLowerCase()).join(" · ") : T("Tidak ada yang menunggu");
      $$("[data-v3-qbadge]").forEach((b) => { const n = byMod[b.dataset.v3Qbadge] || 0; b.hidden = !n; b.textContent = n; b.title = n + " " + T("menunggu"); });
      const lv = DATA.leave, left = lv.total - lv.used;
      $('[data-v3-stat="leave"]').innerHTML = left + " <small>" + T("hari") + "</small>";
      $('[data-v3-stat-sub="leave"]').textContent = TF("dari {total} hari · terpakai {used}", lv);
      $('[data-v3-stat="overtime"]').innerHTML = DATA.overtime.hours.toFixed(1).replace(".", lang() === "id" ? "," : ".") + " <small>" + T("hrs") + "</small>";
      $('[data-v3-stat-sub="overtime"]').textContent = TF("dari {count} pengajuan", DATA.overtime);
      const work = CAL_SUM.work || 1, ok = CAL_SUM.present + CAL_SUM.leave, pct = Math.round((ok / work) * 100);
      $('[data-v3-stat="attendance"]').innerHTML = pct + "<small>%</small>";
      $('[data-v3-stat-sub="attendance"]').textContent = TF("{ok} dari {work} hari kerja", { ok: ok, work: work });
      const bar = $('[data-v3-bar="attendance"]'); if (bar) bar.style.width = pct + "%";
      const lbar = $('[data-v3-bar="leave"]'); if (lbar) lbar.style.width = Math.round((left / lv.total) * 100) + "%";
    }

    /* ---------------- To-do hari ini ---------------- */
    function renderTodo() {
      const n = now();
      const healthDone = !!store.get("matta.health." + n.toDateString(), null);
      const items = [
        { done: healthDone, title: T("Formulir kesehatan harian"), sub: healthDone ? T("Sudah diisi hari ini") : T("Isi sebelum pukul 09:00 WIB"), href: "health.html", cta: healthDone ? T("Lihat") : T("Isi sekarang") },
        { done: !ABS.length, warn: ABS.length > 0, title: TF("Lengkapi {n} keterangan absen", { n: ABS.length }), sub: T("Tanpa keterangan bulan ini"), href: "form-permit.html", cta: T("Lengkapi") },
        { done: false, info: true, title: TF("Slip gaji {m} tersedia", { m: MONTHS[lang()][9] + " 2026" }), sub: T("Unduh dalam format PDF"), href: "payroll-slip.html", cta: T("Unduh") }
      ];
      $("[data-v3-todo]").innerHTML = items.map((it) =>
        '<li class="' + (it.done ? "done" : it.warn ? "warn" : it.info ? "info" : "") + '"><span class="td-check">' + (it.done ? ICO.check : "") + '</span><span class="td-main"><b>' + esc(it.title) + "</b><span>" + esc(it.sub) + '</span></span><a class="td-cta" href="' + it.href + '">' + esc(it.cta) + " " + ICO.arrow + "</a></li>").join("");
    }

    /* ---------------- Ajukan cepat ---------------- */
    const form = $("#form-v3-apply");
    let applyMod = null;
    root.addEventListener("click", (e) => {
      const b = e.target.closest("[data-v3-apply]"); if (!b) return;
      applyMod = b.dataset.v3Apply;
      if (applyMod === "exit-clearance") {
        window.location.href = "form-exit-clearance.html?open=statement";
        return;
      }
      const mod = MODS[applyMod];
      $("[data-v3-apply-title]").textContent = T(mod.apply);
      const icoBox = $("[data-v3-apply-ico]"); icoBox.innerHTML = ICONS[applyMod]; icoBox.style.setProperty("--ic", mod.ic); icoBox.style.setProperty("--ib", mod.ib);
      form.reset();
      M.useFieldset(form, applyMod);
      const dd = $("#v3-more-menu"); dd && dd.classList.remove("open");
      M.openModal("modal-v3-apply");
    });
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      if (!M.validateForm(form)) return;
      const mod = MODS[applyMod], fd = new FormData(form), n = now();
      const s = new Date(fd.get("start"));
      const list = extraReq();
      list.unshift({ id: "#" + mod.prefix + "-" + n.getFullYear() + "-" + String(Math.floor(Math.random() * 9000) + 1000), mod: applyMod, title: mod.short,
        note: String(fd.get("reason") || fd.get("note") || "").slice(0, 60), date: s.getFullYear() + "-" + pad(s.getMonth() + 1) + "-" + pad(s.getDate()), status: "waiting", stage: "Menunggu Muhammad Rizky", approver: "Muhammad Rizky" });
      store.set("matta.v3.req", list);
      M.closeModal("modal-v3-apply");
      tab = "mine"; filter = "all";
      $$("[data-v3-tab]").forEach((x) => x.setAttribute("aria-selected", String(x.dataset.v3Tab === "mine")));
      $$("[data-v3-filter]").forEach((x) => x.setAttribute("aria-pressed", String(x.dataset.v3Filter === "all")));
      renderList(); renderStats();
      const first = $("[data-v3-list] li"); first && first.classList.add("row-new");
      M.toast(T(mod.short) + " " + T("berhasil diajukan dan menunggu persetujuan."));
    });

    /* ---------------- Init & refresh ---------------- */
    function renderNewsDates() { $$("[data-v3-iso]").forEach((el) => { const m = el.dataset.v3Iso.split("-"); el.textContent = (+m[2]) + " " + MONTHS[lang()][+m[1] - 1] + " " + m[0]; }); }
    function all() { renderNewsDates(); renderHead(); renderAttend(); renderCal(); renderStats(); renderList(); renderTodo(); }
    all();
    setInterval(() => { renderHead(); renderAttend(); }, 1000);
    document.addEventListener("matta:lang", all);
  });
})();
