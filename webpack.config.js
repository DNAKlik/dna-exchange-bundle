var Encore = require('@symfony/webpack-encore');

Encore.setOutputPath('src/Resources/public/build')
    .setPublicPath('/build')
    .setManifestKeyPrefix('bundles/dnaklikdnaexchange')

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .disableSingleRuntimeChunk()
    .enableStimulusBridge('./assets/controllers.json')

    .addEntry('app', './assets/app.js')
    .addStyleEntry('theme', './assets/css/theme.scss')
    // enables Sass/SCSS support
    .enableSassLoader()
;

module.exports = Encore.getWebpackConfig();