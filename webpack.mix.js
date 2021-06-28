let mix = require('laravel-mix');
let productionSourceMaps = false;

mix
  .setPublicPath('public/dist/resources/assets')
  .js('resources/assets/js/relation/index.js', 'js/relation/bundle.js')
  .react()
  .sourceMaps(productionSourceMaps, 'source-map');

// mix.copy([
//     'dist/js/index.js',
// ], '../../public/vendor/formash/js');
