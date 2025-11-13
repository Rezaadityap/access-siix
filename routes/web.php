<?php

use App\Http\Controllers\IT\UsersController;
use App\Http\Controllers\KittingController;
use App\Http\Controllers\OpKittingController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ReportsKittingController;
use App\Http\Controllers\WiDocumentController;
use App\Models\Employee;
use App\Models\ExternalEmployee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // WI Document
    Route::get('/wi-document/{path?}', [WiDocumentController::class, 'index'])
        ->where('path', '.*')
        ->name('wi-document.index');
    Route::get('/wi-documents/view/{path}', [WiDocumentController::class, 'view'])
        ->where('path', '.*')
        ->name('wi-document.view');

    // Kitting
    Route::get('/kitting/prod1', [OpKittingController::class, 'kitting_prod1'])->name('kitting.prod1');
    Route::get('/record-history', [OpKittingController::class, 'history'])->name('op-kitting.history');
    Route::post('/op-kitting/history/replace/{id}', [OpKittingController::class, 'replace'])->name('op-kitting.history.replace');
    Route::post('/record_material/store', [OpKittingController::class, 'store'])->name('record-material.store');
    Route::post('/record-material/upload', [OpKittingController::class, 'upload'])->name('record-material.upload');
    Route::get('/record_material/by-po/{po_numbers}', [OpKittingController::class, 'getRecord'])
        ->name('record-material.by-po');
    Route::get('/record_material/getSearchRecord', [OpKittingController::class, 'getRecordSearch'])->name('record-material.getSearch');
    Route::post('/record_material/check-material', [OpKittingController::class, 'checkMaterial'])->name('record-material.checkMaterialRecord');
    Route::post('/record_material/store-wh', [OpKittingController::class, 'saveWhMaterial'])->name('record-material.saveWh');
    Route::post('/record_material/store-smd', [OpKittingController::class, 'saveRackSmd'])->name('record-material.saveSmd');
    Route::post('/record_material/store-sto', [OpKittingController::class, 'saveRackSto'])->name('record-material.saveSto');
    Route::post('/record_material/store-mar', [OpKittingController::class, 'saveAfter'])->name('record-material.saveMar');
    Route::post('/record_material/store-mismatch', [OpKittingController::class, 'saveMismatch'])->name('record-material.saveMismatch');
    Route::post('/record_material/check-batch', [OpKittingController::class, 'checkBatch'])->name('record-material.check-batch');
    Route::get('/record-material/history', [OpKittingController::class, 'getBatchHistory']);
    Route::post('/record_material/delete-po', [OpKittingController::class, 'deletePO']);
    Route::put('/op-kitting/update-info', [OpKittingController::class, 'updateInfo'])->name('op-kitting.update-info');
    Route::match(['GET', 'POST'], '/reports/kitting', [ReportsKittingController::class, 'index'])->name('reports.kitting');
    Route::post('/reports-kitting/material_lines', [ReportsKittingController::class, 'materials'])->name('reports.kitting.materials');
    Route::post('/reports-kitting/export', [ReportsKittingController::class, 'export'])
        ->name('reports.kitting.export');
    Route::get('/reports/batches', [ReportsKittingController::class, 'batches'])->name('reports.kitting.batches');
    Route::get('/reports/batches/export', [ReportsKittingController::class, 'exportBatches'])
        ->name('reports.batches.export');


    // ROUTE FOR IT
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
});

Route::get('/sync-employees', function () {
    $employees = ExternalEmployee::all();

    foreach ($employees as $emp) {
        if (in_array($emp->employee_status, [1, 2, 3])) {
            $employee = Employee::updateOrCreate(
                ['nik' => $emp->user_login],
                [
                    'name' => $emp->display_name,
                    'section' => $emp->Section,
                    'department' => $emp->Departement,
                    'photo' => $emp->Photo,
                    'status' => $emp->employee_status
                ]
            );

            $employee_id = $employee->id;

            User::updateOrCreate(
                ['nik' => $emp->user_login],
                [
                    'nik' => $emp->user_login,
                    'name' => $emp->display_name,
                    'email' => $emp->email ?: "{$emp->user_login}@siix-global.com",
                    'password' => Hash::make($emp->user_login),
                    'employee_id' => $employee_id
                ]
            );
        }
    }
    return 'Employees synced successfully!';
});

Route::get('/upload', [PriceController::class, 'index']);
Route::post('/upload-file', [PriceController::class, 'upload'])->name('upload-price');
