'use strict';

const del = require('del');
const gulp = require('gulp');
const autoprefixer = require('gulp-autoprefixer');
const sass = require('gulp-sass');
const rename = require('gulp-rename');
const sourcemaps = require('gulp-sourcemaps');
const cssnano = require('gulp-cssnano');
const postcss = require('gulp-postcss');
const stylefmt = require('gulp-stylefmt');
const sorting = require('postcss-sorting');
const atimport = require('postcss-import');
const torem = require('postcss-pxtorem');
const sortOrder = require('./.postcss-sorting.json');
const pkg = require('./package.json');

// Config
const build = {
	css: './phpBB/styles/prosilver/theme/',
};

const AUTOPREFIXER_BROWSERS = [
	'> 1%',
	'last 2 versions'
];

gulp.task('css:fix', () => {
	const css = gulp
	.src(build.css + '*.css')
	.pipe(autoprefixer(AUTOPREFIXER_BROWSERS))
	.pipe(
		postcss([
			sorting(sortOrder)
		])
	)
	.pipe(stylefmt())
	.pipe(rename({
		basename: 'stylesheet',
		extname: '.css'
	}))
	.pipe(gulp.dest(build.css));

	return css;
});

gulp.task('css:build', () => {
	const css = gulp
	.src(build.css + 'theme.css')
	.pipe(
		postcss([
			atimport()
		])
	)
	.pipe(rename({
		basename: 'stylesheet',
		extname: '.css'
	}))
	.pipe(gulp.dest(build.css));

	return css;
});

gulp.task('clean', () => {
	del(['dist']);
});

gulp.task('minify', () => {
	const css = gulp
	.src(build.css + '/stylesheet.css')
	.pipe(sourcemaps.init())
	.pipe(cssnano())
	.pipe(rename({
		suffix: '.min',
		extname: '.css'
	}))
	.pipe(sourcemaps.write('./'))
	.pipe(gulp.dest(build.css));

	return css;
});

gulp.task('watch', () => {
	gulp.watch(['phpBB/styles/prosilver/theme/*.css', '!phpBB/styles/prosilver/theme/*.min.*'], gulp.series('minify'));
});

gulp.task('default', gulp.series('css:fix', 'css:build', 'watch'));
