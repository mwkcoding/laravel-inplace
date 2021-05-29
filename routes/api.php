<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::post('/inplace/save', [devsrv\inplace\Controller\Request::class, 'save'])->name('inplace.save');
    Route::post('/inplace/relation/save', [devsrv\inplace\Controller\RelationRequest::class, 'save'])->name('inplace.relation.save');
});