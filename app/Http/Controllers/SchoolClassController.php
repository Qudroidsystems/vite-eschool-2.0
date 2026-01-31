<?php

namespace App\Http\Controllers;

use App\Models\Schoolarm;
use App\Models\Schoolclass;
use App\Models\ClassTeacher;
use Illuminate\Http\Request;
use App\Models\Classcategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;
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
        Log::channel('schoolclass')->info('School Class Index Request');

        $pagetitle = "School Class Management";

        try {
            $query = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->leftJoin('schoolclass_classcategory', 'schoolclass_classcategory.schoolclass_id', '=', 'schoolclass.id')
                ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass_classcategory.classcategory_id')
                ->select(
                    'schoolclass.id',
                    'schoolclass.schoolclass',
                    'schoolarm.arm as arm_name',
                    'schoolclass.arm as arm_id',
                    DB::raw('GROUP_CONCAT(DISTINCT classcategories.category ORDER BY classcategories.category SEPARATOR ", ") as classcategory'),
                    DB::raw('GROUP_CONCAT(DISTINCT classcategories.id ORDER BY classcategories.id SEPARATOR "," ) as classcategoryids'),
                    'schoolclass.updated_at'
                )
                ->groupBy('schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm', 'schoolclass.arm', 'schoolclass.updated_at');

            if ($request->has('search')) {
                $search = $request->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('schoolclass.schoolclass', 'like', '%' . $search . '%')
                      ->orWhere('schoolarm.arm', 'like', '%' . $search . '%')
                      ->orWhere('classcategories.category', 'like', '%' . $search . '%');
                });
            }

            $all_classes = $query->orderBy('schoolclass.schoolclass')->paginate(100);
            $arms = Schoolarm::all();
            $classcategories = Classcategory::all();

            return view('schoolclass.index')
                ->with('all_classes', $all_classes)
                ->with('arms', $arms)
                ->with('classcategories', $classcategories)
                ->with('pagetitle', $pagetitle);

        } catch (\Exception $e) {
            Log::channel('schoolclass')->error('Index Error:', ['error' => $e->getMessage()]);
            return back()->with('danger', 'Error loading school classes: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // Create debug log
        $debugLog = storage_path('logs/schoolclass_debug_' . date('Y-m-d_H-i-s') . '.log');
        $debugContent = "=== SCHOOL CLASS STORE REQUEST ===\n";
        $debugContent .= "Time: " . date('Y-m-d H:i:s') . "\n";
        $debugContent .= "User: " . auth()->user()->name . " (ID: " . auth()->id() . ")\n\n";

        // Log ALL request data
        $debugContent .= "=== ALL REQUEST DATA ===\n";
        $allData = $request->all();
        $debugContent .= print_r($allData, true) . "\n\n";

        // Log specific fields
        $debugContent .= "=== SPECIFIC FIELDS ===\n";
        $debugContent .= "schoolclass: " . ($request->input('schoolclass') ?? 'NOT SET') . "\n";

        $armIds = $request->input('arm_id', []);
        $debugContent .= "arm_id: " . print_r($armIds, true) . "\n";
        $debugContent .= "arm_id is array: " . (is_array($armIds) ? 'YES' : 'NO') . "\n";
        $debugContent .= "arm_id count: " . (is_array($armIds) ? count($armIds) : 'N/A') . "\n";

        $categoryIds = $request->input('classcategoryid', []);
        $debugContent .= "classcategoryid: " . print_r($categoryIds, true) . "\n";
        $debugContent .= "classcategoryid is array: " . (is_array($categoryIds) ? 'YES' : 'NO') . "\n";
        $debugContent .= "classcategoryid count: " . (is_array($categoryIds) ? count($categoryIds) : 'N/A') . "\n\n";

        // Log headers
        $debugContent .= "=== REQUEST HEADERS ===\n";
        foreach ($request->headers->all() as $key => $value) {
            $debugContent .= $key . ": " . implode(', ', $value) . "\n";
        }

        // Check for debug header from JavaScript
        if ($request->hasHeader('X-Debug-JS')) {
            $debugContent .= "\n=== DEBUG DATA FROM JAVASCRIPT ===\n";
            $debugContent .= $request->header('X-Debug-JS') . "\n";
        }

        DB::beginTransaction();
        try {
            // Log to regular channel
            Log::channel('schoolclass')->info('Store request received', [
                'schoolclass' => $request->input('schoolclass'),
                'arm_id_count' => is_array($armIds) ? count($armIds) : 0,
                'classcategoryid_count' => is_array($categoryIds) ? count($categoryIds) : 0
            ]);

            // Check database table structure
            $debugContent .= "\n=== DATABASE TABLE STRUCTURE ===\n";
            try {
                $tableColumns = DB::select('DESCRIBE schoolclass');
                $debugContent .= "schoolclass table columns:\n";
                foreach ($tableColumns as $column) {
                    $debugContent .= "- {$column->Field} ({$column->Type}, Null: {$column->Null}, Default: {$column->Default})\n";
                }

                // Check if classcategoryid column exists
                $hasClassCategoryId = collect($tableColumns)->contains(function ($column) {
                    return $column->Field === 'classcategoryid';
                });

                $debugContent .= "\nHas classcategoryid column: " . ($hasClassCategoryId ? 'YES' : 'NO') . "\n";

                if ($hasClassCategoryId) {
                    $debugContent .= "WARNING: classcategoryid column exists in schoolclass table!\n";
                    $debugContent .= "This column should NOT exist here. It should only be in the pivot table.\n";
                }

            } catch (\Exception $e) {
                $debugContent .= "Error checking table structure: " . $e->getMessage() . "\n";
            }

            $debugContent .= "\n=== VALIDATION ===\n";

            $validator = Validator::make($request->all(), [
                'schoolclass' => 'required|string|max:255',
                'arm_id' => 'required|array|min:1',
                'arm_id.*' => 'exists:schoolarm,id',
                'classcategoryid' => 'required|array|min:1',
                'classcategoryid.*' => 'exists:classcategories,id',
            ], [
                'schoolclass.required' => 'Please enter a school class name.',
                'arm_id.required' => 'Please select at least one arm.',
                'arm_id.*.exists' => 'One or more selected arms do not exist.',
                'classcategoryid.required' => 'Please select at least one category.',
                'classcategoryid.*.exists' => 'One or more selected categories do not exist.',
            ]);

            $debugContent .= "Validation rules applied\n";

            $validator->after(function ($validator) use ($request) {
                $armIds = $request->arm_id ?? [];
                foreach ($armIds as $armId) {
                    $exists = Schoolclass::where('schoolclass', $request->schoolclass)
                        ->where('arm', $armId)
                        ->exists();
                    if ($exists) {
                        $arm = Schoolarm::find($armId);
                        $armName = $arm ? $arm->arm : 'Unknown';
                        $validator->errors()->add(
                            'schoolclass',
                            "The combination of class '{$request->schoolclass}' and arm '{$armName}' already exists."
                        );
                    }
                }
            });

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                $debugContent .= "Validation failed:\n";
                $debugContent .= print_r($errors, true) . "\n";

                DB::rollBack();

                // Save debug log
                file_put_contents($debugLog, $debugContent);

                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'debug' => [
                        'log_file' => basename($debugLog),
                        'received_data' => $allData
                    ]
                ], 422);
            }

            $debugContent .= "Validation passed!\n\n";
            $debugContent .= "=== PROCESSING DATA ===\n";

            $createdRecords = [];
            $armIds = $request->arm_id;
            $categoryIds = $request->classcategoryid;
            $description = $request->description ?? 'Null';

            $debugContent .= "Processing:\n";
            $debugContent .= "- School Class: " . $request->schoolclass . "\n";
            $debugContent .= "- Arm IDs: " . implode(', ', $armIds) . "\n";
            $debugContent .= "- Category IDs: " . implode(', ', $categoryIds) . "\n";
            $debugContent .= "- Description: " . $description . "\n\n";

            // Check if arms exist
            $existingArms = Schoolarm::whereIn('id', $armIds)->count();
            if ($existingArms != count($armIds)) {
                throw new \Exception("Some selected arms do not exist in the database");
            }

            // Check if categories exist
            $existingCategories = Classcategory::whereIn('id', $categoryIds)->count();
            if ($existingCategories != count($categoryIds)) {
                throw new \Exception("Some selected categories do not exist in the database");
            }

            $categories = Classcategory::whereIn('id', $categoryIds)->get(['id', 'category']);

            $debugContent .= "=== CREATING SCHOOL CLASSES ===\n";

            foreach ($armIds as $index => $armId) {
                $debugContent .= "\nCreating record " . ($index + 1) . " for arm ID: " . $armId . "\n";

                $schoolclass = new Schoolclass();
                $schoolclass->schoolclass = $request->schoolclass;
                $schoolclass->arm = $armId;
                $schoolclass->description = $description;

                // TEMPORARY FIX: If classcategoryid column exists, set it to first category
                if (Schema::hasColumn('schoolclass', 'classcategoryid')) {
                    $firstCategory = $categoryIds[0] ?? null;
                    $schoolclass->classcategoryid = $firstCategory;
                    $debugContent .= "WARNING: Setting classcategoryid in main table: " . $firstCategory . "\n";
                }

                $debugContent .= "Saving school class with data:\n";
                $debugContent .= "- schoolclass: " . $schoolclass->schoolclass . "\n";
                $debugContent .= "- arm: " . $schoolclass->arm . "\n";
                $debugContent .= "- description: " . $schoolclass->description . "\n";
                if (Schema::hasColumn('schoolclass', 'classcategoryid')) {
                    $debugContent .= "- classcategoryid: " . ($schoolclass->classcategoryid ?? 'NULL') . "\n";
                }

                $schoolclass->save();

                $debugContent .= "School class saved successfully! ID: " . $schoolclass->id . "\n";

                // Attach categories to pivot table
                $debugContent .= "Attaching categories to pivot table: " . implode(', ', $categoryIds) . "\n";
                $schoolclass->classcategories()->attach($categoryIds);
                $debugContent .= "Categories attached successfully\n";

                $arm = Schoolarm::find($armId);

                $createdRecords[] = [
                    'id' => $schoolclass->id,
                    'schoolclass' => $schoolclass->schoolclass,
                    'arm_id' => $schoolclass->arm,
                    'arm_name' => $arm ? $arm->arm : 'Unknown',
                    'classcategories' => $categories->toArray(),
                    'description' => $schoolclass->description,
                    'updated_at' => $schoolclass->updated_at->toISOString(),
                    'created_at' => $schoolclass->created_at->toISOString()
                ];

                $debugContent .= "Record created successfully\n";
            }

            DB::commit();

            $debugContent .= "\n=== TRANSACTION COMMITTED SUCCESSFULLY ===\n";
            $debugContent .= "Total records created: " . count($createdRecords) . "\n";

            // Save debug log
            file_put_contents($debugLog, $debugContent);

            Log::channel('schoolclass')->info('School classes created successfully', [
                'count' => count($createdRecords),
                'schoolclass' => $request->schoolclass
            ]);

            return response()->json([
                'message' => 'School class(es) added successfully!',
                'schoolclasses' => $createdRecords,
                'debug' => [
                    'log_file' => basename($debugLog),
                    'data_received' => [
                        'schoolclass' => $request->schoolclass,
                        'arm_ids' => $armIds,
                        'category_ids' => $categoryIds
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            $debugContent .= "\n=== ERROR OCCURRED ===\n";
            $debugContent .= "Error: " . $e->getMessage() . "\n";
            $debugContent .= "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            $debugContent .= "Trace:\n" . $e->getTraceAsString() . "\n";

            // Save debug log
            file_put_contents($debugLog, $debugContent);

            Log::channel('schoolclass')->error('Store failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $allData
            ]);

            return response()->json([
                'message' => 'Error storing school class: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'debug' => [
                    'log_file' => basename($debugLog),
                    'request_data' => $allData
                ]
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::channel('schoolclass')->info('Update School Class Request', ['id' => $id]);

        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'schoolclass' => 'required|string|max:255',
                'arm_id' => 'required|exists:schoolarm,id',
                'classcategoryid' => 'required|array|min:1',
                'classcategoryid.*' => 'exists:classcategories,id',
            ], [
                'schoolclass.required' => 'Please enter a school class name.',
                'arm_id.required' => 'Please select one arm.',
                'arm_id.exists' => 'The selected arm does not exist.',
                'classcategoryid.required' => 'Please select at least one category.',
                'classcategoryid.*.exists' => 'One or more selected categories do not exist.',
            ]);

            $validator->after(function ($validator) use ($request, $id) {
                $armId = $request->arm_id;
                $exists = Schoolclass::where('schoolclass', $request->schoolclass)
                    ->where('arm', $armId)
                    ->where('id', '!=', $id)
                    ->exists();
                if ($exists) {
                    $arm = Schoolarm::find($armId);
                    $armName = $arm ? $arm->arm : 'Unknown';
                    $validator->errors()->add(
                        'schoolclass',
                        "The combination of class '{$request->schoolclass}' and arm '{$armName}' already exists."
                    );
                }
            });

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $schoolclass = Schoolclass::findOrFail($id);
            $schoolclass->schoolclass = $request->schoolclass;
            $schoolclass->arm = $request->arm_id;
            $schoolclass->description = $request->description ?? 'Null';
            $schoolclass->save();

            $schoolclass->classcategories()->sync($request->classcategoryid);

            $arm = Schoolarm::find($schoolclass->arm);
            $categories = Classcategory::whereIn('id', $request->classcategoryid)->get(['id', 'category']);

            $updatedRecord = [
                'id' => $schoolclass->id,
                'schoolclass' => $schoolclass->schoolclass,
                'arm_id' => $schoolclass->arm,
                'arm_name' => $arm ? $arm->arm : 'Unknown',
                'classcategories' => $categories->toArray(),
                'description' => $schoolclass->description,
                'updated_at' => $schoolclass->updated_at->toISOString(),
                'created_at' => $schoolclass->created_at->toISOString()
            ];

            DB::commit();

            Log::channel('schoolclass')->info('School class updated successfully', $updatedRecord);

            return response()->json([
                'message' => 'School class updated successfully!',
                'schoolclass' => $updatedRecord
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolclass')->error('Update failed:', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);

            return response()->json([
                'message' => 'Error updating school class: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::channel('schoolclass')->info('Destroy School Class Request', ['id' => $id]);

        DB::beginTransaction();
        try {
            $schoolclass = Schoolclass::findOrFail($id);

            // Detach categories
            $schoolclass->classcategories()->detach();

            // Delete from class teacher table
            ClassTeacher::where('schoolclassid', $id)->delete();

            // Delete the school class
            $schoolclass->delete();

            DB::commit();

            Log::channel('schoolclass')->info('School class deleted successfully', ['id' => $id]);

            return response()->json([
                'message' => 'School class deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolclass')->error('Destroy failed:', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);

            return response()->json([
                'message' => 'Error deleting school class: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteschoolclass(Request $request)
    {
        Log::channel('schoolclass')->info('Delete School Class AJAX Request', $request->all());

        DB::beginTransaction();
        try {
            $schoolclass = Schoolclass::find($request->schoolclassid);
            if ($schoolclass) {
                ClassTeacher::where('schoolclassid', $request->schoolclassid)->delete();
                $schoolclass->classcategories()->detach();
                $schoolclass->delete();

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'School class has been removed'
                ]);
            }

            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'School class not found'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('schoolclass')->error('Delete school class failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error deleting school class'
            ], 500);
        }
    }

    public function getArms($id)
    {
        Log::channel('schoolclass')->info('Get Arms Request', ['id' => $id]);
        try {
            $schoolClass = Schoolclass::findOrFail($id);
            $armIds = [$schoolClass->arm];
            return response()->json(['success' => true, 'armIds' => $armIds], 200);
        } catch (\Exception $e) {
            Log::channel('schoolclass')->error('Get arms failed:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch arms'], 500);
        }
    }
}
