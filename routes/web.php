<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/api/documentation', function () {
    $documentation = 'default';
    $documentationConfig = config('l5-swagger.documentations.' . $documentation);
    $documentationTitle = $documentationConfig['api']['title'] ?? 'API Documentation';
    $urlsToDocs = [
        $documentationTitle => route('l5-swagger.'.$documentation.'.docs', [], true),
    ];

    return view('l5-swagger::index', compact('documentation', 'urlsToDocs', 'documentationTitle'));
});