var del = require('del');
var elixir = require('laravel-elixir');
var gulp = require("gulp");
var concat = require("gulp-concat");
var coffee = require("gulp-coffee");
var Notification = require('laravel-elixir/ingredients/commands/Notification');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */


var onError = function(e) {
    new Notification().error(e, 'CoffeeScript Compilation Failed!');
    this.emit('end');
};


 elixir.extend("combinePublicApp", function() {
    gulp.task('combinePublicApp', function() {
      gulp.src('resources/assets/coffee/*.coffee')
        .pipe(concat('public-combined.coffee'))
        .pipe(coffee({}).on('error', onError))
        .pipe(gulp.dest('public/js'))
    });

    this.registerWatcher("combinePublicApp", "resources/assets/coffee/**/*.coffee");

    return this.queueTask("combinePublicApp");
 });

elixir(function(mix) {
    del('/tmp/elixir-quotebot-build/*', {force: true});

    // less
    mix
        .less('app.less')


    // combined build
        .combinePublicApp()

        ;
});
