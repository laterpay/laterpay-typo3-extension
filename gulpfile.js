var gulp    = require('gulp'),
    plugins = require('gulp-load-plugins')(),
    nib     = require('nib'),
    del     = require('del'),
    p               = {
                        allfiles    : [
                                            './**/*.php',
                                            './**/*.phtml',
                                            './asset_sources/stylus/**/*.styl',
                                            './asset_sources/js/*.js'
                                      ],
                        phpfiles    : ['./**/*.php', '!./laterpay/library/**/*.php'],
                        srcStylus   : './asset_sources/stylus/*.styl',
                        srcJS       : './asset_sources/js/',
                        srcSVG      : './asset_sources/img/**/*.svg',
                        srcPNG      : './asset_sources/img/**/*.png',
                        distJS      : './res/js/',
                        distCSS     : './res/css/',
                        distIMG     : './res/img/',
                    };


// TASKS -----------------------------------------------------------------------
// clean up all files in the target directories
gulp.task('clean', function(cb) {
    del([p.distJS + '*.js', p.distCSS + '*.css'], cb);
});

// CSS related tasks
gulp.task('css-watch', function() {
    gulp.src(p.srcStylus)
        .pipe(plugins.stylus({                                                  // process Stylus sources to CSS
            use     : nib(),
            linenos : true                                                      // make line numbers available in browser dev tools
        }))
        .pipe(plugins.sourcemaps.init())
        .pipe(plugins.autoprefixer('last 3 versions', '> 2%', 'ff > 23', 'ie > 8')) // vendorize properties for supported browsers
        .pipe(plugins.sourcemaps.write('./maps'))                               // write sourcemaps
        .on('error', plugins.notify.onError())
        .pipe(gulp.dest(p.distCSS));                                            // move to target folder
});

gulp.task('css-build', function() {
    gulp.src(p.srcStylus)
        .pipe(plugins.stylus({                                                  // process Stylus sources to CSS
            use     : nib(),
            compress: true
        }))
        .on('error', plugins.notify.onError())
        .pipe(plugins.autoprefixer('last 3 versions', '> 2%', 'ff > 23', 'ie > 8')) // vendorize properties for supported browsers
        .pipe(gulp.dest(p.distCSS));                                            // move to target folder
});

// Javascript related tasks
gulp.task('js-watch', function() {
    gulp.src(p.srcJS + '*.js')
        .pipe(plugins.cached('hinting'))                                        // only process modified files
            .pipe(plugins.jshint('.jshintrc'))
            .pipe(plugins.jshint.reporter(plugins.stylish))
            .pipe(plugins.sourcemaps.init())
            .pipe(plugins.sourcemaps.write('./maps'))                           // write sourcemaps
            .pipe(gulp.dest(p.distJS));                                         // move to target folder
});

gulp.task('js-build', function() {
    gulp.src(p.srcJS + '*.js')
        .pipe(plugins.jshint('.jshintrc'))
        .pipe(plugins.jshint.reporter(plugins.stylish))
        .pipe(plugins.uglify())                                                 // compress with uglify
        .pipe(gulp.dest(p.distJS));                                             // move to target folder
});

// Image related tasks
gulp.task('img-build', function() {
    gulp.src(p.srcSVG)
        .pipe(plugins.svgmin())                                                 // compress with svgmin
        .pipe(gulp.dest(p.distIMG));                                            // move to target folder

    gulp.src(p.srcPNG)
        .pipe(plugins.tinypng('-XMDmj3TW4Rymyizj1jcUXIvtc-uYJcM'))              // compress with TinyPNG
        .pipe(gulp.dest(p.distIMG));                                            // move to target folder
});

// update git submodules
gulp.task('updateSubmodules', function() {
    plugins.git.updateSubmodule({args: '--init'});
});


// COMMANDS --------------------------------------------------------------------
gulp.task('default', ['clean', 'img-build', 'css-watch', 'js-watch'], function() {
    // watch for changes
    gulp.watch(p.stylus,            ['css-watch']);
    gulp.watch(p.srcJS + '*.js',    ['js-watch']);
});

// build project for release
gulp.task('build', ['clean', 'updateSubmodules'], function() {
    // TODO: git archive is the right option to export the entire repo
    gulp.start('img-build');
    gulp.start('css-build');
    gulp.start('js-build');
});
