const Encore = require("@symfony/webpack-encore");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore.setOutputPath("public_html/assets/")
  .setPublicPath("/assets")
  .addEntry("app", "./assets/app.js")
  .addEntry("wallpaper-list", "./assets/wallpaper-list.js")
  .disableSingleRuntimeChunk()
  .splitEntryChunks()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(true)
  .enableVersioning(Encore.isProduction())
  .configureBabel((config) => {
    "use strict";
    config.plugins.push("@babel/plugin-proposal-class-properties");
  })
  .configureBabelPresetEnv((config) => {
    "use strict";
    config.useBuiltIns = "usage";
    config.corejs = 3;
  })
  .enableSassLoader()
  .enableTypeScriptLoader();

module.exports = Encore.getWebpackConfig();
