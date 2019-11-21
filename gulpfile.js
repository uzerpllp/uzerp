// gulpfile.js for uzERP
//
// Steve Blamey <blameys@blueloop.net>
// License GPLv3 or later
// Copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.

var gulp = require('gulp');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var less = require('gulp-less');
var minify = require('gulp-clean-css');
var merge = require('merge-stream');
var hash = require('gulp-hash-filename');
var clean = require('gulp-clean');
var wrap = require('gulp-wrap');

var less_source = 'assets/css/uzerp';
var less_source_libs = 'assets/css/lib';


//script paths
var jsFiles = [
        'assets/js/lib/console.js',
        // jQuery functions
        'assets/js/lib/functions.js',
        'assets/js/lib/ajax.js',
        'assets/js/lib/print_dialog.js',
        'assets/js/lib/rules.js',
	'assets/js/lib/click-info.js',
        // jQuery plugins
        'assets/js/vendor/uiBlock/uiBlock.js',
        'assets/js/vendor/superfish-1.4.8/hoverIntent.js',
        'assets/js/vendor/superfish-1.4.8/superfish.js',
        'assets/js/vendor/jquery.multiSelect-1.2.2/jquery.bgiframe.min.js',
        'assets/js/vendor/jquery.multiSelect-1.2.2/jquery.multiSelect.js',
        'assets/js/vendor/jquery.watermark-3.1.1/jquery.watermark.js',
        'assets/js/lib/uz-collection/jquery.uz-grid.js',
        'assets/js/lib/uz-collection/jquery.uz-validation.js',
        'assets/js/lib/uz-collection/jquery.uz-autocomplete.js',
        'assets/js/lib/uz-collection/jquery.uz-constrains.js',
        'assets/js/lib/uz-collection/charts/uz-chart.js',
        'assets/js/lib/uz-collection/charts/jquery.uz-pie-chart.js',
        'assets/js/lib/uz-collection/charts/jquery.uz-line-chart.js',
        'assets/js/lib/uz-collection/charts/jquery.uz-bar-chart.js',
        'assets/js/vendor/fullcalendar-1.6.6/fullcalendar/fullcalendar.min.js',
        'assets/js/vendor/fullcalendar-1.6.6/fullcalendar/gcal.js',
        'assets/js/vendor/jQuery-contextMenu-2.9.0/dist/jquery.contextMenu.js',
        'assets/js/vendor/jQuery-contextMenu-2.9.0/dist/jquery.ui.position.js',
        'assets/js/vendor/collapsibleCheckboxTree/jquery.collapsibleCheckboxTree.js',
        'assets/js/vendor/tabby/tabby.js',
        'assets/js/vendor/scrollTo/jquery.scrollTo-1.4.2-min.js',
        'assets/js/vendor/tinysort/jquery.tinysort.min.js',
        'assets/js/vendor/jqPagination/js/jqPagination.jquery.js',
        'assets/js/vendor/nestedSortable/jquery.ui.nestedSortable.js',
        'assets/js/vendor/wijmo/external/raphael.js',
        'assets/js/vendor/wijmo/jquery.wijmo.wijchartcore.js',
        'assets/js/vendor/wijmo/jquery.wijmo.wijpiechart.min.js',
        'assets/js/vendor/wijmo/jquery.wijmo.wijlinechart.min.js',
        'assets/js/vendor/wijmo/jquery.wijmo.wijbarchart.min.js',
        'assets/js/vendor/wijmo/external/jquery.glob.min.js',
        'assets/js/vendor/jquery.tableScroll/jquery.tablescroll.js',
        // uzLET js
        'modules/public_pages/**/resources/js/*.uzlet.js'
    ],

    cssHead = [
        'assets/css/reset.css',
    ];

    lessHead = [
        `${less_source}/theme.less`,
    ];

    cssFiles = [
        // jQuery CSS files
        `${less_source_libs}/jquery.watermark-3.1.1/jquery.watermark.css`,
        //`${less_source_libs}/fullcalendar/fullcalendar.css`,
	'assets/js/vendor/fullcalendar-1.6.6/fullcalendar/fullcalendar.css',
        'assets/js/vendor/collapsibleCheckboxTree/jquery.collapsibleCheckboxTree.css',
        'assets/js/vendor/jqPagination/css/style.css',
        'assets/js/vendor/jQuery-contextMenu-2.9.0/dist/jquery.contextMenu.css',
        'modules/public_pages/**/resources/css/**/*.css',
    ];

    lessFiles = [
        'modules/public_pages/**/*.less',
        '!modules/public_pages/login/resources/css/login.less'
    ];

    jsDest = 'dist/js';
    cssDest = 'dist/css';

gulp.task('clean-scripts', function () {
  return gulp.src(['dist/js/**/*.js',], {read: false})
    .pipe(clean());
});


