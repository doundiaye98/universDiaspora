(() => {
  "use strict";

  const root = document.querySelector(".ud-rdv-wizard");
  const form = root?.querySelector(".ud-rdv-wizard__form");
  if (!root || !form) return;

  const steps = Array.from(root.querySelectorAll("[data-rdv-step]"));
  const panels = Array.from(root.querySelectorAll("[data-rdv-panel]"));
  const skipService = root.dataset.skipService === "1";
  const voletsByService = (() => {
    try {
      return JSON.parse(root.dataset.volets || "{}");
    } catch {
      return {};
    }
  })();

  const hiddenService = form.querySelector('[name="service_slug"]');
  const hiddenVolet = form.querySelector('[name="volet_id"]');
  const serviceSelect = form.querySelector("#udRdvService");
  const voletWrap = form.querySelector("#udRdvVoletWrap");
  const voletSelect = form.querySelector("#udRdvVolet");
  const messageField = form.querySelector('[data-auto-message-field="1"]');
  const summaryEl = form.querySelector("#udRdvSummary");

  let current = root.dataset.initialStep || (skipService ? "office" : "service");

  const stepIndex = (id) => steps.findIndex((s) => s.dataset.rdvStep === id);

  const setActiveStep = (id) => {
    if (!panels.some((p) => p.dataset.rdvPanel === id)) return;
    current = id;
    panels.forEach((p) => {
      const on = p.dataset.rdvPanel === id;
      p.hidden = !on;
      p.classList.toggle("is-active", on);
    });
    steps.forEach((s) => {
      const sid = s.dataset.rdvStep;
      const idx = stepIndex(sid);
      const curIdx = stepIndex(id);
      const done = idx >= 0 && curIdx >= 0 && idx < curIdx;
      const on = sid === id;
      s.classList.toggle("is-active", on);
      s.classList.toggle("is-done", done || (skipService && sid === "service"));
      s.setAttribute("aria-selected", on ? "true" : "false");
    });
    const panel = panels.find((p) => p.dataset.rdvPanel === id);
    panel?.querySelector("input, select, textarea, button")?.focus({ preventScroll: true });
    window.scrollTo({ top: root.offsetTop - 12, behavior: "smooth" });
  };

  const prevStep = (id) => {
    const idx = stepIndex(id);
    if (idx <= 0) return id;
    let prev = steps[idx - 1]?.dataset.rdvStep;
    if (skipService && prev === "service") prev = steps[idx - 2]?.dataset.rdvStep;
    return prev || id;
  };

  const nextStep = (id) => {
    const idx = stepIndex(id);
    if (idx < 0 || idx >= steps.length - 1) return id;
    return steps[idx + 1]?.dataset.rdvStep || id;
  };

  const showFieldError = (el, msg) => {
    if (!el) return;
    el.classList.add("is-invalid");
    let fb = el.parentElement?.querySelector(".invalid-feedback");
    if (!fb) {
      fb = document.createElement("div");
      fb.className = "invalid-feedback";
      el.parentElement?.appendChild(fb);
    }
    fb.textContent = msg;
  };

  const clearFieldError = (el) => {
    if (!el) return;
    el.classList.remove("is-invalid");
    const fb = el.parentElement?.querySelector(".invalid-feedback");
    if (fb && !fb.dataset.server) fb.remove();
  };

  const validatePanel = (id) => {
    const panel = panels.find((p) => p.dataset.rdvPanel === id);
    if (!panel) return true;
    let ok = true;

    if (id === "service") {
      if (!panel) return true;
      clearFieldError(serviceSelect);
      if (serviceSelect && !serviceSelect.value) {
        showFieldError(serviceSelect, "Choisissez un service ou « Besoin général ».");
        ok = false;
      } else if (hiddenService) {
        hiddenService.value = serviceSelect?.value === "__general__" ? "" : (serviceSelect?.value || "");
      }
      if (hiddenVolet && voletSelect && !voletWrap?.hidden) {
        hiddenVolet.value = voletSelect.value || "";
      } else if (hiddenVolet) {
        hiddenVolet.value = "";
      }
    }

    if (id === "office") {
      const office = panel.querySelector('[name="office"]:checked');
      panel.querySelectorAll('[name="office"]').forEach((r) => clearFieldError(r));
      if (!office) {
        const first = panel.querySelector('[name="office"]');
        showFieldError(first, "Choisissez un bureau.");
        ok = false;
      }
    }

    if (id === "slot") {
      const date = panel.querySelector('[name="date"]');
      const time = panel.querySelector('[name="time"]');
      clearFieldError(date);
      clearFieldError(time);
      if (!date?.value) {
        showFieldError(date, "Indiquez une date.");
        ok = false;
      }
      if (!time?.value) {
        showFieldError(time, "Indiquez une heure.");
        ok = false;
      }
    }

    if (id === "contact") {
      ["name", "email"].forEach((name) => {
        const el = panel.querySelector(`[name="${name}"]`);
        clearFieldError(el);
        if (!el?.value?.trim()) {
          showFieldError(el, "Ce champ est obligatoire.");
          ok = false;
        }
      });
      const email = panel.querySelector('[name="email"]');
      if (email?.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        showFieldError(email, "Adresse e-mail invalide.");
        ok = false;
      }
    }

    return ok;
  };

  const months = [
    "janvier", "février", "mars", "avril", "mai", "juin",
    "juillet", "août", "septembre", "octobre", "novembre", "décembre",
  ];

  const formatDateFr = (ymd) => {
    if (!ymd) return "";
    const p = ymd.split("-");
    if (p.length !== 3) return ymd;
    const d = parseInt(p[2], 10);
    const m = parseInt(p[1], 10) - 1;
    if (!d || m < 0 || m > 11) return ymd;
    return `${d} ${months[m]} ${p[0]}`;
  };

  const getServiceTitle = () => {
    if (!serviceSelect) return root.dataset.serviceTitle || "";
    const opt = serviceSelect.selectedOptions[0];
    if (!opt || opt.value === "__general__" || !opt.value) return "";
    return opt.textContent?.trim() || "";
  };

  const getVoletLabel = () => {
    if (!voletSelect || voletWrap?.hidden) return root.dataset.voletLabel || "";
    const opt = voletSelect.selectedOptions[0];
    return opt?.value ? (opt.textContent?.trim() || "") : "";
  };

  const buildMessage = () => {
    const serviceTitle = root.dataset.serviceTitle || getServiceTitle();
    if (!serviceTitle) return "";
    const voletLabel = root.dataset.voletLabel || getVoletLabel();
    const office = form.querySelector('[name="office"]')?.value?.trim() || "";
    const date = form.querySelector('[name="date"]')?.value?.trim() || "";
    const time = (form.querySelector('[name="time"]')?.value?.trim() || "").slice(0, 5);
    const lines = ["Bonjour,", "", "Je souhaite prendre rendez-vous pour être accompagné(e) sur :"];
    lines.push(`- Service : ${serviceTitle}`);
    if (voletLabel) lines.push(`- Volet : ${voletLabel}`);
    if (office || date || time) {
      lines.push("", "Créneau souhaité :");
      if (office) lines.push(`- Bureau : ${office}`);
      if (date) lines.push(`- Date : ${formatDateFr(date)}`);
      if (time) lines.push(`- Heure : ${time}`);
    }
    lines.push("", "Merci de me recontacter pour confirmer ce créneau et m’indiquer les documents à préparer.");
    return lines.join("\n");
  };

  let userEditedMessage = false;
  if (messageField) {
    messageField.addEventListener("input", () => {
      userEditedMessage = true;
    });
  }

  const refreshMessage = () => {
    if (!messageField || userEditedMessage) return;
    const msg = buildMessage();
    if (msg) messageField.value = msg;
  };

  const refreshSummary = () => {
    if (!summaryEl) return;
    const serviceTitle = root.dataset.serviceTitle || getServiceTitle() || "—";
    const voletLabel = root.dataset.voletLabel || getVoletLabel();
    const office = form.querySelector('[name="office"]')?.value || "—";
    const date = formatDateFr(form.querySelector('[name="date"]')?.value || "") || "—";
    const time = (form.querySelector('[name="time"]')?.value || "").slice(0, 5) || "—";
    const name = form.querySelector('[name="name"]')?.value || "—";
    const email = form.querySelector('[name="email"]')?.value || "—";
  const phone = form.querySelector('[name="phone"]')?.value || "—";

    summaryEl.innerHTML = `
      <dl class="ud-rdv-summary__list">
        <div><dt>Service</dt><dd>${escapeHtml(serviceTitle)}</dd></div>
        ${voletLabel ? `<div><dt>Volet</dt><dd>${escapeHtml(voletLabel)}</dd></div>` : ""}
        <div><dt>Bureau</dt><dd>${escapeHtml(office)}</dd></div>
        <div><dt>Date</dt><dd>${escapeHtml(date)}</dd></div>
        <div><dt>Heure</dt><dd>${escapeHtml(time)}</dd></div>
        <div><dt>Nom</dt><dd>${escapeHtml(name)}</dd></div>
        <div><dt>E-mail</dt><dd>${escapeHtml(email)}</dd></div>
        <div><dt>Téléphone</dt><dd>${escapeHtml(phone)}</dd></div>
      </dl>`;
  };

  const escapeHtml = (s) =>
    String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");

  const populateVolets = (slug) => {
    if (!voletWrap || !voletSelect) return;
    const list = voletsByService[slug] || [];
    voletSelect.innerHTML = '<option value="">Sans précision de volet</option>';
    list.forEach((v) => {
      const o = document.createElement("option");
      o.value = v.id;
      o.textContent = v.label;
      voletSelect.appendChild(o);
    });
    voletWrap.hidden = list.length === 0;
    const preset = root.dataset.voletId || hiddenVolet?.value || "";
    if (preset) voletSelect.value = preset;
  };

  if (serviceSelect && hiddenService) {
    const syncServiceFields = () => {
      const slug = serviceSelect.value;
      hiddenService.value = slug === "__general__" || !slug ? "" : slug;
      if (hiddenVolet && voletSelect && !voletWrap?.hidden) {
        hiddenVolet.value = voletSelect.value || "";
      } else if (hiddenVolet && !root.dataset.voletId) {
        hiddenVolet.value = "";
      }
    };
    serviceSelect.addEventListener("change", syncServiceFields);
    syncServiceFields();
  }

  if (serviceSelect) {
    serviceSelect.addEventListener("change", () => {
      const slug = serviceSelect.value;
      if (slug && slug !== "__general__") populateVolets(slug);
      else if (voletWrap) voletWrap.hidden = true;
      if (hiddenService) {
        hiddenService.value = slug === "__general__" ? "" : slug;
      }
      root.dataset.serviceTitle = getServiceTitle();
      root.dataset.voletLabel = "";
      refreshMessage();
    });
    if (serviceSelect.value && serviceSelect.value !== "__general__") {
      populateVolets(serviceSelect.value);
    }
  }

  if (voletSelect) {
    voletSelect.addEventListener("change", () => {
      root.dataset.voletLabel = getVoletLabel();
      refreshMessage();
    });
  }

  ["office", "date", "time"].forEach((name) => {
    form.querySelector(`[name="${name}"]`)?.addEventListener("change", () => {
      refreshMessage();
      refreshSummary();
    });
  });

  ["name", "email", "phone"].forEach((name) => {
    form.querySelector(`[name="${name}"]`)?.addEventListener("input", refreshSummary);
  });

  root.querySelectorAll("[data-rdv-next]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const from = btn.closest("[data-rdv-panel]")?.dataset.rdvPanel;
      if (!from || !validatePanel(from)) return;
      if (from === "contact") refreshSummary();
      if (from === "slot" || from === "contact") refreshMessage();
      setActiveStep(nextStep(from));
    });
  });

  root.querySelectorAll("[data-rdv-back]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const from = btn.closest("[data-rdv-panel]")?.dataset.rdvPanel;
      if (!from) return;
      setActiveStep(prevStep(from));
    });
  });

  steps.forEach((s) => {
    s.addEventListener("click", () => {
      const id = s.dataset.rdvStep;
      const curIdx = stepIndex(current);
      const tgtIdx = stepIndex(id);
      if (tgtIdx < 0 || tgtIdx > curIdx) return;
      if (skipService && id === "service") return;
      setActiveStep(id);
    });
  });

  const dateInput = form.querySelector('[name="date"]');
  if (dateInput && !dateInput.min) {
    const today = new Date();
    dateInput.min = today.toISOString().slice(0, 10);
  }

  form.addEventListener("submit", (e) => {
    if (serviceSelect && hiddenService) {
      const slug = serviceSelect.value;
      hiddenService.value = slug === "__general__" || !slug ? "" : slug;
    }
    if (voletSelect && hiddenVolet && !voletWrap?.hidden) {
      hiddenVolet.value = voletSelect.value || "";
    }
    const toValidate = skipService ? ["office", "slot", "contact"] : ["service", "office", "slot", "contact"];
    for (const id of toValidate) {
      if (!validatePanel(id)) {
        e.preventDefault();
        setActiveStep(id);
        return;
      }
    }
    refreshMessage();
  });

  setActiveStep(current);
  refreshMessage();
  refreshSummary();
})();
