'use strict';

const gulp = require('gulp');
const autoprefixer = require('autoprefixer');
const rename = require('gulp-rename');
const cssnano = require('cssnano');
const postcss = require('gulp-postcss');
const sorting = require('postcss-sorting');
const atimport = require('postcss-import');
const sortOrder = require('./.postcss-sorting.json');
// const pkg = require('./package.json');

// Config
const paths = {
	styles: {
		src: './phpBB/styles/prosilver/theme/*.css',
		css: './phpBB/styles/prosilver/theme/',
	},
};

function css() {
	return gulp.src(paths.styles.src)
		.pipe(
			postcss([
				autoprefixer(),
				sorting(sortOrder),
			]),
		)
		.pipe(gulp.dest(paths.styles.css));
}

/** @todo: currently does not properly work, needs to be fixed */
function minify() {
	return gulp.src(paths.styles.src, { sourcemaps: true })
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
		.pipe(gulp.dest(paths.styles.css));
}

function watch() {
	gulp.watch(paths.styles.src, css);
}

exports.css = css;
exports.minify = minify;
exports.watch = watch;

exports.default = gulp.series(css, watch);