gulp.task('clean-styles', function () {
  return gulp.src('dist/css/*.css', {read: false})
    .pipe(clean());
});


gulp.task('scripts', function() {
    var stream =  gulp.src(jsFiles)
        .pipe(concat('scripts.js'))
        .pipe(rename('scripts.min.js'))
        .pipe(hash())
        .pipe(uglify())
        .pipe(gulp.dest(jsDest));
    return stream;
});

gulp.task('dev-scripts', function() {
    jsFiles.unshift('assets/js/vendor/jquery-migrate-1.4.1.js',);
    return gulp.src(jsFiles)
	    .pipe(wrap('// <%= file.path %>\n<%= contents %>'))
        .pipe(concat('scripts.js'))
        .pipe(hash())
        .pipe(gulp.dest(jsDest));
});

gulp.task('module-scripts', function () {
    return gulp.src(['modules/public_pages/**/*.js', '!modules/public_pages/**/*uzlet.js'])
        .pipe(hash({'template': '<%= name %>-<%= hash %>.min<%= ext %>'}))
        .pipe(uglify())
        .pipe(gulp.dest( 'dist/js/modules' ));
});

gulp.task('dev-module-scripts', function () {
    return gulp.src(['modules/public_pages/**/*.js', '!modules/public_pages/**/*uzlet.js'])
	    .pipe(wrap('// <%= file.path %>\n<%= contents %>'))
	    .pipe(hash())
        .pipe(gulp.dest( 'dist/js/modules' ));
});

gulp.task('styles', function() {
    var cssHeadStream = gulp.src(cssHead)
        .pipe(concat('css-head.css'));

    var lessHeadStream = gulp.src(lessHead)
        .pipe(less())
        .pipe(concat('less-head.less'));

    var cssStream = gulp.src(cssFiles)
        .pipe(concat('css-files.css'));

    var lessStream = gulp.src(lessFiles)
        .pipe(less({paths: [`${less_source}`,]}))
        .pipe(concat('less-files.less'));

    var mergedStream = merge(cssHeadStream, lessHeadStream, lessStream, cssStream )
        .pipe(concat('main.css'))
        .pipe(hash())
        .pipe(minify())
        .pipe(gulp.dest(cssDest));

    return mergedStream;
});


gulp.task('dev-styles', function() {
    var cssHeadStream = gulp.src(cssHead)
	    .pipe(wrap('/* <%= file.path %> */\n<%= contents %>'))
        .pipe(concat('css-head.css'));

    var lessHeadStream = gulp.src(lessHead)
	    .pipe(wrap('/* <%= file.path %> */\n<%= contents %>'))
        .pipe(less())
        .pipe(concat('less-head.less'));

    var cssStream = gulp.src(cssFiles)
    	.pipe(wrap('/* <%= file.path %> */\n<%= contents %>'))
        .pipe(concat('css-files.css'));

    var lessStream = gulp.src(lessFiles)
	    .pipe(wrap('/* <%= file.path %> */\n<%= contents %>'))
        .pipe(less({paths: [`${less_source}`,]}))
        .pipe(concat('less-files.less'));

    var mergedStream = merge(cssHeadStream, lessHeadStream, lessStream, cssStream )
        .pipe(concat('main.css'))
        .pipe(hash())
        .pipe(gulp.dest(cssDest));

    return mergedStream;
});


gulp.task('login-styles', function() {
    return gulp.src('modules/public_pages/login/resources/css/login.less')
        .pipe(less({paths: [`${less_source}`,]}))
        .pipe(hash())
        .pipe(minify())
        .pipe(gulp.dest(cssDest));
});


gulp.task('watch', function() {
    // Watch .js files
    gulp.watch(
        [
            'assets/**/*.js',
            'lib/js/**/*.js',
            'modules/public_pages/**/*.js'
        ],
        gulp.series(
            'clean-scripts',
            'dev-scripts',
            'dev-module-scripts'
        )
    );
    // Watch styles
    gulp.watch([
            'assets/**/*.css',
            'assets/**/*.less',
            'modules/public_pages/**/resources/css/**/*.css',
            'modules/public_pages/**/resources/css/**/*.less',
            'lib/js/**/*.css'
        ],
        gulp.series(
            'clean-styles',
            'dev-styles',
	    'login-styles'
        )
    );
});


gulp.task(
    'default',
        gulp.series(
            gulp.parallel(
                'clean-scripts',
                'clean-styles'
            ),
            gulp.parallel(
                'scripts',
                'module-scripts',
                'styles',
                'login-styles'
            )
        )
);

gulp.task(
    'build-dev',
        gulp.series(
            gulp.parallel(
                'clean-scripts',
                'clean-styles'
            ),
            gulp.parallel(
                'dev-scripts',
                'dev-module-scripts',
                'dev-styles',
                'login-styles'
            )
        )
);
