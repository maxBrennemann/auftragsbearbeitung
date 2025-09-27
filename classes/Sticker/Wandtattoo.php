<?php

namespace Classes\Sticker;

class Wandtattoo extends AufkleberWandtattoo
{

    public const TYPE = "wandtattoo";

    private bool $isWalldecal = false;

    public function __construct(int $idWaldecal)
    {
        parent::__construct($idWaldecal);
        $this->instanceType = "wandtattoo";

        /* is true, if sticker exists or is activated */
        $this->isWalldecal = $this->stickerData["is_walldecal"] == "1";
    }

    public function isInShop(): bool
    {
        return parent::checkIsInShop(self::TYPE);
    }

    public function getName(): string
    {
        return "Wandtattoo " . parent::getName();
    }

    public function getIsWalldecal(): bool
    {
        return $this->isWalldecal;
    }

    public function getAltTitle(string $default = ""): string
    {
        return parent::getAltTitle(self::TYPE);
    }

    public function getShopLink(): string
    {
        return parent::getShopLinkHelper(self::TYPE);
    }

    public function save(bool $isOverwrite = false): ?string
    {
        if (!$this->getIsWalldecal()) {
            return null;
        }

        $description = "<p><span>Es wird jeweils nur der entsprechende Artikel oder das einzelne Motiv verkauft. Andere auf den Bildern befindliche Dinge sind nicht Bestandteil des Angebotes.</span></p>" . $this->getDescription();

        $productId = (int) $this->getIdProduct();
        $stickerUpload = new StickerUpload($this->idSticker, $this->getName(), $this->getBasePrice(), $description, $this->getDescriptionShortWithDefaultText());

        $stickerCombination = new StickerCombination($this);

        if ($productId == 0) {
            $this->idProduct = $productId = $stickerUpload->createSticker();
        } else {
            $stickerUpload->updateSticker($productId);
        }
        $stickerUpload->setCategoires([2, 62, 13]);

        $stickerCombination->removeOldCombinations($productId);

        $stickerTagManager = new StickerTagManager($this->getId(), $this->getName());
        $stickerTagManager->setProductId($productId);
        try {
            $stickerTagManager->saveTags();
        } catch (\Exception $e) {
            return "Fehler beim Speichern der Tags: " . $e->getMessage();
        }

        $stickerCombination->createCombinations();

        $this->connectAccessoires();

        if ($isOverwrite) {
            $this->imageData->deleteAllImages($this->idProduct);
        }

        $this->imageData->handleImageProductSync("wandtattoo", $this->idProduct);
        return null;
    }

    /**
     * @return array<int, array<int>>
     */
    public function getAttributes(): array
    {
        return [$this->getSizeIds()];
    }
}
