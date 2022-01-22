let mix = require('laravel-mix');

mix.sass('assets/src/sass/frontend.scss', 'assets/build/css')
    .js('assets/src/js/admin.js', 'assets/build/js');
