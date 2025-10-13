<?php

use App\Http\Controllers\OpKittingController;
use App\Http\Controllers\WiDocumentController;
use App\Models\Employee;
use App\Models\ExternalEmployee;
use Illuminate\Support\Facades\Auth;
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

    Route::get('/wi-document/get-pic', [WiDocumentController::class, 'getPic'])->name('datatable.index');
    Route::get('/wi-document/get-absent', [WiDocumentController::class, 'getAbsent'])->name('datatable.absent');

    Route::get('/op-kitting', [OpKittingController::class, 'index'])->name('op-kitting.index');

    Route::get('/data-pic', [WiDocumentController::class, 'getDataTable'])->name('employeesPic.index');
    Route::get('/data-absent', [WiDocumentController::class, 'getDataAbsent'])->name('employeesAbsent.index');

    Route::get('/sync-employees', function () {
        $employees = ExternalEmployee::all();

        foreach ($employees as $emp) {
            if (in_array($emp->employee_status, [1, 2, 3])) {
                Employee::updateOrCreate(
                    ['nik' => $emp->user_login],
                    [
                        'name' => $emp->display_name,
                        'section' => $emp->Section,
                        'department' => $emp->Departement,
                        'photo' => $emp->Photo,
                        'status' => $emp->employee_status
                    ]
                );
            }
        }
        return 'Employees synced successfully!';
    });
});
