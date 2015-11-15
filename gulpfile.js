'use strict';

// Plugins
var gulp            = require('gulp'),
	rename 			= require('gulp-rename'),
	postcss         = require('gulp-postcss');

// Dir Variables
var theme          = 'prosilver',
	cssDir         = './phpbb/styles/' + theme + '/theme/',
	css            =  [
						'base.css',
						'bidi.css',
						'buttons.css',
						'colours.css',
						'common.css',
						'content.css',
						'cp.css',
						'forms.css',
						'icons.css',
						'normalize.css',
						'pupload.css',
						'print.css',
						'responsive.css',
						'tweaks.css',
						'utilities.css',
					]
	browserSupport = 'last 2 versions, IE >= 10',
	minify         = true,
	suf            = '';

// Tasks
gulp.task('build:css', function () {
	var processors = [
		require('precss'),
		require('autoprefixer')(),

	];

	if (minify) {
		processors.push(require('csswring')());
		css = 'stylesheet.css'
		suf = '.min';
	}

	return gulp.src(css)
				.pipe(postcss(processors))
				.pipe(rename({suffix: suf}))
				.pipe(gulp.dest(cssDir));

});

gulp.task('watch', function () {
	gulp.watch('*.css', ['build:css']);
});

gulp.task('default', ['build:css', 'watch']);
