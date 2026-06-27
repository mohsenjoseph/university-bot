<?php

Route::post('/webhook', [App\Http\Controllers\BotController::class, 'handle']);