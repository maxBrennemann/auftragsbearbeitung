import "../../../../input.css";

import { init } from "../global.js";

if (import.meta.hot) {
    import.meta.hot.accept(() => {
        init();
    })
}
