const path = require('path');
const CompressionPlugin = require("compression-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");

module.exports = {
    mode: 'development',
    entry: {
        global: './files/res/js/global.js',
        angebot: './files/res/js/angebot.js',
        attributes: './files/res/js/attributes.js',
        auftrag: './files/res/js/auftrag.js',
        diagramme: './files/res/js/diagramme.js',
        einstellungen: './files/res/js/einstellungen.js',
        funktionen: './files/res/js/funktionen.js',
        kunde: './files/res/js/kunde.js',
        leistungen: './files/res/js/leistungen.js',
        list: './files/res/js/list.js',
        listmaker: './files/res/js/listmaker.js',
        login: './files/res/js/login.js',
        main: './files/res/js/.js',
        mitarbeiter: './files/res/js/mitarbeiter.js',
        neuerAuftrag: './files/res/js/neuerAuftrag.js',
        neuerKunde: './files/res/js/neuerKunde.js',
        neuesProdukt: './files/res/js/neuesProdukt.js',
        offeneRechnungen: './files/res/js/offeneRechnungen.js',
        produkt: './files/res/js/produkt.js',
        rechnung: './files/res/js/rechnung.js',
        stickerOverview: './files/res/js/stickerOverview.js',
        sticker: './files/res/js/sticker.js',
        tags: './files/res/js/tags.js',
        wiki: './files/res/js/wiki.js',
        zeiterfassung: './files/res/js/zeiterfassung.js',

        ajax: './files/res/js/classes/ajax.js',
        binding: './files/res/js/classes/binding.js',
        deviceDetector: './files/res/js/classes/deviceDetector.js',
        fileUploader: './files/res/js/classes/fileUploader.js',
        statusInfo: './files/res/js/classes/statusInfo.js',
        tableSorter: './files/res/js/classes/tableSorter.js',
    },
    output: {
        filename: '[name].min.js',
        path: path.resolve(__dirname, 'files/res/js/min'),
        clean: true,
    },
    plugins: [new CompressionPlugin()],
    optimization: {
        minimize: true,
        minimizer: [new TerserPlugin()],
    },
};
