<?php

namespace App\Http\Controllers;

use App\Models\Schoolterm;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Studentclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\Principalscomment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Studentpersonalityprofile;

class MyPrincipalsCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View my-principals-comment', ['only' => ['index']]);
        $this->middleware('permission:Update my-principals-comment', ['only' => ['classBroadsheet', 'updateComments']]);
    }

    /**
     * List of classes assigned to the current principal/staff
     */
    public function index()
    {
        $pagetitle = "My Principal's Comment Assignments";

        $assignments = Principalscomment::where('staffId', Auth::id())
            ->join('schoolclass', 'principalscomments.schoolclassid', '=', 'schoolclass.id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolsession', 'principalscomments.sessionid', '=', 'schoolsession.id')
            ->leftJoin('schoolterm', 'principalscomments.termid', '=', 'schoolterm.id')
            ->select([
                'principalscomments.id',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as sclass',
                'schoolarm.arm as schoolarm',
                'schoolsession.session as session_name',
                'schoolterm.term as term_name',
                'principalscomments.updated_at'
            ])
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('schoolarm.arm')
            ->get();

        // Get current session and term for default links
        $currentSession = Schoolsession::where('status', 'Current')->first() ?? Schoolsession::latest()->first();
        $currentTerm = Schoolterm::latest()->first();

        return view('myprincipalscomment.index')
            ->with(compact('assignments', 'pagetitle', 'currentSession', 'currentTerm'));
    }

    /**
     * Show broadsheet for a class with Principal comment inputs + grades modal
     */
    public function classBroadsheet($schoolclassid, $sessionid, $termid)
    {
        // Strict authorization check with session & term
        $isAssigned = Principalscomment::where('staffId', Auth::id())
            ->where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid', $termid)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'You are not authorized to enter Principal comments for this class in this session and term.');
        }

        $pagetitle = "Principal's Comment & Class Broadsheet";

        // Students in the class for this session
        $students = Studentclass::where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->join('studentRegistration', 'studentRegistration.id', '=', 'studentclass.studentId')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->orderBy('studentRegistration.lastname')
            ->orderBy('studentRegistration.firstname')
            ->get([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentpicture.picture as picture',
            ]);

        // All subjects for this class/session/term
        $subjects = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->distinct()
            ->orderBy('subject.subject')
            ->pluck('subject.subject')
            ->toArray();

        // All scores
        $scores = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->get([
                'broadsheet_records.student_id',
                'subject.subject as subject_name',
                'broadsheets.total',
            ]);

        // Existing Principal comments for this exact session/term
        $profiles = Studentpersonalityprofile::where('schoolclassid', $schoolclassid)
            ->where('termid', $termid)
            ->where('sessionid', $sessionid)
            ->pluck('principalscomment', 'studentid')
            ->toArray();

        // Class info
        $schoolclass = Schoolclass::with('arm')->findOrFail($schoolclassid);
        $schoolclass->arm_name = $schoolclass->arm?->arm ?? '';

        $schoolterm = Schoolterm::find($termid)?->term ?? 'N/A';
        $schoolsession = Schoolsession::find($sessionid)?->session ?? 'N/A';

        // Fetch class category for grade calculation (junior/senior)
        $classCategory = $schoolclass->classcategory()->first();
        $isSenior = $classCategory?->is_senior ?? false;

        // Fetch scores with calculated grades
        $rawGrades = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->select([
                'broadsheet_records.student_id',
                'subject.subject as subject_name',
                'broadsheets.total',
            ])
            ->get();

        // Group and calculate grades with detailed analysis
        $studentGrades = [];
        $studentGradeAnalysis = []; // New array for detailed analysis
        $intelligentComments = []; // Array for intelligent comments
        
        foreach ($rawGrades as $row) {
            $total = $row->total ?? 0;
            $studentId = $row->student_id;
            $subjectName = $row->subject_name;
            
            // Initialize arrays if not exists
            if (!isset($studentGradeAnalysis[$studentId])) {
                $studentGradeAnalysis[$studentId] = [
                    'grades' => [],
                    'counts' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0],
                    'weak_subjects' => [] // Subjects with D or F
                ];
            }

            $grade = 'F';
            $gradeLetter = 'F'; // Just the letter (A, B, C, D, E, F)
            
            if ($isSenior) {
                if ($total >= 75) { $grade = 'A1'; $gradeLetter = 'A1'; }
                elseif ($total >= 70) { $grade = 'B2'; $gradeLetter = 'B2'; }
                elseif ($total >= 65) { $grade = 'B3'; $gradeLetter = 'B3'; }
                elseif ($total >= 60) { $grade = 'C4'; $gradeLetter = 'C4'; }
                elseif ($total >= 55) { $grade = 'C5'; $gradeLetter = 'C5'; }
                elseif ($total >= 50) { $grade = 'C6'; $gradeLetter = 'C6'; }
                elseif ($total >= 45) { $grade = 'D7'; $gradeLetter = 'D7'; }
                elseif ($total >= 40) { $grade = 'E8'; $gradeLetter = 'E8'; }
                else { $grade = 'F9'; $gradeLetter = 'F9'; }
            } else {
                if ($total >= 70) { $grade = 'A'; $gradeLetter = 'A'; }
                elseif ($total >= 60) { $grade = 'B'; $gradeLetter = 'B'; }
                elseif ($total >= 50) { $grade = 'C'; $gradeLetter = 'C'; }
                elseif ($total >= 40) { $grade = 'D'; $gradeLetter = 'D'; }
                else { $grade = 'F'; $gradeLetter = 'F'; }
            }

            // Store grade for display
            $studentGrades[$studentId][] = [
                'subject' => $subjectName,
                'score'   => $total,
                'grade'   => $grade,
                'grade_letter' => $gradeLetter
            ];
            
            // Update grade analysis
            $studentGradeAnalysis[$studentId]['grades'][] = [
                'subject' => $subjectName,
                'score' => $total,
                'grade' => $grade,
                'grade_letter' => $gradeLetter
            ];
            
            // Update grade counts
            if (isset($studentGradeAnalysis[$studentId]['counts'][$gradeLetter])) {
                $studentGradeAnalysis[$studentId]['counts'][$gradeLetter]++;
            }
            
            // Track weak subjects (D or F) with grade
            if ($gradeLetter === 'D' || $gradeLetter === 'F') {
                $studentGradeAnalysis[$studentId]['weak_subjects'][] = [
                    'subject' => $subjectName,
                    'grade' => $gradeLetter
                ];
            }
        }

        // Define regular comments for combining
        $regularComments = [
            'Excellent result, keep it up!',
            'A very good result, keep it up!',
            'Good result, keep it up!',
            "Average result, there's still room for improvement next term.",
            'You can do better next term.',
            'You need to sit up and be serious.',
            'Wake up and be serious.'
        ];

        // Process existing comments to check if they are combined
        foreach ($students as $student) {
            $studentId = $student->id;
            $existingComment = $profiles[$studentId] ?? '';
            
            // Check if the existing comment is already a combined comment
            $isCombinedComment = false;
            $baseComment = '';
            $intelligentPart = '';
            
            if ($existingComment) {
                // Check if this is a combined comment (contains a regular comment + intelligent comment)
                foreach ($regularComments as $regularComment) {
                    if (strpos($existingComment, $regularComment) === 0) {
                        // Found a regular comment at the beginning
                        $remaining = trim(substr($existingComment, strlen($regularComment)));
                        
                        // Check if there's additional content (likely intelligent comment)
                        if (!empty($remaining) && (strpos($remaining, "\n\n") === 0 || strpos($remaining, "\n") === 0)) {
                            $isCombinedComment = true;
                            $baseComment = $regularComment;
                            $intelligentPart = trim($remaining);
                            break;
                        }
                    }
                }
                
                // If not a combined comment, check if it's exactly one of the regular comments
                if (!$isCombinedComment && in_array($existingComment, $regularComments)) {
                    $baseComment = $existingComment;
                    $intelligentPart = '';
                }
                
                // If not a regular comment, check if it matches an intelligent comment pattern
                if (!$isCombinedComment && empty($baseComment)) {
                    // This might be just an intelligent comment or custom comment
                    $intelligentPart = $existingComment;
                }
            }
        }

        // Generate intelligent comments based on performance with student first names only
        foreach ($students as $student) {
            $studentId = $student->id;
            $studentFirstName = $student->fname; // Using only first name
            $analysis = $studentGradeAnalysis[$studentId] ?? ['counts' => [], 'weak_subjects' => []];
            
            $comment = '';
            $gradeSummary = '';
            
            // Build grade summary: "5 A's, 2 B's, 1 C, 1 D (Yoruba) and 1 F (French)"
            $gradeParts = [];
            foreach (['A', 'B', 'C', 'D', 'F'] as $gradeLetter) {
                $count = $analysis['counts'][$gradeLetter] ?? 0;
                if ($count > 0) {
                    $gradeParts[] = $count . " " . $gradeLetter . ($count > 1 ? "'s" : '');
                }
            }
            
            if (!empty($gradeParts)) {
                $gradeSummary = implode(', ', array_slice($gradeParts, 0, -1));
                if (count($gradeParts) > 1) {
                    $gradeSummary .= ' and ' . end($gradeParts);
                } else {
                    $gradeSummary = $gradeParts[0];
                }
            }
            
            // Determine overall performance comment
            $totalGrades = array_sum($analysis['counts']);
            $goodGrades = ($analysis['counts']['A'] ?? 0) + ($analysis['counts']['B'] ?? 0) + ($analysis['counts']['C'] ?? 0);
            $percentageGood = $totalGrades > 0 ? ($goodGrades / $totalGrades) * 100 : 0;
            
            // Generate intelligent comment with student first name and grade summary
            $intelligentComment = '';
            
            if (!empty($gradeSummary)) {
                $intelligentComment = $studentFirstName . " has " . $gradeSummary . ". ";
            }
            
            // Add performance assessment
            if ($percentageGood >= 80) {
                $intelligentComment .= "Excellent result, keep it up!";
            } elseif ($percentageGood >= 70) {
                $intelligentComment .= "A very good result, keep it up!";
            } elseif ($percentageGood >= 60) {
                $intelligentComment .= "Good result, keep it up!";
            } elseif ($percentageGood >= 50) {
                $intelligentComment .= "Average result, there's still room for improvement next term.";
            } elseif ($percentageGood >= 40) {
                $intelligentComment .= "You can do better next term.";
            } elseif ($percentageGood >= 30) {
                $intelligentComment .= "You need to sit up and be serious.";
            } else {
                $intelligentComment .= "Wake up and be serious.";
            }
            
            // Add subject-specific advice for weak subjects with subject names
            $weakSubjects = $analysis['weak_subjects'] ?? [];
            if (!empty($weakSubjects)) {
                $subjectList = [];
                foreach ($weakSubjects as $weak) {
                    $subjectList[] = $weak['subject'] . " (" . $weak['grade'] . ")";
                }
                
                if (count($subjectList) == 1) {
                    $intelligentComment .= "\n" . $studentFirstName . " should work harder to achieve a higher average in " . $subjectList[0] . ".";
                } elseif (count($subjectList) == 2) {
                    $intelligentComment .= "\n" . $studentFirstName . " should work harder to achieve a higher average in " . implode(' and ', $subjectList) . ".";
                } elseif (count($subjectList) > 2) {
                    $intelligentComment .= "\n" . $studentFirstName . " should work harder to achieve a higher average in " . implode(', ', array_slice($subjectList, 0, -1)) . " and " . end($subjectList) . ".";
                }
            }
            
            $intelligentComments[$studentId] = $intelligentComment;
        }

        return view('myprincipalscomment.classbroadsheet')
            ->with(compact(
                'students',
                'subjects',
                'scores',
                'profiles',
                'schoolclass',
                'schoolterm',
                'schoolsession',
                'schoolclassid',
                'sessionid',
                'termid',
                'pagetitle',
                'studentGrades',
                'studentGradeAnalysis',
                'intelligentComments'
            ));
    }

    /**
     * Save all Principal comments – only for the current session/term
     */
    public function updateComments(Request $request, $schoolclassid, $sessionid, $termid)
    {
        // Strict authorization
        $isAssigned = Principalscomment::where('staffId', Auth::id())
            ->where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid', $termid)
            ->exists();

        if (!$isAssigned) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'You are not authorized.');
        }

        try {
            $request->validate([
                'teacher_comments.*' => 'nullable|string|max:2000',
            ]);

            $comments = $request->input('teacher_comments', []);
            $updatedCount = 0;

            DB::transaction(function () use ($comments, $schoolclassid, $sessionid, $termid, &$updatedCount) {
                foreach ($comments as $studentId => $comment) {
                    $comment = $comment ? trim($comment) : null;
                    
                    if ($comment) {
                        // Check if we need to update or insert
                        $existing = Studentpersonalityprofile::where('studentid', $studentId)
                            ->where('schoolclassid', $schoolclassid)
                            ->where('sessionid', $sessionid)
                            ->where('termid', $termid)
                            ->first();

                        if ($existing) {
                            // Update existing record
                            if ($existing->principalscomment !== $comment) {
                                $existing->update([
                                    'staffid' => Auth::id(),
                                    'principalscomment' => $comment,
                                    'updated_at' => now(),
                                ]);
                                $updatedCount++;
                            }
                        } else {
                            // Create new record
                            Studentpersonalityprofile::create([
                                'studentid' => $studentId,
                                'schoolclassid' => $schoolclassid,
                                'sessionid' => $sessionid,
                                'termid' => $termid,
                                'staffid' => Auth::id(),
                                'principalscomment' => $comment,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $updatedCount++;
                        }
                    }
                }
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => $updatedCount > 0 
                        ? $updatedCount . ' comment(s) saved successfully' 
                        : 'No comments to save'
                ]);
            }

            return redirect()->back()->with('success', 
                $updatedCount > 0 
                    ? $updatedCount . ' principal comment(s) saved successfully' 
                    : 'No comments to save'
            );

        } catch (\Exception $e) {
            \Log::error('Principal comment save error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'schoolclassid' => $schoolclassid,
                'sessionid' => $sessionid,
                'termid' => $termid,
                'comments_count' => count($comments)
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Error saving comments: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error saving comments: ' . $e->getMessage());
        }
    }

    /**
     * Helper function to check if a comment is a combined comment
     */
    private function isCombinedComment($comment, $regularComments)
    {
        if (empty($comment)) return false;
        
        foreach ($regularComments as $regularComment) {
            if (strpos($comment, $regularComment) === 0) {
                $remaining = trim(substr($comment, strlen($regularComment)));
                if (!empty($remaining) && (strpos($remaining, "\n\n") === 0 || strpos($remaining, "\n") === 0)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Helper function to extract regular comment from combined comment
     */
    private function extractRegularComment($comment, $regularComments)
    {
        if (empty($comment)) return '';
        
        foreach ($regularComments as $regularComment) {
            if (strpos($comment, $regularComment) === 0) {
                return $regularComment;
            }
        }
        
        return '';
    }

    /**
     * Helper function to extract intelligent part from combined comment
     */
    private function extractIntelligentPart($comment, $regularComment)
    {
        if (empty($comment) || empty($regularComment)) return $comment;
        
        $remaining = trim(substr($comment, strlen($regularComment)));
        return trim($remaining, "\n\t\r ");
    }
}