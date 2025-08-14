<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schoolterm;

class SchooltermController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View term|Create term|Update term|Delete term', ['only' => ['index']]);
        $this->middleware('permission:Create term', ['only' => ['store']]);
        $this->middleware('permission:Update term', ['only' => ['update', 'updateterm']]);
        $this->middleware('permission:Delete term', ['only' => ['destroy', 'deleteterm']]);
    }

    public function index(Request $request)
    {
        // \Log::info('Index Term Request:', $request->all());
        $pagetitle = "Term Management";
        $query = Schoolterm::query();

        if ($request->has('search')) {
            $query->where('term', 'like', '%' . $request->query('search') . '%');
        }

        $terms = $query->paginate(10);

        if ($request->ajax()) {
            return response()->json(['terms' => $terms->items()]);
        }

        return view('term.index')->with('terms', $terms)->with('pagetitle', $pagetitle);
    }

    public function store(Request $request)
    {
        // \Log::info('Store Term Request:', $request->all());
        $request->validate(['term' => 'required|string|max:255']);
        $checkterm = Schoolterm::where('term', $request->input('term'))->exists();
        if ($checkterm) {
            // \Log::warning('Term already taken:', ['term' => $request->input('term')]);
            return response()->json(['success' => false, 'message' => 'Term is already taken'], 422);
        }
        $term = Schoolterm::create($request->only('term'));
        // \Log::info('Term Created:', $term->toArray());
        return response()->json(['success' => true, 'message' => 'Term has been created successfully']);
    }

    public function update(Request $request, $id)
    {
        // \Log::info('Update Term Request:', ['id' => $id, 'data' => $request->all()]);
        $request->validate(['term' => 'required|string|max:255']);
        $checkterm = Schoolterm::where('term', $request->input('term'))->where('id', '!=', $id)->exists();
        if ($checkterm) {
            // \Log::warning('Term already taken:', ['term' => $request->input('term')]);
            return response()->json(['success' => false, 'message' => 'Term is already taken'], 422);
        }
        $term = Schoolterm::findOrFail($id);
        $term->update($request->only('term'));
        // \Log::info('Term Updated:', $term->toArray());
        return response()->json(['success' => true, 'message' => 'Term has been updated successfully']);
    }

    public function destroy($id)
    {
        // \Log::info('Delete Term Request:', ['id' => $id]);
        $term = Schoolterm::findOrFail($id);
        $term->delete();
        // \Log::info('Term Deleted:', ['id' => $id]);
        return response()->json(['success' => true, 'message' => 'Term has been deleted successfully']);
    }

    public function deleteterm(Request $request)
    {
        \Log::info('Delete Term AJAX Request:', $request->all());
        $request->validate(['termid' => 'required|exists:schoolterms,id']);
        $term = Schoolterm::findOrFail($request->termid);
        $term->delete();
        // \Log::info('Term Deleted via AJAX:', ['id' => $request->termid]);
        return response()->json(['success' => true, 'message' => 'Term has been deleted successfully']);
    }

    public function updateterm(Request $request)
    {
        // \Log::info('Update Term AJAX Request:', $request->all());
        $request->validate([
            'id' => 'required|exists:schoolterms,id',
            'term' => 'required|string|max:255'
        ]);
        $checkterm = Schoolterm::where('term', $request->input('term'))->where('id', '!=', $request->id)->exists();
        if ($checkterm) {
            // \Log::warning('Term already taken:', ['term' => $request->input('term')]);
            return response()->json(['success' => false, 'message' => 'Term is already taken'], 422);
        }
        $term = Schoolterm::findOrFail($request->id);
        $term->update($request->only('term'));
        // \Log::info('Term Updated via AJAX:', $term->toArray());
        return response()->json(['success' => true, 'message' => 'Term has been updated successfully']);
    }
}