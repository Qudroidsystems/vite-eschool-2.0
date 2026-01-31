<?php

namespace App\Http\Controllers;

use App\Models\Schoolarm;
use App\Models\Schoolclass;
use App\Models\ClassTeacher;
use Illuminate\Http\Request;
use App\Models\Classcategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        Log::channel('schoolclass')->info('=== SCHOOL CLASS INDEX REQUEST ===', $request->all());
        Log::channel('schoolclass')->info('User:', ['id' => auth()->id(), 'name' => auth()->user()->name]);

        $pagetitle = "School Class Management";

        try {
            // Get all data with relationships
            $query = Schoolclass::with(['arm', 'classcategories'])
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
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
                Log::channel('schoolclass')->info('Search query:', ['term' => $search]);
                $query->where(function ($q) use ($search) {
                    $q->where('schoolclass.schoolclass', 'like', '%' . $search . '%')
                      ->orWhere('schoolarm.arm', 'like', '%' . $search . '%')
                      ->orWhere('classcategories.category', 'like', '%' . $search . '%');
                });
            }

            $all_classes = $query->orderBy('schoolclass.schoolclass')->paginate(100);
            $arms = Schoolarm::all();
            $classcategories = Classcategory::all();

            Log::channel('schoolclass')->info('Data loaded:', [
                'classes_count' => $all_classes->count(),
                'total_classes' => $all_classes->total(),
                'arms_count' => $arms->count(),
                'categories_count' => $classcategories->count(),
                'first_class' => $all_classes->first() ? $all_classes->first()->toArray() : null,
                'first_arm' => $arms->first() ? $arms->first()->toArray() : null,
                'first_category' => $classcategories->first() ? $classcategories->first()->toArray() : null
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('schoolclass.index', compact('all_classes', 'arms', 'classcategories', 'pagetitle'))->render(),
                    'count' => $all_classes->count(),
                    'total' => $all_classes->total(),
                ]);
            }

            return view('schoolclass.index')
                ->with('all_classes', $all_classes)
                ->with('arms', $arms)
                ->with('classcategories', $classcategories)
                ->with('pagetitle', $pagetitle);

        } catch (\Exception $e) {
            Log::channel('schoolclass')->error('Index Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return back()->with('danger', 'Error loading school classes: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        Log::channel('schoolclass')->info('=== STORE SCHOOL CLASS REQUEST ===');
        Log::channel('schoolclass')->info('Request Data:', $request->all());
        Log::channel('schoolclass')->info('User:', ['id' => auth()->id(), 'name' => auth()->user()->name]);

        DB::beginTransaction();
        try {
            Log::channel('schoolclass')->info('Validating request data...');

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

            Log::channel('schoolclass')->info('Validator created, running validation...');

            $validator->after(function ($validator) use ($request) {
                Log::channel('schoolclass')->info('Running custom validation...');
                $armIds = $request->arm_id ?? [];
                Log::channel('schoolclass')->info('Checking existing combinations for arms:', $armIds);

                foreach ($armIds as $armId) {
                    $exists = Schoolclass::where('schoolclass', $request->schoolclass)
                        ->where('arm', $armId)
                        ->exists();

                    Log::channel('schoolclass')->info("Check for schoolclass: {$request->schoolclass}, arm: {$armId}, exists: " . ($exists ? 'yes' : 'no'));

                    if ($exists) {
                        $arm = Schoolarm::find($armId);
                        $armName = $arm ? $arm->arm : 'Unknown';
                        $validator->errors()->add(
                            'schoolclass',
                            "The combination of class '{$request->schoolclass}' and arm '{$armName}' already exists."
                        );
                        Log::channel('schoolclass')->warning("Duplicate combination found: {$request->schoolclass} + {$armName}");
                    }
                }
            });

            if ($validator->fails()) {
                Log::channel('schoolclass')->error('Validation failed:', [
                    'errors' => $validator->errors()->all(),
                    'input' => $request->all(),
                    'arm_id' => $request->arm_id,
                    'classcategoryid' => $request->classcategoryid
                ]);

                DB::rollBack();
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            Log::channel('schoolclass')->info('Validation passed!');

            $createdRecords = [];
            $armIds = $request->arm_id;
            $categoryIds = $request->classcategoryid;
            $description = $request->description ?? 'Null';

            Log::channel('schoolclass')->info('Creating school classes with:', [
                'schoolclass' => $request->schoolclass,
                'armIds' => $armIds,
                'categoryIds' => $categoryIds,
                'armCount' => count($armIds),
                'categoryCount' => count($categoryIds),
                'description' => $description
            ]);

            // Check if arms exist
            $existingArms = Schoolarm::whereIn('id', $armIds)->count();
            if ($existingArms != count($armIds)) {
                Log::channel('schoolclass')->error('Some arm IDs do not exist:', [
                    'requested' => $armIds,
                    'existing' => $existingArms
                ]);
                throw new \Exception("Some selected arms do not exist in the database");
            }

            // Check if categories exist
            $existingCategories = Classcategory::whereIn('id', $categoryIds)->count();
            if ($existingCategories != count($categoryIds)) {
                Log::channel('schoolclass')->error('Some category IDs do not exist:', [
                    'requested' => $categoryIds,
                    'existing' => $existingCategories
                ]);
                throw new \Exception("Some selected categories do not exist in the database");
            }

            $categories = Classcategory::whereIn('id', $categoryIds)->get(['id', 'category']);
            Log::channel('schoolclass')->info('Categories found:', $categories->toArray());

            foreach ($armIds as $armId) {
                Log::channel('schoolclass')->info("Creating school class for arm ID: {$armId}");

                $schoolclass = new Schoolclass();
                $schoolclass->schoolclass = $request->schoolclass;
                $schoolclass->arm = $armId;
                $schoolclass->description = $description;

                Log::channel('schoolclass')->info('School class model created:', [
                    'attributes' => $schoolclass->getAttributes(),
                    'original' => $schoolclass->getOriginal()
                ]);

                $saveResult = $schoolclass->save();

                if (!$saveResult) {
                    Log::channel('schoolclass')->error('Failed to save school class:', [
                        'schoolclass' => $request->schoolclass,
                        'arm' => $armId,
                        'model' => $schoolclass->toArray()
                    ]);
                    throw new \Exception("Failed to save school class for arm: {$armId}");
                }

                Log::channel('schoolclass')->info('School class saved successfully:', [
                    'id' => $schoolclass->id,
                    'schoolclass' => $schoolclass->schoolclass,
                    'arm' => $schoolclass->arm
                ]);

                // Attach categories
                Log::channel('schoolclass')->info('Attaching categories:', $categoryIds);

                try {
                    $attachResult = $schoolclass->classcategories()->attach($categoryIds);
                    Log::channel('schoolclass')->info('Categories attached successfully:', [
                        'schoolclass_id' => $schoolclass->id,
                        'category_ids' => $categoryIds,
                        'result' => $attachResult
                    ]);
                } catch (\Exception $e) {
                    Log::channel('schoolclass')->error('Failed to attach categories:', [
                        'error' => $e->getMessage(),
                        'schoolclass_id' => $schoolclass->id,
                        'category_ids' => $categoryIds
                    ]);
                    throw new \Exception("Failed to attach categories: " . $e->getMessage());
                }

                $arm = Schoolarm::find($armId);
                Log::channel('schoolclass')->info('Found arm:', $arm ? $arm->toArray() : 'Not found');

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

                Log::channel('schoolclass')->info('Record added to createdRecords:', end($createdRecords));
            }

            DB::commit();

            Log::channel('schoolclass')->info('=== STORE SUCCESS ===');
            Log::channel('schoolclass')->info('School classes stored successfully:', [
                'total_created' => count($createdRecords),
                'records' => $createdRecords
            ]);

            return response()->json([
                'message' => 'School class(es) added successfully!',
                'schoolclasses' => $createdRecords,
                'count' => count($createdRecords)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolclass')->error('=== STORE ERROR ===');
            Log::channel('schoolclass')->error('Error storing school class:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'input' => $request->all(),
                'arm_id' => $request->arm_id,
                'classcategoryid' => $request->classcategoryid
            ]);

            return response()->json([
                'message' => 'Error storing school class: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::channel('schoolclass')->info('=== UPDATE SCHOOL CLASS REQUEST ===');
        Log::channel('schoolclass')->info('Request Data:', ['id' => $id, 'data' => $request->all()]);

        DB::beginTransaction();
        try {
            Log::channel('schoolclass')->info('Validating update request...');

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

                Log::channel('schoolclass')->info("Checking duplicate: class={$request->schoolclass}, arm={$armId}, exclude={$id}, exists=" . ($exists ? 'yes' : 'no'));

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
                Log::channel('schoolclass')->error('Update validation failed:', [
                    'errors' => $validator->errors()->all(),
                    'input' => $request->all()
                ]);

                DB::rollBack();
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            Log::channel('schoolclass')->info('Finding school class with ID:', ['id' => $id]);
            $schoolclass = Schoolclass::findOrFail($id);

            Log::channel('schoolclass')->info('School class found:', $schoolclass->toArray());

            $schoolclass->schoolclass = $request->schoolclass;
            $schoolclass->arm = $request->arm_id;
            $schoolclass->description = $request->description ?? 'Null';

            Log::channel('schoolclass')->info('Updating school class with:', [
                'schoolclass' => $request->schoolclass,
                'arm' => $request->arm_id,
                'description' => $request->description ?? 'Null'
            ]);

            $saveResult = $schoolclass->save();

            if (!$saveResult) {
                Log::channel('schoolclass')->error('Failed to update school class');
                throw new \Exception("Failed to update school class");
            }

            Log::channel('schoolclass')->info('School class updated successfully');

            // Sync categories
            Log::channel('schoolclass')->info('Syncing categories:', $request->classcategoryid);
            $schoolclass->classcategories()->sync($request->classcategoryid);

            Log::channel('schoolclass')->info('Categories synced successfully');

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

            Log::channel('schoolclass')->info('=== UPDATE SUCCESS ===');
            Log::channel('schoolclass')->info('School class updated successfully:', $updatedRecord);

            return response()->json([
                'message' => 'School class updated successfully!',
                'schoolclass' => $updatedRecord
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolclass')->error('=== UPDATE ERROR ===');
            Log::channel('schoolclass')->error('Error updating school class:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'input' => $request->all(),
                'id' => $id
            ]);

            return response()->json([
                'message' => 'Error updating school class: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::channel('schoolclass')->info('=== DESTROY SCHOOL CLASS REQUEST ===');
        Log::channel('schoolclass')->info('Deleting school class ID:', ['id' => $id]);

        DB::beginTransaction();
        try {
            Log::channel('schoolclass')->info('Finding school class...');
            $schoolclass = Schoolclass::findOrFail($id);

            Log::channel('schoolclass')->info('School class found:', $schoolclass->toArray());

            // Check if class has any dependencies
            $teacherCount = ClassTeacher::where('schoolclassid', $id)->count();
            Log::channel('schoolclass')->info('Class teacher records:', ['count' => $teacherCount]);

            // Detach categories first
            Log::channel('schoolclass')->info('Detaching categories...');
            $schoolclass->classcategories()->detach();
            Log::channel('schoolclass')->info('Categories detached');

            // Delete from class teacher table
            if ($teacherCount > 0) {
                Log::channel('schoolclass')->info('Deleting class teacher records...');
                ClassTeacher::where('schoolclassid', $id)->delete();
                Log::channel('schoolclass')->info('Class teacher records deleted');
            }

            // Delete the school class
            Log::channel('schoolclass')->info('Deleting school class...');
            $deleteResult = $schoolclass->delete();

            if (!$deleteResult) {
                Log::channel('schoolclass')->error('Failed to delete school class');
                throw new \Exception("Failed to delete school class");
            }

            DB::commit();

            Log::channel('schoolclass')->info('=== DESTROY SUCCESS ===');
            Log::channel('schoolclass')->info('School class deleted successfully:', ['id' => $id]);

            return response()->json([
                'message' => 'School class deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolclass')->error('=== DESTROY ERROR ===');
            Log::channel('schoolclass')->error('Error deleting school class:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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
        Log::channel('schoolclass')->info('=== DELETE SCHOOL CLASS AJAX REQUEST ===');
        Log::channel('schoolclass')->info('Request:', ['schoolclassid' => $request->schoolclassid]);

        DB::beginTransaction();
        try {
            $schoolclass = Schoolclass::find($request->schoolclassid);

            if (!$schoolclass) {
                Log::channel('schoolclass')->error('School class not found:', ['id' => $request->schoolclassid]);
                return response()->json([
                    'success' => false,
                    'message' => 'School class not found'
                ], 404);
            }

            Log::channel('schoolclass')->info('School class found:', $schoolclass->toArray());

            // Delete from class teacher table
            ClassTeacher::where('schoolclassid', $request->schoolclassid)->delete();
            Log::channel('schoolclass')->info('Class teacher records deleted');

            // Detach categories
            $schoolclass->classcategories()->detach();
            Log::channel('schoolclass')->info('Categories detached');

            // Delete the school class
            $schoolclass->delete();
            Log::channel('schoolclass')->info('School class deleted');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'School class has been removed'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('schoolclass')->error('Error in deleteschoolclass:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $request->schoolclassid
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting school class: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getArms($id)
    {
        Log::channel('schoolclass')->info('=== GET ARMS REQUEST ===');
        Log::channel('schoolclass')->info('Fetching arms for schoolclass:', ['id' => $id]);

        try {
            $schoolClass = Schoolclass::findOrFail($id);
            $armIds = [$schoolClass->arm];

            Log::channel('schoolclass')->info('Arms found:', $armIds);

            return response()->json(['success' => true, 'armIds' => $armIds], 200);
        } catch (\Exception $e) {
            Log::channel('schoolclass')->error('Get arms error:', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);

            return response()->json(['message' => 'Failed to fetch arms: ' . $e->getMessage()], 500);
        }
    }
}
