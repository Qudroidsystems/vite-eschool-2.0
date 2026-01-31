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

        $query = Schoolclass::select(
            'schoolclass.schoolclass',
            'schoolclass.arm',
            'schoolarm.arm as arm_name',
            DB::raw('GROUP_CONCAT(DISTINCT classcategories.category ORDER BY classcategories.category SEPARATOR ", ") as all_categories'),
            DB::raw('GROUP_CONCAT(DISTINCT classcategories.id ORDER BY classcategories.id SEPARATOR ",") as category_ids'),
            DB::raw('GROUP_CONCAT(DISTINCT schoolclass.description SEPARATOR " | ") as description'),
            DB::raw('MIN(schoolclass.id) as first_id') // for edit/delete reference
        )
        ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
        ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
        ->groupBy('schoolclass.schoolclass', 'schoolclass.arm', 'schoolarm.arm')
        ->orderBy('schoolclass.schoolclass')
        ->orderBy('schoolarm.arm');

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('schoolclass.schoolclass', 'like', '%' . $search . '%')
                  ->orWhere('schoolarm.arm', 'like', '%' . $search . '%')
                  ->orWhere('classcategories.category', 'like', '%' . $search . '%')
                  ->orWhere('schoolclass.description', 'like', '%' . $search . '%');
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
            'description'       => 'nullable|string|max:1000',
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

        Log::info('VALIDATION PASSED - STARTING CREATION');

        $createdRecords = [];
        $createdIds     = [];

        DB::beginTransaction();

        try {
            $className    = trim($request->schoolclass);
            $armIds       = $request->input('arm_id', []);
            $categoryIds  = $request->input('classcategoryid', []);
            $description  = $request->filled('description') ? trim($request->description) : null;

            Log::info('Data summary', [
                'class_name'     => $className,
                'arms'           => $armIds,
                'categories'     => $categoryIds,
                'description'    => $description ? substr($description, 0, 100) . '...' : 'null',
            ]);

            foreach ($armIds as $armId) {
                foreach ($categoryIds as $catId) {
                    $exists = Schoolclass::where('schoolclass', $className)
                        ->where('arm', $armId)
                        ->where('classcategoryid', $catId)
                        ->exists();

                    if ($exists) {
                        Log::info('Skipped duplicate', [
                            'schoolclass' => $className,
                            'arm'         => $armId,
                            'category'    => $catId,
                        ]);
                        continue;
                    }

                    $record = new Schoolclass();
                    $record->schoolclass     = $className;
                    $record->arm             = $armId;
                    $record->classcategoryid = $catId;
                    $record->description     = $description;
                    $record->save();

                    Log::info('Record saved', ['id' => $record->id]);

                    $createdRecords[] = $record->id;
                    $createdIds[] = $record->id;
                }
            }

            DB::commit();

            Log::info('=== STORE SCHOOL CLASS SUCCESS ===', [
                'total_created' => count($createdRecords),
                'created_ids'   => $createdIds,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'message'       => 'Created ' . count($createdRecords) . ' class combinations successfully!',
                    'schoolclasses' => $createdRecords,
                    'created_ids'   => $createdIds,
                ], 201);
            }

            return redirect()->back()->with('success', 'School class combinations registered successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('=== STORE FAILED ===', [
                'message'     => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to save: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to save classes: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update School Class Request:', ['id' => $id, 'data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'schoolclass'     => 'required|string|max:255',
            'arm_id'          => 'required|exists:schoolarm,id',
            'classcategoryid' => 'required|exists:classcategories,id',
            'description'     => 'nullable|string|max:1000',
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
