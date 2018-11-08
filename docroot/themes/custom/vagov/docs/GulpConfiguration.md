# Gulp Configuration & Tasks
## Gulp Plugins
1. gulp-sass: sass compiling
2. gulp-livereload: automatically reload the browser upon making a change to styles or javascript during local dev
3. gulp-autoprefixer: automatically add necessary browser prefixes while compiling sass
4. gulp-sourcemaps: create inline sourcemaps
5. gulp-concat: concatenate javascript files


## Gulp Tasks
1. ``` sass ``` compile sass into css, add necessary browser prefixes, compress, and save in assets/css 
2. ``` watch ``` watch project directory for changes to theme files (sass, js, and twig files) and reloads browser to display changes
3. ``` scripts ``` compile and concatenate javascript 
4. ``` clearcache ``` clear drupal cache
