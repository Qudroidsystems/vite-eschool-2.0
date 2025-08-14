<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BioModel;
use App\Models\Student;
use App\Models\User;
use DB;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View user|Create user|Update user|Delete user', ['only' => ['index', 'store']]);
        $this->middleware('permission:Create user', ['only' => ['create', 'store']]);
        $this->middleware('permission:Update user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete user', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $pagetitle = "User Management";
        $data = User::latest()->get(); // Fetch all users instead of paginating
        $roles = Role::pluck('name', 'name')->toArray();
        $role_permissions = Role::all();

        $role_counts = [];
        foreach ($roles as $role) {
            $role_counts[$role] = User::role($role)->count();
        }
        $role_counts['No Role'] = User::doesntHave('roles')->count();

        if (config('app.debug')) {
            \Log::info('Roles for select:', $roles);
            \Log::info('User roles example:', User::first()->getRoleNames()->toArray());
        }

        return view('users.index', compact('data', 'roles', 'role_permissions', 'pagetitle', 'role_counts'));
    }

    public function roles(): JsonResponse
    {
        $roles = Role::pluck('name')->all();
        return response()->json(['roles' => $roles]);
    }

    public function create(): View
    {
        $title = "Create User";
        $roles = Role::pluck('name', 'name')->all();
        return view('users.create', compact('roles', 'title'));
    }

     public function store(Request $request): JsonResponse
    {
        \Log::debug("Creating user", $request->all());

        try {
            if (!auth()->user()->hasPermissionTo('Create user')) {
                \Log::warning("User ID " . auth()->user()->id . " attempted to create user without permission");
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have the right permissions',
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,name',
                'phone_number' => 'nullable|string|regex:/^\+[1-9]\d{1,14}$/', // Optional E.164 phone number
            ]);

            \Log::info("Validated data for create:", $validated);

            $input = $request->all();
            $plainPassword = $input['password']; // Store plain password for WhatsApp
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->syncRoles($request->input('roles'));

            \Log::info("User ID: {$user->id} created successfully, roles:", $request->input('roles'));

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'phone_number' => $user->phone_number,
                    'password' => $plainPassword, // Include plain password for WhatsApp
                ],
            ], 201);
        } catch (ValidationException $e) {
            \Log::error("Validation error creating user: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error("Create user error: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        \Log::debug("Updating user ID: {$id}", $request->all());

        try {
            if (!auth()->user()->hasPermissionTo('Update user')) {
                \Log::warning("User ID " . auth()->user()->id . " attempted to update user without permission");
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have the right permissions',
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'nullable|min:8|confirmed',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,name',
                'phone_number' => 'nullable|string|regex:/^\+[1-9]\d{1,14}$/', // Optional E.164 phone number
            ]);

            \Log::info("Validated data for update:", $validated);

            $input = $request->all();
            $plainPassword = !empty($input['password']) ? $input['password'] : null;
            if (!empty($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            } else {
                $input = Arr::except($input, ['password']);
            }

            $user = User::findOrFail($id);
            $user->update($input);
            $user->syncRoles($request->input('roles'));

            \Log::info("User ID: {$id} updated successfully, roles:", $request->input('roles'));

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'phone_number' => $user->phone_number,
                    'password' => $plainPassword, // Include plain password if updated
                ],
            ], 200);
        } catch (ValidationException $e) {
            \Log::error("Validation error updating user ID {$id}: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error("Update user error for ID {$id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function store(Request $request): JsonResponse
    // {
    //     \Log::debug("Creating user", $request->all());

    //     if (!auth()->user()->hasPermissionTo('Create user')) {
    //         \Log::warning("User ID " . auth()->user()->id . " attempted to create user without 'Create user' permission");
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'User does not have the right permissions',
    //         ], 403);
    //     }

    //     try {
    //         $validated = $request->validate([
    //             'name' => 'required|string|max:255',
    //             'email' => 'required|email:rfc,dns|unique:users,email',
    //             'password' => 'required|string|min:8|confirmed',
    //             'roles' => 'required|array',
    //             'roles.*' => 'exists:roles,name',
    //         ]);

    //         $user = User::create([
    //             'name' => $validated['name'],
    //             'email' => $validated['email'],
    //             'password' => Hash::make($validated['password']),
    //         ]);
    //         $user->assignRole($validated['roles']);

    //         \Log::debug("User created successfully: ID {$user->id}");
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'User created successfully',
    //             'user' => [
    //                 'id' => $user->id,
    //                 'name' => $user->name,
    //                 'email' => $user->email,
    //                 'roles' => $user->roles->pluck('name')->toArray(),
    //             ],
    //         ], 201);
    //     } catch (ValidationException $e) {
    //         \Log::error("Validation error creating user: " . json_encode($e->errors()));
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         \Log::error("Create user error: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to create user: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function show($id): View
    {
        $pagetitle = "User Overview";
        $user = User::find($id);
        $userroles = $user->roles->all();
        $userbio = $user->bio;
        return view('users.useroverview', compact('user', 'userroles', 'userbio', 'pagetitle'));
    }

    public function edit($id): View
    {
        $user = User::find($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();
        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    // public function update(Request $request, $id): JsonResponse
    // {
    //     \Log::debug("Updating user ID: {$id}", $request->all());

    //     try {
    //         $validated = $request->validate([
    //             'name' => 'required|string|max:255',
    //             'email' => 'required|email|unique:users,email,' . $id,
    //             'password' => 'nullable|min:8|confirmed',
    //             'roles' => 'required|array',
    //             'roles.*' => 'exists:roles,name',
    //         ]);

    //         $input = $request->all();
    //         if (!empty($input['password'])) {
    //             $input['password'] = Hash::make($input['password']);
    //         } else {
    //             $input = Arr::except($input, ['password']);
    //         }

    //         $user = User::findOrFail($id);
    //         $user->update($input);
    //         \DB::table('model_has_roles')->where('model_id', $id)->delete();

    //         \Log::debug("Roles to assign:", $request->input('roles'));
    //         $user->assignRole($request->input('roles'));

    //         \Log::debug("User ID: {$id} updated successfully");

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'User updated successfully',
    //             'user' => [
    //                 'id' => $user->id,
    //                 'name' => $user->name,
    //                 'email' => $user->email,
    //                 'roles' => $user->roles->pluck('name')->toArray(),
    //             ],
    //         ], 200);
    //     } catch (ValidationException $e) {
    //         \Log::error("Validation error updating user ID {$id}: " . json_encode($e->errors()));
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         \Log::error("Update user error for ID {$id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update user: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function createFromStudentForm(): View
    {
        $roles = Role::pluck('name', 'name')->all();
        $students = Student::select('id', 'admissionNo', 'firstname', 'lastname')
            ->where('statusId', 1)
            ->orderBy('admissionNo')
            ->get();

        return view('users.add-student-user', compact('roles', 'students'));
    }

    public function createFromStudent(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'student_id' => 'required|exists:studentregistration,id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required',
        ]);

        $student = Student::findOrFail($request->student_id);

        $user = new User();
        $user->name = $student->firstname . ' ' . $student->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->student_id = $student->id;
        $user->save();

        $user->assignRole($request->input('roles'));

        BioModel::updateOrCreate(
            ['user_id' => $user->id],
            [
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'othernames' => $student->othername ?? '',
                'phone' => '',
                'address' => $student->home_address ?? '',
                'gender' => $student->gender ?? '',
                'maritalstatus' => '',
                'nationality' => $student->nationlity ?? '',
                'dob' => $student->dateofbirth ?? '',
            ]
        );

        return redirect()->route('users.index')
            ->with('success', 'Student added as user successfully');
    }

    public function destroy($id): JsonResponse
    {
        \Log::debug("Attempting to delete user ID: {$id}");
        try {
            $user = User::findOrFail($id);

            \Log::debug("Deleting BioModel for user ID: {$id}");
            BioModel::where('user_id', $id)->delete();

            \Log::debug("Removing roles for user ID: {$id}");
            $user->roles()->detach();

            \Log::debug("Deleting user ID: {$id}");
            $user->delete();

            \Log::debug("User ID: {$id} deleted successfully");
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Delete user error for ID {$id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage(),
            ], 500);
        }
    }
}