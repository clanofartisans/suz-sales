const mix = require('laravel-mix');

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

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');

mix.js('resources/js/app-footer.js', 'public/js');

mix.styles([
	'node_modules/bootstrap/dist/css/bootstrap.css',
	'node_modules/@fortawesome/fontawesome-free/css/fontawesome.min.css',
	'node_modules/@fortawesome/fontawesome-free/css/regular.min.css',
	'node_modules/@fortawesome/fontawesome-free/css/solid.min.css',
	'node_modules/@fortawesome/fontawesome-free/css/brands.min.css',
	'resources/css/app.css'
	], 'public/css/app.css');

mix.js(
	'node_modules/gasparesganga-jquery-loading-overlay/dist/loadingoverlay.js',
	'public/js');
