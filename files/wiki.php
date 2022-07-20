<?php 

$eintraege = DBAccess::selectQuery("SELECT title, content FROM wiki_articles");

$content_id = 0;
if (isset($_GET["id"])) {
    $content_id = $_GET["id"];
}

?>
<div class="defCont overrideColorscheme">
    <span class="search_wrapper">
        <input class="search" type="text" placeholder="Suchen">
        <span id="lupeSpan"><span id="lupe">&#9906;</span></span>
    </span>
</div>
<div class="defCont">
    <span>Titel</span>
    <br>
    <input type="text" id="newTitle">
    <br>
    <span>Inhalt</span>
    <br>
    <textarea id="newContent">
    </textarea>
    <br>
    <button onclick="addToDB()">Hinzufügen</button>
</div>
<?php foreach ($eintraege as $eintrag): ?>
    <div class="defCont">
        <h2><?=$eintrag["title"]?></h2>
        <p><?=$eintrag["content"]?></p>
    </div>
<?php endforeach; ?>
<style>
    .overrideColorscheme {
        background: white;
    }

    .search_wrapper {
        border-bottom: 0.5px solid grey;
    }

    .search_wrapper:focus {
        border-bottom: 1px solid black;
    }
    
    .search {
        border: none;
        font-style: italic;
        width: 70vw;
        padding: 0;
    }

    input:focus {
        outline: none
    }

    #lupeSpan {
        position: relative;
        display: inline-block;
        left: -22px;
        font-size: 1.5em;
        top: 4px;
    }

    #lupe {
        display: inline-block;
        -webkit-transform: rotate(45deg);
        -moz-transform: rotate(45deg);
        -o-transform: rotate(45deg);
        transform: rotate(45deg);
    }
</style>