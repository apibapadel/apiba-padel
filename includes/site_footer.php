<?php
$base = "/apiba-padel";
if (!isset($section)) $section = 'public';
?>

<?php if ($section === 'public'): ?>
    </div>
  </div>

  <footer style="max-width:1180px;margin:0 auto 22px;padding:0 14px;">
    <div class="card" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;">
      <span>© <?= date('Y') ?> APiBA Pádel</span>
      <span class="muted">Sitio público</span>
      <a class="btn" href="<?= $base ?>/admin/login.php">Admin</a>
    </div>
  </footer>
<?php endif; ?>
</body>
</html>
