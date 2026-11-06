<?php

use App\Http\Controllers\PlanMasterController;
use App\Http\Controllers\CellInfoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QcoController;
use App\Http\Controllers\qcoDashboardController;
use App\Http\Controllers\QcoListController;
use App\Http\Controllers\WhatsappController;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Route;

Route::get('/test-delete-wa-logs', function (WhatsappService $whatsappService) {
    try {
        $whatsappService->deleteDailyWhatsAppLogs();
        return response()->json([
            'success' => true,
            'message' => 'Proses hapus pesan WhatsApp dan log berhasil dijalankan!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});
Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    return view('welcome');
})->middleware(['auth', 'verified'])->name('main');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/cell-info', [CellInfoController::class, 'index'])->name('cell-info.index');
    Route::get('/cell-info/main', [CellInfoController::class, 'main'])->name('cell-info.main');
    Route::post('/cell-info', [CellInfoController::class, 'store'])->name('cell-info.store');
    Route::get('/cell-info/{id}/edit', [CellInfoController::class, 'edit'])->name('cell-info.edit');
    Route::post('/cell-info/{id}', [CellInfoController::class, 'update'])->name('cell-info.update'); // Menggunakan POST + _method PUT
    Route::delete('/cell-info/{id}', [CellInfoController::class, 'destroy'])->name('cell-info.destroy');

    Route::get('/QCO', [qcoDashboardController::class, 'index']);
    Route::post('/QCO/dataModal', [qcoDashboardController::class, 'dataModal'])->name('qco.dataModal');
    Route::post('/QCO/update-actual-co', [qcoDashboardController::class, 'update_act_co'])->name('QCO.update-actual-co');
    Route::post('/QCO/cancelQCO', [qcoDashboardController::class, 'cancelQCO'])->name('QCO.cancelQCO');
    Route::post('/QCO/updateStatusCancel', [qcoDashboardController::class, 'updateStatusCancel'])->name('QCO.updateStatusCancel');
    Route::post('/QCO/update', [qcoDashboardController::class, 'update'])->name('QCO.update');
    Route::post('/trigger-reminder', [qcoDashboardController::class, 'sendReminder']);

    Route::get('/QCO/Form', [QcoController::class, 'form']);
    Route::post('/QCO/Form/Store', [QcoController::class, 'store'])->name('qco.form.store');
    Route::post('/QCO/Form/Preparation/Store', [QcoController::class, 'store_preparation'])->name('QCO.preparation.store');
    Route::delete('/QCO/Form/reset/{id_qco}/{id_list}', [QcoController::class, 'destroy'])->name('QCO.destroy');

    Route::get('/QCO/List', [QcoListController::class, 'index']);
    Route::POST('/QCO/List', [QcoListController::class, 'store'])->name('QCO.import_list');
    Route::get('/QCO/List/main', [QcoListController::class, 'main'])->name('QCO.list.main');
    Route::get('/QCO/{id}/edit', [QcoListController::class, 'edit'])->name('QCO.edit');
    Route::put('/QCO/{id}', [QcoListController::class, 'update'])->name('QCO.update'); // Menggunakan POST + _method PUT
    Route::delete('/QCO/{id}', [QcoListController::class, 'destroy'])->name('QCO.destroy');

    Route::get('/plan-master', [PlanMasterController::class, 'index'])->name('plan_master.index');
    Route::post('/plan-master/import', [PlanMasterController::class, 'import'])->name('plan_master.import');

    Route::get('/whatsapp/management', [WhatsappController::class, 'index'])->name('whatsapp.index');
    Route::get('/whatsapp/sync', [WhatsappController::class, 'syncGroups'])->name('whatsapp.sync');
});






require __DIR__.'/auth.php';
