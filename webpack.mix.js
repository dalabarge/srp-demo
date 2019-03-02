let mix = require('laravel-mix')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/scripts/app.js', 'public/js')
   .sass('resources/styles/app.scss', 'public/css')
   .extract([
       '@fortawesome/fontawesome-svg-core',
       '@fortawesome/free-brands-svg-icons',
       '@fortawesome/free-regular-svg-icons',
       '@fortawesome/free-solid-svg-icons',
       '@fortawesome/vue-fontawesome',
       'axios',
       'lodash',
       'uikit',
       'vue',
   ])

if ( ! mix.inProduction()) {
    mix.disableSuccessNotifications()
       // .browserSync(process.env.MIX_APP_URL)
       .sourceMaps()
}

if (mix.inProduction()) {
    mix.version()
}
