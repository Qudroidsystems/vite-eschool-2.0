<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BadgeModel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\JsonResponse;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:View role|Create role|Update role|Delete role|Add user-role|Update user-role|Remove user-role', ['only' => ['index','store']]);
         $this->middleware('permission:Create role', ['only' => ['create','store']]);
         $this->middleware('permission:Update role', ['only' => ['edit','update']]);
         $this->middleware('permission:Delete role', ['only' => ['destroy','removeuserRole']]);
         $this->middleware('permission:Update user-role', ['only' => ['adduser','updateuserrole']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request):View
    {

        #page title
        $pagetitle = "Role Management";

        $roles = Role::orderBy('name','DESC')->get();
        $role = Role::orderBy('name','DESC')->get();


        $permission = Permission::get();
        $perm_title = Permission::get(['title']);
        $array = [];
        foreach ($perm_title as $title ){
               $array[] = $title->title ;
         }

        $ar = implode(',', $array);
        $ex = explode(',',$ar);
       return view('roles.index', compact('role'), compact('roles'),compact('permission'))->with('perm_title',$ex)->with('pagetitle',$pagetitle);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $permission = Permission::get();
        return view('roles.create',compact('permission'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        #page title
        $pagetitle = "Role Management";

        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required|array', // Ensure permission is an array
            'permission.*' => 'exists:permissions,id', // Validate each permission ID exists
            'title' => 'nullable|string', // Optional validation for title
            'badge' => 'nullable|string', // Optional validation for badge
        ]);

        // Create the role
        $role = Role::create([
            'name' => $request->input('name'),
            'title' => $request->input('title'),
            'badge' => $request->input('badge'),
        ]);

        // Find permissions by their IDs
        $permissionIds = $request->input('permission');
        $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();

        // Sync permissions to the role
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')
                        ->with('success', 'Role created successfully')
                        ->with('pagetitle', $pagetitle);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pagetitle = "Role Management";

        // Count users with the role
        $userRoleCount = DB::table('model_has_roles')->where('role_id', $id)->count();

        // Fetch paginated users with the specified role
        $usersWithRole = User::leftJoin("roles", "roles.id", "=", "users.id")
            ->join("model_has_roles", "model_has_roles.model_id", "=", "users.id")
            ->where("model_has_roles.role_id", $id)
            ->select([
                'users.id as id',
                'users.name as username',
                'users.email as email',
                'users.created_at as created_at',
                'model_has_roles.role_id as roleid'
            ])
            ->paginate(5); // Set per-page limit to match frontend (perPage = 5)

        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $id)
            ->get();
        $rolePermissions2 = DB::table("role_has_permissions")
            ->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

        $permission = Permission::get();
        $perm_title = Permission::get(['title']);
        $array = [];
        foreach ($perm_title as $title) {
            $array[] = $title->title;
        }

        $ar = implode(',', $array);
        $ex = explode(',', $ar);

        Session::put('role_url', request()->fullUrl());

        return view('roles.show', compact(
            'role',
            'rolePermissions',
            'rolePermissions2',
            'usersWithRole',
            'userRoleCount',
            'pagetitle'
        ))->with('perm_title', $ex);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {

         #page title
         $pagetitle = "Role Management";


        $role = Role::find($id);
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
         ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();
        $permission = Permission::get();
        $perm_title = Permission::get(['title']);
        $array = [];
        foreach ($perm_title as $title ){
               $array[] = $title->title ;
         }

        $ar = implode(',', $array);
        $ex = explode(',',$ar);



        return view('roles.edit',compact('role','permission','rolePermissions'))->with('perm_title',$ex)->with('pagetitle',$pagetitle);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
        #page title
        $pagetitle = "Role Management";

        $this->validate($request, [
            'name' => 'required|unique:roles,name,' . $id, // Ensure name is unique except for current role
            'permission' => 'required|array', // Ensure permission is an array
            'permission.*' => 'exists:permissions,id', // Validate each permission ID exists
            'badge' => 'nullable|string', // Optional validation for badge
        ]);

        $role = Role::findOrFail($id); // Use findOrFail for safety
        $role->update([
            'name' => $request->input('name'),
            'badge' => $request->input('badge'),
        ]);

        // Convert permission IDs to permission names
        $permissionIds = $request->input('permission');
        $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();

        // Sync permissions to the role
        $role->syncPermissions($permissions);

        if (session('role_url')) {
            return redirect(session('role_url'))
                ->with('success', 'Role Updated successfully')
                ->with('pagetitle', $pagetitle);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role Updated successfully')
            ->with('pagetitle', $pagetitle);
    }

      /**
     * add user to role..
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function adduser($id): View
    {

         #page title
         $pagetitle = "Role Management";

        $role = Role::find($id);
        $r = $role->name;
        $users = User::whereDoesntHave('roles', function ($q) use ($r)  {
                            $q->where('name', $r); })->get();
        return view('roles.adduser')->with('role',$role)
                                    ->with('users',$users)->with('pagetitle',$pagetitle);
    }



      /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateuserrole(Request $request): RedirectResponse
    {
        #page title
        $pagetitle = "Role Management";

        $this->validate($request, [
            'users' => 'required|array', // Validate users as an array
            'users.*' => 'exists:users,id', // Ensure each user ID exists
            'roleid' => 'required|exists:roles,id', // Validate role ID
        ]);

        $role = Role::findOrFail($request->input('roleid')); // Find role by ID
        $userIds = $request->input('users'); // Get array of user IDs

        foreach ($userIds as $userId) {
            $user = User::findOrFail($userId); // Find user or fail
            $user->assignRole($role->name); // Assign role to each user
        }

        return redirect()->route('roles.show', $role->id)
                        ->with('success', 'Users added to role successfully')
                        ->with('pagetitle', $pagetitle);
    }

  /**
     * Remove user from role.
     */
    public function removeuserrole(Request $request, $userid, $roleid): JsonResponse
{
    \Log::info("Removing user role", ['user_id' => $userid, 'role_id' => $roleid]);
    try {
        $user = User::findOrFail($userid);
       // \Log::info("User found", ['user_id' => $userid]);
        $role = Role::findOrFail($roleid);
       // \Log::info("Role found", ['role_id' => $roleid]);
        $user->removeRole($role->name);
      //  \Log::info("User role removed successfully", ['user_id' => $userid, 'role_id' => $roleid]);
        return response()->json(['success' => true, 'message' => 'User role removed successfully']);
    } catch (\Exception $e) {
        \Log::error("Error removing user role", [
            'error' => $e->getMessage(),
            'user_id' => $userid,
            'role_id' => $roleid,
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['message' => 'Error removing user role: ' . $e->getMessage()], 500);
    }
}

    // delete user
    public function delete($id)
    {
        $delete = User::destroy($id);

        // check data deleted or not
        if ($delete == 1) {
            $success = true;
            $message = "User deleted successfully";
        } else {
            $success = true;
            $message = "User not found";
        }

        //  return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
         #page title
         $pagetitle = "Role Management";

        DB::table("roles")->where('id',$id)->delete();
        return redirect()->route('roles.index')
                        ->with('success','Role deleted successfully')->with('pagetitle',$pagetitle);
    }

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SchoolInformationController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:View schoolinformation|Create schoolinformation|Update schoolinformation|Delete schoolinformation', ['only' => ['index', 'show']]);
    //     $this->middleware('permission:Create schoolinformation', ['only' => ['create', 'store']]);
    //     $this->middleware('permission:Update schoolinformation', ['only' => ['edit', 'update']]);
    //     $this->middleware('permission:Delete schoolinformation', ['only' => ['destroy']]);
    // }

    public function index(Request $request): View
    {
        $pagetitle = "School Information Management";
        $data = SchoolInformation::latest()->paginate(10);
        $status_counts = [
            'Active' => SchoolInformation::where('is_active', true)->count(),
            'Inactive' => SchoolInformation::where('is_active', false)->count(),
        ];

        if (config('app.debug')) {
            \Log::info('School information loaded:', ['count' => $data->count()]);
        }

        return view('schoolinformation.index', compact('data', 'pagetitle', 'status_counts'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    public function create(): View
    {
        $title = "Create School Information";
        return view('schoolinformation.create', compact('title'));
    }

    public function store(Request $request): JsonResponse
    {
        \Log::debug("Creating school information", $request->all());

        if (!auth()->user()->hasPermissionTo('Create schoolinformation')) {
            \Log::warning("User ID " . auth()->user()->id . " attempted to create school information without 'Create schoolinformation' permission");
            return response()->json([
                'success' => false,
                'message' => 'User does not have the right permissions',
            ], 403);
        }

        try {
            $validated = $request->validate([
                'school_name' => 'required|string|max:255',
                'school_address' => 'required|string|max:500',
                'school_phone' => 'required|string|max:20',
                'school_email' => 'required|email:rfc,dns|unique:school_information,school_email',
                'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'school_motto' => 'nullable|string|max:255',
                'school_website' => 'nullable|url|max:255',
                'no_of_times_school_opened' => 'required|integer|min:0',
                'date_school_opened' => 'nullable|date',
                'date_next_term_begins' => 'nullable|date',
                'is_active' => 'boolean',
            ], [
                'no_of_times_school_opened.integer' => 'The number of times school opened must be a valid integer.',
                'date_school_opened.date' => 'The date school opened must be a valid date.',
                'date_next_term_begins.date' => 'The date next term begins must be a valid date.',
            ]);

            if ($request->hasFile('school_logo')) {
                $path = $request->file('school_logo')->store('school_logos', 'public');
                $validated['school_logo'] = $path;
            }

            if ($request->hasFile('app_logo')) {
                $path = $request->file('app_logo')->store('app_logos', 'public');
                $validated['app_logo'] = $path;
            }

            if ($validated['is_active']) {
                SchoolInformation::where('is_active', true)->update(['is_active' => false]);
            }

            $school = SchoolInformation::create($validated);

            \Log::debug("School information created successfully: ID {$school->id}");
            return response()->json([
                'success' => true,
                'message' => 'School information created successfully',
                'school' => [
                    'id' => $school->id,
                    'school_name' => $school->school_name,
                    'school_email' => $school->school_email,
                    'is_active' => $school->is_active,
                ],
            ], 201);
        } catch (ValidationException $e) {
            \Log::error("Validation error creating school information: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error("Create school information error: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to create school information: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): View
    {
        $pagetitle = "School Information Overview";
        $school = SchoolInformation::findOrFail($id);
        return view('schoolinformation.show', compact('school', 'pagetitle'));
    }

    public function edit($id): View
    {
        $school = SchoolInformation::findOrFail($id);
        return view('schoolinformation.edit', compact('school'));
    }

    public function update(Request $request, $id)
{
    // Check if this is a POST request with _update flag or PUT request
    $isUpdate = $request->has('_update') || $request->method() === 'PUT';

    if (!$isUpdate) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid request method for update'
        ], 405);
    }

    \Log::debug("Updating school information ID: {$id}", $request->all());

    try {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'school_address' => 'required|string|max:500',
            'school_phone' => 'required|string|max:20',
            'school_email' => 'required|email|unique:school_information,school_email,' . $id,
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'school_motto' => 'nullable|string|max:255',
            'school_website' => 'nullable|url|max:255',
            'no_of_times_school_opened' => 'required|integer|min:0',
            'date_school_opened' => 'nullable|date',
            'date_next_term_begins' => 'nullable|date',
            'is_active' => 'boolean',
        ], [
            'no_of_times_school_opened.integer' => 'The number of times school opened must be a valid integer.',
            'date_school_opened.date' => 'The date school opened must be a valid date.',
            'date_next_term_begins.date' => 'The date next term begins must be a valid date.',
        ]);

        $school = SchoolInformation::findOrFail($id);

        if ($request->hasFile('school_logo')) {
            if ($school->school_logo && Storage::disk('public')->exists($school->school_logo)) {
                Storage::disk('public')->delete($school->school_logo);
            }
            $path = $request->file('school_logo')->store('school_logos', 'public');
            $validated['school_logo'] = $path;
        } else {
            $validated['school_logo'] = $school->school_logo;
        }

        if ($request->hasFile('app_logo')) {
            if ($school->app_logo && Storage::disk('public')->exists($school->app_logo)) {
                Storage::disk('public')->delete($school->app_logo);
            }
            $path = $request->file('app_logo')->store('app_logos', 'public');
            $validated['app_logo'] = $path;
        } else {
            $validated['app_logo'] = $school->app_logo;
        }

        if ($validated['is_active']) {
            SchoolInformation::where('is_active', true)->where('id', '!=', $id)->update(['is_active' => false]);
        }

        $school->update($validated);

        \Log::debug("School information ID: {$id} updated successfully");

        return response()->json([
            'success' => true,
            'message' => 'School information updated successfully',
            'school' => [
                'id' => $school->id,
                'school_name' => $school->school_name,
                'school_email' => $school->school_email,
                'is_active' => $school->is_active,
            ],
        ], 200);
    } catch (ValidationException $e) {
        \Log::error("Validation error updating school information ID {$id}: " . json_encode($e->errors()));
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        \Log::error("Update school information error for ID {$id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
        return response()->json([
            'success' => false,
            'message' => 'Failed to update school information: ' . $e->getMessage(),
        ], 500);
    }
}

    public function destroy($id): JsonResponse
    {
        \Log::debug("Attempting to delete school information ID: {$id}");
        try {
            $school = SchoolInformation::findOrFail($id);

            if ($school->school_logo && Storage::disk('public')->exists($school->school_logo)) {
                Storage::disk('public')->delete($school->school_logo);
            }

            if ($school->app_logo && Storage::disk('public')->exists($school->app_logo)) {
                Storage::disk('public')->delete($school->app_logo);
            }

            $school->delete();

            \Log::debug("School information ID: {$id} deleted successfully");
            return response()->json([
                'success' => true,
                'message' => 'School information deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Delete school information error for ID {$id}: {$e->getMessage()}\nStack trace: {$e->getTraceAsString()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete school information: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editJson($id): JsonResponse
    {
        try {
            $school = SchoolInformation::findOrFail($id);

            return response()->json([
                'success' => true,
                'school' => [
                    'id' => $school->id,
                    'school_name' => $school->school_name,
                    'school_address' => $school->school_address,
                    'school_phone' => $school->school_phone,
                    'school_email' => $school->school_email,
                    'school_motto' => $school->school_motto,
                    'school_website' => $school->school_website,
                    'no_of_times_school_opened' => $school->no_of_times_school_opened,
                    'date_school_opened' => $school->date_school_opened,
                    'date_next_term_begins' => $school->date_next_term_begins,
                    'is_active' => $school->is_active,
                    'logo_url' => $school->getLogoUrlAttribute(),
                    'app_logo_url' => $school->getAppLogoUrlAttribute(),
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Edit JSON error for ID {$id}: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to load school data',
            ], 500);
        }
    }

    public function bulkRemoveUsers(Request $request)
{
    try {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'selected_users' => 'required|array',
            'selected_users.*' => 'exists:users,id'
        ]);

        $role = Role::findOrFail($request->role_id);
        $users = User::whereIn('id', $request->selected_users)->get();

        $removedCount = 0;
        foreach ($users as $user) {
            if ($user->hasRole($role->name)) {
                $user->removeRole($role);
                $removedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully removed {$removedCount} user(s) from the {$role->name} role.",
            'removed_count' => $removedCount
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to remove users: ' . $e->getMessage()
        ], 500);
    }
}
}

}
