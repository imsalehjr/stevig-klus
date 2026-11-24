<?php $name = preg_match('/^[a-z-]+$/', $name ?? '') ? $name : 'tools'; ?>
<svg class="icon<?= isset($class) ? ' ' . e($class) : '' ?>" aria-hidden="true" focusable="false"><use href="<?= e(asset('img/icons.svg')) ?>#<?= e($name) ?>"></use></svg>
