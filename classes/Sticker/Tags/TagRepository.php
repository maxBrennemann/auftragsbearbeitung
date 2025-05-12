<?php

namespace Classes\Sticker\Tags;

use MaxBrennemann\PhpUtilities\DBAccess;

use Classes\Sticker\Sticker;

class TagRepository {

    private int $idSticker;
    private string $title;
    private array $tags;

    public function __construct(int $idSticker, string $title)
    {
        $query = "SELECT * FROM module_sticker_tags t 
            JOIN module_sticker_sticker_tag st 
                ON st.id_tag = t.id 
            WHERE st.id_sticker = $idSticker";
        $this->tags = DBAccess::selectQuery($query);

        $this->idSticker = $idSticker;

        if ($title == "") {
            $sticker = new Sticker($idSticker);
            $title = $sticker->getName();
        }
        $this->title = $title;
    }

    public function get()
    {
        $tagsContent = [];

        foreach ($this->tags as $t) {
            $tagsContent[] = $t["content"];
        }

        return $tagsContent;
    }

}
