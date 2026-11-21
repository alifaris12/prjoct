/* =========================================================
   MATTA — Enterprise Workplace
   Interaksi frontend (vanilla JS, tanpa library)
   ========================================================= */
(function () {
  "use strict";

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const store = {
    get(key, fallback) { try { const v = localStorage.getItem(key); return v === null ? fallback : JSON.parse(v); } catch (e) { return fallback; } },
    set(key, value) { try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { /* storage tidak tersedia */ } }
  };
  const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const ICON = {
    check: '<svg class="icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
    alert: '<svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
    eye: '<svg class="icon" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
    link: '<svg class="icon icon-xs" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',
    left: '<svg class="icon icon-xs" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>',
    right: '<svg class="icon icon-xs" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>'
  };

  /* ---------------- Toast (Pop-up Kanan Atas) ---------------- */
  function toast(message, type = "success") {
    let stack = $(".toast-stack");
    if (!stack) {
      stack = document.createElement("div");
      stack.className = "toast-stack";
      stack.setAttribute("aria-live", "polite");
      document.body.appendChild(stack);
    }
    const el = document.createElement("div");
    const isError = type === "error";
    el.className = "toast " + (isError ? "error" : "success");

    const titleText = isError ? T("Pemberitahuan") : T("Berhasil");
    const iconSvg = isError
      ? '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
      : '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';

    el.innerHTML =
      '<div class="toast-ico ' + (isError ? "error" : "success") + '">' + iconSvg + '</div>' +
      '<div class="toast-body">' +
        '<div class="toast-title">' + esc(titleText) + '</div>' +
        '<div class="toast-text">' + esc(message) + '</div>' +
      '</div>' +
      '<button type="button" class="toast-close" aria-label="' + T("Tutup") + '">' +
        '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
      '</button>' +
      '<div class="toast-progress"><div class="toast-progress-bar"></div></div>';

    const dismiss = () => {
      if (el._dismissed) return;
      el._dismissed = true;
      el.classList.add("hide");
      setTimeout(() => el.remove(), 230);
    };

    const closeBtn = el.querySelector(".toast-close");
    if (closeBtn) closeBtn.addEventListener("click", dismiss);

    stack.appendChild(el);
    setTimeout(dismiss, 3600);
  }
  window.MATTA = { toast };


  /* ---------------- Aturan peringatan kehadiran ----------------
     SEMENTARA: peringatan muncul setelah 3x tidak hadir TANPA KETERANGAN dalam satu periode (bulan).
     Angka final menunggu konfirmasi Human Capital — cukup ubah warnThreshold.
     Daftar tanggal (absences) nantinya dikirim oleh backend. */
  const ATTENDANCE_RULE = {
    warnThreshold: 3,
    period: "Oktober 2026",
    absences: ["2026-10-02", "2026-10-06", "2026-10-16"]
  };
  const ABSENCES = ATTENDANCE_RULE.absences;
  window.MATTA.attendanceRule = ATTENDANCE_RULE;

  // T dengan isian: TF("Halo {n}", { n: 3 })
  function TF(str, vars) { return T(str).replace(/\{(\w+)\}/g, (_, k) => (vars && k in vars ? vars[k] : "")); }
  window.MATTA.tf = TF;

  function initAttendanceAlert() {
    const boxes = $$("[data-att-alert]");
    if (!boxes.length) return;
    const n = ABSENCES.length, max = ATTENDANCE_RULE.warnThreshold;
    const today = new Date().toDateString();
    if (!n || store.get("matta.attAlertDismissed", "") === today) return;
    const warn = n >= max;
    const onLog = !!$("[data-page='att-log']");
    function render() {
      const DOW = lang === "en" ? ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"] : ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
      const MON = lang === "en" ? ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"] : ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
      const label = (iso) => { const [y, m, d] = iso.split("-").map(Number); const dt = new Date(y, m - 1, d); return DOW[dt.getDay()] + ", " + pad(d) + " " + MON[m - 1]; };
      const meter = Array.from({ length: max }, (_, i) => '<i class="' + (i < n ? "on" : "") + '"></i>').join("");
      const v = { n: n, max: max, period: T(ATTENDANCE_RULE.period) };
      const html =
        '<span class="aa-ico"><svg class="icon icon-md" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>' +
        '<div style="min-width:0"><div class="aa-title">' + (warn ? TF("Peringatan Kehadiran — {n} kali tidak hadir tanpa keterangan", v) : TF("Perhatian: {n} kali tidak hadir tanpa keterangan", v)) + "</div>" +
        '<div class="aa-text">' + (warn
          ? TF("Pada periode {period} Anda telah mencapai batas {max} kali ketidakhadiran tanpa keterangan. Segera lengkapi keterangan (izin atau sakit) agar dapat ditinjau atasan dan tim Human Capital.", v)
          : TF("Peringatan resmi diberikan setelah {max} kali. Lengkapi keterangan agar tidak bertambah.", v)) + "</div>" +
        '<div class="aa-dates">' + ABSENCES.map((d) => '<span class="aa-date">' + label(d) + "</span>").join("") + "</div>" +
        '<div class="aa-meter">' + meter + "<span>&nbsp;" + n + " / " + max + "</span></div></div>" +
        '<div class="aa-actions"><a class="btn ' + (warn ? "btn-danger" : "btn-primary") + ' btn-sm" href="form-permit.html">' + T("Lengkapi Keterangan") + "</a>" +
        (onLog ? '<button type="button" class="btn btn-outline btn-sm" data-aa-history>' + T("Lihat Riwayat") + "</button>" : '<a class="btn btn-outline btn-sm" href="attendance-log.html#duration">' + T("Lihat Riwayat") + "</a>") + "</div>" +
        '<button type="button" class="icon-btn aa-close" aria-label="' + T("Tutup peringatan hari ini") + '"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>';
      const compact =
        '<span class="aa-ico sm"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>' +
        '<span class="aa-line"><b>' + (warn ? TF("{n}x tidak hadir tanpa keterangan bulan ini", v) : TF("{n}x tidak hadir tanpa keterangan", v)) + "</b>" +
        '<span class="aa-dates">' + ABSENCES.map((d) => '<span class="aa-date">' + label(d) + "</span>").join("") + "</span>" +
        '<span class="aa-meter">' + meter + "<span>&nbsp;" + n + " / " + max + "</span></span></span>" +
        '<a class="aa-cta" href="form-permit.html">' + T("Lengkapi Keterangan") + ' <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>' +
        '<button type="button" class="icon-btn aa-close" aria-label="' + T("Tutup peringatan hari ini") + '"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>';
      boxes.forEach((box) => {
        if (box.dataset.dismissed) return;
        const isCompact = box.dataset.variant === "compact";
        box.className = "att-alert " + (warn ? "warn" : "notice") + (isCompact ? " compact" : "");
        box.setAttribute("role", warn ? "alert" : "status");
        box.innerHTML = isCompact ? compact : html;
        box.hidden = false;
        $(".aa-close", box).addEventListener("click", () => { box.hidden = true; box.dataset.dismissed = "1"; store.set("matta.attAlertDismissed", today); });
        const h = $("[data-aa-history]", box);
        h && h.addEventListener("click", () => { const t = $("[aria-controls=panel-duration]"); t && t.click(); t && t.scrollIntoView({ behavior: "smooth", block: "center" }); });
      });
    }
    render();
    document.addEventListener("matta:lang", render);
    // Notifikasi popup sekali per hari di Dashboard saat batas tercapai
    if (warn && $("[data-page^='dashboard']") && store.get("matta.attPopup", "") !== today) {
      store.set("matta.attPopup", today);
      setTimeout(() => confirmDialog(T("Peringatan Kehadiran"), TF("Anda tercatat {n} kali tidak hadir tanpa keterangan pada periode {period} (batas: {max} kali). Lengkapi keterangan sekarang?", { n: n, max: max, period: T(ATTENDANCE_RULE.period) }), T("Lengkapi Keterangan"), true)
        .then((ok) => { if (ok) location.href = "form-permit.html"; }), 700);
    }
  }

  /* ---------------- Versi tampilan (V1 / V2) ---------------- */
  // Pilihan tampilan disimpan terpisah untuk Home (V1/V2/V3) dan Feeds (V1/V2)
  function initUiVersion() {
    const cur = document.body.dataset.ui, kind = document.body.dataset.uiKind;
    if (cur && kind) store.set("matta.ui." + kind, cur);
    const home = store.get("matta.ui.home", "v1"), feeds = store.get("matta.ui.feeds", "v1");
    const map = {};
    if (home !== "v1") map["index.html"] = home === "v2" ? "index-v2.html" : "index-v3.html";
    if (feeds === "v2") map["feeds.html"] = "feeds-v2.html";
    $$("a[href]").forEach((a) => { const h = a.getAttribute("href"); if (map[h] && !a.hasAttribute("data-ui-set")) a.setAttribute("href", map[h]); });
  }

  /* ---------------- Transisi & loading antar halaman ---------------- */
  function initTransitions() {
    document.body.classList.add("is-loading");
    setTimeout(() => document.body.classList.remove("is-loading"), 650);
    document.addEventListener("click", (e) => {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      const a = e.target.closest("a[href]");
      if (!a || (a.target && a.target !== "_self") || a.hasAttribute("download")) return;
      const href = a.getAttribute("href");
      if (!href || href.charAt(0) === "#" || /^(mailto|tel|javascript|https?):/i.test(href) || !/\.html(\?|#|$)/.test(href)) return;
      e.preventDefault();
      document.body.classList.add("page-leaving");
      setTimeout(() => { location.href = a.href; }, 220);
    });
    window.addEventListener("pageshow", () => document.body.classList.remove("page-leaving"));
  }

  /* ---------------- Sidebar ---------------- */
  function initSidebar() {
    const toggles = $$("[data-sidebar-toggle]");
    const overlay = $(".sidebar-overlay");
    const isMobile = () => window.matchMedia("(max-width: 768px)").matches;
    // Jangan auto-collapse pada load halaman (sidebar selalu terbuka default di desktop/laptop)
    document.body.classList.remove("sidebar-collapsed");
    toggles.forEach((toggle) => toggle.addEventListener("click", () => {
      if (isMobile()) {
        document.body.classList.toggle("sidebar-open");
      } else {
        const collapsed = document.body.classList.toggle("sidebar-collapsed");
        store.set("matta.sidebarCollapsed", collapsed);
      }
      const open = isMobile() ? document.body.classList.contains("sidebar-open") : !document.body.classList.contains("sidebar-collapsed");
      toggles.forEach((t) => t.setAttribute("aria-expanded", String(open)));
    }));
    overlay && overlay.addEventListener("click", () => document.body.classList.remove("sidebar-open"));

    // Submenu (Forms)
    $$("[data-submenu-toggle]").forEach((btn) => {
      const sub = document.getElementById(btn.getAttribute("aria-controls"));
      btn.addEventListener("click", () => {
        const open = btn.getAttribute("aria-expanded") !== "true";
        btn.setAttribute("aria-expanded", String(open));
        sub && sub.classList.toggle("open", open);
      });
    });
  }

  /* ---------------- Dropdown profil ---------------- */
  function initDropdowns() {
    $$("[data-dropdown]").forEach((trigger) => {
      const menu = document.getElementById(trigger.getAttribute("data-dropdown"));
      if (!menu) return;
      trigger.addEventListener("click", (e) => {
        e.stopPropagation();
        const open = !menu.classList.contains("open");
        $$(".dropdown.open").forEach((m) => m.classList.remove("open"));
        menu.classList.toggle("open", open);
        trigger.setAttribute("aria-expanded", String(open));
      });
    });
    document.addEventListener("click", (e) => {
      $$(".dropdown.open").forEach((m) => {
        if (!m.contains(e.target)) {
          m.classList.remove("open");
          $$('[data-dropdown="' + m.id + '"]').forEach((t) => t.setAttribute("aria-expanded", "false"));
        }
      });
    });
    $$("[data-action='logout']").forEach((b) => b.addEventListener("click", () => {
      confirmDialog(T("Keluar dari MATTA?"), T("Sesi Anda akan diakhiri. (Demo frontend: tidak ada proses logout sungguhan.)"), T("Keluar"))
        .then((ok) => ok && toast("Anda telah keluar (simulasi)."));
    }));
  }

  /* ---------------- Bahasa (EN / ID) ----------------
     Kamus ada di assets/js/i18n.js: { "teks sumber": ["English", "Indonesia"] }.
     - Teks statis di HTML diterjemahkan otomatis (text node, placeholder, title, aria-label).
     - Teks yang dibuat JS ikut diterjemahkan lewat MutationObserver, atau memakai T("...") untuk kalimat dengan angka/waktu. */
  const DICT = (() => {
    const src = window.MATTA_DICT || {}, out = {};
    Object.keys(src).forEach((k) => { out[k] = src[k]; });
    // kamus dua arah: teks EN atau ID yang sudah tampil tetap bisa dibalik
    Object.keys(src).forEach((k) => src[k].forEach((v) => { if (!(v in out)) out[v] = src[k]; }));
    return out;
  })();
  let lang = store.get("matta.lang", "en");
  function T(str) {
    const e = DICT[str];
    return e ? e[lang === "en" ? 0 : 1] : str;
  }
  window.MATTA.t = T;
  window.MATTA.getLang = () => lang;
  window.MATTA.store = store;
  const ATTRS = ["placeholder", "title", "aria-label"];
  const LAST = new WeakMap(), ORIG = new WeakMap();
  function trString(raw) {
    const m = /^(\s*)([\s\S]*?)(\s*)$/.exec(raw);
    if (!m[2]) return raw;
    const e = DICT[m[2]];
    return e ? m[1] + e[lang === "en" ? 0 : 1] + m[3] : raw;
  }
  function trTextNode(n) {
    // teks berubah dari luar (JS lain) → jadikan sumber baru
    if (!ORIG.has(n) || LAST.get(n) !== n.nodeValue) ORIG.set(n, n.nodeValue);
    const v = trString(ORIG.get(n));
    if (v !== n.nodeValue) n.nodeValue = v;
    LAST.set(n, n.nodeValue);
  }
  function trElementAttrs(el) {
    ATTRS.forEach((at) => {
      if (!el.hasAttribute(at)) return;
      const key = "i18n" + at.replace(/-./g, (x) => x[1].toUpperCase());
      const cur = el.getAttribute(at);
      if (!(key in el.dataset) || el.dataset[key + "Last"] !== cur) el.dataset[key] = cur;
      const v = trString(el.dataset[key]);
      if (v !== cur) el.setAttribute(at, v);
      el.dataset[key + "Last"] = el.getAttribute(at);
    });
  }
  const SKIP = /^(SCRIPT|STYLE|NOSCRIPT|TEXTAREA|CODE)$/;
  function translateTree(root) {
    if (!root) return;
    if (root.nodeType === 3) { if (root.parentNode && !SKIP.test(root.parentNode.nodeName) && !root.parentNode.closest("[data-no-i18n]")) trTextNode(root); return; }
    if (root.nodeType === 1 && root.nodeName === "TEXTAREA" && !root.closest("[data-no-i18n]")) { trElementAttrs(root); return; }
    if (root.nodeType !== 1 || SKIP.test(root.nodeName) || root.closest("[data-no-i18n]")) return;
    trElementAttrs(root);
    const w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT, {
      acceptNode: (n) => {
        if (n.nodeType !== 1) return NodeFilter.FILTER_ACCEPT;
        if (n.nodeName === "TEXTAREA" && !n.hasAttribute("data-no-i18n")) trElementAttrs(n); // isi textarea tidak diterjemahkan, placeholder-nya iya
        return SKIP.test(n.nodeName) || n.hasAttribute("data-no-i18n") ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT;
      }
    });
    let n;
    while ((n = w.nextNode())) { if (n.nodeType === 3) trTextNode(n); else trElementAttrs(n); }
  }
  function applyLang() {
    document.documentElement.lang = lang;
    translateTree(document.body);
    $$("[data-lang]").forEach((b) => b.setAttribute("aria-pressed", String(b.dataset.lang === lang)));
    document.dispatchEvent(new CustomEvent("matta:lang", { detail: lang }));
  }
  function initLang() {
    $$("[data-lang]").forEach((b) => b.addEventListener("click", () => {
      if (lang === b.dataset.lang) return;
      lang = b.dataset.lang; store.set("matta.lang", lang); applyLang();
      toast(lang === "en" ? "Language: English" : "Bahasa: Indonesia");
    }));
    applyLang();
    // terjemahkan konten yang ditambahkan / diubah oleh JS
    new MutationObserver((muts) => {
      muts.forEach((mu) => {
        if (mu.type === "characterData") translateTree(mu.target);
        else if (mu.type === "attributes") trElementAttrs(mu.target);
        else mu.addedNodes.forEach((nd) => translateTree(nd));
      });
    }).observe(document.body, { childList: true, subtree: true, characterData: true, attributes: true, attributeFilter: ATTRS });
  }

  /* ---------------- Modal ---------------- */
  let lastFocus = null;
  function openModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    lastFocus = document.activeElement;
    m.classList.add("open");
    m.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    setTimeout(() => { const f = $("input:not([type=hidden]):not([readonly]):not(:disabled), select:not(:disabled), textarea:not(:disabled), button", $(".modal-body", m) || m); f && f.focus(); }, 60);
  }
  function closeModal(m) {
    if (typeof m === "string") m = document.getElementById(m);
    if (!m) return;
    m.classList.remove("open");
    m.setAttribute("aria-hidden", "true");
    if (!$(".modal-backdrop.open")) document.body.style.overflow = "";
    lastFocus && lastFocus.focus && lastFocus.focus();
  }
  function initModals() {
    document.addEventListener("click", (e) => {
      const opener = e.target.closest("[data-modal-open]");
      if (opener) { e.preventDefault(); openModal(opener.getAttribute("data-modal-open")); return; }
      const closer = e.target.closest("[data-modal-close]");
      if (closer) { closeModal(closer.closest(".modal-backdrop")); return; }
      if (e.target.classList && e.target.classList.contains("modal-backdrop")) closeModal(e.target);
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") { const m = $$(".modal-backdrop.open").pop(); m && closeModal(m); }
    });
  }
  function confirmDialog(title, text, okLabel = "Ya, lanjutkan", danger = false) {
    return new Promise((resolve) => {
      const wrap = document.createElement("div");
      wrap.className = "modal-backdrop";
      wrap.innerHTML =
        '<div class="modal" role="alertdialog" aria-modal="true" style="max-width:420px"><div class="modal-body">' +
        '<div class="modal-title">' + esc(title) + '</div><p class="modal-sub" style="margin-top:8px;font-size:13px">' + esc(text) + "</p></div>" +
        '<div class="modal-footer"><button class="btn btn-outline" data-x="0">' + T("Batal") + '</button><button class="btn ' + (danger ? "btn-danger" : "btn-primary") + '" data-x="1">' + esc(okLabel) + "</button></div></div>";
      document.body.appendChild(wrap);
      requestAnimationFrame(() => wrap.classList.add("open"));
      const done = (v) => { wrap.classList.remove("open"); setTimeout(() => wrap.remove(), 200); resolve(v); };
      wrap.addEventListener("click", (e) => { const b = e.target.closest("[data-x]"); if (b) done(b.dataset.x === "1"); else if (e.target === wrap) done(false); });
      setTimeout(() => $("[data-x='1']", wrap).focus(), 50);
    });
  }
  window.MATTA.openModal = openModal;
  window.MATTA.closeModal = closeModal;
  window.MATTA.confirm = confirmDialog;

  /* ---------------- Validasi form ---------------- */
  function validateForm(form) {
    let ok = true;
    $$("[required], [data-after], input[type=file][data-max-mb]", form).forEach((input) => {
      if (input.disabled) return;
      const field = input.closest(".field") || input.parentElement;
      const empty = input.type === "checkbox" ? !input.checked : !String(input.value).trim();
      let invalid = empty && input.required;
      if (!empty && input.type === "email") invalid = !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
      // tanggal akhir tidak boleh sebelum tanggal mulai
      if (!empty && input.dataset.after) { const ref = form.elements[input.dataset.after]; if (ref && ref.value && input.value < ref.value) invalid = true; }
      // lampiran: ukuran & format
      if (input.type === "file" && input.files && input.files[0]) {
        const f = input.files[0], max = parseFloat(input.dataset.maxMb || "0") * 1024 * 1024;
        const okExt = !input.accept || input.accept.split(",").some((x) => f.name.toLowerCase().endsWith(x.trim().toLowerCase()));
        if ((max && f.size > max) || !okExt) invalid = true;
      }
      input.classList.toggle("is-invalid", invalid);
      field && field.classList.toggle("has-error", invalid);
      if (invalid && ok) { input.focus(); ok = false; }
    });
    return ok;
  }
  document.addEventListener("input", (e) => {
    if (e.target.classList && e.target.classList.contains("is-invalid")) {
      e.target.classList.remove("is-invalid");
      const f = e.target.closest(".field"); f && f.classList.remove("has-error");
    }
  });
  window.MATTA.validateForm = validateForm;
  // Form dengan beberapa set isian (mis. Sick Leave punya isian khusus): tampilkan set sesuai modul
  function useFieldset(form, key) {
    const sets = $$("[data-fieldset]", form);
    if (!sets.length) return;
    const k = sets.some((x) => x.dataset.fieldset === key) ? key : "default";
    sets.forEach((x) => {
      const on = x.dataset.fieldset === k;
      x.hidden = !on;
      $$("input, textarea, select", x).forEach((i) => { i.disabled = !on; i.classList.remove("is-invalid"); });
      $$(".has-error", x).forEach((f) => f.classList.remove("has-error"));
    });
    form.classList.toggle("modal-lg", k !== "default");
  }
  window.MATTA.useFieldset = useFieldset;
  // Detail tambahan dari isian Sick Leave untuk card detail
  function extraRows(fd) {
    return [["Alasan", "reason"], ["PIC Pengganti", "pic"], ["Telepon Pengaju", "phone"], ["Alamat", "address"]]
      .filter(([, k]) => fd.get(k) && String(fd.get(k)).trim()).map(([l, k]) => [l, String(fd.get(k)).trim()]);
  }
  window.MATTA.extraRows = extraRows;

  /* ---------------- Data table (cari, urut, halaman, ekspor) ---------------- */
  class DataTable {
    constructor(card) {
      this.card = card;
      this.table = $("table.dt", card);
      this.tbody = this.table.tBodies[0];
      this.rows = $$("tr", this.tbody);
      this.length = parseInt(($("[data-dt-length]", card) || {}).value || card.dataset.pageLength || 10, 10);
      this.page = 1;
      this.query = "";
      this.filters = {};
      this.sort = null;
      this.label = card.dataset.label || "data";
      this.bind();
      this.render();
    }
    bind() {
      const c = this.card;
      const s = $("[data-dt-search]", c);
      s && s.addEventListener("input", () => { this.query = s.value.trim().toLowerCase(); this.page = 1; this.render(); });
      const l = $("[data-dt-length]", c);
      l && l.addEventListener("change", () => { this.length = parseInt(l.value, 10); this.page = 1; this.render(); });
      $$("th[data-sort]", this.table).forEach((th, i) => {
        th.tabIndex = 0;
        const idx = Array.from(th.parentElement.children).indexOf(th);
        const go = () => {
          const dir = this.sort && this.sort.idx === idx && this.sort.dir === "asc" ? "desc" : "asc";
          this.sort = { idx, dir, type: th.dataset.sort };
          $$("th[data-sort]", this.table).forEach((h) => h.removeAttribute("aria-sort"));
          th.setAttribute("aria-sort", dir === "asc" ? "ascending" : "descending");
          this.render();
        };
        th.addEventListener("click", go);
        th.addEventListener("keydown", (e) => { if (e.key === "Enter" || e.key === " ") { e.preventDefault(); go(); } });
      });
      $$("[data-dt-filter]", c).forEach((sel) => {
        sel.addEventListener("change", () => { this.filters[sel.dataset.dtFilter] = sel.value; this.page = 1; this.render(); });
        if (sel.value) this.filters[sel.dataset.dtFilter] = sel.value;
      });
      $$("[data-dt-export]", c).forEach((b) => b.addEventListener("click", () => this.export(b.dataset.dtExport)));
    }
    refresh() { this.rows = $$("tr:not(.dt-empty)", this.tbody); this.render(); }
    cellText(tr, idx) { const td = tr.children[idx]; return td ? (td.dataset.value ?? td.textContent).trim() : ""; }
    filtered() {
      let rows = this.rows.filter((tr) => {
        if (this.query && !tr.textContent.toLowerCase().includes(this.query)) return false;
        for (const [key, val] of Object.entries(this.filters)) {
          if (val && (tr.dataset[key] || "") !== val) return false;
        }
        return true;
      });
      if (this.sort) {
        const { idx, dir, type } = this.sort;
        const parse = (v) => {
          if (type === "num") return parseFloat(v.replace(/[^\d.,-]/g, "").replace(",", ".")) || 0;
          if (type === "date") { const m = v.match(/(\d{1,4})[-/](\d{1,2})[-/](\d{2,4})/); if (m) { return m[1].length === 4 ? m[1] + m[2].padStart(2, "0") + m[3].padStart(2, "0") : m[3] + m[2].padStart(2, "0") + m[1].padStart(2, "0"); } }
          return v.toLowerCase();
        };
        rows = rows.slice().sort((a, b) => {
          const x = parse(this.cellText(a, idx)), y = parse(this.cellText(b, idx));
          return (x > y ? 1 : x < y ? -1 : 0) * (dir === "asc" ? 1 : -1);
        });
      }
      return rows;
    }
    render() {
      const rows = this.filtered();
      const total = rows.length;
      const pages = Math.max(1, Math.ceil(total / this.length));
      this.page = Math.min(this.page, pages);
      const start = (this.page - 1) * this.length;
      const visible = rows.slice(start, start + this.length);
      this.rows.forEach((tr) => (tr.hidden = true));
      // urutkan ulang DOM sesuai hasil sort, lalu tampilkan halaman aktif
      rows.forEach((tr) => this.tbody.appendChild(tr));
      visible.forEach((tr) => (tr.hidden = false));
      // nomor urut
      visible.forEach((tr, i) => { const n = $("[data-dt-no]", tr); if (n) n.textContent = start + i + 1; });
      let empty = $(".dt-empty", this.tbody);
      if (!total) {
        if (!empty) { empty = document.createElement("tr"); empty.className = "dt-empty"; empty.innerHTML = '<td colspan="' + this.table.tHead.rows[0].children.length + '">Tidak ada data yang cocok.</td>'; }
        this.tbody.appendChild(empty);
      } else if (empty) empty.remove();
      const info = $("[data-dt-info]", this.card);
      if (info) info.innerHTML = total ? T("Menampilkan") + " <b>" + (start + 1) + " - " + (start + visible.length) + "</b> " + T("dari") + " <b>" + total + "</b> " + esc(T(this.label)) : T("Menampilkan") + " 0 " + esc(T(this.label));
      const pager = $("[data-dt-pager]", this.card);
      if (pager) {
        let html = '<button type="button" data-p="prev"' + (this.page === 1 ? " disabled" : "") + ">" + ICON.left + "Previous</button>";
        for (let p = 1; p <= pages; p++) html += '<button type="button" data-p="' + p + '"' + (p === this.page ? ' class="active" aria-current="page"' : "") + ">" + p + "</button>";
        html += '<button type="button" data-p="next"' + (this.page === pages ? " disabled" : "") + ">Next" + ICON.right + "</button>";
        pager.innerHTML = html;
        $$("button", pager).forEach((b) => b.addEventListener("click", () => {
          const v = b.dataset.p;
          this.page = v === "prev" ? this.page - 1 : v === "next" ? this.page + 1 : parseInt(v, 10);
          this.render();
        }));
      }
    }
    matrix() {
      const heads = $$("thead th", this.table);
      const keep = heads.map((h) => !h.hasAttribute("data-noexport"));
      const head = heads.filter((_, i) => keep[i]).map((h) => h.textContent.trim());
      const body = this.filtered().map((tr) => Array.from(tr.children).filter((_, i) => keep[i]).map((td) => td.innerText.replace(/\s*\n\s*/g, " / ").trim()));
      return [head, ...body];
    }
    download(name, content, type) {
      const blob = new Blob([content], { type });
      const a = document.createElement("a");
      a.href = URL.createObjectURL(blob);
      a.download = name;
      document.body.appendChild(a); a.click(); a.remove();
      setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    }
    export(kind) {
      const data = this.matrix();
      const file = (this.card.dataset.file || "data") + "-" + new Date().toISOString().slice(0, 10);
      if (kind === "copy") {
        const text = data.map((r) => r.join("\t")).join("\n");
        (navigator.clipboard ? navigator.clipboard.writeText(text) : Promise.reject())
          .then(() => toast((data.length - 1) + " " + T("baris disalin ke clipboard.")))
          .catch(() => { const t = document.createElement("textarea"); t.value = text; document.body.appendChild(t); t.select(); document.execCommand("copy"); t.remove(); toast((data.length - 1) + " " + T("baris disalin ke clipboard.")); });
      } else if (kind === "csv") {
        const csv = data.map((r) => r.map((c) => '"' + String(c).replace(/"/g, '""') + '"').join(",")).join("\r\n");
        this.download(file + ".csv", "﻿" + csv, "text/csv;charset=utf-8");
        toast("File CSV diunduh.");
      } else if (kind === "excel") {
        const html = '<html><head><meta charset="utf-8"></head><body><table border="1">' +
          data.map((r, i) => "<tr>" + r.map((c) => (i ? "<td>" : "<th>") + esc(c) + (i ? "</td>" : "</th>")).join("") + "</tr>").join("") + "</table></body></html>";
        this.download(file + ".xls", html, "application/vnd.ms-excel");
        toast("File Excel diunduh.");
      } else if (kind === "pdf" || kind === "print") {
        const w = window.open("", "_blank");
        if (!w) { toast("Izinkan pop-up untuk mencetak.", "error"); return; }
        const title = ($(".dt-title", this.card) || {}).textContent || "Data";
        w.document.write('<!doctype html><html><head><meta charset="utf-8"><title>' + esc(title) + "</title><style>body{font-family:Arial,sans-serif;padding:24px;color:#0f172a}h1{font-size:18px;margin:0 0 4px}p{color:#64748b;font-size:12px;margin:0 0 16px}table{width:100%;border-collapse:collapse;font-size:11px}th,td{border:1px solid #e2e8f0;padding:6px 8px;text-align:left}th{background:#f8fafc;text-transform:uppercase}</style></head><body><h1>" + esc(title) + "</h1><p>Dicetak " + new Date().toLocaleString("id-ID") + "</p><table>" +
          data.map((r, i) => "<tr>" + r.map((c) => (i ? "<td>" : "<th>") + esc(c) + (i ? "</td>" : "</th>")).join("") + "</tr>").join("") + "</table><script>window.onload=function(){window.print();}<\/script></body></html>");
        w.document.close();
        if (kind === "pdf") toast('Pilih "Save as PDF" pada dialog cetak.');
      }
    }
  }
  const tables = new Map();
  function initTables() {
    $$("[data-datatable]").forEach((card) => tables.set(card, new DataTable(card)));
    document.addEventListener("matta:lang", () => tables.forEach((t) => t.render()));
  }
  window.MATTA.tables = tables;
  window.MATTA.DataTable = DataTable;

  /* Tombol toggle panel filter */
  function initFilterToggles() {
    $$("[data-toggle-target]").forEach((btn) => {
      const target = document.getElementById(btn.dataset.toggleTarget);
      btn.addEventListener("click", () => {
        if (!target) return;
        const hidden = target.classList.toggle("hidden");
        btn.setAttribute("aria-expanded", String(!hidden));
        if (!hidden) { const f = $("select, input", target); f && f.focus(); }
      });
    });
  }

  /* Detail baris (modal kunci–nilai) */
  /* ---------------- Card detail (panel samping) ----------------
     Format data-detail (JSON) pada <tr>:
     { eyebrow, title, icon|img, module, badges:[[teks, warna]], person:{name,nip,role},
       sections:[{h, rows:[[label, nilai]]}], map:{lat,lng,label,valid}, attachment, timeline:[{t,by,at,state}] }
     Format lama (objek datar {label: nilai}) tetap didukung. */
  const DR_ICON = {
    clock: '<svg class="icon icon-md" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    doc: '<svg class="icon icon-md" viewBox="0 0 24 24"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>',
    pin: '<svg class="icon icon-md" viewBox="0 0 24 24"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>',
    file: '<svg class="icon" viewBox="0 0 24 24"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>',
    ext: '<svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>'
  };
  function openDetail(data, focus) {
    const dr = document.getElementById("drawer-detail");
    if (!dr) return;
    if (!data.sections && !data.title) data = { title: "Detail", sections: [{ h: "Detail", rows: Object.entries(data) }] };
    dr.setAttribute("data-module", data.module || "");
    $("[data-dr-eyebrow]", dr).textContent = T(data.eyebrow || "Detail");
    $("[data-dr-title]", dr).textContent = data.title || "Detail";
    $("[data-dr-ico]", dr).innerHTML = data.img ? '<img src="' + esc(data.img) + '" alt="">' : (DR_ICON[data.icon] || DR_ICON.doc);
    const val = (v) => {
      if (v === "-" || v === "" || v == null) return '<span class="muted">-</span>';
      const m = /^(\d+(?:\.\d+)?) Jam$/.exec(String(v));
      return m ? esc(m[1] + " " + T("hrs")) : esc(T(String(v)));
    };
    let html = "";
    if (data.badges && data.badges.length) html += '<div class="dr-badges">' + data.badges.map((b) => '<span class="dr-badge ' + esc(b[1]) + '">' + esc(T(b[0])) + "</span>").join("") + "</div>";
    if (data.person) {
      const ini = data.person.name.split(/\s+/).filter((w) => /^[A-Za-z]/.test(w)).slice(0, 2).map((w) => w[0]).join("").toUpperCase();
      html += '<div class="dr-person"><span class="avatar-initial">' + esc(ini) + '</span><div><div class="person-name">' + esc(data.person.name) + '</div><div class="person-sub">' + esc(data.person.nip) + (data.person.role ? " · " + esc(data.person.role) : "") + "</div></div></div>";
    }
    if (data.map) {
      const m = data.map;
      html += '<section class="dr-sec" id="dr-map"><h3>' + T("Lokasi Presensi") + "</h3>" +
        (m.valid
          ? '<div class="dr-map"><iframe title="Peta lokasi" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=' + encodeURIComponent(m.lat + "," + m.lng) + '&hl=id&z=16&output=embed"></iframe></div>' +
            '<div class="dr-map-meta">' + DR_ICON.pin + "<div><b>" + esc(T(m.label)) + "</b><span>" + esc(m.lat) + ", " + esc(m.lng) + '</span></div>' +
            '<div style="display:flex; gap:6px; margin-left:auto; align-items:center;">' +
            '<a class="btn btn-outline btn-xs" target="_blank" rel="noopener" href="https://www.google.com/maps?q=' + encodeURIComponent(m.lat + "," + m.lng) + '">' + T("Buka Maps") + " " + DR_ICON.ext + "</a></div></div>"
          : '<div class="dr-map empty">' + DR_ICON.pin + "<div><b>" + esc(T(m.label)) + "</b><span>" + T("Koordinat tidak dikirim perangkat (0, 0)") + '</span></div></div>') +
        "</section>";
    }
    (data.sections || []).forEach((sec) => {
      html += '<section class="dr-sec"><h3>' + esc(T(sec.h)) + '</h3><dl class="dr-kv">' + sec.rows.map((r) => "<dt>" + esc(T(r[0])) + "</dt><dd>" + val(r[1]) + "</dd>").join("") + "</dl></section>";
    });
    if (data.attachment) {
      html += '<section class="dr-sec"><h3>' + T("Lampiran") + '</h3><a href="#" class="dr-file" data-fake-file="' + esc(data.attachment) + '">' + DR_ICON.file + "<span>" + esc(data.attachment) + "</span><b>" + T("Unduh") + "</b></a></section>";
    }
    if (data.timeline && data.timeline.length) {
      html += '<section class="dr-sec"><h3>' + T("Alur Persetujuan") + '</h3><ol class="dr-timeline">' + data.timeline.map((st) =>
        '<li class="' + esc(st.state || "todo") + '"><span class="dot"></span><div><b>' + esc(T(st.t)) + "</b><span>" + esc(st.by || "") + (st.at ? " · " + esc(st.at) : st.state === "current" ? " · " + T("menunggu") : "") + "</span></div></li>").join("") + "</ol></section>";
    }
    $("[data-dr-body]", dr).innerHTML = html;
    $("[data-dr-body]", dr).scrollTop = 0;
    openModal("drawer-detail");
    if (focus === "map") setTimeout(() => { const mp = document.getElementById("dr-map"); mp && mp.scrollIntoView({ behavior: "smooth", block: "start" }); }, 250);
  }
  window.MATTA.openDetail = openDetail;

  function initRowDetail() {
    document.addEventListener("click", (e) => {
      const btn = e.target.closest("[data-row-detail]");
      const tr = btn ? btn.closest("tr") : e.target.closest("tr[data-detail]");
      if (!tr || !tr.dataset.detail) return;
      if (!btn && e.target.closest("a, button, input, select, label")) return;
      let data = {};
      try { data = JSON.parse(tr.dataset.detail); } catch (err) { data = {}; }
      openDetail(data, btn && btn.dataset.focus);
    });
    document.addEventListener("keydown", (e) => {
      if ((e.key === "Enter" || e.key === " ") && e.target.matches && e.target.matches("tr[data-detail]")) { e.preventDefault(); e.target.click(); }
    });
    // baris yang punya detail bisa difokus dengan keyboard
    const mark = () => $$("tr[data-detail]:not([tabindex])").forEach((tr) => { tr.tabIndex = 0; tr.classList.add("row-click"); });
    mark();
    new MutationObserver(mark).observe(document.body, { childList: true, subtree: true });
    document.addEventListener("click", (e) => {
      if (e.target.closest("[data-dr-print]")) {
        document.body.classList.add("print-drawer");
        window.print();
        setTimeout(() => document.body.classList.remove("print-drawer"), 300);
      }
      const f = e.target.closest("#drawer-detail [data-fake-file]");
      if (f) { e.preventDefault(); toast(T("Lampiran") + " " + f.dataset.fakeFile + " " + T("(contoh, belum terhubung ke server).")); }
    });
  }

  /* Tab generik (role=tablist) */
  function initTabs() {
    $$("[role=tablist]").forEach((list) => {
      const tabs = $$("[role=tab]", list);
      tabs.forEach((tab) => tab.addEventListener("click", () => {
        tabs.forEach((t) => {
          const sel = t === tab;
          t.setAttribute("aria-selected", String(sel));
          const panel = document.getElementById(t.getAttribute("aria-controls"));
          panel && panel.classList.toggle("hidden", !sel);
        });
        list.dispatchEvent(new CustomEvent("tabchange", { detail: tab }));
      }));
    });
  }

  /* Accordion */
  function initAccordion() {
    $$("[data-acc-trigger]").forEach((btn) => {
      const panel = document.getElementById(btn.getAttribute("aria-controls"));
      btn.addEventListener("click", () => {
        const open = btn.getAttribute("aria-expanded") !== "true";
        btn.setAttribute("aria-expanded", String(open));
        panel && panel.classList.toggle("open", open);
      });
    });
  }

  /* Tombol kembali */
  function initBack() {
    $$("[data-back]").forEach((b) => b.addEventListener("click", (e) => {
      e.preventDefault();
      if (history.length > 1 && document.referrer) history.back(); else location.href = b.getAttribute("href") || "index.html";
    }));
  }

  /* Waktu WIB */
  function realWIB() {
    const d = new Date();
    return new Date(d.getTime() + (d.getTimezoneOffset() + 420) * 60000);
  }
  // Simulasi jam untuk uji coba: tambahkan ?jam=08:45 di URL (waktu tetap berjalan dari jam tsb)
  const SIM_OFFSET = (() => {
    const m = /[?&]jam=(\d{1,2})[:.]?(\d{2})/.exec(location.search);
    if (!m) return 0;
    const r = realWIB(), t = new Date(r); t.setHours(+m[1], +m[2], 0, 0);
    return t - r;
  })();
  function nowWIB() { return new Date(realWIB().getTime() + SIM_OFFSET); }
  window.MATTA.nowWIB = nowWIB;
  const pad = (n) => String(n).padStart(2, "0");
  window.MATTA.pad = pad;

  /* =========================================================
     Halaman-halaman
     ========================================================= */

  /* ---------- Koordinat & Lokasi Presensi ---------- */
  const DEFAULT_HQ_COORDS = { lat: "-6.2088", lng: "106.8227", address: "Kantor Pusat (HQ), Jakarta" };

  async function reverseGeocode(lat, lng) {
    try {
      const url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=" + encodeURIComponent(lat) + "&lon=" + encodeURIComponent(lng) + "&zoom=18&addressdetails=1";
      const ctrl = typeof AbortController !== "undefined" ? new AbortController() : null;
      const tid = ctrl ? setTimeout(() => ctrl.abort(), 3500) : null;
      const res = await fetch(url, { headers: { "Accept-Language": "id,en" }, signal: ctrl ? ctrl.signal : undefined });
      if (tid) clearTimeout(tid);
      if (res.ok) {
        const data = await res.json();
        if (data && data.display_name) {
          const a = data.address || {};
          const p = [a.road || a.pedestrian, a.suburb || a.village || a.neighbourhood, a.city || a.county || a.city_district].filter(Boolean);
          return p.length >= 2 ? p.join(", ") : data.display_name.split(",").slice(0, 3).join(", ");
        }
      }
    } catch (e) {}
    return null;
  }

  async function fetchIpLocation() {
    try {
      const ctrl = typeof AbortController !== "undefined" ? new AbortController() : null;
      const tid = ctrl ? setTimeout(() => ctrl.abort(), 4000) : null;
      const res = await fetch("https://ipwho.is/", { cache: "no-store", signal: ctrl ? ctrl.signal : undefined });
      if (tid) clearTimeout(tid);
      if (res.ok) {
        const data = await res.json();
        if (data && data.success && data.latitude && data.longitude) {
          const lat = Number(data.latitude).toFixed(6);
          const lng = Number(data.longitude).toFixed(6);
          const cityArea = [data.city, data.region, data.country].filter(Boolean).join(", ");
          const rev = await reverseGeocode(lat, lng);
          return { lat: String(lat), lng: String(lng), address: rev || cityArea || "Lokasi IP Terdeteksi" };
        }
      }
    } catch (e) {}

    try {
      const ctrl2 = typeof AbortController !== "undefined" ? new AbortController() : null;
      const tid2 = ctrl2 ? setTimeout(() => ctrl2.abort(), 4000) : null;
      const res2 = await fetch("https://ipapi.co/json/", { signal: ctrl2 ? ctrl2.signal : undefined });
      if (tid2) clearTimeout(tid2);
      if (res2.ok) {
        const d2 = await res2.json();
        if (d2 && d2.latitude && d2.longitude) {
          const lat = Number(d2.latitude).toFixed(6);
          const lng = Number(d2.longitude).toFixed(6);
          const cityArea = [d2.city, d2.region, d2.country_name].filter(Boolean).join(", ");
          const rev = await reverseGeocode(lat, lng);
          return { lat: String(lat), lng: String(lng), address: rev || cityArea };
        }
      }
    } catch (e) {}

    return DEFAULT_HQ_COORDS;
  }

  function detectRealLocation() {
    return new Promise((resolve) => {
      let resolved = false;
      const finish = (coords) => {
        if (!resolved) {
          resolved = true;
          resolve(coords);
        }
      };

      if (typeof navigator !== "undefined" && navigator.geolocation) {
        const geoTimer = setTimeout(() => {
          fetchIpLocation().then(finish);
        }, 4500);

        try {
          navigator.geolocation.getCurrentPosition(
            async (pos) => {
              clearTimeout(geoTimer);
              const lat = Number(pos.coords.latitude).toFixed(6);
              const lng = Number(pos.coords.longitude).toFixed(6);
              const rev = await reverseGeocode(lat, lng);
              finish({
                lat: String(lat),
                lng: String(lng),
                address: rev || ("Lokasi GPS (" + lat + ", " + lng + ")")
              });
            },
            () => {
              clearTimeout(geoTimer);
              fetchIpLocation().then(finish);
            },
            { enableHighAccuracy: true, timeout: 6000, maximumAge: 60000 }
          );
        } catch (e) {
          clearTimeout(geoTimer);
          fetchIpLocation().then(finish);
        }
      } else {
        fetchIpLocation().then(finish);
      }
    });
  }

  function getAttendanceCoords() {
    const custom = store.get("matta.customCoords", null);
    if (custom && custom.lat && custom.lng) {
      return Promise.resolve(custom);
    }
    return detectRealLocation();
  }

  function recordAttendanceLog(type, timestampMs, coords) {
    const dt = new Date(timestampMs);
    const dateStr = pad(dt.getDate()) + "-" + pad(dt.getMonth() + 1) + "-" + dt.getFullYear();
    const timeStr = pad(dt.getHours()) + ":" + pad(dt.getMinutes()) + ":" + pad(dt.getSeconds());
    const fullTimestamp = dateStr + " " + timeStr;
    const isClockIn = type === "in";
    const action = isClockIn ? "Clock-In" : "Clock-out";
    const status = isClockIn ? "Book-In" : "Book-out";

    const custom = store.get("matta.customCoords", null);
    const resolvedCoords = (coords && coords.lat) ? coords : (custom || DEFAULT_HQ_COORDS);

    const entry = {
      id: "att-" + timestampMs,
      type: type,
      dateKey: "matta.att." + dt.getFullYear() + pad(dt.getMonth() + 1) + pad(dt.getDate()),
      dateStr: dateStr,
      timestamp: fullTimestamp,
      timeMs: timestampMs,
      action: action,
      status: status,
      lat: String(resolvedCoords.lat),
      lng: String(resolvedCoords.lng),
      address: resolvedCoords.address || DEFAULT_HQ_COORDS.address,
      delay: "-"
    };

    const list = store.get("matta.attLogList", []);
    const filtered = list.filter((item) => !(item.dateKey === entry.dateKey && item.type === entry.type));
    filtered.unshift(entry);
    store.set("matta.attLogList", filtered);
    return entry;
  }

  /* ---------- Modal Pengaturan Lokasi & Koordinat ---------- */
  function initLocationModal() {
    let modal = document.getElementById("modal-location");
    if (!modal) {
      modal = document.createElement("div");
      modal.className = "modal-backdrop";
      modal.id = "modal-location";
      modal.setAttribute("aria-hidden", "true");
      modal.innerHTML = '<div class="modal" role="dialog" aria-modal="true" aria-labelledby="loc-modal-title" style="max-width:520px;">' +
        '<div class="modal-header"><div><div class="modal-title" id="loc-modal-title">Atur Lokasi & Koordinat Presensi</div><div class="modal-sub">Deteksi otomatis atau tentukan titik koordinat lokasi presensi Anda.</div></div><button class="icon-btn" type="button" data-modal-close aria-label="Tutup"><svg class="icon" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button></div>' +
        '<form id="form-location" class="modal-body" style="display:flex; flex-direction:column; gap:12px;">' +
        '<div style="background:#FFF9F1; border:1px solid #e8dec8; border-radius:10px; padding:12px; display:flex; align-items:center; justify-content:space-between; gap:10px;">' +
        '<div><div style="font-size:12px; font-weight:700; color:#5c4b37;">Deteksi GPS / IP Otomatis</div><div style="font-size:11px; color:#856404;">Deteksi otomatis lokasi riil perangkat Anda saat ini</div></div>' +
        '<button type="button" class="btn btn-sm btn-primary" id="btn-detect-gps" style="display:inline-flex; align-items:center; gap:5px;"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg><span id="txt-detect-gps">Deteksi Sekarang</span></button>' +
        '</div>' +
        '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">' +
        '<label class="field"><span class="field-label">Latitude <span class="req">*</span></span><input class="form-control mono" type="text" name="lat" id="loc-input-lat" placeholder="-6.2088" required></label>' +
        '<label class="field"><span class="field-label">Longitude <span class="req">*</span></span><input class="form-control mono" type="text" name="lng" id="loc-input-lng" placeholder="106.8227" required></label>' +
        '</div>' +
        '<label class="field"><span class="field-label">Alamat / Keterangan Lokasi</span><input class="form-control" type="text" name="address" id="loc-input-address" placeholder="Contoh: Kantor / Rumah / Jl. Sudirman"></label>' +
        '<div style="border-radius:8px; overflow:hidden; border:1px solid #e2e8f0; height:180px; position:relative;">' +
        '<iframe id="loc-iframe-preview" style="width:100%; height:100%; border:none;" src="" loading="lazy"></iframe>' +
        '</div>' +
        '<div class="modal-footer" style="padding:10px 0 0; display:flex; justify-content:space-between; align-items:center;">' +
        '<button type="button" class="btn btn-outline btn-xs" id="btn-reset-default-loc" style="background:#f8fafc;">Reset Default HQ</button>' +
        '<div style="display:flex; gap:8px;">' +
        '<button class="btn btn-outline" type="button" data-modal-close>Batal</button>' +
        '<button class="btn btn-primary" type="submit">Simpan Lokasi</button>' +
        '</div></div>' +
        '</form>' +
        '</div>';
      document.body.appendChild(modal);
    }

    const form = document.getElementById("form-location");
    const latInp = document.getElementById("loc-input-lat");
    const lngInp = document.getElementById("loc-input-lng");
    const addrInp = document.getElementById("loc-input-address");
    const preview = document.getElementById("loc-iframe-preview");
    const btnDetect = document.getElementById("btn-detect-gps");
    const txtDetect = document.getElementById("txt-detect-gps");
    const btnReset = document.getElementById("btn-reset-default-loc");

    function updatePreview(lat, lng) {
      if (lat && lng && preview) {
        preview.src = "https://maps.google.com/maps?q=" + encodeURIComponent(lat + "," + lng) + "&hl=id&z=16&output=embed";
      }
    }

    [latInp, lngInp].forEach((inp) => {
      inp && inp.addEventListener("input", () => {
        updatePreview(latInp.value.trim(), lngInp.value.trim());
      });
    });

    document.addEventListener("click", (e) => {
      const trg = e.target.closest("[data-open-modal='modal-location']");
      if (trg) {
        const cur = store.get("matta.customCoords", null) || DEFAULT_HQ_COORDS;
        if (latInp) latInp.value = cur.lat || "";
        if (lngInp) lngInp.value = cur.lng || "";
        if (addrInp) addrInp.value = cur.address || "";
        updatePreview(cur.lat, cur.lng);
        openModal("modal-location");
      }
    });

    if (btnDetect) {
      btnDetect.addEventListener("click", async () => {
        txtDetect.textContent = "Mendeteksi...";
        btnDetect.disabled = true;
        try {
          const res = await detectRealLocation();
          if (latInp) latInp.value = res.lat;
          if (lngInp) lngInp.value = res.lng;
          if (addrInp) addrInp.value = res.address;
          updatePreview(res.lat, res.lng);
          toast("Lokasi terkini berhasil dideteksi: " + res.address, "success");
        } catch (e) {
          toast("Gagal mendeteksi lokasi otomatis.", "error");
        } finally {
          txtDetect.textContent = "Deteksi Sekarang";
          btnDetect.disabled = false;
        }
      });
    }

    if (btnReset) {
      btnReset.addEventListener("click", () => {
        if (latInp) latInp.value = DEFAULT_HQ_COORDS.lat;
        if (lngInp) lngInp.value = DEFAULT_HQ_COORDS.lng;
        if (addrInp) addrInp.value = DEFAULT_HQ_COORDS.address;
        updatePreview(DEFAULT_HQ_COORDS.lat, DEFAULT_HQ_COORDS.lng);
      });
    }

    if (form) {
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        const lat = latInp.value.trim();
        const lng = lngInp.value.trim();
        const address = addrInp.value.trim() || ("Lokasi (" + lat + ", " + lng + ")");
        if (!lat || !lng) {
          toast("Latitude dan Longitude wajib diisi.", "error");
          return;
        }

        const newCoords = { lat: lat, lng: lng, address: address };
        store.set("matta.customCoords", newCoords);

        // Update presensi hari ini jika sudah ada
        const n = nowWIB();
        const tKey = "matta.att." + n.getFullYear() + pad(n.getMonth() + 1) + pad(n.getDate());
        let att = store.get(tKey, null);
        if (att) {
          if (att.in) { att.inLat = lat; att.inLng = lng; att.inAddress = address; }
          if (att.out) { att.outLat = lat; att.outLng = lng; att.outAddress = address; }
          store.set(tKey, att);
        }

        let list = store.get("matta.attLogList", []);
        list = list.map((item) => {
          if (item.dateKey === tKey) {
            return Object.assign({}, item, { lat: lat, lng: lng, address: address });
          }
          return item;
        });
        store.set("matta.attLogList", list);

        const dr = document.getElementById("drawer-detail");
        if (dr) {
          const iframe = dr.querySelector("#dr-map iframe");
          if (iframe) iframe.src = "https://maps.google.com/maps?q=" + encodeURIComponent(lat + "," + lng) + "&hl=id&z=16&output=embed";
          const metaSpan = dr.querySelector(".dr-map-meta span");
          if (metaSpan) metaSpan.textContent = lat + ", " + lng;
          const metaB = dr.querySelector(".dr-map-meta b");
          if (metaB) metaB.textContent = address;
        }

        const locLabel = document.querySelector("[data-active-loc-name]");
        if (locLabel) locLabel.textContent = address.split(",")[0] || "Lokasi Tersimpan";

        closeModal("modal-location");
        toast("Koordinat lokasi presensi berhasil diperbarui!", "success");

        if (document.getElementById("panel-raw") || document.querySelector('[data-file="attendance-log"]') || document.querySelector('[data-file="attendance-recap"]')) {
          setTimeout(() => location.reload(), 400);
        }
      });
    }
  }

  /* ---------- Sinkronisasi Attendance Log & Recap ---------- */
  function initAttendanceLogSync() {
    const rawTbody = $("#panel-raw tbody") || $('[data-file="attendance-log"] tbody');
    const recapTbody = $('[data-file="attendance-recap"] tbody');
    const durTbody = $("#panel-duration tbody") || $('[data-file="attendance-duration"] tbody');
    if (!rawTbody && !recapTbody && !durTbody) return;

    const n = nowWIB();
    const tKey = "matta.att." + n.getFullYear() + pad(n.getMonth() + 1) + pad(n.getDate());
    const att = store.get(tKey, null);
    let list = store.get("matta.attLogList", []);
    const defCoords = store.get("matta.customCoords", null) || DEFAULT_HQ_COORDS;

    if (att && att.in) {
      const hasIn = list.some((item) => item.dateKey === tKey && item.type === "in");
      if (!hasIn) {
        const dt = new Date(att.in);
        const dStr = pad(dt.getDate()) + "-" + pad(dt.getMonth() + 1) + "-" + dt.getFullYear();
        const tStr = pad(dt.getHours()) + ":" + pad(dt.getMinutes()) + ":" + pad(dt.getSeconds());
        list.push({
          id: "att-" + att.in,
          type: "in",
          dateKey: tKey,
          dateStr: dStr,
          timestamp: dStr + " " + tStr,
          timeMs: att.in,
          action: "Clock-In",
          status: "Book-In",
          lat: String(att.inLat || defCoords.lat),
          lng: String(att.inLng || defCoords.lng),
          address: att.inAddress || defCoords.address,
          delay: "-"
        });
      }
    }
    if (att && att.out) {
      const hasOut = list.some((item) => item.dateKey === tKey && item.type === "out");
      if (!hasOut) {
        const dt = new Date(att.out);
        const dStr = pad(dt.getDate()) + "-" + pad(dt.getMonth() + 1) + "-" + dt.getFullYear();
        const tStr = pad(dt.getHours()) + ":" + pad(dt.getMinutes()) + ":" + pad(dt.getSeconds());
        list.push({
          id: "att-" + att.out,
          type: "out",
          dateKey: tKey,
          dateStr: dStr,
          timestamp: dStr + " " + tStr,
          timeMs: att.out,
          action: "Clock-out",
          status: "Book-out",
          lat: String(att.outLat || defCoords.lat),
          lng: String(att.outLng || defCoords.lng),
          address: att.outAddress || defCoords.address,
          delay: "-"
        });
      }
    }
    store.set("matta.attLogList", list);

    function buildRawRow(entry) {
      const isClockIn = entry.action === "Clock-In";
      const pillAction = isClockIn ? "pill-blue" : "pill-green";
      const pillStatus = isClockIn ? "Book-In" : "Book-out";
      const tr = document.createElement("tr");
      tr.dataset.dept = "BOD-DIR";

      const detailObj = {
        eyebrow: "Attendance",
        title: entry.action + " · " + entry.timestamp,
        icon: "clock",
        badges: [[entry.action, isClockIn ? "blue" : "green"], [pillStatus, "green"], ["MATTA", "orange"]],
        person: { name: "M. Daffa Dzaky Pratama", nip: "P0928169", role: "Internship Alita" },
        sections: [
          {
            h: "Data Presensi",
            rows: [
              ["Tanggal", entry.dateStr],
              ["Timestamp", entry.timestamp],
              ["Action", entry.action],
              ["Status", pillStatus],
              ["Delay", entry.delay || "-"],
              ["Type", "Regular"],
              ["Working Group", "Reguler - 0830-1730"],
              ["Source", "MATTA"],
              ["ID_FP", "-"]
            ]
          },
          {
            h: "Organisasi",
            rows: [
              ["Perusahaan", "Internship Alita"],
              ["Direktorat", "BOD-DIR"],
              ["Divisi", "Corporate Secretary"],
              ["Departemen", "Corporate Secretary"],
              ["Lokasi", "HQ"]
            ]
          },
          {
            h: "Lokasi Presensi",
            rows: [
              ["Longitude", String(entry.lng)],
              ["Latitude", String(entry.lat)],
              ["Address", entry.address]
            ]
          }
        ],
        map: {
          lat: String(entry.lat),
          lng: String(entry.lng),
          label: entry.address,
          valid: true
        }
      };
      tr.setAttribute("data-detail", JSON.stringify(detailObj));
      tr.innerHTML = '<td class="num" data-dt-no></td>' +
        '<td class="strong">' + esc(entry.dateStr) + '</td>' +
        '<td class="mono">' + esc(entry.timestamp) + '</td>' +
        '<td class="cell-2"><div class="main-line">M. Daffa Dzaky Pratama</div><div class="sub-line">Internship Alita</div></td>' +
        '<td class="mono">P0928169</td>' +
        '<td class="center mono"><span class="muted">-</span></td>' +
        '<td>Internship Alita</td>' +
        '<td>BOD-DIR</td>' +
        '<td>Corporate Secretary</td>' +
        '<td>Corporate Secretary</td>' +
        '<td class="strong">HQ</td>' +
        '<td class="muted-2">Reguler - 0830-1730</td>' +
        '<td class="center"><span class="pill ' + pillAction + '">' + esc(entry.action) + '</span></td>' +
        '<td class="center mono">' + esc(entry.lng) + '</td>' +
        '<td class="center mono">' + esc(entry.lat) + '</td>' +
        '<td class="center">' + esc(entry.address) + '</td>' +
        '<td class="center"><button type="button" class="map-pin" data-row-detail data-focus="map" aria-label="Lihat lokasi">' + DR_ICON.pin + '</button></td>' +
        '<td class="center"><span class="pill pill-green strong-pill">' + pillStatus + '</span></td>' +
        '<td class="center muted-2">' + esc(entry.delay || "-") + '</td>' +
        '<td class="center"><span class="pill pill-gray">Regular</span></td>' +
        '<td class="center"><span class="src src-matta">MATTA</span></td>';
      return tr;
    }

    const sortedLogs = list.slice().sort((a, b) => b.timeMs - a.timeMs);

    [rawTbody, recapTbody].forEach((tb) => {
      if (!tb) return;
      sortedLogs.slice().reverse().forEach((entry) => {
        tb.insertBefore(buildRawRow(entry), tb.firstChild);
      });
    });

    if (durTbody && att && att.in) {
      const dt = new Date(att.in);
      const dStr = pad(dt.getDate()) + "-" + pad(dt.getMonth() + 1) + "-" + dt.getFullYear();
      const inTime = pad(dt.getHours()) + ":" + pad(dt.getMinutes());
      let outTime = "-";
      let durStr = "-";
      if (att.out) {
        const outDt = new Date(att.out);
        outTime = pad(outDt.getHours()) + ":" + pad(outDt.getMinutes());
        const diffMs = att.out - att.in;
        const diffHrs = Math.floor(diffMs / 3600000);
        const diffMins = Math.floor((diffMs % 3600000) / 60000);
        durStr = diffHrs + " j " + diffMins + " m";
      }
      const inMinutes = dt.getHours() * 60 + dt.getMinutes();
      const statusBadge = inMinutes > (8 * 60 + 30)
        ? '<span class="badge badge-pill badge-amber">Terlambat</span>'
        : '<span class="badge badge-pill badge-green">Tepat Waktu</span>';

      const dTr = document.createElement("tr");
      dTr.innerHTML = '<td class="num" data-dt-no></td>' +
        '<td class="strong">' + esc(dStr) + '</td>' +
        '<td class="cell-2"><div class="main-line">M. Daffa Dzaky Pratama</div><div class="sub-line">Internship Alita</div></td>' +
        '<td class="mono">P0928169</td>' +
        '<td class="mono">' + esc(inTime) + '</td>' +
        '<td class="mono">' + esc(outTime) + '</td>' +
        '<td class="strong">' + esc(durStr) + '</td>' +
        '<td class="center">' + statusBadge + '</td>';
      durTbody.insertBefore(dTr, durTbody.firstChild);
    }
  }

  /* ---------- Dashboard ---------- */
  // Reset presensi hari ini (khusus uji coba): tombol [data-att-reset] atau URL ?reset=1
  function initAttReset() {
    const clear = () => {
      Object.keys(localStorage).filter((k) => /^matta\.att\.\d{8}$/.test(k)).forEach((k) => { try { localStorage.removeItem(k); } catch (e) {} });
      try { localStorage.removeItem("matta.attLogList"); } catch (e) {}
    };
    try {
      const u = new URL(location.href);
      if (u.searchParams.has("reset")) { clear(); u.searchParams.delete("reset"); history.replaceState(null, "", u.toString()); }
    } catch (e) {}
    $$("[data-att-reset]").forEach((b) => b.addEventListener("click", () => {
      confirmDialog(T("Reset presensi hari ini?"), T("Data Clock In / Clock Out hari ini akan dihapus supaya bisa dicoba lagi dari awal."), T("Reset"), true)
        .then((ok) => { if (!ok) return; clear(); store.set("matta.attResetDone", 1); store.set("matta.resetToast", 1); location.reload(); });
    }));
    if (store.get("matta.resetToast", 0)) { store.set("matta.resetToast", 0); setTimeout(() => toast(T("Presensi hari ini sudah direset. Silakan coba Clock In lagi.")), 400); }
  }
  function initDashboard() {
    const root = $("[data-page='dashboard']");
    if (!root) return;
    const DAYS = { en: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"], id: ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"] };
    const MONTHS = { en: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"], id: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"] };
    const GREET = { en: ["Good Morning", "Good Afternoon", "Good Evening"], id: ["Selamat Pagi", "Selamat Siang", "Selamat Malam"] };

    // Jam & salam
    const timeEl = $("[data-clock]"), dateEl = $("[data-today]"), greetEl = $("[data-greeting]");
    function tick() {
      const n = nowWIB(), h = n.getHours();
      if (timeEl) timeEl.textContent = pad(h) + ":" + pad(n.getMinutes());
      if (dateEl) dateEl.textContent = DAYS[lang][n.getDay()] + ", " + n.getDate() + " " + MONTHS[lang][n.getMonth()] + " " + n.getFullYear();
      if (greetEl) greetEl.textContent = GREET[lang][h < 12 ? 0 : h < 18 ? 1 : 2];
      renderAttend();
    }

    // ---------------- Absen masuk / pulang ----------------
    const WORK_RULE = { start: 8 * 60 + 30, tolerance: 9 * 60, duration: 9 * 60 };
    window.MATTA.workRule = WORK_RULE;
    const todayKey = () => { const n = nowWIB(); return "matta.att." + n.getFullYear() + pad(n.getMonth() + 1) + pad(n.getDate()); };
    let att = store.get(todayKey(), null);
    if (!att && store.get("matta.attResetDone", 0) !== 1) {
      const d = nowWIB();
      att = { in: new Date(d.getFullYear(), d.getMonth(), d.getDate(), 8, 15).getTime(), out: null, early: false };
      store.set(todayKey(), att);
    } else if (!att) {
      att = { in: null, out: null, early: false };
    }

    const card = $("[data-attend-card]"), cta = $("[data-attend-cta]");
    const CTA_ICO = {
      in: '<svg class="icon-clock-out" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 5 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>',
      out: '<svg class="icon-clock-out" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 5 12 10 7"></polyline><line x1="5" y1="12" x2="15" y2="12"></line></svg>',
      done: '<svg class="icon-clock-out" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
    };
    function setCta(state, label, title, disabled = false) {
      if (!cta) return;
      const isV4 = card && card.classList.contains("attend-card-v4");
      cta.className = (isV4 ? "btn btn-clock-out " : "btn btn-lg attend-cta ") + state;
      cta.dataset.state = state;
      cta.disabled = disabled || state === "done";
      cta.innerHTML = (CTA_ICO[state] || CTA_ICO.out) + ' <span data-attend-cta-label>' + label + '</span>';
      cta.title = title || "";
    }
    const fmt = (t) => { const d = new Date(t); return pad(d.getHours()) + ":" + pad(d.getMinutes()); };
    const hm = (t) => fmt(t);
    const minOf = (t) => { const d = new Date(t); return d.getHours() * 60 + d.getMinutes(); };
    function inStatus() {
      const m = minOf(att.in);
      if (m <= WORK_RULE.start) return { cls: "badge-green", text: "Tepat Waktu", key: "ok" };
      if (m <= WORK_RULE.tolerance) return { cls: "badge-amber", text: "Toleransi", key: "tol" };
      return { cls: "badge-red", text: "Terlambat", key: "late" };
    }
    function renderAttend() {
      if (!card) return;
      const title = $("[data-attend-title]", card), badge = $("[data-attend-badge]", card), time = $("[data-attend-time]", card);
      card.classList.remove("pending", "done", "late", "tol");
      if (!att.in) {
        card.classList.add("pending");
        if (title) title.textContent = "CLOCK IN TERCATAT";
        if (time) time.textContent = "--:--";
        if (badge) badge.textContent = "Belum Absen";
        setCta("in", "Clock In", T("Klik untuk absen masuk"), false);
      } else if (!att.out) {
        const st = inStatus();
        if (title) title.textContent = "CLOCK IN TERCATAT";
        if (time) time.textContent = hm(att.in);
        if (badge) badge.textContent = st.key === "late" ? "Terlambat" : "Tepat Waktu";
        if (st.key === "late") card.classList.add("late");

        const nw = nowWIB();
        const curMinutes = nw.getHours() * 60 + nw.getMinutes();
        const before1730 = curMinutes < (17 * 60 + 30);
        if (before1730) {
          setCta("out", T("Clock Out (Mulai 17:30)"), T("Clock Out baru dapat dilakukan mulai pukul 17:30 WIB"), true);
        } else {
          setCta("out", "Clock Out", T("Klik untuk absen pulang"), false);
        }
      } else {
        card.classList.add("done");
        if (title) title.textContent = "CLOCK OUT TERCATAT";
        if (time) time.textContent = hm(att.out);
        if (badge) badge.textContent = "Selesai";
        setCta("done", "Selesai Hari Ini", "Sudah absen pulang", true);
      }
    }
    function clockIn() {
      if (att.in) return;
      getAttendanceCoords().then((coords) => {
        att.in = nowWIB().getTime();
        att.out = null;
        att.early = false;
        att.inLat = coords.lat;
        att.inLng = coords.lng;
        att.inAddress = coords.address;
        store.set(todayKey(), att);
        recordAttendanceLog("in", att.in, coords);
        renderAttend();
        renderCal();
        toast(T("Absen masuk tercatat pukul") + " " + hm(att.in) + " WIB (Lat: " + coords.lat + ", Long: " + coords.lng + ").", "success");
      });
    }
    function clockOut() {
      if (!att.in || att.out) return;
      const nw = nowWIB();
      const curMinutes = nw.getHours() * 60 + nw.getMinutes();
      if (curMinutes < (17 * 60 + 30)) {
        toast(T("Clock Out belum dapat dilakukan sebelum pukul 17:30 WIB."), "error");
        return;
      }
      confirmDialog(T("Absen pulang sekarang?"), T("Jam pulang akan dicatat pukul") + " " + hm(nw.getTime()) + " WIB.", "Clock Out")
        .then((ok) => {
          if (!ok) return;
          getAttendanceCoords().then((coords) => {
            att.out = nowWIB().getTime();
            att.outLat = coords.lat;
            att.outLng = coords.lng;
            att.outAddress = coords.address;
            store.set(todayKey(), att);
            recordAttendanceLog("out", att.out, coords);
            renderAttend();
            renderCal();
            toast(T("Absen pulang tercatat. Sampai jumpa besok!"), "success");
          });
        });
    }
    if (cta) {
      cta.addEventListener("click", () => {
        if (cta.disabled) {
          if (cta.dataset.state === "out") {
            toast(T("Clock Out belum dapat dilakukan sebelum pukul 17:30 WIB."), "error");
          }
          return;
        }
        if (cta.dataset.state === "in") clockIn();
        else if (cta.dataset.state === "out") clockOut();
      });
    }

    // ---------------- Kalender presensi ----------------
    const calGrid = $("[data-cal-grid]"), calTitle = $("[data-cal-title]");
    const realToday = nowWIB();
    let view = new Date(realToday.getFullYear(), realToday.getMonth(), 1); // Tahun dan bulan sekarang (Oktober 2026)

    // Daftar Hari Libur Nasional & Tanggal Merah Indonesia
    const HOLIDAYS = {
      // 2026
      "2026-01-01": "Tahun Baru 2026 Masehi",
      "2026-01-16": "Cuti Bersama Isra Mi'raj",
      "2026-01-17": "Isra Mi'raj Nabi Muhammad SAW",
      "2026-02-16": "Cuti Bersama Tahun Baru Imlek",
      "2026-02-17": "Tahun Baru Imlek 2577 Kongzili",
      "2026-03-03": "Hari Suci Nyepi (Tahun Baru Saka 1948)",
      "2026-03-19": "Cuti Bersama Idul Fitri 1447 H",
      "2026-03-20": "Hari Raya Idul Fitri 1447 H",
      "2026-03-21": "Hari Raya Idul Fitri 1447 H",
      "2026-03-23": "Cuti Bersama Idul Fitri 1447 H",
      "2026-03-24": "Cuti Bersama Idul Fitri 1447 H",
      "2026-04-03": "Wafat Yesus Kristus (Jumat Agung)",
      "2026-04-05": "Kebangkitan Yesus Kristus (Paskah)",
      "2026-05-01": "Hari Buruh Internasional",
      "2026-05-14": "Kenaikan Yesus Kristus",
      "2026-05-27": "Hari Raya Idul Adha 1447 H",
      "2026-05-31": "Hari Raya Waisak 2570 BE",
      "2026-06-01": "Hari Lahir Pancasila",
      "2026-06-16": "Tahun Baru Islam 1448 H",
      "2026-08-17": "Hari Kemerdekaan RI (HUT RI ke-81)",
      "2026-08-25": "Maulid Nabi Muhammad SAW",
      "2026-12-24": "Cuti Bersama Hari Raya Natal",
      "2026-12-25": "Hari Raya Natal",
      // 2025
      "2025-01-01": "Tahun Baru 2025 Masehi",
      "2025-01-27": "Isra Mi'raj Nabi Muhammad SAW",
      "2025-01-29": "Tahun Baru Imlek 2576 Kongzili",
      "2025-03-29": "Hari Suci Nyepi",
      "2025-03-31": "Hari Raya Idul Fitri 1446 H",
      "2025-04-01": "Hari Raya Idul Fitri 1446 H",
      "2025-04-18": "Wafat Yesus Kristus",
      "2025-04-20": "Kebangkitan Yesus Kristus",
      "2025-05-01": "Hari Buruh Internasional",
      "2025-05-12": "Hari Raya Waisak",
      "2025-05-29": "Kenaikan Yesus Kristus",
      "2025-06-01": "Hari Lahir Pancasila",
      "2025-06-07": "Hari Raya Idul Adha",
      "2025-06-27": "Tahun Baru Islam",
      "2025-08-17": "Hari Kemerdekaan RI",
      "2025-09-05": "Maulid Nabi Muhammad SAW",
      "2025-12-25": "Hari Raya Natal"
    };

    function renderCal() {
      const y = view.getFullYear(), m = view.getMonth();
      if (calTitle) calTitle.textContent = MONTHS[lang][m] + " " + y;
      const first = new Date(y, m, 1).getDay();
      const days = new Date(y, m + 1, 0).getDate();
      const prevDays = new Date(y, m, 0).getDate();
      const cells = [];
      for (let i = first - 1; i >= 0; i--) cells.push({ d: prevDays - i, other: true });
      for (let d = 1; d <= days; d++) cells.push({ d });
      while (cells.length % 7) cells.push({ d: cells.length - days - first + 1, other: true });

      if (!calGrid) return;

      const isCurrentMonth = (y === realToday.getFullYear() && m === realToday.getMonth());
      const isFutureMonth = (y > realToday.getFullYear() || (y === realToday.getFullYear() && m > realToday.getMonth()));
      const isPastMonth = (y < realToday.getFullYear() || (y === realToday.getFullYear() && m < realToday.getMonth()));

      let countPresent = 0;
      let countLate = 0;
      let countAlpha = 0;
      let countCuti = 0;
      let countSakit = 0;

      calGrid.innerHTML = cells.map((c, i) => {
        const dow = i % 7; // 0 = Min, 1 = Sen, ..., 6 = Sab
        if (c.other) {
          const isSun = dow === 0;
          const isSat = dow === 6;
          const cls = "cal-day-cell other" + (isSun ? " is-sunday" : isSat ? " is-saturday" : "");
          return '<div class="' + cls + '"><span class="cal-num">' + c.d + '</span></div>';
        }

        const dateStr = y + "-" + pad(m + 1) + "-" + pad(c.d);
        const holidayName = HOLIDAYS[dateStr];
        const isSunday = (dow === 0);
        const isSaturday = (dow === 6);
        const isHoliday = !!holidayName || isSunday;
        const isToday = isCurrentMonth && (c.d === realToday.getDate());
        const isFutureDate = isFutureMonth || (isCurrentMonth && c.d > realToday.getDate());
        const isPastDate = isPastMonth || (isCurrentMonth && c.d < realToday.getDate());

        let cellClasses = ["cal-day-cell"];
        if (isToday) cellClasses.push("today");
        if (isHoliday) cellClasses.push("is-holiday");
        if (isSunday) cellClasses.push("is-sunday");
        if (isSaturday) cellClasses.push("is-saturday");

        let dotHtml = '';
        let titleAttr = holidayName ? ' title="' + esc(holidayName) + '"' : (isSunday ? ' title="Hari Minggu (Tanggal Merah)"' : (isSaturday ? ' title="Hari Sabtu (Akhir Pekan)"' : ''));

        // Indikator presensi & aturan keterlambatan (Regular: > 09:00 WIB, Non-Regular: > shift start):
        if (isFutureDate) {
          dotHtml = '';
        } else if (isToday) {
          if (att && att.in) {
            const inTime = hm(att.in);
            const isLate = inTime > "09:00";
            if (isLate) {
              dotHtml = '<span class="cal-dot dot-amber" title="Terlambat (Clock In ' + inTime + ' WIB - Batas Toleransi 09:00)"></span>';
              countLate++;
            } else {
              dotHtml = '<span class="cal-dot dot-green" title="Hadir (Clock In ' + inTime + ' WIB)"></span>';
              countPresent++;
            }
          } else {
            dotHtml = '';
          }
        } else if (isPastDate) {
          if (isCurrentMonth) {
            if (dateStr === "2026-10-02" || dateStr === "2026-10-06" || dateStr === "2026-10-16" || ABSENCES.includes(dateStr)) {
              dotHtml = '<span class="cal-dot dot-orange" title="Alpha (Tanpa Keterangan)"></span>';
              countAlpha++;
            } else if (dateStr === "2026-10-08" || dateStr === "2026-10-14") {
              dotHtml = '<span class="cal-dot dot-amber" title="Terlambat (Late-in)"></span>';
              countLate++;
            } else if (dateStr === "2026-10-01") {
              dotHtml = '<span class="cal-dot dot-blue" title="Cuti Tahunan"></span>';
              countCuti++;
            } else if (!isSunday && !isSaturday && !holidayName) {
              dotHtml = '<span class="cal-dot dot-green" title="Hadir Tepat Waktu"></span>';
              countPresent++;
            }
          } else if (isPastMonth) {
            if (!isSunday && !isSaturday && !holidayName) {
              dotHtml = '<span class="cal-dot dot-green" title="Hadir Tepat Waktu"></span>';
              countPresent++;
            }
          }
        }

        if (holidayName && !isSunday) {
          dotHtml = '<span class="cal-dot dot-red" title="' + esc(holidayName) + '"></span>';
        }

        if (isToday) {
          return '<div class="' + cellClasses.join(" ") + '"' + titleAttr + '><div class="cal-today-box">' + c.d + '</div>' + dotHtml + '</div>';
        }

        return '<div class="' + cellClasses.join(" ") + '"' + titleAttr + '><span class="cal-num">' + c.d + '</span>' + dotHtml + '</div>';
      }).join("");

      const presEl = $("[data-cal-present]");
      const lateEl = $("[data-cal-late]");
      const alphaEl = $("[data-cal-alpha]");
      const cutiEl = $("[data-cal-cuti]");
      const sakitEl = $("[data-cal-sakit]");
      if (presEl) presEl.textContent = countPresent;
      if (lateEl) lateEl.textContent = countLate;
      if (alphaEl) alphaEl.textContent = countAlpha;
      if (cutiEl) cutiEl.textContent = countCuti;
      if (sakitEl) sakitEl.textContent = countSakit;
    }
    const calPrev = $("[data-cal-prev]");
    const calNext = $("[data-cal-next]");
    if (calPrev) calPrev.addEventListener("click", () => { view.setMonth(view.getMonth() - 1); renderCal(); });
    if (calNext) calNext.addEventListener("click", () => { view.setMonth(view.getMonth() + 1); renderCal(); });

    // ---------------- Carousel info & pengumuman ----------------
    const carSection = $(".info-carousel-section");
    const carTrack = $("[data-info-track]"), carDots = $$("[data-info-dots] [data-dot]");
    const carPrev = $("[data-info-prev]"), carNext = $("[data-info-next]");
    if (carTrack) {
      let carIndex = 0;
      const totalSlides = carTrack.children.length;
      let autoTimer = null;
      const AUTO_DELAY = 3500; // 3.5 detik per slide bergerak horizontal ke kiri

      function showCarSlide(idx) {
        carIndex = (idx + totalSlides) % totalSlides;
        carTrack.style.transform = "translateX(-" + (carIndex * 100) + "%)";
        carDots.forEach((d, i) => d.classList.toggle("active", i === carIndex));
      }

      function nextSlide() {
        showCarSlide(carIndex + 1);
      }

      function startAutoSlide() {
        stopAutoSlide();
        autoTimer = setInterval(nextSlide, AUTO_DELAY);
      }

      function stopAutoSlide() {
        if (autoTimer) {
          clearInterval(autoTimer);
          autoTimer = null;
        }
      }

      function restartAutoSlide() {
        stopAutoSlide();
        startAutoSlide();
      }

      if (carPrev) carPrev.addEventListener("click", () => { showCarSlide(carIndex - 1); restartAutoSlide(); });
      if (carNext) carNext.addEventListener("click", () => { showCarSlide(carIndex + 1); restartAutoSlide(); });
      carDots.forEach((d, i) => d.addEventListener("click", () => { showCarSlide(i); restartAutoSlide(); }));

      // Pause saat kursor mouse diarahkan/hover, lanjut saat mouse keluar
      if (carSection) {
        carSection.addEventListener("mouseenter", stopAutoSlide);
        carSection.addEventListener("mouseleave", startAutoSlide);
      }

      // Pause saat tab browser tidak aktif
      document.addEventListener("visibilitychange", () => {
        if (document.hidden) stopAutoSlide();
        else startAutoSlide();
      });

      // Gestur swipe di layar sentuh
      let startX = 0, isDragging = false;
      carTrack.addEventListener("touchstart", (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
        stopAutoSlide();
      }, { passive: true });
      carTrack.addEventListener("touchend", (e) => {
        if (!isDragging) return;
        isDragging = false;
        const diffX = e.changedTouches[0].clientX - startX;
        if (diffX > 40) showCarSlide(carIndex - 1);
        else if (diffX < -40) showCarSlide(carIndex + 1);
        startAutoSlide();
      }, { passive: true });

      // Jalankan otomatis
      startAutoSlide();
    }

    // Kartu modul → detail tabel dinamis
    const MODULES = JSON.parse($("#module-data").textContent);
    const detail = $("[data-module-detail]");
    let current = store.get("matta.dashModule", "overtime");
    if (!MODULES[current]) current = "overtime";
    function statusBadge(s) {
      const map = { Approved: "badge-green", Pending: "badge-amber", Rejected: "badge-red", "Waiting Approval": "badge-orange" };
      return '<span class="badge badge-pill badge-dot ' + (map[s] || "badge-gray") + '">' + esc(s) + "</span>";
    }
    // Indikator di kartu modul (Dashboard V2): tampil hanya jika user punya data di modul tsb
    function renderIndicators() {
      $$("[data-m2-ind]").forEach((el) => {
        const key = el.closest("[data-module-card]").dataset.moduleCard;
        const rows = store.get("matta.extra." + key, []).concat(MODULES[key].rows);
        const wait = rows.filter((r) => r.status === "Waiting Approval" || r.status === "Pending").length;
        el.hidden = !rows.length;
        el.className = "m2-ind" + (wait ? " wait" : "");
        el.innerHTML = wait ? '<i aria-hidden="true"></i>' + esc(TF("{n} menunggu", { n: wait })) : esc(rows.length === 1 ? T("1 data") : TF("{n} data", { n: rows.length }));
        el.title = TF("{n} pengajuan Anda di modul ini", { n: rows.length });
        el.closest("[data-module-card]").classList.toggle("is-empty", !rows.length);
      });
    }
    function updateCardBadges() {
      $$("[data-module-card]").forEach((c) => {
        const key = c.dataset.moduleCard;
        if (!key || !MODULES[key]) return;
        const badge = $(".m-corner-badge", c);
        if (!badge) return;
        const rows = (store.get("matta.extra." + key, [])).concat(MODULES[key].rows);
        if (key === "confirmation" && rows.length >= 5) {
          badge.textContent = rows.length > 5 ? "5+" : "5";
          badge.classList.add("badge-plus");
        } else {
          badge.textContent = rows.length;
          badge.classList.remove("badge-plus");
        }
      });
    }

    function makeRowDetail(r, modKey) {
      const m = MODULES[modKey] || MODULES[current] || {};
      return JSON.stringify({
        eyebrow: m.name || "",
        title: r.id,
        module: modKey || current,
        img: "assets/img/modules/" + (modKey || current) + ".png",
        badges: [[r.status, r.status === "Approved" ? "green" : r.status === "Rejected" ? "red" : "orange"]],
        person: { name: r.name, nip: r.nip, role: r.role + " · " + r.unit },
        sections: [{ h: "Detail Pengajuan", rows: [["ID Formulir", r.id], ["Jenis", r.form], ["Tanggal", r.date], ["Jam", r.time], ["Unit Kerja", r.unit]] }],
        attachment: r.file && r.file !== "-" ? r.file : null,
        timeline: [
          { t: "Diajukan", by: r.name, at: r.date + " " + r.time, state: "done" },
          { t: "Persetujuan Atasan", by: "Muhammad Rizky", at: r.status === "Approved" ? r.date : "", state: r.status === "Approved" ? "done" : "current" },
          { t: "Verifikasi Human Capital", by: "Dewi Kaniawati", at: "", state: r.status === "Approved" ? "done" : "todo" }
        ]
      }).replace(/'/g, "&#39;");
    }

    function renderModule() {
      const mod = MODULES[current];
      if (!mod || !detail) return;
      detail.setAttribute("data-module", current);
      $$("[data-module-card]").forEach((c) => c.setAttribute("aria-pressed", String(c.dataset.moduleCard === current)));
      const mdTitle = $("[data-md-title]", detail);
      if (mdTitle) mdTitle.textContent = T(mod.list);
      const mdDesc = $("[data-md-desc]", detail);
      if (mdDesc) mdDesc.textContent = T(mod.desc);
      const mdImg = $("[data-md-img]", detail);
      if (mdImg) mdImg.src = "assets/img/modules/" + current + ".png";
      const iconBox = $("[data-md-iconbox]", detail), card = $('[data-module-card="' + current + '"]');
      if (iconBox && card) {
        const src = $(".m-ico-box, .m2-ico", card);
        if (src) iconBox.innerHTML = src.innerHTML;
        iconBox.style.setProperty("--ic", card.style.getPropertyValue("--ic"));
        iconBox.style.setProperty("--ib", card.style.getPropertyValue("--ib"));
      }
      detail.classList.remove("swap"); void detail.offsetWidth; detail.classList.add("swap");
      const mdApplyLbl = $("[data-md-apply-label]", detail);
      if (mdApplyLbl) mdApplyLbl.textContent = T(mod.apply);
      const mdLink = $("[data-md-link]", detail);
      if (mdLink) mdLink.href = mod.href;
      renderIndicators();
      updateCardBadges();
      const rows = (store.get("matta.extra." + current, [])).concat(mod.rows);
      const mdBody = $("[data-md-body]", detail);
      if (mdBody) {
        mdBody.innerHTML = rows.length ? "" : '<tr class="md-empty"><td colspan="8">' + esc(T("Belum ada pengajuan.")) + " " + esc(T("Klik tombol di atas untuk membuat pengajuan baru.")) + "</td></tr>";
        if (rows.length) {
          mdBody.innerHTML = rows.map((r, i) =>
            "<tr data-detail='" + makeRowDetail(r, current) + "'><td>" + (i + 1) + '</td><td><span class="id-chip">' + esc(r.id) + "</span></td>" +
            '<td><div class="doc-name">' + esc(r.form) + '</div><a class="doc-link" href="#" data-fake-file="' + esc(r.file) + '">' + ICON.link + esc(r.file) + "</a></td>" +
            '<td><div class="person-name">' + esc(r.name) + '</div><div class="person-sub mono">' + esc(r.nip) + "</div></td>" +
            "<td>" + esc(r.role) + "</td><td>" + esc(r.unit) + '</td><td><div class="doc-name">' + esc(r.date) + '</div><div class="person-sub">' + esc(r.time) + "</div></td>" +
            "<td>" + statusBadge(r.status) + "</td></tr>").join("");
        }
      }
      const mdInfo = $("[data-md-info]", detail);
      if (mdInfo) {
        mdInfo.textContent = T("Menampilkan") + " 1–" + rows.length + " " + T("dari") + " " + rows.length + " " + T("data " + mod.short.toLowerCase());
      }
      store.set("matta.dashModule", current);
    }

    // Popup modal untuk kartu dashboard
    function openCardPopup(modKey) {
      const mod = MODULES[modKey];
      if (!mod) return;
      const pop = $("#modal-card-popup");
      if (!pop) return;

      const rows = (store.get("matta.extra." + modKey, [])).concat(mod.rows);
      const total = rows.length;
      const approved = rows.filter((r) => r.status === "Approved").length;
      const waiting = rows.filter((r) => r.status === "Waiting Approval" || r.status === "Pending").length;

      $("[data-card-pop-title]", pop).textContent = mod.name;
      $("[data-card-pop-sub]", pop).textContent = mod.desc;

      const popImg = $("[data-card-pop-img]", pop);
      if (popImg) {
        popImg.src = "assets/img/modules/" + modKey + ".png";
        popImg.alt = mod.name;
      }

      const statTotal = $("[data-pop-stat-total]", pop);
      if (statTotal) statTotal.textContent = total;
      const statApp = $("[data-pop-stat-approved]", pop);
      if (statApp) statApp.textContent = approved;
      const statWait = $("[data-pop-stat-waiting]", pop);
      if (statWait) statWait.textContent = waiting;

      const statLbls = $$("[data-card-pop-stats] .card-pop-stat-lbl", pop);
      if (statLbls[0]) statLbls[0].textContent = "Total Pengajuan";
      if (statLbls[1]) statLbls[1].textContent = "Disetujui";
      if (statLbls[2]) statLbls[2].textContent = "Menunggu";

      const tbody = $("[data-card-pop-tbody]", pop);
      if (tbody) {
        if (!rows.length) {
          tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--slate-400)">Belum ada riwayat pengajuan di modul ini.</td></tr>';
        } else {
          tbody.innerHTML = rows.map((r) => {
            return '<tr class="clickable" data-detail=\'' + makeRowDetail(r, modKey) + '\'>' +
              '<td><span class="id-chip">' + esc(r.id) + '</span></td>' +
              '<td><div class="strong" style="font-size:13px">' + esc(r.form) + '</div><div class="muted-2" style="font-size:11px">' + esc(r.name) + ' (' + esc(r.role) + ')</div></td>' +
              '<td><div style="font-size:12px">' + esc(r.date) + '</div><div class="muted-2" style="font-size:11px">' + esc(r.time) + '</div></td>' +
              '<td>' + statusBadge(r.status) + '</td>' +
              '<td style="text-align:right"><button type="button" class="btn btn-outline btn-xs" style="padding:4px 8px" data-row-detail>Detail</button></td>' +
            '</tr>';
          }).join("");
        }
      }

      const linkBtn = $("[data-card-pop-link]", pop);
      if (linkBtn) linkBtn.href = mod.href;
      const linkLbl = $("[data-card-pop-link-label]", pop);
      if (linkLbl) linkLbl.textContent = "Buka Halaman Lengkap";

      const applyBtn = $("[data-card-pop-apply]", pop);
      if (applyBtn) {
        applyBtn.style.display = "";
        applyBtn.onclick = () => {
          closeModal("modal-card-popup");
          $("[data-apply-title]").textContent = mod.apply;
          applyForm.reset();
          useFieldset(applyForm, modKey);
          openModal("modal-apply");
        };
        const applyLbl = $("[data-card-pop-apply-label]", pop);
        if (applyLbl) applyLbl.textContent = "Ajukan Sekarang";
      }

      openModal("modal-card-popup");
    }

    function openAttendPopup() {
      const pop = $("#modal-card-popup");
      if (!pop) return;
      $("[data-card-pop-title]", pop).textContent = "Detail Presensi Hari Ini";
      $("[data-card-pop-sub]", pop).textContent = "Catatan waktu masuk, jam kerja, dan lokasi presensi karyawan";

      const popImg = $("[data-card-pop-img]", pop);
      if (popImg) {
        popImg.src = "assets/img/logo-matta.png";
        popImg.alt = "MATTA Presensi";
      }

      $("[data-pop-stat-total]", pop).textContent = "08:15";
      $("[data-pop-stat-approved]", pop).textContent = "Tepat Waktu";
      $("[data-pop-stat-waiting]", pop).textContent = "HQ Jakarta";

      const statLbls = $$("[data-card-pop-stats] .card-pop-stat-lbl", pop);
      if (statLbls[0]) statLbls[0].textContent = "Clock In";
      if (statLbls[1]) statLbls[1].textContent = "Status";
      if (statLbls[2]) statLbls[2].textContent = "Lokasi";

      const tbody = $("[data-card-pop-tbody]", pop);
      if (tbody) {
        tbody.innerHTML = `
          <tr><td><b>Jadwal Kerja</b></td><td colspan="4">Reguler (08:30 – 17:30 WIB)</td></tr>
          <tr><td><b>Waktu Clock In</b></td><td colspan="4"><span class="pill pill-blue">08:15:22 WIB</span> (Tepat Waktu)</td></tr>
          <tr><td><b>Target Jam Kerja</b></td><td colspan="4">9.0 Jam (Termasuk istirahat 1 jam)</td></tr>
          <tr><td><b>Metode Presensi</b></td><td colspan="4"><span class="src src-matta">MATTA Mobile / Web</span></td></tr>
          <tr><td><b>Lokasi Presensi</b></td><td colspan="4">Headquarter — Jl. TB Simatupang No. 18, Jakarta Selatan</td></tr>
        `;
      }

      const linkBtn = $("[data-card-pop-link]", pop);
      if (linkBtn) linkBtn.href = "attendance-log.html";
      const linkLbl = $("[data-card-pop-link-label]", pop);
      if (linkLbl) linkLbl.textContent = "Buka Attendance Log";

      const applyBtn = $("[data-card-pop-apply]", pop);
      if (applyBtn) {
        applyBtn.onclick = () => {
          closeModal("modal-card-popup");
          const cta = $("[data-attend-cta]");
          cta && cta.click();
        };
        const applyLbl = $("[data-card-pop-apply-label]", pop);
        if (applyLbl) applyLbl.textContent = "Catat Presensi";
      }

      openModal("modal-card-popup");
    }

    function openCalPopup() {
      const pop = $("#modal-card-popup");
      if (!pop) return;
      $("[data-card-pop-title]", pop).textContent = "Kalender Presensi — Oktober 2026";
      $("[data-card-pop-sub]", pop).textContent = "Rekapitulasi kehadiran, cuti, sakit, dan alpha selama bulan berjalan";

      const popImg = $("[data-card-pop-img]", pop);
      if (popImg) {
        popImg.src = "assets/img/logo-matta.png";
        popImg.alt = "Kalender";
      }

      $("[data-pop-stat-total]", pop).textContent = "10";
      $("[data-pop-stat-approved]", pop).textContent = "2";
      $("[data-pop-stat-waiting]", pop).textContent = "3";

      const statLbls = $$("[data-card-pop-stats] .card-pop-stat-lbl", pop);
      if (statLbls[0]) statLbls[0].textContent = "Hadir (Hari)";
      if (statLbls[1]) statLbls[1].textContent = "Cuti (Hari)";
      if (statLbls[2]) statLbls[2].textContent = "Alpha / Off";

      const tbody = $("[data-card-pop-tbody]", pop);
      if (tbody) {
        tbody.innerHTML = `
          <tr><td><b>Persentase Kehadiran</b></td><td colspan="4"><b style="color:var(--green-600)">76.9%</b> dari 13 hari kerja efektif</td></tr>
          <tr><td><b>Total Jam Kerja</b></td><td colspan="4">88 Jam 45 Menit</td></tr>
          <tr><td><b>Terlambat (Late)</b></td><td colspan="4">0 Hari (Disiplin 100%)</td></tr>
          <tr><td><b>Pulang Awal (Early)</b></td><td colspan="4">0 Hari</td></tr>
          <tr><td><b>Sisa Hak Cuti Tahunan</b></td><td colspan="4"><span class="pill pill-blue">8 Hari Tersisa</span> (dari total 12 hari)</td></tr>
        `;
      }

      const linkBtn = $("[data-card-pop-link]", pop);
      if (linkBtn) linkBtn.href = "attendance-recap.html";
      const linkLbl = $("[data-card-pop-link-label]", pop);
      if (linkLbl) linkLbl.textContent = "Buka Attendance Recap";

      const applyBtn = $("[data-card-pop-apply]", pop);
      if (applyBtn) {
        applyBtn.onclick = () => {
          window.location.href = "attendance-recap.html";
        };
        const applyLbl = $("[data-card-pop-apply-label]", pop);
        if (applyLbl) applyLbl.textContent = "Lihat Rekap Lengkap";
      }

      openModal("modal-card-popup");
    }

    function openInfoPopup(title, badge, img) {
      const pop = $("#modal-card-popup");
      if (!pop) return;
      $("[data-card-pop-title]", pop).textContent = title;
      $("[data-card-pop-sub]", pop).textContent = badge + " — Informasi resmi internal MATTA Enterprise";

      const popImg = $("[data-card-pop-img]", pop);
      if (popImg) {
        popImg.src = img || "assets/img/logo-matta.png";
        popImg.alt = title;
      }

      $("[data-pop-stat-total]", pop).textContent = "Aktif";
      $("[data-pop-stat-approved]", pop).textContent = "Semua";
      $("[data-pop-stat-waiting]", pop).textContent = "Baru";

      const statLbls = $$("[data-card-pop-stats] .card-pop-stat-lbl", pop);
      if (statLbls[0]) statLbls[0].textContent = "Status Feed";
      if (statLbls[1]) statLbls[1].textContent = "Target Karyawan";
      if (statLbls[2]) statLbls[2].textContent = "Kategori";

      const tbody = $("[data-card-pop-tbody]", pop);
      if (tbody) {
        tbody.innerHTML = `
          <tr><td><b>Judul Informasi</b></td><td colspan="4"><b>` + esc(title) + `</b></td></tr>
          <tr><td><b>Kategori</b></td><td colspan="4"><span class="badge badge-pill badge-green">` + esc(badge) + `</span></td></tr>
          <tr><td><b>Tanggal Terbit</b></td><td colspan="4">24 Oktober 2026</td></tr>
          <tr><td><b>Diterbitkan Oleh</b></td><td colspan="4">Corporate Secretary & Human Capital MATTA</td></tr>
          <tr><td><b>Ringkasan</b></td><td colspan="4">Pengumuman penting dan pembaruan kegiatan operasional perusahaan untuk seluruh karyawan PT MATTA Solusindo. Silakan periksa detail lengkap pada menu Feeds.</td></tr>
        `;
      }

      const linkBtn = $("[data-card-pop-link]", pop);
      if (linkBtn) linkBtn.href = "feeds.html";
      const linkLbl = $("[data-card-pop-link-label]", pop);
      if (linkLbl) linkLbl.textContent = "Buka Halaman Feeds";

      const applyBtn = $("[data-card-pop-apply]", pop);
      if (applyBtn) {
        applyBtn.onclick = () => {
          window.location.href = "feeds.html";
        };
        const applyLbl = $("[data-card-pop-apply-label]", pop);
        if (applyLbl) applyLbl.textContent = "Lihat Semua Berita";
      }

      openModal("modal-card-popup");
    }

    $$("[data-module-card]").forEach((c) => c.addEventListener("click", () => {
      const key = c.dataset.moduleCard;
      if (!key || !MODULES[key]) return;
      // Popup HANYA untuk kartu MORE (confirmation)
      if (key === "confirmation") {
        openModal("modal-card-popup");
      } else {
        current = key;
        renderModule();
      }
    }));

    // Kartu pilihan di dalam Pop-up MORE (misal Card Konfirmasi)
    const popMore = $("#modal-card-popup");
    if (popMore) {
      $$("[data-pop-select]", popMore).forEach((btn) => {
        btn.addEventListener("click", () => {
          const targetKey = btn.dataset.popSelect || "confirmation";
          closeModal("modal-card-popup");
          if (MODULES[targetKey]) {
            current = targetKey;
            renderModule();
            setTimeout(() => {
              detail.scrollIntoView({ behavior: "smooth", block: "start" });
            }, 120);
            toast(T("Menampilkan tabel") + " " + T(MODULES[targetKey].list), "success");
          }
        });
      });
    }

    detail.addEventListener("click", (e) => {
      const f = e.target.closest("[data-fake-file]");
      if (f) { e.preventDefault(); toast(T("Lampiran") + " " + f.dataset.fakeFile + " " + T("(contoh, belum terhubung ke server).")); }
    });

    // Form pengajuan dari dashboard
    const applyForm = $("#form-apply");
    $("[data-md-apply]", detail).addEventListener("click", () => {
      if (current === "exit-clearance") {
        window.location.href = "form-exit-clearance.html?open=statement";
        return;
      }
      $("[data-apply-title]").textContent = MODULES[current].apply;
      applyForm.reset();
      useFieldset(applyForm, current);
      openModal("modal-apply");
    });
    applyForm.addEventListener("submit", (e) => {
      e.preventDefault();
      if (!validateForm(applyForm)) return;
      const mod = MODULES[current];
      const fd = new FormData(applyForm);
      const start = new Date(fd.get("start"));
      const n = nowWIB();
      const fileInput = applyForm.querySelector("input[type=file]:not(:disabled)");
      const extra = store.get("matta.extra." + current, []);
      extra.unshift({
        id: "#" + mod.prefix + "-" + n.getFullYear() + "-" + String(Math.floor(Math.random() * 9000) + 1000),
        form: mod.formName, file: fileInput.files[0] ? fileInput.files[0].name : "-",
        name: "M. Daffa Dzaky", nip: "P0826155", role: "Frontend Developer", unit: "Infrastructure",
        date: pad(start.getDate()) + " " + MONTHS.id[start.getMonth()].slice(0, 3) + " " + start.getFullYear(),
        time: pad(n.getHours()) + ":" + pad(n.getMinutes()) + " WIB", status: "Waiting Approval"
      });
      store.set("matta.extra." + current, extra);
      closeModal("modal-apply");
      renderModule();
      toast(T(mod.short) + " " + T("berhasil diajukan dan menunggu persetujuan."));
    });

    document.addEventListener("matta:lang", () => { tick(); renderAttend(); renderCal(); renderModule(); });
    tick(); setInterval(tick, 1000);
    renderAttend(); renderCal(); renderModule();
  }

  /* ---------- Feeds ---------- */
  function initFeeds() {
    const root = $("[data-page='feeds']");
    if (!root) return;
    const list = $("[role=tablist]", root);
    const title = $("[data-feeds-title]");
    function apply(status) {
      let shown = 0;
      $$(".feed-card", root).forEach((c) => { const ok = c.dataset.status === status; c.hidden = !ok; if (ok) shown++; });
      $("[data-feed-empty]").hidden = shown > 0;
      title.textContent = status === "active" ? "Feeds Aktif" : "Feeds Non Aktif";
      const sub = $("[data-feeds-sub]"), note = $("[data-archive-note]");
      if (sub) sub.textContent = status === "active" ? "Pengumuman yang sedang berlaku dan tampil untuk seluruh karyawan." : "Arsip pengumuman yang sudah tidak berlaku.";
      if (note) note.hidden = status === "active";
      $$(".feed-card", root).forEach((c) => { c.style.animation = "none"; void c.offsetWidth; c.style.animation = ""; });
      store.set("matta.feedTab", status);
    }
    list.addEventListener("tabchange", (e) => apply(e.detail.dataset.status));
    const saved = store.get("matta.feedTab", "active");
    const tab = $('[data-status="' + saved + '"]', list);
    if (tab && saved !== "active") tab.click(); else apply("active");
  }

  /* ---------- Feeds versi 2 ---------- */
  function initFeedsV2() {
    const btn = $("[data-f2-toggle]");
    if (!btn) return;
    const arch = document.getElementById(btn.getAttribute("aria-controls"));
    const label = $("[data-f2-toggle-label]", btn);
    btn.addEventListener("click", () => {
      const open = arch.hidden;
      arch.hidden = !open;
      btn.setAttribute("aria-expanded", String(open));
      label.textContent = open ? "Sembunyikan Arsip" : "Lihat Semua";
      if (open) setTimeout(() => arch.scrollIntoView({ behavior: "smooth", block: "start" }), 50);
    });
  }

  /* ---------- Detail feed ---------- */
  function initFeedDetail() {
    const root = $("[data-page='feed-detail']");
    if (!root) return;
    const FEEDS = JSON.parse($("#feed-data").textContent);
    const slug = new URLSearchParams(location.search).get("id") || FEEDS[0].slug;
    const f = FEEDS.find((x) => x.slug === slug);
    if (!f) {
      root.innerHTML = '<div class="empty-state">Feed tidak ditemukan. <a class="link-more" href="feeds.html">Kembali ke Feeds</a></div>';
      return;
    }
    document.title = f.title + " — MATTA";
    const crumb = $("[data-crumb-current]"); if (crumb) crumb.textContent = f.title;
    const pref = store.get("matta.ui.feeds", "v1");
    const feedsHref = pref === "v2" ? "feeds-v2.html" : "feeds.html";
    const inactive = f.status !== "active";
    root.classList.toggle("is-archived", inactive);
    const want = pref === "v2" ? "v2" : "v1";
    const sameSet = (x) => (f.set === "both" ? (x.set === "both" || x.set === want) : (x.set === f.set || x.set === "both"));
    const iconDoc = '<svg class="icon icon-lg" viewBox="0 0 24 24"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>';
    const words = [f.lead].concat(...f.sections.map((s) => s.paras)).join(" ").replace(/<[^>]+>/g, "").split(/\s+/).length;
    const mins = Math.max(1, Math.round(words / 200));
    const toc = f.sections.map((s) => '<li><a href="#' + s.id + '" data-toc="' + s.id + '">' + esc(s.h) + "</a></li>").join("");
    const body = f.sections.map((s) => '<section id="' + s.id + '" class="art-section"><h2>' + esc(s.h) + "</h2>" + s.paras.map((p) => "<p>" + p + "</p>").join("") + "</section>").join("");
    const more = FEEDS.filter((x) => x.slug !== f.slug && x.status === f.status && sameSet(x)).slice(0, 3).map((x) =>
      '<a class="more-card" href="feed-detail.html?id=' + x.slug + '">' + (x.img ? '<img src="assets/img/feeds/' + x.img + '.jpg" alt="" loading="lazy">' : '<span class="more-ph">' + iconDoc + "</span>") + '<span class="more-tag">' + esc(x.cat || x.tag) + '</span><span class="more-title">' + esc(x.title) + "</span></a>").join("");
    root.innerHTML =
      '<div class="read-progress" aria-hidden="true"><span data-progress></span></div>' +
      '<article class="article">' +
      (inactive ? '<div class="art-archived"><svg class="icon icon-sm" viewBox="0 0 24 24"><rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/></svg><span>' + T("Feed ini sudah tidak aktif dan disimpan sebagai arsip.") + '</span></div>' : "") +
      '<a class="art-back" href="' + feedsHref + '"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>Kembali ke Feeds</a>' +
      '<h1 class="art-title">' + esc(f.title) + "</h1>" +
      '<dl class="art-meta"><div><dt>Ditulis oleh</dt><dd>' + esc(f.author) + "</dd></div><div><dt>Dipublikasikan</dt><dd>" + esc(f.date) + "</dd></div><div><dt>Waktu baca</dt><dd>" + mins + " " + T("menit") + "</dd></div><div><dt>Kategori</dt><dd>" + esc(f.tagLabel) + "</dd></div></dl>" +
      (f.img ? '<figure class="art-hero"><img src="assets/img/feeds/' + f.img + '.jpg" alt="' + esc(f.title) + '"><figcaption>' + esc(f.caption) + "</figcaption></figure>" : "") +
      '<div class="art-grid"><div class="art-body"><p class="art-lead">' + f.lead + "</p>" + body + "</div>" +
      '<aside class="art-aside"><div class="aside-block"><div class="aside-label">Di artikel ini</div><ul class="aside-list">' + toc + "</ul></div>" +
      '<div class="aside-block"><div class="aside-label">Bagikan</div><ul class="aside-list">' +
      '<li><button type="button" data-share="copy">Salin tautan</button></li>' +
      '<li><a data-share="wa" target="_blank" rel="noopener">WhatsApp</a></li>' +
      '<li><a data-share="mail">Email</a></li></ul></div></aside></div>' +
      "</article>" +
      (more ? '<section class="art-more"><h2>Feed Lainnya</h2><div class="more-grid">' + more + "</div></section>" : "");

    // Bagikan
    const url = location.href;
    $("[data-share=wa]", root).href = "https://wa.me/?text=" + encodeURIComponent(f.title + " — " + url);
    $("[data-share=mail]", root).href = "mailto:?subject=" + encodeURIComponent(f.title) + "&body=" + encodeURIComponent(url);
    $("[data-share=copy]", root).addEventListener("click", () => {
      (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(() => toast("Tautan disalin."), () => toast("Salin tautan dari address bar browser.", "error"));
    });

    // Daftar isi aktif + progres baca
    const links = $$("[data-toc]", root);
    const bar = $("[data-progress]", root);
    const art = $(".article", root);
    function onScroll() {
      const r = art.getBoundingClientRect();
      const total = r.height - window.innerHeight;
      bar.style.width = Math.max(0, Math.min(1, -r.top / (total > 0 ? total : 1))) * 100 + "%";
      let current = links.length ? links[0].dataset.toc : null;
      f.sections.forEach((s) => { const el = document.getElementById(s.id); if (el && el.getBoundingClientRect().top < 140) current = s.id; });
      if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4 && links.length) {
        // sudah di dasar halaman: tandai bagian terakhir yang terlihat
        const last = f.sections.filter((s) => { const el = document.getElementById(s.id); return el && el.getBoundingClientRect().top < window.innerHeight; }).pop();
        if (last) current = last.id;
      }
      links.forEach((l) => l.classList.toggle("active", l.dataset.toc === current));
    }
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }

  /* ---------- Health ---------- */
  function initHealth() {
    const form = $("#form-health");
    if (!form) return;
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      let firstBad = null;
      $$(".acc-item", form).forEach((item) => {
        const req = $$("[required]", item);
        const bad = req.some((i) => !i.value);
        item.classList.toggle("has-error", bad);
        req.forEach((i) => { i.classList.toggle("is-invalid", !i.value); const f = i.closest(".field"); f && f.classList.toggle("has-error", !i.value); });
        if (bad && !firstBad) firstBad = item;
      });
      if (firstBad) {
        const t = $("[data-acc-trigger]", firstBad), p = document.getElementById(t.getAttribute("aria-controls"));
        t.setAttribute("aria-expanded", "true"); p.classList.add("open");
        toast("Lengkapi semua pertanyaan Ring 1–3 terlebih dahulu.", "error");
        return;
      }
      const data = Object.fromEntries(new FormData(form));
      const risky = Object.values(data).some((v) => v === "tidak-sehat" || v === "ya");
      const res = $("[data-health-result]");
      res.className = "badge badge-pill badge-dot " + (risky ? "badge-amber" : "badge-green");
      res.textContent = risky ? "Perlu perhatian — HR/K3 akan menghubungi Anda" : "Terkirim — kondisi aman";
      res.hidden = false;
      store.set("matta.health." + nowWIB().toDateString(), data);
      toast(risky ? "Formulir terkirim. Tim K3 akan menindaklanjuti." : "Formulir kesehatan berhasil dikirim. Terima kasih!");
    });
    $$("select", form).forEach((s) => s.addEventListener("change", () => s.closest(".acc-item").classList.remove("has-error")));
  }

  /* ---------- Payroll ---------- */
  function initPayroll() {
    const root = $("[data-page='payroll']");
    if (!root) return;
    root.addEventListener("click", (e) => {
      const dl = e.target.closest("[data-slip-download]");
      const view = e.target.closest("[data-slip-view]");
      const tr = (dl || view) && (dl || view).closest("tr");
      if (!tr) return;
      const m = $("#modal-slip");
      $("[data-slip-period]", m).textContent = tr.dataset.month + " " + tr.dataset.year;
      openModal("modal-slip");
      if (dl) setTimeout(() => window.print(), 300);
    });
    $("[data-slip-print]") && $("[data-slip-print]").addEventListener("click", () => window.print());
  }

  /* ---------- Propose (lembur) ---------- */
  function initPropose() {
    const form = $("#form-propose");
    if (!form) return;
    const card = form.dataset.table ? document.getElementById(form.dataset.table) : $("[data-datatable]");
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      if (!validateForm(form)) return;
      const fd = new FormData(form);
      if (fd.get("end") <= fd.get("start")) { const i = form.elements.end; i.classList.add("is-invalid"); i.closest(".field").classList.add("has-error"); toast("Jam selesai harus setelah jam mulai.", "error"); return; }
      const approver = form.elements.approver.selectedOptions[0];
      const n = nowWIB();
      const created = n.getFullYear() + "-" + pad(n.getMonth() + 1) + "-" + pad(n.getDate()) + " " + pad(n.getHours()) + ":" + pad(n.getMinutes());
      const tr = document.createElement("tr");
      tr.className = "row-new";
      tr.dataset.status = "waiting";
      tr.dataset.detail = JSON.stringify({ eyebrow: "Overtime Propose", title: "Lembur " + fd.get("date"), icon: "clock",
        badges: [["Waiting Approval", "orange"]], person: { name: "M. Daffa Dzaky Pratama", nip: "P0826155", role: "Frontend Developer" },
        sections: [{ h: "Detail Pengajuan", rows: [["Tanggal Lembur", fd.get("date")], ["Jam", fd.get("start") + " - " + fd.get("end") + " WIB"], ["Alasan", fd.get("reason")], ["Approved By", approver.textContent], ["Approved NIP", approver.value]] }],
        timeline: [{ t: "Diajukan", by: "M. Daffa Dzaky Pratama", at: created, state: "done" }, { t: "Persetujuan Atasan", by: approver.textContent, at: "", state: "current" }] });
      tr.innerHTML = '<td class="num" data-dt-no></td><td>' + created + '</td><td class="strong">M. Daffa Dzaky Pratama</td><td class="mono">P0826155</td><td>' + esc(approver.textContent) + '</td><td class="mono">' + esc(approver.value) + '</td><td class="center"><span class="badge badge-pill badge-orange">Waiting Approval</span></td>';
      const tbody = $("table.dt tbody", card);
      tbody.insertBefore(tr, tbody.firstChild);
      tables.get(card).refresh();
      closeModal("modal-propose");
      form.reset();
      toast(T("Pengajuan lembur terkirim ke") + " " + approver.textContent + ".");
    });
  }

  /* ---------- Halaman modul Forms ---------- */
  function initModulePage() {
    const form = $("#form-module");
    if (!form) return;
    const card = $("[data-datatable]");
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      if (!validateForm(form)) return;
      const fd = new FormData(form);
      const n = nowWIB();
      const prefix = form.dataset.prefix;
      const id = "#" + prefix + "-" + n.getFullYear() + "-" + String(Math.floor(Math.random() * 9000) + 1000);
      const start = fd.get("start"), end = fd.get("end") || fd.get("start");
      const tr = document.createElement("tr");
      tr.className = "row-new";
      const modKey = document.body.dataset.module || "";
      const fileName = (form.elements.file.files[0] || {}).name || null;
      tr.dataset.detail = JSON.stringify({ eyebrow: form.dataset.title || "Forms", title: id, module: modKey, img: "assets/img/modules/" + modKey + ".png",
        badges: [["Waiting Approval", "orange"]], person: { name: "M. Daffa Dzaky Pratama", nip: "P0826155", role: "Frontend Developer" },
        sections: [{ h: "Detail Pengajuan", rows: [["ID Formulir", id], ["Periode", start + (end !== start ? " s/d " + end : "")]].concat(extraRows(fd), fd.get("note") ? [["Keterangan", fd.get("note")]] : [], [["Unit Kerja", "PT MATTA Solusindo — Engineering / IT Workplace"]]) }],
        attachment: fileName,
        timeline: [{ t: "Diajukan", by: "M. Daffa Dzaky Pratama", at: start + " " + pad(n.getHours()) + ":" + pad(n.getMinutes()), state: "done" },
                   { t: "Persetujuan Atasan", by: "Muhammad Rizky", at: "", state: "current" }, { t: "Verifikasi Human Capital", by: "Dewi Kaniawati", at: "", state: "todo" }] });
      tr.innerHTML = '<td class="num" data-dt-no></td><td><span class="id-chip">' + id + '</span></td><td><div class="person"><span class="avatar-initial">MD</span><div><div class="person-name">M. Daffa Dzaky Pratama</div><div class="person-sub">P0826155</div></div></div></td><td>Frontend Developer</td><td class="cell-2"><div>PT MATTA Solusindo</div><div class="sub-line">Engineering / IT Workplace</div></td>' +
        '<td class="cell-2" data-value="' + start + '"><div class="main-line">' + start + '</div><div class="sub-line">' + pad(n.getHours()) + ":" + pad(n.getMinutes()) + ' WIB</div></td><td class="center"><span class="badge badge-pill badge-orange">Waiting</span></td><td class="center"><button class="icon-btn" type="button" data-row-detail data-title="Detail ' + esc(id) + '" aria-label="Lihat detail">' + ICON.eye + "</button></td>";
      const tbody = $("table.dt tbody", card);
      tbody.insertBefore(tr, tbody.firstChild);
      tables.get(card).refresh();
      closeModal("modal-module");
      form.reset();
      toast(T("Pengajuan") + " " + T(form.dataset.name) + " " + T("berhasil dikirim."));
    });
  }

  /* ---------- Profil ---------- */
  function initProfile() {
    const form = $("#form-profile");
    if (!form) return;
    const snapshot = () => Object.fromEntries(new FormData(form));
    const saved = store.get("matta.profile", null);
    if (saved) Object.entries(saved).forEach(([k, v]) => { if (form.elements[k]) form.elements[k].value = v; });
    let original = snapshot();
    let editing = false;
    const fields = $$("input, select, textarea", form);
    const editBtn = $("[data-profile-edit]"), mode = $("[data-profile-mode]"), foot = $("[data-profile-foot]");
    function setEdit(on) {
      editing = on;
      fields.forEach((f) => (f.disabled = !on));
      form.classList.toggle("form-locked", !on);
      mode.textContent = on ? "Mode Edit Aktif" : "Mode Baca";
      foot.classList.toggle("hidden", !on);
      editBtn.setAttribute("aria-pressed", String(on));
      if (on) fields[0].focus();
    }
    editBtn.addEventListener("click", () => setEdit(!editing));
    $("[data-profile-cancel]").addEventListener("click", () => {
      Object.entries(original).forEach(([k, v]) => { form.elements[k].value = v; form.elements[k].classList.remove("is-invalid"); });
      setEdit(false);
      toast("Perubahan dibatalkan.");
    });
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      if (!validateForm(form)) return;
      original = snapshot();
      store.set("matta.profile", original);
      setEdit(false);
      $("[data-hero-name]").textContent = original.full_name;
      $("[data-hero-email]").textContent = original.email;
      toast("Data pribadi disimpan dan menunggu verifikasi HR.");
    });
    setEdit(true);

    // Tabel riwayat (anak, pendidikan, dll) — tambah/edit/hapus
    const childForm = $("#form-child");
    let editingRow = null;
    $("[data-child-add]").addEventListener("click", () => { editingRow = null; childForm.reset(); $("[data-child-modal-title]").textContent = "Tambah Data Anak"; openModal("modal-child"); });
    const childCard = $("#panel-anak");
    childCard.addEventListener("click", (e) => {
      const ed = e.target.closest("[data-row-edit]"), del = e.target.closest("[data-row-delete]");
      if (ed) {
        editingRow = ed.closest("tr");
        childForm.elements.order.value = editingRow.dataset.order;
        childForm.elements.child_name.value = editingRow.dataset.name;
        $("[data-child-modal-title]").textContent = "Edit Data Anak";
        openModal("modal-child");
      }
      if (del) {
        const tr = del.closest("tr");
        confirmDialog("Hapus data anak?", "Data " + tr.dataset.name + " akan dihapus dari riwayat.", "Hapus", true).then((ok) => {
          if (!ok) return; tr.remove(); tables.get(childCard).refresh(); updateCount(); toast("Data anak dihapus.");
        });
      }
    });
    function updateCount() { $("[data-count-anak]").textContent = $$("#panel-anak tbody tr:not(.dt-empty)").length; }
    childForm.addEventListener("submit", (e) => {
      e.preventDefault();
      if (!validateForm(childForm)) return;
      const order = childForm.elements.order.value, name = childForm.elements.child_name.value.trim();
      const html = '<td class="num" data-dt-no></td><td>' + esc(order) + '</td><td><div class="person"><span class="avatar-initial">' + esc(name[0].toUpperCase()) + "</span>" + esc(name) + '</div></td><td class="center"><div class="cell-actions" style="justify-content:flex-end"><button type="button" class="btn btn-xs badge-blue" data-row-edit>Edit</button><button type="button" class="btn btn-xs badge-red" data-row-delete>Hapus</button></div></td>';
      if (editingRow) { editingRow.innerHTML = html; editingRow.dataset.order = order; editingRow.dataset.name = name; }
      else {
        const tr = document.createElement("tr"); tr.className = "row-new"; tr.dataset.order = order; tr.dataset.name = name; tr.innerHTML = html;
        $("tbody", childCard).appendChild(tr);
      }
      tables.get(childCard).refresh();
      updateCount();
      closeModal("modal-child");
      toast(editingRow ? "Data anak diperbarui." : "Data anak ditambahkan.");
    });
  }

  /* ---------------- Boot ---------------- */
  document.addEventListener("DOMContentLoaded", () => {
    initSidebar();
    initDropdowns();
    initModals();
    initTabs();
    initAccordion();
    initAttendanceLogSync();
    initLocationModal();
    initTables();
    initFilterToggles();
    initRowDetail();
    initBack();
    initLang();
    initAttReset();
    initDashboard();
    initFeeds();
    initFeedsV2();
    initFeedDetail();
    initAttendanceAlert();
    initHealth();
    initPayroll();
    initPropose();
    initModulePage();
    initProfile();
    initUiVersion();
    initTransitions();
    if (location.hash === "#duration") { const t = $("[aria-controls=panel-duration]"); t && t.click(); }
  });
})();
