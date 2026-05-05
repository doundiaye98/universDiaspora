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
    if (open) input.focus();
  };

  const appendMessage = (text, from) => {
    const article = document.createElement("article");
    article.className = `ud-ai-msg ${from === "user" ? "ud-ai-msg--user" : "ud-ai-msg--bot"}`;
    article.textContent = text;
    messages.appendChild(article);
    messages.scrollTop = messages.scrollHeight;
  };

  let pending = false;
  const ask = async (message) => {
    if (pending) return;
    pending = true;
    appendMessage(message, "user");
    appendMessage("Je vous réponds...", "bot");
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
        appendMessage("Je ne peux pas répondre pour le moment. Merci de réessayer dans quelques instants.", "bot");
        return;
      }
      appendMessage(data.answer, "bot");
    } catch (e) {
      if (waitingNode && waitingNode.parentNode) waitingNode.parentNode.removeChild(waitingNode);
      appendMessage("Service indisponible temporairement. Vous pouvez aussi utiliser le formulaire contact.", "bot");
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
})();

