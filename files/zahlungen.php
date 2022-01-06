<?php 

// SELECT id, (CASE WHEN amount > 0 AND paymentDate = '0000-00-00' THEN amount ELSE 0 END) AS forderungen FROM payments GROUP BY id

$data = DBAccess::selectQuery("SELECT * FROM payments");
$forderung = 0;
$zahlungen = 0;

foreach($data as $d) {
    if ((float) $d["amount"] > 0) {
        $forderung += abs($d["amount"]);
    } else if ((float) $d["amount"] < 0) {
        $zahlungen += abs($d["amount"]);
    }
}

$forderung = number_format($forderung, 2, ',', '') . ' €';
$zahlungen = number_format($zahlungen, 2, ',', '') . ' €';

$column_names = array(
    0 => array("COLUMN_NAME" => "Bezeichnung"), 
    1 => array("COLUMN_NAME" => "Beschreibung"), 
    2 => array("COLUMN_NAME" => "Betrag"), 
    3 => array("COLUMN_NAME" => "nächstes Datum"), 
    4 => array("COLUMN_NAME" => "Intervall")
);

/* 
 * interval types 
 * 1 monthly
 * 2 quarterly
 * 3 half-yearly
 * 4 yearly
 */
$data = DBAccess::selectQuery("SELECT `short_description` as `Bezeichnung`, `description` as `Beschreibung`, `amount` as `Betrag`, `date` as `nächstes Datum`, recurring as `Intervall` FROM recurring_payments");
if ($data != null) {
    $typen = [
        1 => "monatlich",
        2 => "vierteljährlich",
        3 => "halbjährlich",
        4 => "jährlich"
    ];
    for ($i = 0; $i < sizeof($data); $i++) {
        $data[$i]["Intervall"] = $typen[(int) $data[$i]["Intervall"]];
        $data[$i]["Betrag"] = number_format($data[$i]["Betrag"], 2, ',', '') . ' €';
        $data[$i]["nächstes Datum"] = date_format(date_create($data[$i]["nächstes Datum"]), "d.m.Y");
    }
}

$t = new Table();
$t->createByData($data, $column_names);
$t->addNewLineButton();

$pattern = [
    "short_description" => [
        "status" => "unset",
        "value" => 0
    ],
    "description" => [
        "status" => "unset",
        "value" => 1
    ],
    "amount" => [
        "status" => "unset",
        "value" => 2
    ],
    "date" => [
        "status" => "unset",
        "value" => 3
    ],
    "recurring" => [
        "status" => "unset",
        "value" => 4
    ],
    "type" => [
        "status" => "preset",
        "value" => "1"
    ]
];

$t->defineUpdateSchedule(new UpdateSchedule("recurring_payments", $pattern));
$table =  $t->getTable();
$_SESSION[$t->getTableKey()] = serialize($t);

/* table for current payments */
$columns = array(
    0 => array("COLUMN_NAME" => "Bezeichnung"), 
    1 => array("COLUMN_NAME" => "Beschreibung"), 
    2 => array("COLUMN_NAME" => "Betrag"), 
    3 => array("COLUMN_NAME" => "nächstes Datum"), 
    4 => array("COLUMN_NAME" => "Intervall")
);
$data = DBAccess::selectQuery("SELECT `short_description` as `Bezeichnung`, `description` as `Beschreibung`, `amount` as `Betrag`, `date` as `nächstes Datum`, recurring as `Intervall` FROM recurring_payments WHERE CURDATE() > `date`");
$aktuell_anstehend = new Table();
$aktuell_anstehend->createByData($data, $columns);
$aktuell_anstehend =  $aktuell_anstehend->getTable();

?>
<div class="defCont">
    <span>Offene Forderungen: <?=$forderung?></span>
    <br>
    <span>Offene Rechnungen: <?=$zahlungen?></span>
</div>
<div class="defCont">
    <h4>Aktuell anstehend</h4>
    <?=$aktuell_anstehend?>
</div>
<div class="defCont" id="recurring_payments">
    <h4>Wiederkehrende Zahlungen</h4>
    <?=$table?>
</div>
<style>

/* class for round + button */
.addToTable {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    line-height: 20px;
    text-align: center;
    font-size: 20px;
    border: none;
    box-shadow: 1px 0px 5px 0px grey;
    font-weight: bold;
    color: grey;
}

/* positionating button in middle of table */
#recurring_payments {
    display: inline-block;
    text-align: center;
}

table {
    text-align: left;
}

</style>