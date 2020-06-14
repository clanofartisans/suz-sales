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
	'resources/bower/bootstrap/dist/css/bootstrap.css',
	'resources/bower/font-awesome/css/font-awesome.css',
	'resources/css/sass.css',
	'resources/css/app.css'
	], 'public/css/app.css');

mix.js(
	'resources/bower/gasparesganga-jquery-loading-overlay/src/loadingoverlay.js',
	'public/js');

mix.copy(
	'resources/bower/gasparesganga-jquery-loading-overlay/src/loading.gif',
	'public/js');

mix.copy(
	'resources/bower/font-awesome/fonts',
	'public/fonts');
