'use strict';

var gulp = require('gulp'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify'),
    csso = require('gulp-csso');

gulp.task('style', function () {
    var path = '../assets/css/';

    return gulp.src(path + 'flexible-content-copy.css')
        .pipe(csso())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest(path));
});

gulp.task('js', function () {
    var path = '../assets/js/';

    return gulp.src(path + 'flexible-content-copy.js')
        .pipe(uglify({
            mangle: true
        }))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest(path));
});

gulp.task('default', ['js', 'style']);