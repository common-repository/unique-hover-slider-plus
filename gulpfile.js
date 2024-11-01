// Including plugins
var gulp = require('gulp'),
    notify = require("gulp-notify"),
    compass = require("gulp-compass"),
    concat = require("gulp-concat"),
    minifyCss = require("gulp-minify-css"),
    plumber = require("gulp-plumber"),
    rename = require('gulp-rename'),
    retinize = require("gulp-retinize"),
    sass = require("gulp-sass"),
    sourcemaps = require("gulp-sourcemaps"),
    uglify = require("gulp-uglify"),
    watch = require("gulp-watch");

function errorAlert(error){
	notify.onError({
        title: "Oops",
        message: "<%= error.message %>",
        sound: "Sosumi"
    })(error); // Error Notification
	console.log(error.toString()); // Prints Error to Console
	this.emit("end"); // End function
};

// Compile SCSS to CSS and minify.
gulp.task('compass', function() {
    gulp.src('./source/sass/stylesheet.scss')
    .pipe(plumber({errorHandler: errorAlert}))
    .pipe(compass({
        css: 'assets/css',
        sass: 'source/sass',
        image: 'assets/images'
    }))
    .pipe(rename('stylesheet.min.css'))
    .pipe(minifyCss())
    .pipe(gulp.dest('./assets/css/'))
    .pipe(notify({ title: 'SCSS', message: 'Compiled and minified' }));
});

// Compile JS and map and minify.
gulp.task('scripts', function() {
    return gulp.src([
        './source/js/app.js'
    ])
    .pipe(plumber({errorHandler: errorAlert}))
    .pipe(sourcemaps.init())
    .pipe(concat('script.js'))
    .pipe(gulp.dest('./assets/js/'))
    .pipe(rename('script.min.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./assets/js/'))
    .pipe(notify({ title: 'Javascript', message: 'Compiled and minified' }));
});

// Compile vendor JS and map and minify.
gulp.task('vendor', function() {
    return gulp.src([
        './source/js/vendor/ResizeSensor.js',
        './source/js/vendor/ElementQueries.js'
    ])
    .pipe(plumber({errorHandler: errorAlert}))
    .pipe(sourcemaps.init())
    .pipe(concat('vendor.js'))
    .pipe(gulp.dest('./assets/js/'))
    .pipe(rename('vendor.min.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./assets/js/'))
    .pipe(notify({ title: 'Javascript', message: 'Compiled and minified' }));
});

// Compile colorpicker JS and map and minify.
gulp.task('colorpicker', function() {
    return gulp.src([
        './source/js/colorpicker/colors.js',
        './source/js/colorpicker/jqColorPicker.js',
        './source/js/colorpicker/script.js'
    ])
    .pipe(plumber({errorHandler: errorAlert}))
    .pipe(sourcemaps.init())
    .pipe(concat('colorpicker.js'))
    .pipe(gulp.dest('./assets/js/'))
    .pipe(rename('colorpicker.min.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./assets/js/'))
    .pipe(notify({ title: 'Javascript', message: 'Compiled and minified' }));
});

// Compile images to retina friendly.
gulp.task('images', function() {
    gulp.src('./source/images/**/*.{png,jpg,jpeg}')
        .pipe(retinize())
        .pipe(gulp.dest('./assets/images/'));
});

// Gulp watch, keep watching while programming.
gulp.task('watch', function() {
    gulp.watch('source/sass/**/*.scss', ['compass']);
    gulp.watch('source/js/**/*.js', ['scripts']);
    gulp.watch('source/js/**/*.js', ['colorpicker']);
    gulp.watch('source/images/**/*.{png,jpg,jpeg}', ['images']);
});

gulp.task('default', function() {});
