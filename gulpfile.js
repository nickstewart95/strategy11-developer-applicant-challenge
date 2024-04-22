var gulp = require('gulp');
var sass = require('gulp-sass')(require('sass'));
var minify = require('gulp-minify');

function defaultTask(cb) {
	return new Promise(function (resolve, reject) {
		gulp
			.src('src/resources/js/global.js')
			.pipe(minify())
			.pipe(gulp.dest('src/resources/build/'));

		gulp
			.src('src/resources/scss/global.scss')
			.pipe(sass())
			.pipe(gulp.dest('src/resources/build/'));

		resolve();
	});
}

exports.default = defaultTask;
