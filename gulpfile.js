/* jshint node: true */

var gulp = require('gulp');

gulp.task('default', ['sass'], function() {
    // place code for your default task here
});

gulp.task('sass', function() {
    var sass = require('gulp-sass');

    return gulp.src('module/Application/sass/**/*.scss')
            .pipe(sass({
                includePaths: ['node_modules/bootstrap-sass/assets/stylesheets'],
                outputStyle: 'compressed'
            }).on('error', sass.logError))
            .pipe(gulp.dest('htdocs/css'));
});
