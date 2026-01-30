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
use App\Models\Studentpicture;

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
        $data = User::latest()->get();
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
                'name'         => 'required|string|max:255',
                'email'        => 'required|email|unique:users,email',
                'password'     => 'required|min:8|confirmed',
                'roles'        => 'required|array',
                'roles.*'      => 'exists:roles,name',
                'phone_number' => 'nullable|string|regex:/^\+[1-9]\d{1,14}$/',
            ]);

            $input = $request->all();
            $plainPassword = $input['password'];
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->syncRoles($request->input('roles'));

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user'    => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'roles'        => $user->roles->pluck('name')->toArray(),
                    'phone_number' => $user->phone_number,
                    'password'     => $plainPassword,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error("Create user error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
            ], 500);
        }
    }
public function show($id): View
{
    $pagetitle = "User Overview";

    // Eager load everything you need
    $user = User::with([
        'roles',
        'bio',
        'student',                      // ← make sure this relation exists
        'staffemploymentDetails',
        'staffPicture'
    ])->findOrFail($id);

    $userbio       = $user->bio;
    $staffInfo     = $user->staffemploymentDetails;
    $staffPicture  = $user->staffPicture;
    $studentPicture = null;

    if ($user->isStudent() && $user->student_id) {
        $studentPicture = Studentpicture::where('studentid', $user->student_id)->first();
    }

    // Prepare variables for Blade
    $isStudentUser  = $user->hasRole('student');
    $studentData    = $user->student;           // ← this is the fix

    return view('users.overview', compact(
        'user',
        'userbio',
        'staffInfo',
        'staffPicture',
        'studentPicture',
        'pagetitle',
        'isStudentUser',
        'studentData'                       // ← now passed
    ));
}

    public function edit($id): View
    {
        $user = User::findOrFail($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();
        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            if (!auth()->user()->hasPermissionTo('Update user')) {
                return response()->json(['success' => false, 'message' => 'No permission'], 403);
            }

            $validated = $request->validate([
                'name'         => 'required|string|max:255',
                'email'        => 'required|email|unique:users,email,' . $id,
                'password'     => 'nullable|min:8|confirmed',
                'roles'        => 'required|array',
                'roles.*'      => 'exists:roles,name',
                'phone_number' => 'nullable|string|regex:/^\+[1-9]\d{1,14}$/',
            ]);

            $input = $request->all();
            if (!empty($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            } else {
                unset($input['password']);
            }

            $user = User::findOrFail($id);
            $user->update($input);
            $user->syncRoles($request->input('roles'));

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $isStudent = $user->hasRole('student');

            BioModel::where('user_id', $id)->delete();
            $user->roles()->detach();

            // Student record is NOT deleted — only user account is removed
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => $isStudent
                    ? 'User account removed. Student record remains in Student Management.'
                    : 'User deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Delete user error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
            ], 500);
        }
    }

    public function roles(): JsonResponse
    {
        $roles = Role::pluck('name')->all();
        return response()->json(['roles' => $roles]);
    }

    public function storeStudent(Request $request): JsonResponse
    {
        try {
            if (!auth()->user()->hasPermissionTo('Create user')) {
                return response()->json(['success' => false, 'message' => 'No permission'], 403);
            }

            $validated = $request->validate([
                'student_id' => 'required|exists:studentRegistration,id',
                'name'       => 'required|string|max:255',
                'email'      => 'required|email|unique:users,email',
                'username'   => 'required|string|unique:users,username|max:255',
                'password'   => 'required|min:8|confirmed',
                'roles'      => 'required|array|min:1',
                'roles.*'    => 'exists:roles,name',
            ]);

            $input = $request->all();
            $input['username'] = str_replace('/', '_', $input['username']);
            $plainPassword = $input['password'];
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->syncRoles($request->input('roles'));

            $student = Student::findOrFail($validated['student_id']);

            BioModel::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'firstname'     => $student->firstname,
                    'lastname'      => $student->lastname,
                    'othernames'    => $student->othername ?? '',
                    'phone'         => $student->phone_number ?? '',
                    'address'       => $student->home_address2 ?? '',
                    'gender'        => $student->gender ?? '',
                    'maritalstatus' => '',
                    'nationality'   => $student->nationality ?? '',
                    'dob'           => $student->dateofbirth ?? '',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Student user created successfully',
                'user'    => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'username' => $user->username,
                    'roles'    => $user->roles->pluck('name')->toArray(),
                    'password' => $plainPassword,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error("storeStudent error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student user',
            ], 500);
        }
    }

    public function getStudents(Request $request): JsonResponse
    {
        try {
            $students = Student::query()
                ->select('id', 'admissionNo', 'firstname', 'lastname', 'email')
                ->when($request->search, function ($q, $search) {
                    $q->where('admissionNo', 'like', "%{$search}%")
                      ->orWhere('firstname', 'like', "%{$search}%")
                      ->orWhere('lastname', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$search}%"]);
                })
                ->orderBy('admissionNo')
                ->limit(300)
                ->get();

            return response()->json([
                'success'  => true,
                'students' => $students->map(fn($s) => [
                    'id'          => $s->id,
                    'admissionNo' => $s->admissionNo,
                    'name'        => trim("{$s->firstname} {$s->lastname}"),
                    'email'       => $s->email ?? '',
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load students',
            ], 500);
        }
    }
}
