<?php
// includes/public_footer.php
$BASE = '/apiba-padel';
?>
  </div>
</div>

<div class="footer">
  <div class="container">
    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; justify-content:space-between;">
      <span>© <?= date('Y') ?> APIBA Pádel</span>
      <span style="color:var(--muted);">Sitio público</span>
    </div>
  </div>
</div>

<script src="<?= $BASE ?>/assets/js/theme.js"></script>

<script>
// accesibilidad: toggle con Enter/Space
document.addEventListener("keydown", function(e){
  const el = document.activeElement;
  if (!el) return;
  if (el.classList && el.classList.contains("theme-toggle")) {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      if (typeof APIBA_toggleTheme === "function") APIBA_toggleTheme();
    }
  }
});
</script>

</body>
</html>
