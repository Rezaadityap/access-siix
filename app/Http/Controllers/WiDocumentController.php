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

    // public function view($path)
    // {
    //     $disk = Storage::disk('work_instruction');
    //     if (!$disk->exists($path)) {
    //         abort(404);
    //     }

    //     $mime = File::mimeType($disk->path($path));

    //     if ($mime === 'application/pdf') {
    //         $disk->path($path);
    //         // dd($fullPath, file_exists($fullPath), is_readable($fullPath));
    //     }

    //     abort(403, 'Only PDF files can be viewed.');
    // }

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

    public function getDataAbsent(Request $request)
    {
        if ($request->ajax()) {
            $department = Employee::join('users', 'employees.nik', '=', 'users.nik')
                ->where('users.id', Auth::id())
                ->value('employees.department');

            $department = trim($department);

            $data = Employee::select('id', 'name', 'nik', 'department', 'photo')
                ->whereRaw('TRIM(department) = ?', [$department]);

            return DataTables::of($data)
                ->addColumn('photo', function ($row) {
                    $photoUrl = asset('assets/img/' . $row->photo);
                    return "<img src='$photoUrl' alt='Employee Photo Absent' width='45' height='45' class='rounded-circle' loading='lazy'>";
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-primary select-absent" data-id="' . $row->id . '" data-nik="' . $row->nik . '" data-name="' . e($row->name) . '" data-dept="' . e($row->department) . '" data-img="' . asset('assets/img/' . $row->photo) . '">Select</button>';
                })
                ->rawColumns(['photo', 'action'])
                ->make(true);
        }
    }

    public function getDataTable(Request $request)
    {
        if ($request->ajax()) {
            // Ambil department user yang login
            $department = Employee::join('users', 'employees.nik', '=', 'users.nik')
                ->where('users.id', Auth::id())
                ->value('employees.department');

            $department = trim($department);

            $data = Employee::select('id', 'name', 'nik', 'department', 'photo')
                ->whereRaw('TRIM(department) = ?', [$department]);

            return DataTables::of($data)
                ->addColumn('photo', function ($row) {
                    $photoUrl = asset('assets/img/' . $row->photo);
                    return "<img src='$photoUrl' alt='Employee Photo PIC' width='45' height='45' class='rounded-circle' loading='lazy'>";
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-primary select-pic" data-id="' . $row->id . '" data-nik="' . $row->nik . '" data-name="' . e($row->name) . '" data-dept="' . e($row->department) . '" data-img="' . asset('assets/img/' . $row->photo) . '">Select</button>';
                })
                ->rawColumns(['photo', 'action'])
                ->make(true);
        }
    }
}
