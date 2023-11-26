export default class StatsManager {

    constructor() {
        this.startDate = "";
        this.endDate = "";

        this.#initListeners();
    }

    #initListeners() {
        const start = document.getElementById("statsStart");
        start.addEventListener("change", function(e) {
            this.startDate = e.target.value;
        }, false);

        const end = document.getElementById("statsEnd");
        end.addEventListener("change", function(e) {
            this.endDate = e.target.value;
        }, false);
    }

    getGoogleSearchConsole() {
        ajax.get().then(r => {

        });
    }

}
