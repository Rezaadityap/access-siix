<?php

namespace App\Http\Controllers\IT;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Level;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $levels = Level::orderBy('level_name')->get();
        $departments = Employee::select('department')
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $query = User::with('employee');

        if ($request->filled('department')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return view('IT.users', compact('users', 'departments', 'levels'));
    }

    public function show(User $user)
    {
        $user->load('employee');

        $photoUrl = null;
        if ($user->employee && $user->employee->photo) {
            $photoUrl = "http://192.168.61.8/photos/employee/" . $user->employee->photo;
        }

        return response()->json([
            'id' => $user->id,
            'nik' => $user->nik,
            'name' => $user->name,
            'email' => $user->email,
            'level_id' => $user->level_id,
            'employee' => $user->employee ? [
                'id' => $user->employee->id,
                'department' => $user->employee->department,
                'section' => $user->employee->section,
                'photo' => $photoUrl,
            ] : null,
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Validasi
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'nik')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'department' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'level_id' => 'nullable|integer|exists:level,id',
        ]);

        // Update user basic
        $user->name = $validated['name'];
        $user->nik = $validated['nik'];
        $user->email = $validated['email'];
        if (isset($validated['level_id'])) {
            $user->level_id = $validated['level_id'];
        }
        $user->save();

        $employee = $user->employee;
        if (!$employee) {
            // create if not exists
            $employee = $user->employee()->create([]);
        }

        $employee->department = $validated['department'] ?? $employee->department;
        $employee->section = $validated['section'] ?? $employee->section;

        $employee->save();

        // Return updated payload
        $user->load('employee');

        return response()->json([
            'success' => true,
            'message' => 'User updated',
            'user' => [
                'id' => $user->id,
                'nik' => $user->nik,
                'name' => $user->name,
                'email' => $user->email,
                'employee' => $user->employee ? [
                    'department' => $user->employee->department,
                    'section' => $user->employee->section,
                ] : null,
            ],
        ]);
    }
}
