<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/inplace/save', [devsrv\inplace\Controller\Request::class, 'save'])->name('inplace.save');