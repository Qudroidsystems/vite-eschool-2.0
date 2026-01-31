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

        $query = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->select(
                'schoolclass.id',
                'schoolclass.schoolclass',
                'schoolarm.arm as arm_name',
                'schoolclass.arm as arm_id',
                'classcategories.category as classcategory',
                'schoolclass.classcategoryid',
                'schoolclass.updated_at'
            )
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('schoolclass.arm')
            ->orderBy('classcategories.category');

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('schoolclass.schoolclass', 'like', '%' . $search . '%')
                  ->orWhere('schoolarm.arm', 'like', '%' . $search . '%')
                  ->orWhere('classcategories.category', 'like', '%' . $search . '%');
            });
        }

        $all_classes = $query->paginate(100);
        $arms = Schoolarm::all();
        $classcategories = Classcategory::all();

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
        Log::info('=== STORE SCHOOL CLASS START ===', [
            'all_input' => $request->all(),
            'user_id'   => auth()->id() ?? 'guest',
        ]);

        $validator = Validator::make($request->all(), [
            'schoolclass'       => 'required|string|max:255',
            'arm_id'            => 'required|array|min:1',
            'arm_id.*'          => 'exists:schoolarm,id',
            'classcategoryid'   => 'required|array|min:1',
            'classcategoryid.*' => 'exists:classcategories,id',
        ], [
            'schoolclass.required'       => 'Please enter a school class name.',
            'arm_id.required'            => 'Please select at least one arm.',
            'classcategoryid.required'   => 'Please select at least one category.',
        ]);

        if ($validator->fails()) {
            Log::warning('VALIDATION FAILED', ['errors' => $validator->errors()->all()]);
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Log::info('VALIDATION PASSED');

        $createdRecords = [];
        $createdIds     = [];

        try {
            DB::beginTransaction();

            $className    = $request->schoolclass;
            $armIds       = $request->input('arm_id', []);
            $categoryIds  = $request->input('classcategoryid', []);
            $description  = $request->input('description', null);

            Log::info('Data to process', [
                'class_name'    => $className,
                'arm_count'     => count($armIds),
                'category_count'=> count($categoryIds),
            ]);

            foreach ($armIds as $armId) {
                foreach ($categoryIds as $catId) {
                    // Prevent duplicate combinations
                    $exists = Schoolclass::where('schoolclass', $className)
                        ->where('arm', $armId)
                        ->where('classcategoryid', $catId)
                        ->exists();

                    if ($exists) {
                        Log::info('Skipped duplicate', [
                            'schoolclass' => $className,
                            'arm_id'      => $armId,
                            'category_id' => $catId,
                        ]);
                        continue;
                    }

                    $schoolclass = new Schoolclass();
                    $schoolclass->schoolclass     = $className;
                    $schoolclass->arm             = $armId;
                    $schoolclass->classcategoryid = $catId;  // ← single category per record
                    $schoolclass->description     = $description;
                    $schoolclass->saveOrFail();

                    $arm      = Schoolarm::find($armId);
                    $category = Classcategory::find($catId);

                    $createdRecords[] = [
                        'id'             => $schoolclass->id,
                        'schoolclass'    => $schoolclass->schoolclass,
                        'arm_id'         => $schoolclass->arm,
                        'arm_name'       => $arm ? $arm->arm : 'Unknown',
                        'category_id'    => $catId,
                        'category_name'  => $category ? $category->category : 'Unknown',
                        'description'    => $schoolclass->description,
                        'created_at'     => $schoolclass->created_at->toISOString(),
                    ];

                    $createdIds[] = $schoolclass->id;

                    Log::info('Created record', ['id' => $schoolclass->id]);
                }
            }

            DB::commit();

            Log::info('=== STORE SCHOOL CLASS SUCCESS ===', [
                'total_created' => count($createdRecords),
                'ids'           => $createdIds,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'message'       => 'School class(es) added successfully!',
                    'schoolclasses' => $createdRecords,
                    'created_ids'   => $createdIds,
                ], 201);
            }

            return redirect()->back()->with('success', 'School class(es) registered successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ERROR STORING SCHOOL CLASS', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Error saving school class: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error saving school class');
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update School Class Request:', ['id' => $id, 'data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'schoolclass'     => 'required|string|max:255',
            'arm_id'          => 'required|exists:schoolarm,id',
            'classcategoryid' => 'required|exists:classcategories,id',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $schoolclass = Schoolclass::findOrFail($id);
            $schoolclass->schoolclass     = $request->schoolclass;
            $schoolclass->arm             = $request->arm_id;
            $schoolclass->classcategoryid = $request->classcategoryid;
            $schoolclass->description     = $request->description ?? null;
            $schoolclass->saveOrFail();

            if ($request->ajax()) {
                return response()->json(['message' => 'School class updated successfully!']);
            }
            return redirect()->route('schoolclass.index')->with('success', 'School class updated successfully.');
        } catch (\Exception $e) {
            Log::error('Update error', ['error' => $e->getMessage()]);
            if ($request->ajax()) {
                return response()->json(['message' => 'Error updating school class'], 500);
            }
            return redirect()->back()->with('error', 'Error updating school class');
        }
    }

    public function destroy($id)
    {
        Log::info('Delete School Class Request:', ['id' => $id]);

        try {
            $schoolclass = Schoolclass::findOrFail($id);
            ClassTeacher::where('schoolclassid', $id)->delete();
            $schoolclass->delete();

            return response()->json(['message' => 'School class deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Delete Error:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting school class'], 500);
        }
    }

    public function deleteschoolclass(Request $request)
    {
        Log::info('Delete School Class AJAX Request:', ['schoolclassid' => $request->schoolclassid]);

        $schoolclass = Schoolclass::find($request->schoolclassid);
        if ($schoolclass) {
            DB::table('classteacher')->where('schoolclassid', $request->schoolclassid)->delete();
            $schoolclass->delete();
            return response()->json([
                'success' => true,
                'message' => 'School class has been removed'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'School class not found'
        ], 404);
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
