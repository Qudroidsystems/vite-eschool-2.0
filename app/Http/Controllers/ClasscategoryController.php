<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\Request;
use App\Models\Classcategory;
use App\Models\SubAssessment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClasscategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View class-category|Create class-category|Update class-category|Delete class-category', ['only' => ['index']]);
        $this->middleware('permission:Create class-category', ['only' => ['store']]);
        $this->middleware('permission:Update class-category', ['only' => ['update', 'updateclasscategory']]);
        $this->middleware('permission:Delete class-category', ['only' => ['destroy', 'deleteclasscategory']]);
    }

    public function index(Request $request)
    {
        Log::info('Index Class Category Request:', $request->all());
        $pagetitle = "Class Category Management";
        $query = Classcategory::with('assessments.subAssessments');

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where('category', 'like', '%' . $search . '%')
                  ->orWhereHas('assessments', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('max_score', 'like', '%' . $search . '%');
                  });
        }

        $classcategories = $query->orderBy('category')->paginate(10);

        if ($request->ajax()) {
            return response()->json(['categories' => $classcategories->items()]);
        }

        return view('classcategories.index', compact('classcategories', 'pagetitle'));
    }

    public function store(Request $request)
    {
        Log::info('Store Class Category Request:', $request->all());

        $request->validate([
            'category' => 'required|string|max:255|unique:classcategories,category',
            'is_senior' => 'required|boolean',
            'assessments' => 'required|array|size:1',
            'assessments.0.name' => 'required|string|max:100',
            'assessments.0.sub_assessments' => 'required|array|min:1',
            'assessments.0.sub_assessments.*.name' => 'nullable|string|max:100',
            'assessments.0.sub_assessments.*.max_score' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $category = Classcategory::create([
                'category' => $request->input('category'),
                'is_senior' => $request->input('is_senior'),
            ]);

            $assessmentData = $request->input('assessments')[0];
            $subAssessments = $assessmentData['sub_assessments'];
            $avg = count($subAssessments) > 0 ? array_sum(array_column($subAssessments, 'max_score')) / count($subAssessments) : 0;

            $assessment = Assessment::create([
                'classcategory_id' => $category->id,
                'name' => $assessmentData['name'],
                'max_score' => $avg,
            ]);

            foreach ($subAssessments as $sub) {
                SubAssessment::create([
                    'assessment_id' => $assessment->id,
                    'name' => $sub['name'] ?? null,
                    'max_score' => $sub['max_score'],
                ]);
            }

            DB::commit();
            Log::info('Class Category Created:', $category->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Class category and assessment created successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating class category:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create class category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update Class Category Request:', ['id' => $id, 'data' => $request->all()]);

        $request->validate([
            'category' => "required|string|max:255|unique:classcategories,category,{$id}",
            'is_senior' => 'required|boolean',
            'assessments' => 'required|array|size:1',
            'assessments.0.name' => 'required|string|max:100',
            'assessments.0.sub_assessments' => 'required|array|min:1',
            'assessments.0.sub_assessments.*.name' => 'nullable|string|max:100',
            'assessments.0.sub_assessments.*.max_score' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $category = Classcategory::findOrFail($id);
            $category->update([
                'category' => $request->input('category'),
                'is_senior' => $request->input('is_senior'),
            ]);

            // Delete existing assessment and sub-assessments
            Assessment::where('classcategory_id', $id)->delete();

            // Create new assessment and sub-assessments
            $assessmentData = $request->input('assessments')[0];
            $subAssessments = $assessmentData['sub_assessments'];
            $avg = count($subAssessments) > 0 ? array_sum(array_column($subAssessments, 'max_score')) / count($subAssessments) : 0;

            $assessment = Assessment::create([
                'classcategory_id' => $category->id,
                'name' => $assessmentData['name'],
                'max_score' => $avg,
            ]);

            foreach ($subAssessments as $sub) {
                SubAssessment::create([
                    'assessment_id' => $assessment->id,
                    'name' => $sub['name'] ?? null,
                    'max_score' => $sub['max_score'],
                ]);
            }

            DB::commit();
            Log::info('Class Category Updated:', $category->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Class category and assessment updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating class category:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update class category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::info('Delete Class Category Request:', ['id' => $id]);
        try {
            $category = Classcategory::findOrFail($id);
            $category->delete(); // Assessments and sub-assessments are automatically deleted via cascade
            Log::info('Class Category Deleted:', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Class category and its assessment deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting class category:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete class category'
            ], 500);
        }
    }

    public function deleteclasscategory(Request $request)
    {
        Log::info('Delete Class Category AJAX Request:', $request->all());
        $request->validate(['classcategoryid' => 'required|exists:classcategories,id']);
        
        try {
            $category = Classcategory::findOrFail($request->classcategoryid);
            $category->delete(); // Assessments and sub-assessments are automatically deleted via cascade
            Log::info('Class Category Deleted via AJAX:', ['id' => $request->classcategoryid]);

            return response()->json([
                'success' => true,
                'message' => 'Class category and its assessment deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting class category via AJAX:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete class category'
            ], 500);
        }
    }

    public function updateclasscategory(Request $request)
    {
        Log::info('Update Class Category AJAX Request:', $request->all());

        $request->validate([
            'id' => 'required|exists:classcategories,id',
            'category' => "required|string|max:255|unique:classcategories,category,{$request->id}",
            'is_senior' => 'required|boolean',
            'assessments' => 'required|array|size:1',
            'assessments.0.name' => 'required|string|max:100',
            'assessments.0.sub_assessments' => 'required|array|min:1',
            'assessments.0.sub_assessments.*.name' => 'nullable|string|max:100',
            'assessments.0.sub_assessments.*.max_score' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $category = Classcategory::findOrFail($request->id);
            $category->update([
                'category' => $request->input('category'),
                'is_senior' => $request->input('is_senior'),
            ]);

            // Delete existing assessment and sub-assessments
            Assessment::where('classcategory_id', $request->id)->delete();

            // Create new assessment and sub-assessments
            $assessmentData = $request->input('assessments')[0];
            $subAssessments = $assessmentData['sub_assessments'];
            $avg = count($subAssessments) > 0 ? array_sum(array_column($subAssessments, 'max_score')) / count($subAssessments) : 0;

            $assessment = Assessment::create([
                'classcategory_id' => $category->id,
                'name' => $assessmentData['name'],
                'max_score' => $avg,
            ]);

            foreach ($subAssessments as $sub) {
                SubAssessment::create([
                    'assessment_id' => $assessment->id,
                    'name' => $sub['name'] ?? null,
                    'max_score' => $sub['max_score'],
                ]);
            }

            DB::commit();
            Log::info('Class Category Updated via AJAX:', $category->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Class category and assessment updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating class category via AJAX:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update class category: ' . $e->getMessage()
            ], 500);
        }
    }
}