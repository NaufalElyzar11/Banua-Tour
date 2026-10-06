<?php $pager->setSurroundCount(2); ?>
<nav aria-label="Halaman destinasi"><ul class="pagination">
    <?php if ($pager->hasPreviousPage()): ?><li><a href="<?= esc($pager->getPreviousPage(), 'attr') ?>">&larr; Sebelumnya</a></li><?php endif; ?>
    <?php foreach ($pager->links() as $link): ?><li><?php if ($link['active']): ?><span aria-current="page"><?= esc($link['title']) ?></span><?php else: ?><a href="<?= esc($link['uri'], 'attr') ?>" aria-label="Halaman <?= esc($link['title'], 'attr') ?>"><?= esc($link['title']) ?></a><?php endif; ?></li><?php endforeach; ?>
    <?php if ($pager->hasNextPage()): ?><li><a href="<?= esc($pager->getNextPage(), 'attr') ?>">Berikutnya &rarr;</a></li><?php endif; ?>
</ul></nav>
