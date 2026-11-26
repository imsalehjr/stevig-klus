<?php
$c = company();
$footerServices = Database::pdo()->query('SELECT slug, name FROM services WHERE is_active = 1 ORDER BY sort_order, name')->fetchAll();
?>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="logo logo-light" href="<?= e(url()) ?>">
                <span class="logo-word">STEVIG</span>
                <span class="logo-sub">klus &amp; renovatie</span>
            </a>
            <p class="footer-text">Vakwerk uit Eikenrode. Eén aanspreekpunt, een vaste prijs en een opgeruimde werkplek.</p>
        </div>
        <div>
            <h2 class="footer-title">Diensten</h2>
            <ul class="footer-list">
                <?php foreach ($footerServices as $s): ?>
                    <li><a href="<?= e(url('diensten#' . $s['slug'])) ?>"><?= e($s['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h2 class="footer-title">Contact</h2>
            <address>
                <?= e($c['street']) ?><br><?= e($c['postcode'] . ' ' . $c['town']) ?><br>
                <a href="tel:<?= e($c['phone_link']) ?>"><?= e($c['phone']) ?></a><br>
                <a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a>
            </address>
            <p class="footer-text">Ma t/m vr 7:30 tot 17:00</p>
        </div>
    </div>
    <div class="container footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= e($c['name']) ?>. Fictief bedrijf, portfolio-project.</p>
        <p><?php if (is_demo()): ?><a href="<?= e(url('admin/login.php')) ?>">Beheerpaneel (demo)</a> &middot; <?php endif; ?><a href="<?= e(url('privacy')) ?>">Privacyverklaring</a></p>
    </div>
</footer>
</body>
</html>
