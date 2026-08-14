<?php

/**
 * Leistungsdatum soll wahlweise ein-/ausblendbar sein (statt nur über den
 * generischen Text-Toggle, der zu Duplikaten im PDF führen konnte) und
 * wahlweise als Datum oder als Kalenderwoche angegeben werden können.
 */
return new class () {

    private $queries = [
        "ALTER TABLE `invoice`
            ADD `show_performance_date` TINYINT(1) NOT NULL DEFAULT 1 AFTER `performance_date`,
            ADD `performance_date_type` ENUM('date','week') NOT NULL DEFAULT 'date' AFTER `show_performance_date`;",
    ];

    public function getQueries()
    {
        return $this->queries;
    }
};
