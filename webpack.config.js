Encore
    // ... other settings ...
    
    // enables Sass/SCSS support
    .enableSassLoader()
    
    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()
    
    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())
    
    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig(); 