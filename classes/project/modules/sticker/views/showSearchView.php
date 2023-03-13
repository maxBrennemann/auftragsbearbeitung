<div class="searchViewGrid">
    <div>
        <div class="searchBarContainer">
            <input type="search" id="searchShopQuery" placeholder="Aufkleber suchen">
            <span id="searchShopBtn" class="searchIcon"><?=Icon::$iconSearch?></span>
        </div>
        <div id="showSearchResults"></div>
    </div>
    <div>
        <?php foreach ($products as $product): ?>
        <label>
            <input type="checkbox" checked>
            <span>
                Artikel <?=$product["id_product"]?>: <a href=""><?=$product["name"]?></a>
            </span>
        </label>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .searchBarContainer {
        position: relative;
    }

    .searchIcon {
        position: absolute;
        right: 10px;
    }

    #showSearchResults {
        max-height: 400px;
        overflow-y: auto;
    }

    #showSearchResults label {
        margin: 5px;
    }
</style>