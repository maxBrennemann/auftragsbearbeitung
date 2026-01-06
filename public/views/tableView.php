<table class="<?= $tableClassName ?>">
    <thead>
        <?php foreach ($theadElements as $the): ?>
            <th class="<?= $theadClassName ?>" key="<?= $the["key"] ?>" sort="none">
                <span class="inline-flex items-center">
                    <?= $the["label"] ?>
                    <span><?= $the["sortIcon"] ?></span>
                </span>
            </th>
        <?php endforeach; ?>
        <?= $actionColumn ?>
    </thead>

    <tbody>
        <?php foreach ($tbodyElements as $row): ?>
            <tr <?= $primaryKey != null ? 'data-id="' . $primaryKey . '"' : '' ?>>
                <?php foreach ($row as $i => $el): ?>
                    <?php if ($link != null) : ?>
                        <td>
                            <a href="<?= $link . $el["primary"] ?>" class="<?= $el["class"] ?>">
                                <?= $el["content"] ?>
                            </a>
                        </td>
                    <?php else: ?>
                        <td class="<?= $el["class"] ?>">
                            <?= $el["content"] ?>
                        </td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        <?php if (count($tbodyElements) === 0) : ?>
            <tr class="empty-placeholder">
                <td colspan="<?= count($theadElements) + 1 ?>" class="center">
                    Keine Daten verfügbar.
                </td>
            </tr>
        <?php endif; ?>
        <?= $actionElement ?>
    </tbody>

    <?= $tfoot ?>
</table>