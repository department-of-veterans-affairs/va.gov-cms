# Gulp Configuration & Tasks
## Gulp Plugins
1. gulp-sass: sass compiling
2. browser-sync: reflect code changes instantly through live-reloading
3. gulp-autoprefixer: automatically add necessary browser prefixes while compiling sass
4. gulp-sourcemaps: create inline sourcemaps
5. gulp-concat: concatenate javascript files
6. gulp-babel: convert ECMAScript 2015+ code into backwards compatible javascript.
7. gulp-uglify: minify javascript


## Gulp Tasks
##### You can always run ```gulp --tasks``` to get a list of all the gulp tasks in this project with descriptions.
1. ``` sass ``` compile sass into css, add necessary browser prefixes, compress, and save in assets/css 
2. ``` watch ``` watch project directory for changes to theme files (sass, js, and twig files) and reloads browser to display changes
3. ``` scripts ``` compile, concatenate, and minify javascript 
4. ``` clearcache ``` clear drupal cache
