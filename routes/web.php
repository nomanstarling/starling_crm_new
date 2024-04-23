<?php

use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Controllers\ImpersonateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::middleware(['auth'])->group(function () {
    Route::get('users/impersonate/{user?}', [ImpersonateController::class, 'take'])->name('impersonate');
    Route::get('users/impersonate.leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');
// });

Route::get('/', function () {
    // return view('welcome');
    return redirect('login');
});

// Add this to your routes/web.php
Route::get('check-session', function () {
    return response()->json(['sessionTimeout' => auth()->guest()]);
})->name('check.session');

Auth::routes();
Route::get('/logins/google', [App\Http\Controllers\Auth\LoginController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/logins/google/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleGoogleCallback']);
//Route::get('/login/google/callback', 'Auth\LoginController@handleGoogleCallback');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/pdf', [App\Http\Controllers\HomeController::class, 'pdf'])->name('pdf');

Route::get('/getPassword', [App\Http\Controllers\HomeController::class, 'getPassword'])->name('getPassword');

//Route::get('/create', [App\Http\Controllers\HomeController::class, 'createUser'])->name('createUser');


Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');
Route::get('/profile', [App\Http\Controllers\HomeController::class, 'profile'])->name('profile');
Route::post('/profile', [App\Http\Controllers\HomeController::class, 'profilePost'])->name('profilePost');

Route::get('/getPdf', [App\Http\Controllers\HomeController::class, 'getPdf'])->name('getPdf');

Route::get('users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
Route::get('getUsers', [App\Http\Controllers\UserController::class, 'getUsers'])->name('users.getUsers');
Route::get('users/getList', [App\Http\Controllers\UserController::class, 'getList'])->name('users.getList');
Route::get('getTeamUsers', [App\Http\Controllers\UserController::class, 'getTeamUsers'])->name('users.getTeamUsers');
Route::get('users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
Route::post('users/store', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
//Route::get('users/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
Route::get('users/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
Route::post('users/update/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
//Route::delete('users/delete/{user?}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

Route::post('users/bulk-delete', [App\Http\Controllers\UserController::class, 'bulkDelete'])->name('users.bulkDelete');
Route::post('users/bulk-restore', [App\Http\Controllers\UserController::class, 'bulkRestore'])->name('users.bulkRestore');
Route::post('users/bulk-activate', [App\Http\Controllers\UserController::class, 'bulkActivate'])->name('users.bulkActivate');
Route::post('users/bulk-deactivate', [App\Http\Controllers\UserController::class, 'bulkDeactivate'])->name('users.bulkDeactivate');

Route::get('roles', [App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
Route::get('roles/create', [App\Http\Controllers\RoleController::class, 'create'])->name('roles.create');
Route::get('roles/{role}', [App\Http\Controllers\RoleController::class, 'show'])->name('roles.show');

Route::get('roles/{role}/edit', [App\Http\Controllers\RoleController::class, 'edit'])->name('roles.edit');
Route::post('roles', [App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
Route::post('roles/{role}', [App\Http\Controllers\RoleController::class, 'update'])->name('roles.update');
Route::delete('roles/{role}', [App\Http\Controllers\RoleController::class, 'destroy'])->name('roles.destroy');

Route::get('permissions', [App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
Route::get('permissions/create', [App\Http\Controllers\PermissionController::class, 'create'])->name('permissions.create');
Route::get('permissions/{permission}', [App\Http\Controllers\PermissionController::class, 'show'])->name('permissions.show');
Route::get('permissions/{permission}/edit', [App\Http\Controllers\PermissionController::class, 'edit'])->name('permissions.edit');
Route::post('permissions', [App\Http\Controllers\PermissionController::class, 'store'])->name('permissions.store');
Route::post('permissions/{permission}', [App\Http\Controllers\PermissionController::class, 'update'])->name('permissions.update');
Route::delete('permissions/{permission}', [App\Http\Controllers\PermissionController::class, 'destroy'])->name('permissions.destroy');

// Community routes
Route::get('communities', [App\Http\Controllers\CommunitiesController::class, 'index'])->name('communities.index');
Route::get('getCommunities', [App\Http\Controllers\CommunitiesController::class, 'getCommunities'])->name('communities.getCommunities');
Route::get('communities/create', [App\Http\Controllers\CommunitiesController::class, 'create'])->name('communities.create');
Route::post('communities/store', [App\Http\Controllers\CommunitiesController::class, 'store'])->name('communities.store');
Route::get('communities/{community}/edit', [App\Http\Controllers\CommunitiesController::class, 'edit'])->name('communities.edit');
Route::post('communities/update/{community}', [App\Http\Controllers\CommunitiesController::class, 'update'])->name('communities.update');
//Route::delete('communities/delete/{community?}', [App\Http\Controllers\CommunitiesController::class, 'destroy'])->name('communities.destroy');

Route::post('communities/bulk-delete', [App\Http\Controllers\CommunitiesController::class, 'bulkDelete'])->name('communities.bulkDelete');
Route::post('communities/bulk-restore', [App\Http\Controllers\CommunitiesController::class, 'bulkRestore'])->name('communities.bulkRestore');
Route::post('communities/bulk-activate', [App\Http\Controllers\CommunitiesController::class, 'bulkActivate'])->name('communities.bulkActivate');
Route::post('communities/bulk-deactivate', [App\Http\Controllers\CommunitiesController::class, 'bulkDeactivate'])->name('communities.bulkDeactivate');
Route::post('communities/export', [App\Http\Controllers\CommunitiesController::class, 'export'])->name('communities.export');


// Sub-Community routes
Route::get('sub-communities', [App\Http\Controllers\SubCommunitiesController::class, 'index'])->name('subCommunities.index');
Route::get('getSubCommunities', [App\Http\Controllers\SubCommunitiesController::class, 'getSubCommunities'])->name('subCommunities.getSubCommunities');
Route::get('sub-communities/create', [App\Http\Controllers\SubCommunitiesController::class, 'create'])->name('subCommunities.create');
Route::post('sub-communities/store', [App\Http\Controllers\SubCommunitiesController::class, 'store'])->name('subCommunities.store');
Route::get('sub-communities/{sub_community}/edit', [App\Http\Controllers\SubCommunitiesController::class, 'edit'])->name('subCommunities.edit');
Route::post('sub-communities/update/{sub_community}', [App\Http\Controllers\SubCommunitiesController::class, 'update'])->name('subCommunities.update');
//Route::delete('sub-communities/delete/{sub_community?}', [App\Http\Controllers\SubCommunitiesController::class, 'destroy'])->name('subCommunities.destroy');
Route::post('sub-communities/export', [App\Http\Controllers\SubCommunitiesController::class, 'export'])->name('subCommunities.export');

Route::post('sub-communities/bulk-delete', [App\Http\Controllers\SubCommunitiesController::class, 'bulkDelete'])->name('subCommunities.bulkDelete');
Route::post('sub-communities/bulk-restore', [App\Http\Controllers\SubCommunitiesController::class, 'bulkRestore'])->name('subCommunities.bulkRestore');
Route::post('sub-communities/bulk-activate', [App\Http\Controllers\SubCommunitiesController::class, 'bulkActivate'])->name('subCommunities.bulkActivate');
Route::post('sub-communities/bulk-deactivate', [App\Http\Controllers\SubCommunitiesController::class, 'bulkDeactivate'])->name('subCommunities.bulkDeactivate');


// Towers routes
Route::get('towers', [App\Http\Controllers\TowersController::class, 'index'])->name('towers.index');
Route::get('getTowers', [App\Http\Controllers\TowersController::class, 'getTowers'])->name('towers.getTowers');
Route::get('towers/create', [App\Http\Controllers\TowersController::class, 'create'])->name('towers.create');
Route::post('towers/store', [App\Http\Controllers\TowersController::class, 'store'])->name('towers.store');
Route::get('towers/{tower}/edit', [App\Http\Controllers\TowersController::class, 'edit'])->name('towers.edit');
Route::post('towers/update/{tower}', [App\Http\Controllers\TowersController::class, 'update'])->name('towers.update');
//Route::delete('towers/delete/{tower?}', [App\Http\Controllers\TowersController::class, 'destroy'])->name('towers.destroy');
Route::post('towers/export', [App\Http\Controllers\TowersController::class, 'export'])->name('towers.export');

Route::post('towers/bulk-delete', [App\Http\Controllers\TowersController::class, 'bulkDelete'])->name('towers.bulkDelete');
Route::post('towers/bulk-restore', [App\Http\Controllers\TowersController::class, 'bulkRestore'])->name('towers.bulkRestore');
Route::post('towers/bulk-activate', [App\Http\Controllers\TowersController::class, 'bulkActivate'])->name('towers.bulkActivate');
Route::post('towers/bulk-deactivate', [App\Http\Controllers\TowersController::class, 'bulkDeactivate'])->name('towers.bulkDeactivate');


// Tower Units routes
Route::get('tower-units', [App\Http\Controllers\TowerUnitsController::class, 'index'])->name('towerUnits.index');
Route::get('getTowerUnits', [App\Http\Controllers\TowerUnitsController::class, 'getTowerUnits'])->name('towerUnits.getTowerUnits');
Route::get('tower-units/create', [App\Http\Controllers\TowerUnitsController::class, 'create'])->name('towerUnits.create');
Route::post('tower-units/store', [App\Http\Controllers\TowerUnitsController::class, 'store'])->name('towerUnits.store');
Route::get('tower-units/{unit}/edit', [App\Http\Controllers\TowerUnitsController::class, 'edit'])->name('towerUnits.edit');
Route::post('tower-units/update/{unit}', [App\Http\Controllers\TowerUnitsController::class, 'update'])->name('towerUnits.update');
//Route::delete('towers/delete/{tower?}', [App\Http\Controllers\TowerUnitsController::class, 'destroy'])->name('towers.destroy');
Route::post('tower-units/export', [App\Http\Controllers\TowerUnitsController::class, 'export'])->name('towerUnits.export');

Route::post('tower-units/bulk-delete', [App\Http\Controllers\TowerUnitsController::class, 'bulkDelete'])->name('towerUnits.bulkDelete');
Route::post('tower-units/bulk-restore', [App\Http\Controllers\TowerUnitsController::class, 'bulkRestore'])->name('towerUnits.bulkRestore');
Route::post('tower-units/bulk-activate', [App\Http\Controllers\TowerUnitsController::class, 'bulkActivate'])->name('towerUnits.bulkActivate');
Route::post('tower-units/bulk-deactivate', [App\Http\Controllers\TowerUnitsController::class, 'bulkDeactivate'])->name('towerUnits.bulkDeactivate');

Route::get('getCountries', [App\Http\Controllers\CountriesController::class, 'getCountries'])->name('countries.getCountries');
Route::get('getCities', [App\Http\Controllers\CitiesController::class, 'getCities'])->name('cities.getCities');
Route::get('communities/getList', [App\Http\Controllers\CommunitiesController::class, 'getList'])->name('communities.getList');
Route::get('sub-communities/getList', [App\Http\Controllers\SubCommunitiesController::class, 'getList'])->name('subCommunities.getList');
Route::get('towers/getList', [App\Http\Controllers\TowersController::class, 'getList'])->name('towers.getList');


// Property Category routes
Route::get('property-categories', [App\Http\Controllers\PropertyCategoryController::class, 'index'])->name('propertyCats.index');
Route::get('propertyCats', [App\Http\Controllers\PropertyCategoryController::class, 'getPropertyCats'])->name('propertyCats.getPropertyCats');
Route::get('property-categories/create', [App\Http\Controllers\PropertyCategoryController::class, 'create'])->name('propertyCats.create');
Route::post('property-categories/store', [App\Http\Controllers\PropertyCategoryController::class, 'store'])->name('propertyCats.store');
Route::get('property-categories/{property_category}/edit', [App\Http\Controllers\PropertyCategoryController::class, 'edit'])->name('propertyCats.edit');
Route::post('property-categories/update/{property_category}', [App\Http\Controllers\PropertyCategoryController::class, 'update'])->name('propertyCats.update');

Route::post('property-categories/bulk-delete', [App\Http\Controllers\PropertyCategoryController::class, 'bulkDelete'])->name('propertyCats.bulkDelete');
Route::post('property-categories/bulk-restore', [App\Http\Controllers\PropertyCategoryController::class, 'bulkRestore'])->name('propertyCats.bulkRestore');
Route::post('property-categories/bulk-activate', [App\Http\Controllers\PropertyCategoryController::class, 'bulkActivate'])->name('propertyCats.bulkActivate');
Route::post('property-categories/bulk-deactivate', [App\Http\Controllers\PropertyCategoryController::class, 'bulkDeactivate'])->name('propertyCats.bulkDeactivate');


// Property Types routes
Route::get('property-types', [App\Http\Controllers\PropertyTypeController::class, 'index'])->name('propertyTypes.index');
Route::get('propertyTypes', [App\Http\Controllers\PropertyTypeController::class, 'getPropertyTypes'])->name('propertyTypes.getPropertyTypes');
Route::get('property-types/create', [App\Http\Controllers\PropertyTypeController::class, 'create'])->name('propertyTypes.create');
Route::post('property-types/store', [App\Http\Controllers\PropertyTypeController::class, 'store'])->name('propertyTypes.store');
Route::get('property-types/{property_type}/edit', [App\Http\Controllers\PropertyTypeController::class, 'edit'])->name('propertyTypes.edit');
Route::post('property-types/update/{property_type}', [App\Http\Controllers\PropertyTypeController::class, 'update'])->name('propertyTypes.update');

Route::post('property-types/bulk-delete', [App\Http\Controllers\PropertyTypeController::class, 'bulkDelete'])->name('propertyTypes.bulkDelete');
Route::post('property-types/bulk-restore', [App\Http\Controllers\PropertyTypeController::class, 'bulkRestore'])->name('propertyTypes.bulkRestore');
Route::post('property-types/bulk-activate', [App\Http\Controllers\PropertyTypeController::class, 'bulkActivate'])->name('propertyTypes.bulkActivate');
Route::post('property-types/bulk-deactivate', [App\Http\Controllers\PropertyTypeController::class, 'bulkDeactivate'])->name('propertyTypes.bulkDeactivate');

Route::get('property-types/getList', [App\Http\Controllers\PropertyTypeController::class, 'getList'])->name('propertyTypes.getList');


// Project Status routes
Route::get('project-status', [App\Http\Controllers\ProjectStatusController::class, 'index'])->name('projectStatus.index');
Route::get('projectStatus', [App\Http\Controllers\ProjectStatusController::class, 'getProjectStatus'])->name('projectStatus.getProjectStatus');
Route::get('project-status/create', [App\Http\Controllers\ProjectStatusController::class, 'create'])->name('projectStatus.create');
Route::post('project-status/store', [App\Http\Controllers\ProjectStatusController::class, 'store'])->name('projectStatus.store');
Route::get('project-status/{project_status}/edit', [App\Http\Controllers\ProjectStatusController::class, 'edit'])->name('projectStatus.edit');
Route::post('project-status/update/{project_status}', [App\Http\Controllers\ProjectStatusController::class, 'update'])->name('projectStatus.update');

Route::post('project-status/bulk-delete', [App\Http\Controllers\ProjectStatusController::class, 'bulkDelete'])->name('projectStatus.bulkDelete');
Route::post('project-status/bulk-restore', [App\Http\Controllers\ProjectStatusController::class, 'bulkRestore'])->name('projectStatus.bulkRestore');
Route::post('project-status/bulk-activate', [App\Http\Controllers\ProjectStatusController::class, 'bulkActivate'])->name('projectStatus.bulkActivate');
Route::post('project-status/bulk-deactivate', [App\Http\Controllers\ProjectStatusController::class, 'bulkDeactivate'])->name('projectStatus.bulkDeactivate');

// Occupencies routes
Route::get('occupancies', [App\Http\Controllers\ListingOccupanciesController::class, 'index'])->name('occupancies.index');
Route::get('getOccupancies', [App\Http\Controllers\ListingOccupanciesController::class, 'getOccupancies'])->name('occupancies.getOccupancies');
Route::get('occupancies/create', [App\Http\Controllers\ListingOccupanciesController::class, 'create'])->name('occupancies.create');
Route::post('occupancies/store', [App\Http\Controllers\ListingOccupanciesController::class, 'store'])->name('occupancies.store');
Route::get('occupancies/{occupancy}/edit', [App\Http\Controllers\ListingOccupanciesController::class, 'edit'])->name('occupancies.edit');
Route::post('occupancies/update/{occupancy}', [App\Http\Controllers\ListingOccupanciesController::class, 'update'])->name('occupancies.update');

Route::post('occupancies/bulk-delete', [App\Http\Controllers\ListingOccupanciesController::class, 'bulkDelete'])->name('occupancies.bulkDelete');
Route::post('occupancies/bulk-restore', [App\Http\Controllers\ListingOccupanciesController::class, 'bulkRestore'])->name('occupancies.bulkRestore');
Route::post('occupancies/bulk-activate', [App\Http\Controllers\ListingOccupanciesController::class, 'bulkActivate'])->name('occupancies.bulkActivate');
Route::post('occupancies/bulk-deactivate', [App\Http\Controllers\ListingOccupanciesController::class, 'bulkDeactivate'])->name('occupancies.bulkDeactivate');

// amenities routes
Route::get('amenities', [App\Http\Controllers\AmenitiesController::class, 'index'])->name('amenities.index');
Route::get('getAmenities', [App\Http\Controllers\AmenitiesController::class, 'getAmenities'])->name('amenities.getAmenities');
Route::get('amenities/create', [App\Http\Controllers\AmenitiesController::class, 'create'])->name('amenities.create');
Route::post('amenities/store', [App\Http\Controllers\AmenitiesController::class, 'store'])->name('amenities.store');
Route::get('amenities/{amenity}/edit', [App\Http\Controllers\AmenitiesController::class, 'edit'])->name('amenities.edit');
Route::post('amenities/update/{amenity}', [App\Http\Controllers\AmenitiesController::class, 'update'])->name('amenities.update');
Route::get('amenities/getList', [App\Http\Controllers\AmenitiesController::class, 'getList'])->name('amenities.getList');

Route::post('amenities/bulk-delete', [App\Http\Controllers\AmenitiesController::class, 'bulkDelete'])->name('amenities.bulkDelete');
Route::post('amenities/bulk-restore', [App\Http\Controllers\AmenitiesController::class, 'bulkRestore'])->name('amenities.bulkRestore');
Route::post('amenities/bulk-activate', [App\Http\Controllers\AmenitiesController::class, 'bulkActivate'])->name('amenities.bulkActivate');
Route::post('amenities/bulk-deactivate', [App\Http\Controllers\AmenitiesController::class, 'bulkDeactivate'])->name('amenities.bulkDeactivate');


// developers routes
Route::get('developers', [App\Http\Controllers\DevelopersController::class, 'index'])->name('developers.index');
Route::get('getDevelopers', [App\Http\Controllers\DevelopersController::class, 'getDevelopers'])->name('developers.getDevelopers');
Route::get('developers/create', [App\Http\Controllers\DevelopersController::class, 'create'])->name('developers.create');
Route::post('developers/store', [App\Http\Controllers\DevelopersController::class, 'store'])->name('developers.store');
Route::get('developers/{developer}/edit', [App\Http\Controllers\DevelopersController::class, 'edit'])->name('developers.edit');
Route::post('developers/update/{developer}', [App\Http\Controllers\DevelopersController::class, 'update'])->name('developers.update');

Route::post('developers/bulk-delete', [App\Http\Controllers\DevelopersController::class, 'bulkDelete'])->name('developers.bulkDelete');
Route::post('developers/bulk-restore', [App\Http\Controllers\DevelopersController::class, 'bulkRestore'])->name('developers.bulkRestore');
Route::post('developers/bulk-activate', [App\Http\Controllers\DevelopersController::class, 'bulkActivate'])->name('developers.bulkActivate');
Route::post('developers/bulk-deactivate', [App\Http\Controllers\DevelopersController::class, 'bulkDeactivate'])->name('developers.bulkDeactivate');


// sources routes
Route::get('sources', [App\Http\Controllers\SourcesController::class, 'index'])->name('sources.index');
Route::get('getSources', [App\Http\Controllers\SourcesController::class, 'getSources'])->name('sources.getSources');
Route::get('sources/create', [App\Http\Controllers\SourcesController::class, 'create'])->name('sources.create');
Route::post('sources/store', [App\Http\Controllers\SourcesController::class, 'store'])->name('sources.store');
Route::get('sources/{source}/edit', [App\Http\Controllers\SourcesController::class, 'edit'])->name('sources.edit');
Route::post('sources/update/{source}', [App\Http\Controllers\SourcesController::class, 'update'])->name('sources.update');

Route::post('sources/bulk-delete', [App\Http\Controllers\SourcesController::class, 'bulkDelete'])->name('sources.bulkDelete');
Route::post('sources/bulk-restore', [App\Http\Controllers\SourcesController::class, 'bulkRestore'])->name('sources.bulkRestore');
Route::post('sources/bulk-activate', [App\Http\Controllers\SourcesController::class, 'bulkActivate'])->name('sources.bulkActivate');
Route::post('sources/bulk-deactivate', [App\Http\Controllers\SourcesController::class, 'bulkDeactivate'])->name('sources.bulkDeactivate');

// sub sources routes
Route::get('sub-sources', [App\Http\Controllers\SubSourcesController::class, 'index'])->name('subSources.index');
Route::get('getSubSources', [App\Http\Controllers\SubSourcesController::class, 'getSubSources'])->name('subSources.getSubSources');
Route::get('sub-sources/create', [App\Http\Controllers\SubSourcesController::class, 'create'])->name('subSources.create');
Route::post('sub-sources/store', [App\Http\Controllers\SubSourcesController::class, 'store'])->name('subSources.store');
Route::get('sub-sources/{sub_source}/edit', [App\Http\Controllers\SubSourcesController::class, 'edit'])->name('subSources.edit');
Route::post('sub-sources/update/{sub_source}', [App\Http\Controllers\SubSourcesController::class, 'update'])->name('subSources.update');
Route::get('sub-sources/getList', [App\Http\Controllers\SubSourcesController::class, 'getList'])->name('subSources.getList');

Route::post('sub-sources/bulk-delete', [App\Http\Controllers\SubSourcesController::class, 'bulkDelete'])->name('subSources.bulkDelete');
Route::post('sub-sources/bulk-restore', [App\Http\Controllers\SubSourcesController::class, 'bulkRestore'])->name('subSources.bulkRestore');
Route::post('sub-sources/bulk-activate', [App\Http\Controllers\SubSourcesController::class, 'bulkActivate'])->name('subSources.bulkActivate');
Route::post('sub-sources/bulk-deactivate', [App\Http\Controllers\SubSourcesController::class, 'bulkDeactivate'])->name('subSources.bulkDeactivate');

// portals routes
Route::get('portals', [App\Http\Controllers\ListingPortalsController::class, 'index'])->name('portals.index');
Route::get('getPortals', [App\Http\Controllers\ListingPortalsController::class, 'getPortals'])->name('portals.getPortals');
Route::get('portals/create', [App\Http\Controllers\ListingPortalsController::class, 'create'])->name('portals.create');
Route::post('portals/store', [App\Http\Controllers\ListingPortalsController::class, 'store'])->name('portals.store');
Route::get('portals/{portal}/edit', [App\Http\Controllers\ListingPortalsController::class, 'edit'])->name('portals.edit');
Route::post('portals/update/{portal}', [App\Http\Controllers\ListingPortalsController::class, 'update'])->name('portals.update');
Route::get('portals/getList', [App\Http\Controllers\ListingPortalsController::class, 'getList'])->name('portals.getList');

Route::post('portals/bulk-delete', [App\Http\Controllers\ListingPortalsController::class, 'bulkDelete'])->name('portals.bulkDelete');
Route::post('portals/bulk-restore', [App\Http\Controllers\ListingPortalsController::class, 'bulkRestore'])->name('portals.bulkRestore');
Route::post('portals/bulk-activate', [App\Http\Controllers\ListingPortalsController::class, 'bulkActivate'])->name('portals.bulkActivate');
Route::post('portals/bulk-deactivate', [App\Http\Controllers\ListingPortalsController::class, 'bulkDeactivate'])->name('portals.bulkDeactivate');

// owners routes
Route::get('owners', [App\Http\Controllers\OwnersController::class, 'index'])->name('owners.index');
Route::post('getOwners', [App\Http\Controllers\OwnersController::class, 'getOwners'])->name('owners.getOwners');
Route::get('owners/create', [App\Http\Controllers\OwnersController::class, 'create'])->name('owners.create');
Route::post('owners/store', [App\Http\Controllers\OwnersController::class, 'store'])->name('owners.store');
Route::post('owners/storeAjax', [App\Http\Controllers\OwnersController::class, 'storeAjax'])->name('owners.storeAjax');

Route::get('owners/{owner}/edit', [App\Http\Controllers\OwnersController::class, 'edit'])->name('owners.edit');
Route::post('owners/update/{owner}', [App\Http\Controllers\OwnersController::class, 'update'])->name('owners.update');
Route::post('owners/export', [App\Http\Controllers\OwnersController::class, 'export'])->name('owners.export');
Route::get('owners/getList', [App\Http\Controllers\OwnersController::class, 'getList'])->name('owners.getList');
Route::post('owners/searchrefno', [App\Http\Controllers\OwnersController::class, 'searchRefno'])->name('owners.searchRefno');

Route::post('owners/bulk-delete', [App\Http\Controllers\OwnersController::class, 'bulkDelete'])->name('owners.bulkDelete');
Route::post('owners/bulk-restore', [App\Http\Controllers\OwnersController::class, 'bulkRestore'])->name('owners.bulkRestore');
Route::post('owners/bulk-activate', [App\Http\Controllers\OwnersController::class, 'bulkActivate'])->name('owners.bulkActivate');
Route::post('owners/bulk-deactivate', [App\Http\Controllers\OwnersController::class, 'bulkDeactivate'])->name('owners.bulkDeactivate');


// contacts routes
Route::get('contacts', [App\Http\Controllers\ContactsController::class, 'index'])->name('contacts.index');
Route::post('getContacts', [App\Http\Controllers\ContactsController::class, 'getContacts'])->name('contacts.getContacts');
Route::get('contacts/create', [App\Http\Controllers\ContactsController::class, 'create'])->name('contacts.create');
Route::post('contacts/store', [App\Http\Controllers\ContactsController::class, 'store'])->name('contacts.store');
Route::post('contacts/storeAjax', [App\Http\Controllers\ContactsController::class, 'storeAjax'])->name('contacts.storeAjax');

Route::get('contacts/{contact}/edit', [App\Http\Controllers\ContactsController::class, 'edit'])->name('contacts.edit');
Route::post('contacts/update/{contact}', [App\Http\Controllers\ContactsController::class, 'update'])->name('contacts.update');
Route::post('contacts/export', [App\Http\Controllers\ContactsController::class, 'export'])->name('contacts.export');
Route::get('contacts/getList', [App\Http\Controllers\ContactsController::class, 'getList'])->name('contacts.getList');
Route::post('contacts/searchrefno', [App\Http\Controllers\ContactsController::class, 'searchRefno'])->name('contacts.searchRefno');

Route::post('contacts/bulk-delete', [App\Http\Controllers\ContactsController::class, 'bulkDelete'])->name('contacts.bulkDelete');
Route::post('contacts/bulk-restore', [App\Http\Controllers\ContactsController::class, 'bulkRestore'])->name('contacts.bulkRestore');
Route::post('contacts/bulk-activate', [App\Http\Controllers\ContactsController::class, 'bulkActivate'])->name('contacts.bulkActivate');
Route::post('contacts/bulk-deactivate', [App\Http\Controllers\ContactsController::class, 'bulkDeactivate'])->name('contacts.bulkDeactivate');


// CRM Statuses routes
Route::get('crm-statuses', [App\Http\Controllers\StatusesController::class, 'index'])->name('crmStatuses.index');
Route::get('getCrmStatuses', [App\Http\Controllers\StatusesController::class, 'getCrmStatuses'])->name('crmStatuses.getCrmStatuses');
Route::get('crm-statuses/getLeadStatuses', [App\Http\Controllers\StatusesController::class, 'getLeadStatuses'])->name('crmStatuses.getLeadStatuses');
Route::get('crm-statuses/create', [App\Http\Controllers\StatusesController::class, 'create'])->name('crmStatuses.create');
Route::post('crm-statuses/store', [App\Http\Controllers\StatusesController::class, 'store'])->name('crmStatuses.store');
Route::get('crm-statuses/{status}/edit', [App\Http\Controllers\StatusesController::class, 'edit'])->name('crmStatuses.edit');
Route::post('crm-statuses/update/{status}', [App\Http\Controllers\StatusesController::class, 'update'])->name('crmStatuses.update');

Route::post('crm-statuses/bulk-delete', [App\Http\Controllers\StatusesController::class, 'bulkDelete'])->name('crmStatuses.bulkDelete');
Route::post('crm-statuses/bulk-restore', [App\Http\Controllers\StatusesController::class, 'bulkRestore'])->name('crmStatuses.bulkRestore');
Route::post('crm-statuses/bulk-activate', [App\Http\Controllers\StatusesController::class, 'bulkActivate'])->name('crmStatuses.bulkActivate');
Route::post('crm-statuses/bulk-deactivate', [App\Http\Controllers\StatusesController::class, 'bulkDeactivate'])->name('crmStatuses.bulkDeactivate');

// CRM Sub Statuses routes
Route::get('crm-sub-statuses', [App\Http\Controllers\SubStatusesController::class, 'index'])->name('crmSubStatuses.index');
Route::get('getCrmSubStatuses', [App\Http\Controllers\SubStatusesController::class, 'getCrmSubStatuses'])->name('crmSubStatuses.getCrmSubStatuses');
Route::get('crm-sub-statuses/create', [App\Http\Controllers\SubStatusesController::class, 'create'])->name('crmSubStatuses.create');
Route::post('crm-sub-statuses/store', [App\Http\Controllers\SubStatusesController::class, 'store'])->name('crmSubStatuses.store');
Route::get('crm-sub-statuses/{sub_status}/edit', [App\Http\Controllers\SubStatusesController::class, 'edit'])->name('crmSubStatuses.edit');
Route::post('crm-sub-statuses/update/{sub_status}', [App\Http\Controllers\SubStatusesController::class, 'update'])->name('crmSubStatuses.update');

Route::get('crm-sub-statuses/getList', [App\Http\Controllers\SubStatusesController::class, 'getList'])->name('crmSubStatuses.getList');

Route::post('crm-sub-statuses/bulk-delete', [App\Http\Controllers\SubStatusesController::class, 'bulkDelete'])->name('crmSubStatuses.bulkDelete');
Route::post('crm-sub-statuses/bulk-restore', [App\Http\Controllers\SubStatusesController::class, 'bulkRestore'])->name('crmSubStatuses.bulkRestore');
Route::post('crm-sub-statuses/bulk-activate', [App\Http\Controllers\SubStatusesController::class, 'bulkActivate'])->name('crmSubStatuses.bulkActivate');
Route::post('crm-sub-statuses/bulk-deactivate', [App\Http\Controllers\SubStatusesController::class, 'bulkDeactivate'])->name('crmSubStatuses.bulkDeactivate');


// CRM Listings routes
Route::get('listings', [App\Http\Controllers\ListingsController::class, 'index'])->name('listings.index');
Route::post('getListings', [App\Http\Controllers\ListingsController::class, 'getListings'])->name('listings.getListings');

Route::get('listings/getList', [App\Http\Controllers\ListingsController::class, 'getList'])->name('listings.getList');

Route::get('listings/create', [App\Http\Controllers\ListingsController::class, 'create'])->name('listings.create');
Route::post('listings/store', [App\Http\Controllers\ListingsController::class, 'store'])->name('listings.store');
Route::get('listings/{listing}/edit', [App\Http\Controllers\ListingsController::class, 'edit'])->name('listings.edit');
Route::post('listings/update/{listing}', [App\Http\Controllers\ListingsController::class, 'update'])->name('listings.update');
Route::post('listings/export', [App\Http\Controllers\ListingsController::class, 'export'])->name('listings.export');

Route::get('listings/import', [App\Http\Controllers\ListingsController::class, 'import'])->name('listings.import');
Route::post('listings/import', [App\Http\Controllers\ListingsController::class, 'importPost'])->name('listings.importPost');


Route::post('listings/portalCounts', [App\Http\Controllers\ListingsController::class, 'getPortalCounts'])->name('listings.portalCounts');
Route::post('listings/searchrefno', [App\Http\Controllers\ListingsController::class, 'searchRefno'])->name('listings.searchRefno');

Route::post('listings/bulk-delete', [App\Http\Controllers\ListingsController::class, 'bulkDelete'])->name('listings.bulkDelete');
Route::post('listings/bulk-restore', [App\Http\Controllers\ListingsController::class, 'bulkRestore'])->name('listings.bulkRestore');
Route::post('listings/bulk-status-change', [App\Http\Controllers\ListingsController::class, 'bulkStatusChange'])->name('listings.bulkStatusChange');
Route::post('listings/upload-images', [App\Http\Controllers\ListingsController::class, 'uploadImages'])->name('listings.uploadImages');
Route::post('listings/bulk-duplicate', [App\Http\Controllers\ListingsController::class, 'bulkDuplicate'])->name('listings.bulkDuplicate');
Route::post('listings/bulk-sendemail', [App\Http\Controllers\ListingsController::class, 'bulkSendEmail'])->name('listings.bulkSendEmail');
Route::post('listings/bulk-assign', [App\Http\Controllers\ListingsController::class, 'bulkAssign'])->name('listings.bulkAssign');


// CRM Leads routes
Route::get('leads', [App\Http\Controllers\LeadsController::class, 'index'])->name('leads.index');
Route::post('getLeads', [App\Http\Controllers\LeadsController::class, 'getLeads'])->name('leads.getLeads');

Route::get('leads/manual', [App\Http\Controllers\LeadsController::class, 'manual'])->name('leads.manual');
Route::post('leads/manual', [App\Http\Controllers\LeadsController::class, 'manualPost'])->name('leads.manualPost');

Route::post('acceptAjax', [App\Http\Controllers\LeadsController::class, 'acceptAjax'])->name('leads.acceptAjax');
Route::get('leads/create', [App\Http\Controllers\LeadsController::class, 'create'])->name('leads.create');
Route::post('leads/store', [App\Http\Controllers\LeadsController::class, 'store'])->name('leads.store');
Route::get('leads/{lead}/edit', [App\Http\Controllers\LeadsController::class, 'edit'])->name('leads.edit');
Route::post('leads/update/{lead}', [App\Http\Controllers\LeadsController::class, 'update'])->name('leads.update');
Route::post('leads/export', [App\Http\Controllers\LeadsController::class, 'export'])->name('leads.export');

Route::get('leads/import', [App\Http\Controllers\LeadsController::class, 'import'])->name('leads.import');
Route::post('leads/import', [App\Http\Controllers\LeadsController::class, 'importPost'])->name('leads.importPost');
// Route::get('leads/import', [App\Http\Controllers\LeadsController::class, 'import'])->name('leads.import');
// Route::post('leads/import', [App\Http\Controllers\LeadsController::class, 'importPost'])->name('leads.importPost');

Route::post('leads/searchrefno', [App\Http\Controllers\LeadsController::class, 'searchRefno'])->name('leads.searchRefno');

Route::post('leads/bulk-delete', [App\Http\Controllers\LeadsController::class, 'bulkDelete'])->name('leads.bulkDelete');
Route::post('leads/bulk-restore', [App\Http\Controllers\LeadsController::class, 'bulkRestore'])->name('leads.bulkRestore');
Route::post('leads/bulk-status-change', [App\Http\Controllers\LeadsController::class, 'bulkStatusChange'])->name('leads.bulkStatusChange');
Route::post('leads/bulk-assign', [App\Http\Controllers\LeadsController::class, 'bulkAssign'])->name('leads.bulkAssign');


// settings routes
Route::get('settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
Route::post('settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');


// CRM Teams routes
Route::get('teams', [App\Http\Controllers\TeamsController::class, 'index'])->name('teams.index');
Route::post('getTeams', [App\Http\Controllers\TeamsController::class, 'getTeams'])->name('teams.getTeams');
Route::get('teams/create', [App\Http\Controllers\TeamsController::class, 'create'])->name('teams.create');
Route::post('teams/store', [App\Http\Controllers\TeamsController::class, 'store'])->name('teams.store');
Route::get('teams/{team}/edit', [App\Http\Controllers\TeamsController::class, 'edit'])->name('teams.edit');
Route::post('teams/update/{team}', [App\Http\Controllers\TeamsController::class, 'update'])->name('teams.update');
//Route::post('listings/export', [App\Http\Controllers\ListingsController::class, 'export'])->name('listings.export');

Route::post('teams/bulk-delete', [App\Http\Controllers\TeamsController::class, 'bulkDelete'])->name('teams.bulkDelete');
Route::post('teams/bulk-restore', [App\Http\Controllers\TeamsController::class, 'bulkRestore'])->name('teams.bulkRestore');
Route::post('teams/bulk-activate', [App\Http\Controllers\TeamsController::class, 'bulkActivate'])->name('teams.bulkActivate');
Route::post('teams/bulk-deactivate', [App\Http\Controllers\TeamsController::class, 'bulkDeactivate'])->name('teams.bulkDeactivate');

// CRM Campaigns routes
Route::get('campaigns', [App\Http\Controllers\CampaignsController::class, 'index'])->name('campaigns.index');
Route::post('getCampaigns', [App\Http\Controllers\CampaignsController::class, 'getCampaigns'])->name('campaigns.getCampaigns');
Route::get('campaigns/create', [App\Http\Controllers\CampaignsController::class, 'create'])->name('campaigns.create');
Route::post('campaigns/store', [App\Http\Controllers\CampaignsController::class, 'store'])->name('campaigns.store');
Route::get('campaigns/{campaign}/edit', [App\Http\Controllers\CampaignsController::class, 'edit'])->name('campaigns.edit');
Route::post('campaigns/update/{campaign}', [App\Http\Controllers\CampaignsController::class, 'update'])->name('campaigns.update');

Route::post('campaigns/bulk-delete', [App\Http\Controllers\CampaignsController::class, 'bulkDelete'])->name('campaigns.bulkDelete');
Route::post('campaigns/bulk-restore', [App\Http\Controllers\CampaignsController::class, 'bulkRestore'])->name('campaigns.bulkRestore');
Route::post('campaigns/bulk-activate', [App\Http\Controllers\CampaignsController::class, 'bulkActivate'])->name('campaigns.bulkActivate');
Route::post('campaigns/bulk-deactivate', [App\Http\Controllers\CampaignsController::class, 'bulkDeactivate'])->name('campaigns.bulkDeactivate');

//stats routes
Route::get('stats/calls', [App\Http\Controllers\StatsController::class, 'calls'])->name('stats.calls');
Route::get('stats/getCalls', [App\Http\Controllers\StatsController::class, 'getCalls'])->name('stats.getCalls');

// calls routes
Route::get('calls', [App\Http\Controllers\CallsController::class, 'index'])->name('calls.index');
Route::post('getCalls', [App\Http\Controllers\CallsController::class, 'getCalls'])->name('calls.getCalls');

Route::get('email', [App\Http\Controllers\HomeController::class, 'email'])->name('email');
Route::get('email_temp', [App\Http\Controllers\HomeController::class, 'email_temp'])->name('email_temp');

Route::get('searchModule', [App\Http\Controllers\HomeController::class, 'searchModule'])->name('searchModule');

Route::get('listing-email', [App\Http\Controllers\HomeController::class, 'listingEmail'])->name('listingEmail');

Route::get('dashboardStats', [App\Http\Controllers\HomeController::class, 'dashboardStats'])->name('dashboardStats');

Route::get('getCalendarData', [App\Http\Controllers\HomeController::class, 'getCalendarData'])->name('getCalendarData');

Route::get('getLeadCounts', [App\Http\Controllers\HomeController::class, 'getLeadCounts'])->name('getLeadCounts');
Route::get('getGoogleToken', [App\Http\Controllers\HomeController::class, 'getGoogleToken'])->name('getGoogleToken');

//Route::resource('roles', 'RoleController');
///Route::resource('permissions', [App\Http\Controllers\PermissionController::class]);
