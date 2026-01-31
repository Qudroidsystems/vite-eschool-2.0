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
    Log::info('STORE CALLED - RAW INPUT', $request->all());

    // Basic validation only - remove duplicates check for testing
    $validator = Validator::make($request->all(), [
        'schoolclass'       => 'required|string|max:255',
        'arm_id'            => 'required|array|min:1',
        'arm_id.*'          => 'exists:schoolarm,id',
        'classcategoryid'   => 'required|array|min:1',
        'classcategoryid.*' => 'exists:classcategories,id',
    ]);

    if ($validator->fails()) {
        Log::warning('VALIDATION FAILED', $validator->errors()->all());
        return response()->json([
            'message' => 'Validation failed',
            'errors'  => $validator->errors()
        ], 422);
    }

    Log::info('VALIDATION OK - STARTING CREATION');

    $created = [];
    $errors  = [];

    DB::beginTransaction();

    try {
        $className   = $request->schoolclass;
        $arms        = $request->arm_id;
        $categories  = $request->classcategoryid;
        $description = $request->description ?? null;

        Log::info('Processing', [
            'class'      => $className,
            'arms'       => $arms,
            'categories' => $categories,
        ]);

        foreach ($arms as $armId) {
            foreach ($categories as $catId) {
                $record = new Schoolclass();
                $record->schoolclass     = $className;
                $record->arm             = $armId;
                $record->classcategoryid = $catId;
                $record->description     = $description;

                Log::info('Attempting save', $record->toArray());

                $record->save();  // ← no OrFail for now, catch manually

                $created[] = $record->id;

                Log::info('Saved ID: ' . $record->id);
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Created ' . count($created) . ' records',
            'ids'     => $created,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('SAVE FAILED', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
            'last_record' => isset($record) ? $record->toArray() : null,
        ]);

        return response()->json([
            'message' => 'Database error: ' . $e->getMessage(),
            'debug'   => 'See laravel.log'
        ], 500);
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
