'use strict';

const del = require('del');
const gulp = require('gulp');
const autoprefixer = require('gulp-autoprefixer');
// const sass = require('gulp-sass');
const rename = require('gulp-rename');
const sourcemaps = require('gulp-sourcemaps');
const cssnano = require('cssnano');
const postcss = require('gulp-postcss');
const sorting = require('postcss-sorting');
const atimport = require('postcss-import');
// const torem = require('postcss-pxtorem');
const sortOrder = require('./.postcss-sorting.json');
// const pkg = require('./package.json');

// Config
const build = {
	css: './phpBB/styles/prosilver/theme/',
};

gulp.task('css', () => {
	const css = gulp
		.src(build.css + '*.css')
		.pipe(autoprefixer())
		.pipe(
			postcss([
				sorting(sortOrder),
			]),
		)
		.pipe(gulp.dest(build.css));

	return css;
});

gulp.task('clean', () => {
	del([ 'dist' ]);
});

gulp.task('minify', () => {
	const css = gulp
		.src(build.css + '/bidi.css')
		.pipe(sourcemaps.init())
		.pipe(
			postcss([
				atimport(),
				cssnano(),
			]),
		)
		.pipe(rename({
			suffix: '.min',
			extname: '.css',
		}))
		.pipe(sourcemaps.write('./'))
		.pipe(gulp.dest(build.css));

	return css;
});

gulp.task('watch', () => {
	gulp.watch('phpBB/styles/prosilver/theme/*.css', [ 'css' ]);
});

gulp.task('default', [ 'css', 'watch' ]);
