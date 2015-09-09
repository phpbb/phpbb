'use strict';

// Plugins
var gulp            = require('gulp'),
	rename 			= require('gulp-rename'),
	postcss         = require('gulp-postcss');

// Dir Variables
var theme          = 'prosilver',
	cssDir         = './phpbb/styles/' + theme + '/theme/',
	browserSupport = 'last 2 versions, IE >= 10',
	minify         = true,
	suf            = '.dev';

// Tasks
gulp.task('build:css', function () {
	var processors = [
		require('postcss-import')(),
		require('postcss-simple-vars'),
		require('autoprefixer')(),

	];

	if (minify) {
		processors.push(require('csswring')());
		suf = '.min';
	}

	return gulp.src([
					cssDir + "stylesheet.css",
					cssDir + "print.css",
					cssDir + "responsive.css",
					cssDir + "bidi.css",
					cssDir + "tweaks.css",
					cssDir + "plupload.css"
				])
				.pipe(postcss(processors))
				.pipe(rename({suffix: suf}))
				.pipe(gulp.dest(cssDir));

});

gulp.task('watch', function () {
	gulp.watch(cssDir + '(*.css|!*.min.css)', ['build:css']);
});

gulp.task('default', ['build:css', 'watch']);
