<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post('/inplace/save', [devsrv\inplace\Controller\Request::class, 'save'])->name('inplace.save');