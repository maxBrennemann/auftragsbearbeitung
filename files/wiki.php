<?php 

$eintraege = DBAccess::selectQuery("SELECT title, content FROM wiki_articles");

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
    <input type="text">
    <br>
    <span>Inhalt</span>
    <br>
    <textarea>
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
<script>
    function addToDB() {
        var title = document.querySelector("input").value;
        var content = document.querySelector("textarea").value;

        var add = new AjaxCall(`getReason=sendToDB&title=${title}&content=${content}`, "POST", window.location.href);
        add.makeAjaxCall(function (response) {
            if (response == "ok") {
                console.log("data sent to server");
            } else {
                console.log(response);
            }
        });
    }
</script>
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