<?php

namespace App\Http\Controllers;

use App\Models\Schoolarm;
use App\Models\Schoolclass;
use App\Models\ClassTeacher;
use App\Models\Classcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SchoolClassController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View school-class|Create school-class|Update school-class|Delete school-class', ['only' => ['index']]);
        $this->middleware('permission:Create school-class', ['only' => ['store']]);
        $this->middleware('permission:Update school-class', ['only' => ['update']]);
        $this->middleware('permission:Delete school-class', ['only' => ['destroy', 'deleteschoolclass']]);
    }

    public function index(Request $request)
    {
        Log::info('Index School Class Request:', $request->all());
        $pagetitle = "School Class Management";

        // Start query with eager loading for better performance
        $query = Schoolclass::with(['armRelation', 'categories'])
            ->select(
                'schoolclass.id',
                'schoolclass.schoolclass',
                'schoolclass.arm',
                'schoolclass.classcategoryid',
                'schoolclass.description',
                'schoolclass.updated_at'
            )
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('schoolclass.arm');

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('schoolclass.schoolclass', 'like', '%' . $search . '%')
                  ->orWhere('schoolclass.description', 'like', '%' . $search . '%')
                  ->orWhereHas('armRelation', function ($armQuery) use ($search) {
                      $armQuery->where('arm', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('categories', function ($catQuery) use ($search) {
                      $catQuery->where('category', 'like', '%' . $search . '%');
                  });
            });
        }

        $all_classes = $query->paginate(100);

        // Process each class to add formatted category names
        foreach ($all_classes as $class) {
            $class->arm_name = $class->armRelation->arm ?? '—';

            // Get all categories for this class
            $categoryNames = [];
            $categoryIds = explode(',', $class->classcategoryid);
            if (!empty($categoryIds) && $categoryIds[0] !== '') {
                $categories = Classcategory::whereIn('id', $categoryIds)->get();
                $categoryNames = $categories->pluck('category')->toArray();
            }
            $class->all_categories = !empty($categoryNames) ? implode(', ', $categoryNames) : '—';
        }

        $arms = Schoolarm::orderBy('arm')->get();
        $classcategories = Classcategory::orderBy('category')->get();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('schoolclass.index', compact('all_classes', 'arms', 'classcategories', 'pagetitle'))->render(),
                'count' => $all_classes->count(),
                'total' => $all_classes->total(),
            ]);
        }

        return view('schoolclass.index')
            ->with('all_classes', $all_classes)
            ->with('arms', $arms)
            ->with('classcategories', $classcategories)
            ->with('pagetitle', $pagetitle);
    }

    public function store(Request $request)
    {
        Log::info('=== STORE SCHOOL CLASS START ===', $request->all());

        // Validate request
        $validator = Validator::make($request->all(), [
            'schoolclass'       => 'required|string|max:255',
            'arm_id'            => 'required|array|min:1',
            'arm_id.*'          => 'exists:schoolarm,id',
            'classcategoryid'   => 'required|array|min:1',
            'classcategoryid.*' => 'exists:classcategories,id',
            'description'       => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            Log::warning('VALIDATION FAILED', $validator->errors()->all());
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $created = [];
        DB::beginTransaction();

        try {
            $className    = trim($request->schoolclass);
            $armIds       = $request->input('arm_id', []);
            $categoryIds  = $request->input('classcategoryid', []);
            $description  = $request->filled('description') ? trim($request->description) : null;

            // Convert categories to comma-separated string
            $categoriesString = implode(',', $categoryIds);

            foreach ($armIds as $armId) {
                // Check if combination already exists
                $exists = Schoolclass::where('schoolclass', $className)
                    ->where('arm', $armId)
                    ->exists();

                if ($exists) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "A class with name '{$className}' and selected arm already exists."
                    ], 409); // Conflict
                }

                $record = new Schoolclass();
                $record->schoolclass     = $className;
                $record->arm             = $armId;
                $record->classcategoryid = $categoriesString;
                $record->description     = $description;
                $record->save();

                $created[] = $record->id;
            }

            DB::commit();

            Log::info('=== STORE SCHOOL CLASS SUCCESS ===', ['created_ids' => $created]);

            return response()->json([
                'success' => true,
                'message' => 'Created ' . count($created) . ' record(s) successfully!',
                'ids'     => $created,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update School Class Request:', ['id' => $id, 'data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'schoolclass'       => 'required|string|max:255',
            'arm_id'            => 'required|exists:schoolarm,id',
            'classcategoryid'   => 'required|array|min:1',
            'classcategoryid.*' => 'exists:classcategories,id',
            'description'       => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $schoolclass = Schoolclass::findOrFail($id);

            // Check if another record has the same schoolclass and arm (excluding current)
            $exists = Schoolclass::where('schoolclass', $request->schoolclass)
                ->where('arm', $request->arm_id)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => "A class with name '{$request->schoolclass}' and selected arm already exists."
                ], 409);
            }

            // Update the record
            $schoolclass->schoolclass     = $request->schoolclass;
            $schoolclass->arm             = $request->arm_id;
            $schoolclass->classcategoryid = implode(',', $request->classcategoryid);
            $schoolclass->description     = $request->description ?? null;
            $schoolclass->save();

            DB::commit();

            Log::info('Update successful', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
                'data'    => [
                    'id' => $schoolclass->id,
                    'schoolclass' => $schoolclass->schoolclass,
                    'arm_id' => $schoolclass->arm,
                    'categories' => $schoolclass->classcategoryid
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('School class not found', ['id' => $id]);
            return response()->json([
                'message' => 'School class not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error updating record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::info('Delete School Class Request:', ['id' => $id]);

        DB::beginTransaction();

        try {
            $schoolclass = Schoolclass::findOrFail($id);

            // Check if class has any teachers assigned
            $hasTeachers = ClassTeacher::where('schoolclassid', $id)->exists();
            if ($hasTeachers) {
                return response()->json([
                    'message' => 'Cannot delete class. There are teachers assigned to this class.'
                ], 400);
            }

            // Delete the class
            $schoolclass->delete();

            DB::commit();

            Log::info('Delete successful', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('School class not found for deletion', ['id' => $id]);
            return response()->json([
                'message' => 'School class not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error deleting record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteschoolclass(Request $request)
    {
        Log::info('AJAX Delete Request:', $request->all());

        $validator = Validator::make($request->all(), [
            'schoolclassid' => 'required|exists:schoolclass,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid class ID'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $schoolclass = Schoolclass::findOrFail($request->schoolclassid);

            // Check if class has any teachers assigned
            $hasTeachers = ClassTeacher::where('schoolclassid', $request->schoolclassid)->exists();
            if ($hasTeachers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete class. There are teachers assigned to this class.'
                ], 400);
            }

            // Delete the class
            $schoolclass->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Class removed successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('School class not found', ['id' => $request->schoolclassid]);
            return response()->json([
                'success' => false,
                'message' => 'Class not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getArms($id)
    {
        try {
            $schoolClass = Schoolclass::findOrFail($id);
            return response()->json([
                'success' => true,
                'armIds' => [$schoolClass->arm]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch arms', ['error' => $e->getMessage(), 'id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch arms'
            ], 500);
        }
    }
}
