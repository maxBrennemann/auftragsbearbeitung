<table class="innerTable">
    <tr>
        <th>Von</th>
        <th>Bis</th>
        <th>Datum</th>
    </tr>
    <?php foreach ($times as $time): ?>
        <tr>
            <td><?= $time["from"] ?></td>
            <td><?= $time["to"] ?></td>
            <td><?= $time["date"] ?></td>
        </tr>
    <?php endforeach; ?>
</table>