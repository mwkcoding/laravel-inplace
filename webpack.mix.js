let mix = require('laravel-mix');
let productionSourceMaps = false;

mix
  .setPublicPath('public/dist/resources/assets')
  .js('resources/assets/js/relation/index.js', 'js/relation/bundle.js')
  .react()
  .sourceMaps(productionSourceMaps, 'source-map');

/**development purpose */
mix.copy([
    'public/dist/resources/assets/js/relation/bundle.js',
], '../../test/testapp/public/vendor/inplace/resources/assets/js/relation');
