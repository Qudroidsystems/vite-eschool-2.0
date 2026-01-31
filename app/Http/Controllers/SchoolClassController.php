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
        Log::info('Index School Class Request:', $request->all());
        $pagetitle = "School Class Management";

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
    }

    public function store(Request $request)
    {
        Log::info('Store School Class Request:', $request->all());

        DB::beginTransaction();
        try {
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
                Log::error('Validation failed for store school class:', ['errors' => $validator->errors()->all(), 'input' => $request->all()]);
                DB::rollBack();
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $createdRecords = [];
            $armIds = $request->arm_id;
            $categoryIds = $request->classcategoryid;
            $description = $request->description ?? 'Null';
            $categories = Classcategory::whereIn('id', $categoryIds)->get(['id', 'category']);

            Log::info('Creating school classes with:', [
                'armIds' => $armIds,
                'categoryIds' => $categoryIds,
                'schoolclass' => $request->schoolclass
            ]);

            foreach ($armIds as $armId) {
                $schoolclass = new Schoolclass();
                $schoolclass->schoolclass = $request->schoolclass;
                $schoolclass->arm = $armId;
                $schoolclass->description = $description;
                $schoolclass->save();

                Log::info('School class created:', [
                    'id' => $schoolclass->id,
                    'schoolclass' => $schoolclass->schoolclass,
                    'arm' => $schoolclass->arm
                ]);

                // Attach categories
                $schoolclass->classcategories()->attach($categoryIds);

                Log::info('Categories attached:', $categoryIds);

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
            }

            DB::commit();
            Log::info('School classes stored successfully:', $createdRecords);

            return response()->json([
                'message' => 'School class(es) added successfully!',
                'schoolclasses' => $createdRecords
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing school class:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Error storing school class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update School Class Request:', ['id' => $id, 'data' => $request->all()]);

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
                Log::error('Validation failed for update school class:', ['errors' => $validator->errors()->all(), 'input' => $request->all()]);
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
            Log::info('School class updated successfully:', $updatedRecord);

            return response()->json([
                'message' => 'School class updated successfully!',
                'schoolclass' => $updatedRecord
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating school class:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Error updating school class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::info('Delete School Class Request:', ['id' => $id]);

        DB::beginTransaction();
        try {
            $schoolclass = Schoolclass::findOrFail($id);

            // Delete from class teacher table
            ClassTeacher::where('schoolclassid', $id)->delete();

            // Detach categories
            $schoolclass->classcategories()->detach();

            // Delete the school class
            $schoolclass->delete();

            DB::commit();
            Log::info('School class deleted successfully:', ['id' => $id]);

            return response()->json([
                'message' => 'School class deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error deleting school class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteschoolclass(Request $request)
    {
        Log::info('Delete School Class AJAX Request:', ['schoolclassid' => $request->schoolclassid]);

        DB::beginTransaction();
        try {
            $schoolclass = Schoolclass::find($request->schoolclassid);
            if ($schoolclass) {
                // Delete from class teacher table
                ClassTeacher::where('schoolclassid', $request->schoolclassid)->delete();

                // Detach categories
                $schoolclass->classcategories()->detach();

                // Delete the school class
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
            Log::error('Error in deleteschoolclass:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error deleting school class'
            ], 500);
        }
    }

    public function getArms($id)
    {
        Log::info('Fetching arms for schoolclass', ['id' => $id]);
        try {
            $schoolClass = Schoolclass::findOrFail($id);
            $armIds = [$schoolClass->arm];
            return response()->json(['success' => true, 'armIds' => $armIds], 200);
        } catch (\Exception $e) {
            Log::error('Get arms error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch arms'], 500);
        }
    }
}
