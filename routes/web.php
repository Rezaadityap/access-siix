<?php

use App\Http\Controllers\OpKittingController;
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

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/wi-document/{path?}', [WiDocumentController::class, 'index'])
        ->where('path', '.*')
        ->name('wi-document.index');

    Route::get('/wi-documents/view/{path}', [WiDocumentController::class, 'view'])
        ->where('path', '.*')
        ->name('wi-document.view');

    Route::get('/op-kitting', [OpKittingController::class, 'index'])->name('op-kitting.index');

    Route::post('/record_material/store', [OpKittingController::class, 'store'])->name('record-material.store');
    Route::post('/record-material/upload', [OpKittingController::class, 'upload'])->name('record-material.upload');
    Route::get('/record_material/by-po/{po_numbers}', [OpKittingController::class, 'getRecord'])
        ->name('record-material.by-po');
    Route::get('/record_material/getSearchRecord', [OpKittingController::class, 'getRecordSearch'])->name('record-material.getSearch');
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
