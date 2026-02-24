(function () {
  const STORAGE_KEY = "apiba_theme";

  // Aplica tema guardado (si existe)
  const saved = localStorage.getItem(STORAGE_KEY);
  if (saved === "dark" || saved === "light") {
    document.body.setAttribute("data-theme", saved);
  }

  // Función global para el botón
  window.APIBA_toggleTheme = function () {
    const current = document.body.getAttribute("data-theme");
    const next = (current === "dark") ? "light" : "dark";
    document.body.setAttribute("data-theme", next);
    localStorage.setItem(STORAGE_KEY, next);
  };
})();
