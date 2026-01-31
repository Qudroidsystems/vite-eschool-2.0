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
            ->select(
                'schoolclass.id',
                'schoolclass.schoolclass',
                'schoolarm.arm as arm_name',
                'schoolclass.arm as arm_id',
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
                  ->orWhere('schoolarm.arm', 'like', '%' . $search . '%')
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
        Log::info('=== STORE SCHOOL CLASS START ===', $request->all());

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
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
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
                $record = new Schoolclass();
                $record->schoolclass     = $className;
                $record->arm             = $armId;
                $record->classcategoryid = $categoriesString;
                $record->description     = $description;
                $record->save();

                $created[] = $record->id;
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Created ' . count($created) . ' records successfully!',
                    'ids'     => $created,
                ], 201);
            }

            return redirect()->back()->with('success', 'School class registered successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store failed', ['error' => $e->getMessage()]);
            if ($request->ajax()) {
                return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error saving class');
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
            $schoolclass->classcategoryid = implode(',', $request->classcategoryid);
            $schoolclass->description     = $request->description ?? null;
            $schoolclass->saveOrFail();

            if ($request->ajax()) {
                return response()->json(['message' => 'Updated successfully']);
            }
            return redirect()->route('schoolclass.index')->with('success', 'Updated successfully');
        } catch (\Exception $e) {
            Log::error('Update failed', ['error' => $e->getMessage()]);
            if ($request->ajax()) {
                return response()->json(['message' => 'Error updating'], 500);
            }
            return redirect()->back()->with('error', 'Error updating');
        }
    }

    public function destroy($id)
    {
        Log::info('Delete School Class Request:', ['id' => $id]);

        try {
            $schoolclass = Schoolclass::findOrFail($id);
            ClassTeacher::where('schoolclassid', $id)->delete();
            $schoolclass->delete();

            return response()->json(['message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Delete failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting'], 500);
        }
    }

    public function deleteschoolclass(Request $request)
    {
        Log::info('AJAX Delete Request:', $request->all());

        $schoolclass = Schoolclass::find($request->schoolclassid);
        if ($schoolclass) {
            DB::table('classteacher')->where('schoolclassid', $request->schoolclassid)->delete();
            $schoolclass->delete();
            return response()->json(['success' => true, 'message' => 'Removed']);
        }

        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    public function getArms($id)
    {
        try {
            $schoolClass = Schoolclass::findOrFail($id);
            return response()->json(['success' => true, 'armIds' => [$schoolClass->arm]]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch arms'], 500);
        }
    }
}
