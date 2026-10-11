<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'API E-Voting OSIS System is Running!']);
});