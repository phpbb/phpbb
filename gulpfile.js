'use strict';

// Plugins
var gulp			= require('gulp'),
	rename			= require('gulp-rename'),
	sass			= require('gulp-sass'),
	postcss			= require('gulp-postcss');

// Dir Variables
var theme			= 'prosilver',
	srcDir			= './build/src/',
	cssDir			= './phpbb/styles/' + theme + '/theme/',
	css				= [
						'base.scss',
						'bidi.scss',
						'buttons.scss',
						'colours.scss',
						'common.scss',
						'content.scss',
						'cp.scss',
						'forms.scss',
						'icons.scss',
						'normalize.scss',
						'pupload.scss',
						'print.scss',
						'responsive.scss',
						'tweaks.scss',
						'utilities.scss',
					],
	browserSupport	= 'last 2 versions, IE >= 10',
	minify			= false,
	suf				= '';

// Tasks
gulp.task('build:css', function () {
	var processors = [
		require('autoprefixer')()
	];

	if (minify) {
		processors.push(require('csswring')());
		css = ['stylesheet.scss', 'print.scss', 'pupload.scss', 'tweaks.scss', 'bidi.scss'];
		suf = '.min';
	}

	return gulp.src(srcDir + css)
				.pipe(sass().on('error', sass.logError))
				.pipe(postcss(processors))
				.pipe(rename({suffix: suf}))
				.pipe(gulp.dest(cssDir));
});

gulp.task('watch', function () {
	gulp.watch(srcDir + '*.scss', ['build:css']);
});

gulp.task('default', ['build:css', 'watch']);
