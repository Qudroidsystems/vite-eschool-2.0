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

        $currentSession = Schoolsession::where('status', 'Current')->first() ?? Schoolsession::latest()->first();
        $currentTerm = Schoolterm::latest()->first();

        return view('myprincipalscomment.index')
            ->with(compact('assignments', 'pagetitle', 'currentSession', 'currentTerm'));
    }

    public function classBroadsheet($schoolclassid, $sessionid, $termid)
    {
        $isAssigned = Principalscomment::where('staffId', Auth::id())
            ->where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid', $termid)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'You are not authorized to enter Principal comments for this class in this session and term.');
        }

        $pagetitle = "Principal's Comment & Class Broadsheet";

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

        $subjects = Broadsheets::where('broadsheet_records.schoolclass_id', $schoolclassid)
            ->where('broadsheets.term_id', $termid)
            ->where('broadsheet_records.session_id', $sessionid)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadsheet_record_id')
            ->join('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->distinct()
            ->orderBy('subject.subject')
            ->pluck('subject.subject')
            ->toArray();

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

        $profiles = Studentpersonalityprofile::where('schoolclassid', $schoolclassid)
            ->where('termid', $termid)
            ->where('sessionid', $sessionid)
            ->pluck('principalscomment', 'studentid')
            ->toArray();

        $schoolclass = Schoolclass::with('arm')->findOrFail($schoolclassid);
        $schoolclass->arm_name = $schoolclass->arm?->arm ?? '';

        $schoolterm = Schoolterm::find($termid)?->term ?? 'N/A';
        $schoolsession = Schoolsession::find($sessionid)?->session ?? 'N/A';

        $classCategory = $schoolclass->classcategory()->first();
        $isSenior = $classCategory?->is_senior ?? false;

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

        $studentGrades = [];
        $studentGradeAnalysis = [];

        foreach ($rawGrades as $row) {
            $total = $row->total ?? 0;
            $studentId = $row->student_id;
            $subjectName = $row->subject_name;

            if (!isset($studentGradeAnalysis[$studentId])) {
                $studentGradeAnalysis[$studentId] = [
                    'grades' => [],
                    'counts' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0],
                    'weak_subjects' => []
                ];
            }

            // CORRECTED GRADE CALCULATION
            $grade = 'F';
            $gradeLetter = 'F';
            
            if ($isSenior) {
                // Senior grading system (WAEC/NECO style)
                if ($total >= 75 && $total <= 100) {
                    $grade = 'A1';
                    $gradeLetter = 'A';
                } elseif ($total >= 70) {
                    $grade = 'B2';
                    $gradeLetter = 'B';
                } elseif ($total >= 65) {
                    $grade = 'B3';
                    $gradeLetter = 'B';
                } elseif ($total >= 60) {
                    $grade = 'C4';
                    $gradeLetter = 'C';
                } elseif ($total >= 55) {
                    $grade = 'C5';
                    $gradeLetter = 'C';
                } elseif ($total >= 50) {
                    $grade = 'C6';
                    $gradeLetter = 'C';
                } elseif ($total >= 45) {
                    $grade = 'D7';
                    $gradeLetter = 'D';
                } elseif ($total >= 40) {
                    $grade = 'E8';
                    $gradeLetter = 'E';
                } else {
                    $grade = 'F9';
                    $gradeLetter = 'F';
                }
            } else {
                // Junior grading system
                if ($total >= 70 && $total <= 100) {
                    $grade = 'A';
                    $gradeLetter = 'A';
                } elseif ($total >= 60) {
                    $grade = 'B';
                    $gradeLetter = 'B';
                } elseif ($total >= 50) {
                    $grade = 'C';
                    $gradeLetter = 'C';
                } elseif ($total >= 40) {
                    $grade = 'D';
                    $gradeLetter = 'D';
                } else {
                    $grade = 'F';
                    $gradeLetter = 'F';
                }
            }

            $studentGrades[$studentId][] = [
                'subject' => $subjectName,
                'score'   => $total,
                'grade'   => $grade,           // Full grade (A1, B2, C4, etc. for senior)
                'grade_letter' => $gradeLetter // Letter for CSS classes (A, B, C, etc.)
            ];

            $studentGradeAnalysis[$studentId]['grades'][] = [
                'subject' => $subjectName,
                'score' => $total,
                'grade' => $grade,
                'grade_letter' => $gradeLetter
            ];

            $studentGradeAnalysis[$studentId]['counts'][$gradeLetter]++;

            if (in_array($gradeLetter, ['C', 'D', 'E', 'F'])) {
                $studentGradeAnalysis[$studentId]['weak_subjects'][] = [
                    'subject' => $subjectName,
                    'grade' => $grade,
                    'grade_letter' => $gradeLetter
                ];
            }
        }

        // Generate the 7 personalized standard comments for each student
        $standardPersonalizedComments = [];

        $baseTemplates = [
            "Excellent result {NAME}, keep it up!",
            "A very good result {NAME}, keep it up!",
            "Good result {NAME}, keep it up!",
            "Average result {NAME}, there's still room for improvement next term.",
            "{NAME}, you can do better next term.",
            "{NAME}, you need to sit up and be serious.",
            "{NAME}, wake up and be serious.",
        ];

        foreach ($students as $student) {
            $studentId = $student->id;
            $firstName = $student->fname;

            // Pronoun setup
            $pronoun = strtoupper($student->gender) === 'MALE' ? 'He' : 'She';
            $possessive = strtoupper($student->gender) === 'MALE' ? 'his' : 'her';

            // Weak subjects advice
            $weakSubjects = $studentGradeAnalysis[$studentId]['weak_subjects'] ?? [];
            $advice = '';

            if (!empty($weakSubjects)) {
                usort($weakSubjects, function($a, $b) {
                    $order = ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3];
                    return $order[$a['grade_letter']] <=> $order[$b['grade_letter']];
                });

                $subjectList = array_map(fn($ws) => strtoupper($ws['subject']) . " (" . $ws['grade'] . ")", $weakSubjects);

                $subjectsText = count($subjectList) == 1
                    ? $subjectList[0]
                    : (count($subjectList) == 2 ? implode(' and ', $subjectList) : implode(', ', array_slice($subjectList, 0, -1)) . " and " . end($subjectList));

                $advice = "\n\n$pronoun should work harder in $subjectsText to improve $possessive performance.";
            }

            $options = [];
            foreach ($baseTemplates as $template) {
                $comment = str_replace('{NAME}', $firstName, $template);
                $options[] = $comment . $advice;
            }

            $standardPersonalizedComments[$studentId] = $options;
        }

        // Intelligent (auto-generated) comment with name + pronoun advice
        $intelligentComments = [];
        foreach ($students as $student) {
            $studentId = $student->id;
            $firstName = $student->fname;
            $analysis = $studentGradeAnalysis[$studentId] ?? ['counts' => [], 'weak_subjects' => []];

            $gradeParts = [];
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $g) {
                $count = $analysis['counts'][$g] ?? 0;
                if ($count > 0) $gradeParts[] = "$count " . $g . ($count > 1 ? "'s" : '');
            }

            $gradeSummary = !empty($gradeParts)
                ? (count($gradeParts) == 1 ? $gradeParts[0] : implode(', ', array_slice($gradeParts, 0, -1)) . ' and ' . end($gradeParts))
                : 'no grades recorded';

            $totalGrades = array_sum($analysis['counts']);
            $goodGrades = ($analysis['counts']['A'] ?? 0) + ($analysis['counts']['B'] ?? 0);
            $percentageGood = $totalGrades > 0 ? ($goodGrades / $totalGrades) * 100 : 0;

            $baseComment = "{NAME}, wake up and be serious.";
            if ($percentageGood >= 80) $baseComment = "Excellent result {NAME}, keep it up!";
            elseif ($percentageGood >= 70) $baseComment = "A very good result {NAME}, keep it up!";
            elseif ($percentageGood >= 60) $baseComment = "Good result {NAME}, keep it up!";
            elseif ($percentageGood >= 50) $baseComment = "Average result {NAME}, there's still room for improvement next term.";
            elseif ($percentageGood >= 40) $baseComment = "{NAME}, you can do better next term.";
            elseif ($percentageGood >= 30) $baseComment = "{NAME}, you need to sit up and be serious.";

            $comment = "$firstName has $gradeSummary. " . str_replace('{NAME}', $firstName, $baseComment);

            // Pronoun-based advice
            $pronoun = strtoupper($student->gender) === 'MALE' ? 'He' : 'She';
            $possessive = strtoupper($student->gender) === 'MALE' ? 'his' : 'her';

            $weakSubjects = $analysis['weak_subjects'] ?? [];
            if (!empty($weakSubjects)) {
                usort($weakSubjects, function($a, $b) {
                    $order = ['F' => 0, 'E' => 1, 'D' => 2, 'C' => 3];
                    return $order[$a['grade_letter']] <=> $order[$b['grade_letter']];
                });

                $subjectList = array_map(fn($ws) => $ws['subject'] . " (" . $ws['grade'] . ")", $weakSubjects);

                $subjectsText = count($subjectList) == 1
                    ? $subjectList[0]
                    : (count($subjectList) == 2 ? implode(' and ', $subjectList) : implode(', ', array_slice($subjectList, 0, -1)) . " and " . end($subjectList));

                $comment .= "\n\n$pronoun should work harder in $subjectsText to improve $possessive performance.";
            }

            $intelligentComments[$studentId] = $comment;
        }

        // Student Analytics
        $studentTotals = [];
        foreach ($students as $student) {
            $sid = $student->id;
            $total = 0;
            $count = 0;
            foreach ($subjects as $subject) {
                $score = $scores->where('student_id', $sid)->where('subject_name', $subject)->first();
                if ($score) {
                    $total += $score->total;
                    $count++;
                }
            }
            $average = $count > 0 ? round($total / $count, 1) : 0;
            $studentTotals[$sid] = ['total' => $total, 'average' => $average, 'subjects' => $count];
        }

        $sortedStudents = $students->sortByDesc(fn($s) => $studentTotals[$s->id]['average'] ?? 0)->values();

        $positions = [];
        $rank = 1;
        $prevAvg = null;
        foreach ($sortedStudents as $index => $student) {
            $avg = $studentTotals[$student->id]['average'];
            if ($index > 0 && $avg < $prevAvg) $rank = $index + 1;
            $positions[$student->id] = $rank;
            $prevAvg = $avg;
        }

        function getPositionSuffix($num) {
            if ($num % 100 >= 11 && $num % 100 <= 13) return $num . 'th';
            return match ($num % 10) {
                1 => $num . 'st',
                2 => $num . 'nd',
                3 => $num . 'rd',
                default => $num . 'th',
            };
        }

        $classTotalScore = array_sum(array_column($studentTotals, 'total'));
        $classTotalSubjects = array_sum(array_column($studentTotals, 'subjects'));
        $classAverage = $classTotalSubjects > 0 ? round($classTotalScore / $classTotalSubjects, 1) : 0;

        $classAnalytics = [
            'average' => $classAverage,
            'total_students' => $students->count(),
        ];

        $studentAnalytics = [];
        foreach ($students as $student) {
            $sid = $student->id;
            $analysis = $studentGradeAnalysis[$sid] ?? ['counts' => []];
            $totals = $studentTotals[$sid];
            $position = $positions[$sid] ?? null;
            $studentAnalytics[$sid] = [
                'total_score' => $totals['total'],
                'average' => $totals['average'],
                'subjects' => $totals['subjects'],
                'position' => $position,
                'position_text' => $position ? getPositionSuffix($position) : '-',
                'grade_counts' => $analysis['counts'],
            ];
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
                'intelligentComments',
                'standardPersonalizedComments',
                'studentAnalytics',
                'classAnalytics',
                'isSenior'
            ));
    }

    public function updateComments(Request $request, $schoolclassid, $sessionid, $termid)
    {
        $isAssigned = Principalscomment::where('staffId', Auth::id())
            ->where('schoolclassid', $schoolclassid)
            ->where('sessionid', $sessionid)
            ->where('termid', $termid)
            ->exists();

        if (!$isAssigned) {
            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized: You are not assigned to enter comments for this class.'
            ], 403);
        }

        $request->validate(['teacher_comments.*' => 'nullable|string|max:2000']);

        $comments = $request->input('teacher_comments', []);
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($comments as $studentId => $comment) {
                $comment = $comment ? trim($comment) : null;
                
                \Log::info("Processing principal comment", [
                    'student_id' => $studentId,
                    'comment_length' => strlen($comment ?? ''),
                    'schoolclassid' => $schoolclassid,
                    'sessionid' => $sessionid,
                    'termid' => $termid,
                    'staff_id' => Auth::id()
                ]);

                $existing = Studentpersonalityprofile::where('studentid', $studentId)
                    ->where('schoolclassid', $schoolclassid)
                    ->where('sessionid', $sessionid)
                    ->where('termid', $termid)
                    ->first();

                if ($existing) {
                    if ($existing->principalscomment !== $comment) {
                        $existing->update(['staffid' => Auth::id(), 'principalscomment' => $comment]);
                        $updatedCount++;
                        \Log::info("Updated existing comment", ['student_id' => $studentId]);
                    }
                } elseif ($comment) {
                    Studentpersonalityprofile::create([
                        'studentid' => $studentId,
                        'schoolclassid' => $schoolclassid,
                        'sessionid' => $sessionid,
                        'termid' => $termid,
                        'staffid' => Auth::id(),
                        'principalscomment' => $comment,
                    ]);
                    $updatedCount++;
                    \Log::info("Created new comment", ['student_id' => $studentId]);
                }
            }

            DB::commit();

            $message = $updatedCount > 0 
                ? "$updatedCount comment(s) saved successfully" 
                : "No changes detected";

            return response()->json([
                'success' => true, 
                'message' => $message,
                'count' => $updatedCount
            ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving principals comments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => array_keys($comments)
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}