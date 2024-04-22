var gulp = require('gulp');
var sass = require('gulp-sass')(require('sass'));
var plumber = require('gulp-plumber');
var notify = require('gulp-notify');

var config = {
	src: 'src/resources/scss/global.scss',
	dest: 'src/resources/build/',
};

var onError = function (err) {
	notify.onError({
		title: 'Gulp',
		subtitle: 'Failure!',
		message: 'Error: <%= error.message %>',
		sound: 'Beep',
	})(err);

	this.emit('end');
};

function defaultTask(cb) {
	var stream = gulp
		.src([config.src])
		.pipe(plumber({ errorHandler: onError }))
		.pipe(sass().on('error', sass.logError));

	return stream.pipe(gulp.dest(config.dest));
}

exports.default = defaultTask;
