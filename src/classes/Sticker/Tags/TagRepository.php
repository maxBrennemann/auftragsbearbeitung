<?php

namespace Src\Classes\Sticker\Tags;

use Src\Classes\Sticker\Sticker;
use MaxBrennemann\PhpUtilities\DBAccess;

class TagRepository
{
    
    /** @var array<string, mixed> */
    private array $tags;

    public function __construct(int $idSticker, string $title)
    {
        $query = "SELECT * FROM module_sticker_tags t 
            JOIN module_sticker_sticker_tag st 
                ON st.id_tag = t.id 
            WHERE st.id_sticker = $idSticker";
        $this->tags = DBAccess::selectQuery($query);

        if ($title == "") {
            $sticker = new Sticker($idSticker);
            $title = $sticker->getName();
        }
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        $tagsContent = [];

        foreach ($this->tags as $t) {
            $tagsContent[] = $t["content"];
        }

        return $tagsContent;
    }

}
