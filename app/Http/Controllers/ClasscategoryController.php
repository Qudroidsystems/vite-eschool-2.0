<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classcategory;
use App\Models\Assessment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        $query = Classcategory::with('assessments');

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
            'assessments' => 'required|array|min:1',
            'assessments.*.name' => 'required|string|max:100',
            'assessments.*.max_score' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $category = Classcategory::create([
                'category' => $request->input('category'),
                'is_senior' => $request->input('is_senior'),
            ]);

            foreach ($request->input('assessments') as $assessment) {
                Assessment::create([
                    'classcategory_id' => $category->id,
                    'name' => $assessment['name'],
                    'max_score' => $assessment['max_score'],
                ]);
            }

            DB::commit();
            Log::info('Class Category Created:', $category->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Class category and assessments created successfully'
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
            'assessments' => 'required|array|min:1',
            'assessments.*.name' => 'required|string|max:100',
            'assessments.*.max_score' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $category = Classcategory::findOrFail($id);
            $category->update([
                'category' => $request->input('category'),
                'is_senior' => $request->input('is_senior'),
            ]);

            // Delete existing assessments
            Assessment::where('classcategory_id', $id)->delete();

            // Create new assessments
            foreach ($request->input('assessments') as $assessment) {
                Assessment::create([
                    'classcategory_id' => $category->id,
                    'name' => $assessment['name'],
                    'max_score' => $assessment['max_score'],
                ]);
            }

            DB::commit();
            Log::info('Class Category Updated:', $category->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Class category and assessments updated successfully'
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
            $category->delete(); // Assessments are automatically deleted via cascade
            Log::info('Class Category Deleted:', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Class category and its assessments deleted successfully'
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
            $category->delete(); // Assessments are automatically deleted via cascade
            Log::info('Class Category Deleted via AJAX:', ['id' => $request->classcategoryid]);

            return response()->json([
                'success' => true,
                'message' => 'Class category and its assessments deleted successfully'
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
            'assessments' => 'required|array|min:1',
            'assessments.*.name' => 'required|string|max:100',
            'assessments.*.max_score' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $category = Classcategory::findOrFail($request->id);
            $category->update([
                'category' => $request->input('category'),
                'is_senior' => $request->input('is_senior'),
            ]);

            // Delete existing assessments
            Assessment::where('classcategory_id', $request->id)->delete();

            // Create new assessments
            foreach ($request->input('assessments') as $assessment) {
                Assessment::create([
                    'classcategory_id' => $category->id,
                    'name' => $assessment['name'],
                    'max_score' => $assessment['max_score'],
                ]);
            }

            DB::commit();
            Log::info('Class Category Updated via AJAX:', $category->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Class category and assessments updated successfully'
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