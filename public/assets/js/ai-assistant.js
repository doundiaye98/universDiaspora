(() => {
  const root = document.getElementById("udAiChat");
  if (!root) return;

  const panel = document.getElementById("udAiPanel");
  const toggle = document.getElementById("udAiToggle");
  const closeBtn = document.getElementById("udAiClose");
  const form = document.getElementById("udAiForm");
  const input = document.getElementById("udAiInput");
  const messages = document.getElementById("udAiMessages");
  const endpoint = root.getAttribute("data-endpoint") || "";
  if (!panel || !toggle || !closeBtn || !form || !input || !messages || endpoint === "") return;

  const setOpen = (open) => {
    root.classList.toggle("is-open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    toggle.setAttribute(
      "aria-label",
      open ? "Fermer le chatbot IA Univers Diaspora" : "Ouvrir le chatbot IA Univers Diaspora"
    );
    if (open) input.focus();
  };

  const escapeHtml = (s) =>
    String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");

  const linkifyAndBreak = (text) => {
    const safe = escapeHtml(text);
    const withLinks = safe.replace(
      /(https?:\/\/[^\s<>")]+)/g,
      '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
    );
    return withLinks.replace(/\n/g, "<br>");
  };

  const appendMessage = (text, from) => {
    const article = document.createElement("article");
    article.className = `ud-ai-msg ${from === "user" ? "ud-ai-msg--user" : "ud-ai-msg--bot"}`;
    if (from === "user") {
      article.textContent = text;
    } else {
      article.innerHTML = linkifyAndBreak(text);
    }
    messages.appendChild(article);
    messages.scrollTop = messages.scrollHeight;
  };

  let pending = false;
  const ask = async (message) => {
    if (pending) return;
    pending = true;
    appendMessage(message, "user");
    appendMessage("Analyse de votre demande en cours…", "bot");
    const waitingNode = messages.lastElementChild;

    try {
      const res = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message }),
      });
      const data = await res.json();
      if (waitingNode && waitingNode.parentNode) waitingNode.parentNode.removeChild(waitingNode);
      if (!data || !data.ok || typeof data.answer !== "string") {
        appendMessage(
          "Je ne suis pas en mesure de répondre pour l’instant. Vous pouvez prendre rendez-vous ou nous écrire via le formulaire de contact.",
          "bot"
        );
        return;
      }
      appendMessage(data.answer, "bot");
    } catch (e) {
      if (waitingNode && waitingNode.parentNode) waitingNode.parentNode.removeChild(waitingNode);
      appendMessage(
        "Service momentanément indisponible. Vous pouvez nous joindre via le formulaire de contact ou la prise de rendez-vous.",
        "bot"
      );
    } finally {
      pending = false;
    }
  };

  toggle.addEventListener("click", () => setOpen(!root.classList.contains("is-open")));
  closeBtn.addEventListener("click", () => setOpen(false));
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const value = input.value.trim();
    if (value === "" || pending) return;
    input.value = "";
    ask(value);
  });

  /* Boutons de suggestion (visibles surtout sur les pages service) */
  messages.addEventListener("click", (e) => {
    const target = e.target;
    if (!(target instanceof HTMLElement)) return;
    const btn = target.closest(".ud-ai-suggest__btn");
    if (!btn) return;
    e.preventDefault();
    const prefill = btn.getAttribute("data-prefill") || "";
    if (prefill === "" || pending) return;
    const suggestGroup = btn.closest(".ud-ai-suggest");
    if (suggestGroup && suggestGroup.parentNode) {
      suggestGroup.parentNode.removeChild(suggestGroup);
    }
    ask(prefill);
  });
})();

