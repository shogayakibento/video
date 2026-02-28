<?php

use App\Http\Controllers\ActressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TweetVideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/genre', [GenreController::class, 'index'])->name('genre.index');
Route::get('/genre/{slug}', [GenreController::class, 'show'])->name('genre.show');
Route::get('/actress', [ActressController::class, 'index'])->name('actress.index');
Route::get('/actress/{id}', [ActressController::class, 'show'])->name('actress.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Xツイートランキング
Route::get('/tweet-ranking', [RankingController::class, 'tweetIndex'])->name('tweet.ranking.index');
Route::get('/tweet-video/{video}', [TweetVideoController::class, 'show'])->name('tweet.video.show');
Route::get('/tweet-go/{video}', [TweetVideoController::class, 'redirect'])->name('tweet.video.redirect');

// 管理画面
Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');

Route::prefix('admin')->middleware('admin.auth')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/videos', [AdminController::class, 'videos'])->name('admin.videos');
    Route::patch('/video/{video}/likes', [AdminController::class, 'updateLikes'])->name('admin.video.update-likes');
    Route::get('/video/{video}/tweets', [AdminController::class, 'tweetForm'])->name('admin.tweet.form');
    Route::post('/video/{video}/tweets', [AdminController::class, 'storeTweet'])->name('admin.tweet.store');
    Route::delete('/tweet/{tweet}', [AdminController::class, 'deleteTweet'])->name('admin.tweet.delete');
    Route::get('/quick-add', [AdminController::class, 'quickAdd'])->name('admin.quick-add');
    Route::post('/quick-add', [AdminController::class, 'quickAddStore'])->name('admin.quick-add.store');
});
