<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PDO;
use Yajra\DataTables\Facades\DataTables;

class WiDocumentController extends Controller
{
    public function index($path = null)
    {
        $department = Employee::join('users', 'employees.nik', '=', 'users.nik')
            ->where('users.id', Auth::id())
            ->value('employees.department');

        $department = trim($department);

        $employees = Employee::whereRaw('TRIM(department) = ?', [$department])->get();

        $disk = Storage::disk('work_instruction');
        $path = $path ?? '';

        $directories = $disk->directories($path);
        $files = $disk->files($path);

        $items = [];

        foreach ($directories as $dir) {
            $relativePath = trim($dir, '/');
            $items[] = [
                'name' => basename($dir),
                'type' => 'folder',
                'path' => $relativePath,
                'size' => '-',
                'modified' => date('Y-m-d H:i:s', $disk->lastModified($dir)),
            ];
        }

        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($extension !== 'pdf') continue;
            $relativePath = trim($file, '/');
            $items[] = [
                'name' => basename($file),
                'type' => 'file',
                'path' => $relativePath,
                'size' => number_format($disk->size($file) / 1024, 2) . ' KB',
                'modified' => date('Y-m-d H:i:s', $disk->lastModified($file)),
            ];
        }
        $breadcrumb = [];
        if (!empty($path)) {
            $parts = explode('/', $path);
            $accPath = '';
            foreach ($parts as $part) {
                $accPath .= ($accPath ? '/' : '') . $part;
                $breadcrumb[] = [
                    'name' => $part,
                    'path' => $accPath
                ];
            }
        }

        return view('wi-document', compact('items', 'path', 'employees', 'breadcrumb'));
    }

    public function view($path)
    {
        $decodedPath = urldecode($path);
        $fullPath = storage_path('app/public/WI/' . $decodedPath);

        if (!file_exists($fullPath)) {
            abort(404, 'File not found');
        }

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
