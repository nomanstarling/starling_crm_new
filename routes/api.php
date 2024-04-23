<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::get('dinstar/{id?}', [App\Http\Controllers\ApiController::class, 'dinstar'])->name('api.dinstar');

// Route::get('saveUsers', [App\Http\Controllers\ApiController::class, 'saveUsers'])->name('api.saveUsers');
Route::get('importCalls', [App\Http\Controllers\ApiController::class, 'importCalls'])->name('api.importCalls');
Route::get('importDinstar/{id}', [App\Http\Controllers\ApiController::class, 'importDinstar'])->name('api.importDinstar');
Route::get('cron/portal/{portal?}', [App\Http\Controllers\ApiController::class, 'cronPortal'])->name('api.cronPortal');
Route::get('portal/{portal?}', [App\Http\Controllers\ApiController::class, 'getPortal'])->name('api.getPortal');

Route::get('communities', [App\Http\Controllers\ApiController::class, 'importCommunities'])->name('api.communities');
Route::get('sub-communities', [App\Http\Controllers\ApiController::class, 'importSubCommunities'])->name('api.subCommunities');
Route::get('towers', [App\Http\Controllers\ApiController::class, 'importTowers'])->name('api.towers');
Route::get('sources', [App\Http\Controllers\ApiController::class, 'importSources'])->name('api.sources');

Route::get('features', [App\Http\Controllers\ApiController::class, 'importFeatures'])->name('api.features');

Route::get('importContacts', [App\Http\Controllers\ApiController::class, 'importContacts'])->name('api.importContacts');
Route::get('importListings', [App\Http\Controllers\ApiController::class, 'importListings'])->name('api.importListings');

Route::get('syncListings', [App\Http\Controllers\ApiController::class, 'syncListings'])->name('api.syncListings');

Route::get('syncImages', [App\Http\Controllers\ApiController::class, 'syncImages'])->name('api.syncImages');

// Route::get('webhook', [App\Http\Controllers\ApiController::class, 'webhook'])->name('api.webhook');
Route::post('webhooks/email', [App\Http\Controllers\ApiController::class, 'webhookEmail'])->name('api.webhookEmail');
Route::get('cron/leads/reassign', [App\Http\Controllers\ApiController::class, 'reassignLeads'])->name('api.reassignLeads');

Route::get('cron/leads/reassign-offplan', [App\Http\Controllers\ApiController::class, 'reassignLeadsOffPlan'])->name('api.reassignLeadsOffPlan');

Route::get('testNotify', [App\Http\Controllers\ApiController::class, 'testNotify'])->name('api.testNotify');


Route::get('brochure/{refno?}', [App\Http\Controllers\ApiController::class, 'brochure'])->name('api.brochure');
Route::get('listing/{refno?}', [App\Http\Controllers\ApiController::class, 'listingPreview'])->name('api.listingPreview');


