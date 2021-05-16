'use strict';

const del = require('del');
const gulp = require('gulp');
const autoprefixer = require('autoprefixer');
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

gulp.task('css', gulp.series(() => {
	return gulp
		.src(build.css + '*.css')
		.pipe(
			postcss([
				autoprefixer(),
				sorting(sortOrder),
			]),
		)
		.pipe(gulp.dest(build.css));
}));

gulp.task('clean', gulp.series(() => {
	del([ 'dist' ]);
}));

gulp.task('minify', gulp.series(() => {
	return gulp
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
}));

gulp.task('watch', gulp.series(() => {
	gulp.watch('phpBB/styles/prosilver/theme/*.css', gulp.series('css'));
}));

exports.default = gulp.series('css', 'watch');
