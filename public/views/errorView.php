<aside class="defCont errorBox">
    <h2 class="font-bold"><?= $errorData["type"] ?></h2>
    <p class="font-semibold text-red-500"><?= $errorData["message"] ?></p>
    <p>
        <strong><?= $errorData["specific"] ?></strong>
        <details>
            <summary>Stack trace (click to expand)</summary>
            <pre class="whitespace-pre-wrap"><?= $errorData["trace"] ?></pre>
        </details>
        <?php if ($errorData["query"] != ""): ?>
        <details>
            <summary>Last SQL Query (click to expand)</summary>
            <pre class="whitespace-pre-wrap"><?= $errorData["query"] ?></pre>
        </details>
        <?php endif; ?>
    </p>
</aside>