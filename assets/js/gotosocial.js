document.addEventListener("DOMContentLoaded", () => {
  const widget = document.getElementById("gotosocial");
  if (!widget) return;

  const toggleBtn = widget.querySelector(".gotosocial__btn");
  const wrap = widget.querySelector(".gotosocial__wrap");
  const externalTrigger = document.querySelector(".jsopenchats");

  if (!toggleBtn || !wrap) return;

  const setState = (isOpen) => {
    widget.classList.toggle("on", isOpen);
    toggleBtn.classList.toggle("on", isOpen);
    toggleBtn.setAttribute("aria-expanded", isOpen ? "true" : "false");
    wrap.classList.toggle("is-open", isOpen);
    if (isOpen) {
      wrap.removeAttribute("hidden");
      wrap.style.display = "";
    } else {
      wrap.setAttribute("hidden", "");
    }
  };

  const handleToggle = () => {
    const isOpen = !wrap.classList.contains("is-open");
    setState(isOpen);
  };

  toggleBtn.addEventListener("click", handleToggle);

  if (externalTrigger) {
    externalTrigger.addEventListener("click", () => setState(true));
  }

  document.addEventListener("click", (event) => {
    if (!widget.contains(event.target) && wrap.classList.contains("is-open")) {
      setState(false);
    }
  });
});
