<div class="defCont">
    <input type="text" class="input-primary" value="test" id="testInput">
    <button onclick="clickBtn()" class="btn-primary">test</button>
    <script>
        const clickBtn = () => {
            const query = document.querySelector("input#testInput").value;
            fetch(`/api/v1/search?query=${query}`);
        }
    </script>
</div>