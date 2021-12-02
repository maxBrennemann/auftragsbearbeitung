</main>
    <footer>
    <?php foreach (Navigation::getFooterLinks() as $l): ?>
        <a href="<?=$l["link"]?>"></a>
    <?php endforeach; ?>
    </footer>
</body>
</html>