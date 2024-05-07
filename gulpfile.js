'use strict';

const gulp = require('gulp');
const rename = require('gulp-rename');
const concat = require('gulp-concat');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const sorting = require('postcss-sorting');
const sortOrder = require('./.postcss-sorting.json');

// Config
const paths = {
	styles: {
		src: './phpBB/styles/prosilver/theme/*.css',
		css: './phpBB/styles/prosilver/theme/',
	},
};

function styles() {
	return gulp.src(paths.styles.src, { sourcemaps: true })
		.pipe(
			postcss([
				autoprefixer(),
				sorting(sortOrder),
			]),
		)
		.pipe(gulp.dest(paths.styles.css, { sourcemaps: './' }));
}

function minify() {
	return gulp.src([
		paths.styles.css + 'normalize.css',
		paths.styles.css + 'base.css',
		paths.styles.css + 'utilities.css',
		paths.styles.css + 'icons.css',
		paths.styles.css + 'common.css',
		paths.styles.css + 'buttons.css',
		paths.styles.css + 'links.css',
		paths.styles.css + 'mentions.css',
		paths.styles.css + 'content.css',
		paths.styles.css + 'cp.css',
		paths.styles.css + 'forms.css',
		paths.styles.css + 'colours.css',
		paths.styles.css + 'responsive.css',
		paths.styles.css + 'bidi.css',
	], { sourcemaps: true })
		.pipe(concat('stylesheet.css'))
		.pipe(
			postcss([
				cssnano(),
			]),
		)
		.pipe(rename({
			suffix: '.min',
			extname: '.css',
		}))
		.pipe(gulp.dest(paths.styles.css, { sourcemaps: './' }));
}

function watch() {
	gulp.watch(paths.styles.src, styles);
}

exports.style = styles;
exports.minify = minify;
exports.watch = watch;

exports.default = gulp.series(styles, minify, watch);
