<?php if (!empty($errors)): ?>
    <div class="error-summary" role="alert">
        <h2>Niet opgeslagen</h2>
        <ul>
            <?php foreach ($errors as $field => $message): ?>
                <li><a href="#<?= e($field) ?>"><?= e($message) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
