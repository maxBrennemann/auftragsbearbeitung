<div class="searchContainer inline-flex border rounded-xl overflow-hidden shadow-md items-center bg-white">
    <input class="border-none p-1 pl-3 rounded-none shadow-none box-content outline-none placeholder:text-[#374151] h-6 w-16 md:w-48" id="<?= isset($searchId) ? $searchId : "" ?>" value="<?= isset($searchValue) ? $searchValue : "" ?>">
    <span class="inline-flex items-center mr-2">
        <?=\Src\Classes\Project\Icon::getDefaultColorized("iconSearch", "#374151")?>
    </span>
</div>