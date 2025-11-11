<?php
namespace App\Http\Controllers;
use App\Models\Assessment;
use App\Models\Broadsheets;
use App\Models\Schoolclass;
use App\Models\Subjectclass;
use Illuminate\Http\Request;
use App\Models\SubAssessment;
use App\Models\BroadsheetsMock;
use App\Models\PromotionStatus;
use App\Models\BroadsheetRecord;
use App\Exports\MarksSheetExport;
use App\Imports\ScoresheetImport;
use App\Exports\RecordsheetExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\BroadsheetRecordMock;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MockMarksSheetExport;
use App\Exports\MockRecordsheetExport;
use Illuminate\Support\Facades\Storage;
use App\Models\BroadsheetAssessmentScore;
use App\Models\BroadsheetSubAssessmentScore;
class MyScoreSheetController extends Controller
{
   
      public function index(Request $request)
    {
        $pagetitle = 'My Scoresheets';
        $broadsheets = collect();
        Log::info('Index session:', $request->session()->all());
        if (!$request->ajax()) {
            $termId = $request->query('termid', 'ALL');
            $sessionId = $request->query('sessionid', 'ALL');
            if ($termId !== 'ALL' && $sessionId !== 'ALL') {
                $broadsheets = $this->getBroadsheets($request->user()->id, $termId, $sessionId);
                Log::info('Index broadsheets count:', ['count' => $broadsheets->count()]);
            }
        }
        if ($request->ajax()) {
            $termId = $request->input('termid', 'ALL');
            $sessionId = $request->input('sessionid', 'ALL');
            if ($termId === 'ALL' || $sessionId === 'ALL') {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select both term and session.',
                ], 422);
            }
            $broadsheets = $this->getBroadsheets($request->user()->id, $termId, $sessionId);
            return response()->json([
                'success' => true,
                'data' => [
                    'broadsheets' => $broadsheets,
                ],
            ]);
        }
        return view('subjectscoresheet.index', compact('pagetitle', 'broadsheets'));
    }
    public function subjectscoresheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid)
    {
        Log::info('Subjectscoresheet parameters:', compact('schoolclassid', 'subjectclassid', 'staffid', 'termid', 'sessionid'));
        session([
            'schoolclass_id' => $schoolclassid,
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
        ]);
        // Initial broadsheets fetch to check data
        $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);
        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
        $assessments = collect(); // Default empty
        if ($broadsheets->isNotEmpty() && $schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->orderBy('id') // or add 'order' field if exists
                ->get();
            // Update metrics and positions
            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid);
            // Compute and update totals, cums, grades, remarks dynamically
            $this->computeDynamicTotals($broadsheets, $assessments, $schoolclass, $termid, $sessionid);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateClassPositions($schoolclassid, $termid, $sessionid);
            // Refresh broadsheets to ensure updated positions
            $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);
            // Compute overall GPA and CGPA for each student (across subjects)
            $this->computeOverallGPAAndCGPA($broadsheets, $schoolclass, $termid, $sessionid);
            Log::info('Broadsheets after position update:', $broadsheets->map(function ($b) {
                return [
                    'id' => $b->id,
                    'student_id' => $b->student_id,
                    'admissionno' => $b->admissionno,
                    'cum' => $b->cum,
                    'gpa' => $b->gpa,
                    'cgpa' => $b->cgpa,
                    'gpa_grade' => $b->gpa_grade,
                    'subject_position_class' => $b->position,
                ];
            })->toArray());
            $pagetitle = sprintf(
                'Scoresheet for %s (%s) - %s %s - %s %s',
                $broadsheets->first()->subject,
                $broadsheets->first()->subject_code,
                $broadsheets->first()->schoolclass,
                $broadsheets->first()->arm,
                $broadsheets->first()->term,
                $broadsheets->first()->session
            );
        } else {
            $pagetitle = 'Subject Scoresheet';
            Log::warning('No broadsheets found for the given parameters', compact('schoolclassid', 'subjectclassid', 'staffid', 'termid', 'sessionid'));
        }
        $is_senior = $schoolclass && $schoolclass->classcategories->isNotEmpty() ? $schoolclass->classcategories->first()->is_senior ?? false : false;
        return view('subjectscoresheet.index', compact('broadsheets', 'pagetitle', 'is_senior', 'assessments'));
    }
    /**
     * Handle subassessment scoresheet view.
     */
    public function subassessmentScoresheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid, $subassessmentid)
    {
        Log::info('SubassessmentScoresheet parameters:', compact('schoolclassid', 'subjectclassid', 'staffid', 'termid', 'sessionid', 'subassessmentid'));
        session([
            'schoolclass_id' => $schoolclassid,
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
            'subassessment_id' => $subassessmentid, // For use in views/other methods
        ]);
        // Validate and fetch subassessment
        $subassessment = SubAssessment::findOrFail($subassessmentid); // Adjust model name if different
        $assessment = $subassessment->assessment; // Assuming belongsTo relation
        // Fetch broadsheets (load all subAssessmentScores, without scoping for compute)
        $broadsheets = $this->getSubassessmentBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid, $subassessmentid);
        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
        $assessments = collect([$subassessment]); // Focus on this subassessment; expand if needed for context
        $allAssessments = collect(); // For compute
        if ($broadsheets->isNotEmpty() && $schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            // Fetch full assessments for compute
            $allAssessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments') // Assuming hasMany relation
                ->orderBy('id')
                ->get();
            // Optionally fetch parent assessment or related subassessments for full context
            $fullAssessments = $allAssessments;
            $assessments = $fullAssessments->flatMap(function ($a) {
                return $a->subAssessments;
            })->where('id', $subassessmentid); // Or adjust as needed
            // Update metrics, totals, positions (similar to subjectscoresheet)
            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid);
            $this->computeDynamicTotals($broadsheets, $allAssessments, $schoolclass, $termid, $sessionid);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateClassPositions($schoolclassid, $termid, $sessionid);
            // Refresh broadsheets
            $broadsheets = $this->getSubassessmentBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid, $subassessmentid);
            // Compute overall GPA and CGPA for each student (across subjects)
            $this->computeOverallGPAAndCGPA($broadsheets, $schoolclass, $termid, $sessionid);
        } else {
            $pagetitle = 'Subassessment Scoresheet';
            Log::warning('No broadsheets found for subassessment', compact('schoolclassid', 'subjectclassid', 'staffid', 'termid', 'sessionid', 'subassessmentid'));
        }
        $pagetitle = sprintf(
            'Scoresheet for %s (%s) - %s %s - %s %s',
            $subassessment->name,
            $subassessment->max_score ?? 'N/A',
            $broadsheets->first()?->schoolclass ?? 'Class',
            $broadsheets->first()?->arm ?? '',
            $broadsheets->first()?->term ?? '',
            $broadsheets->first()?->session ?? ''
        );
        $is_senior = $schoolclass && $schoolclass->classcategories->isNotEmpty() ? $schoolclass->classcategories->first()->is_senior ?? false : false;
        return view('subjectscoresheet.subassessment-index', compact('broadsheets', 'pagetitle', 'is_senior', 'assessments', 'subassessment'));
    }
  
    /**
     * Handle assessment-specific scoresheet view (shows all sub-assessments under the assessment).
     */
    public function assessmentScoresheet($schoolclassid, $subjectclassid, $staffid, $termid, $sessionid, $assessmentid)
    {
        Log::info('AssessmentScoresheet parameters:', compact('schoolclassid', 'subjectclassid', 'staffid', 'termid', 'sessionid', 'assessmentid'));
        session([
            'schoolclass_id' => $schoolclassid,
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
            'assessment_id' => $assessmentid, // For use in views/other methods
        ]);
        // Validate and fetch assessment
        $assessment = Assessment::with('subAssessments')->findOrFail($assessmentid);
        // Fetch broadsheets (eager loads all assessmentScores and subAssessmentScores)
        $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);
        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassid);
        // Determine subAssessments with is_sub_item flag
        $realSubAssessments = $assessment->subAssessments;
        $is_sub_view = $realSubAssessments->isNotEmpty();
        if (!$is_sub_view) {
            // Treat as direct assessment
            $subAssessments = collect([$assessment]);
            $subAssessments->each(function ($sa) {
                $sa->is_sub_item = false;
            });
        } else {
            // Use real sub-assessments
            $subAssessments = $realSubAssessments;
            $subAssessments->each(function ($sa) {
                $sa->is_sub_item = true;
            });
        }
        $allAssessments = collect(); // For compute
        if ($broadsheets->isNotEmpty() && $schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            // Fetch all assessments for full context (totals computed over all)
            $allAssessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->orderBy('id')
                ->get();
            // Update metrics and positions (over all assessments)
            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid);
            // Compute and update totals, cums, grades, remarks dynamically (using all assessments)
            $this->computeDynamicTotals($broadsheets, $allAssessments, $schoolclass, $termid, $sessionid);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $sessionid);
            $this->updateClassPositions($schoolclassid, $termid, $sessionid);
            // Refresh broadsheets
            $broadsheets = $this->getBroadsheets($staffid, $termid, $sessionid, $schoolclassid, $subjectclassid);
            // Compute overall GPA and CGPA for each student (across subjects)
            $this->computeOverallGPAAndCGPA($broadsheets, $schoolclass, $termid, $sessionid);
        } else {
            Log::warning('No broadsheets found for assessment', compact('schoolclassid', 'subjectclassid', 'staffid', 'termid', 'sessionid', 'assessmentid'));
        }
        $pagetitle = sprintf(
            'Scoresheet for %s (%s) - %s %s - %s %s',
            $assessment->name,
            $assessment->max_score,
            $broadsheets->first()?->schoolclass ?? 'Class',
            $broadsheets->first()?->arm ?? '',
            $broadsheets->first()?->term ?? '',
            $broadsheets->first()?->session ?? ''
        );
        $is_senior = $schoolclass && $schoolclass->classcategories->isNotEmpty() ? $schoolclass->classcategories->first()->is_senior ?? false : false;
        return view('subjectscoresheet.assessment-index', compact('broadsheets', 'pagetitle', 'is_senior', 'subAssessments', 'assessment', 'is_sub_view'));
    }
    /**
     * Compute overall GPA (current term across all subjects) and CGPA (average of last 3 sessions' annual GPAs)
     * for each student in the broadsheets collection.
     */
    private function computeOverallGPAAndCGPA($broadsheets, $schoolclass, $termId, $sessionId)
    {
        if ($schoolclass->classcategories->isEmpty()) {
            Log::warning('No class category found for overall GPA/CGPA computation');
            return;
        }
        $isSenior = $schoolclass->classcategories->first()->is_senior ?? false;
        foreach ($broadsheets as $broadsheet) {
            $gpaCgpaData = $this->computeOverallForStudent(
                $broadsheet->student_id,
                $schoolclass,
                $termId,
                $sessionId,
                $isSenior
            );
            $broadsheet->gpa = round($gpaCgpaData['gpa'], 2);
            $broadsheet->cgpa = round($gpaCgpaData['cgpa'], 2);
            $broadsheet->gpa_grade = $gpaCgpaData['gpa_grade'] ?? 'F';
            $broadsheet->num_subjects = $gpaCgpaData['num_subjects'] ?? 0;
            $broadsheet->total_grade_points = $gpaCgpaData['total_grade_points'] ?? 0.0;
            $calculated_gpa = $broadsheet->num_subjects > 0 ? round($broadsheet->total_grade_points / $broadsheet->num_subjects, 2) : 0.0;
            Log::debug('Computed overall GPA/CGPA for student', [
                'student_id' => $broadsheet->student_id,
                'num_subjects' => $broadsheet->num_subjects,
                'total_grade_points' => $broadsheet->total_grade_points,
                'calculated_gpa' => $calculated_gpa,
                'gpa' => $broadsheet->gpa,
                'cgpa' => $broadsheet->cgpa,
                'gpa_grade' => $broadsheet->gpa_grade,
                'gpa_match' => abs($broadsheet->gpa - $calculated_gpa) < 0.01 ? 'match' : 'mismatch',
            ]);
        }
    }
    /**
     * Compute overall GPA and CGPA for a single student.
     *
     * @param int $studentId
     * @param \App\Models\Schoolclass $schoolclass
     * @param int $termId
     * @param int $sessionId
     * @param bool $isSenior
     * @return array ['gpa' => float, 'cgpa' => float, 'gpa_grade' => string]
     */
    private function computeOverallForStudent($studentId, $schoolclass, $termId, $sessionId, $isSenior)
    {
        // Current Term GPA and Grade (across all subjects) using total scores
        $currentTermBroadsheets = Broadsheets::where('term_id', $termId)
            ->whereHas('broadsheetRecord', function ($q) use ($studentId, $sessionId) {
                $q->where('student_id', $studentId)->where('session_id', $sessionId);
            })
            ->get(['total']);
        // Compute average total score for GPA Grade using totals
        $averageTotal = $currentTermBroadsheets->avg('total') ?? 0.0;
        $category = $schoolclass->classcategories->first();
        $gpaGrade = $category ? $category->calculateGrade($averageTotal) : $this->getDefaultGrade($averageTotal);
        $termGradePoints = $currentTermBroadsheets->map(function ($b) use ($isSenior) {
            return $this->getGradePoint($b->total, $isSenior); // Use total, not cum
        });
        $gpa = $termGradePoints->avg() ?? 0.0;
        $num_subjects = $currentTermBroadsheets->count();
        $total_grade_points = $termGradePoints->sum();
        // CGPA: Average of up to 3 most recent sessions' annual GPAs within the class category
        $annualGPAs = [];
        $studentSessionsInCategory = DB::table('broadsheet_records')
            ->join('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->join('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->where('broadsheet_records.student_id', $studentId)
            ->where('classcategories.is_senior', $isSenior)
            ->select('broadsheet_records.session_id')
            ->distinct()
            ->orderByDesc('broadsheet_records.session_id')
            ->limit(3)
            ->pluck('session_id');
        foreach ($studentSessionsInCategory as $targetSession) {
            $sessionAnnualGPAs = [];
            for ($t = 1; $t <= 3; $t++) {
                $termBroadsheets = Broadsheets::where('term_id', $t)
                    ->whereHas('broadsheetRecord', function ($q) use ($studentId, $targetSession) {
                        $q->where('student_id', $studentId)->where('session_id', $targetSession);
                    })
                    ->get(['total']); // Use total for past terms too
                $termGradePointsPast = $termBroadsheets->map(function ($b) use ($isSenior) {
                    return $this->getGradePoint($b->total, $isSenior); // Use total
                });
                $termGPA = $termGradePointsPast->avg() ?? 0.0;
                $sessionAnnualGPAs[] = $termGPA;
            }
            $annualGPA = collect($sessionAnnualGPAs)->avg() ?? 0.0;
            $annualGPAs[] = $annualGPA;
        }
        $cgpa = collect($annualGPAs)->avg() ?? 0.0;
        return [
            'gpa' => $gpa,
            'cgpa' => $cgpa,
            'gpa_grade' => $gpaGrade,
            'num_subjects' => $num_subjects,
            'total_grade_points' => $total_grade_points,
        ];
    }
    /**
     * Get grade point from score based on the class category scale.
     */
    private function getGradePoint($score, $isSenior = false)
    {
        if (!$isSenior) {
            // Junior scale
            if ($score >= 70) {
                return 5.0;
            } elseif ($score >= 60) {
                return 4.0;
            } elseif ($score >= 50) {
                return 3.0;
            } elseif ($score >= 40) {
                return 2.0;
            }
            return 0.0;
        } else {
            // Senior scale
            if ($score >= 75) {
                return 5.0;
            } elseif ($score >= 65) {
                return 4.0;
            } elseif ($score >= 50) {
                return 3.0;
            } elseif ($score >= 45) {
                return 2.0;
            } elseif ($score >= 40) {
                return 1.0;
            }
            return 0.0;
        }
    }
    /**
     * Compute dynamic totals, cums, grades, and remarks based on assessment scores.
     * Always uses the parent assessment score as authoritative.
     * Cum is calculated as (total + bf) / 2 for terms after term 1, or just total for term 1.
     */
    private function computeDynamicTotals($broadsheets, $assessments, $schoolclass, $termId, $sessionId)
    {
        $totalMax = $assessments->sum('max_score');
        foreach ($broadsheets as $broadsheet) {
            $assessmentScores = $broadsheet->assessmentScores ?? collect();
            $subAssessmentScores = $broadsheet->subAssessmentScores ?? collect(); // Added for sub scores
            $totalRaw = 0;
            foreach ($assessments as $assessment) {
                // Always use parent assessment score
                $scoreObj = $assessmentScores->where('assessment_id', $assessment->id)->first();
                $assessmentScore = $scoreObj ? $scoreObj->score : 0;
                $totalRaw += $assessmentScore;
            }
            // Get BF (brought forward from previous term)
            $newBf = $this->getPreviousTermCum(
                $broadsheet->student_id,
                $broadsheet->subject_id,
                $termId,
                $sessionId
            );
            // Calculate cum as (total + bf) / 2, or just total for term 1
            $newCum = $termId == 1 ? round($totalRaw, 2) : round(($totalRaw + $newBf) / 2, 2);
            $newGrade = $schoolclass && $schoolclass->classcategories->isNotEmpty()
                ? $schoolclass->classcategories->first()->calculateGrade($newCum)
                : $this->getDefaultGrade($newCum);
            $newRemark = $this->getRemark($newGrade);
            $significantChange = abs($broadsheet->total - $totalRaw) > 0.01 ||
                                abs($broadsheet->bf - $newBf) > 0.01 ||
                                abs($broadsheet->cum - $newCum) > 0.01 ||
                                $broadsheet->grade !== $newGrade ||
                                $broadsheet->remark !== $newRemark;
            if ($significantChange) {
                Log::info("computeDynamicTotals: Updating broadsheet {$broadsheet->id} due to significant changes", [
                    'old_values' => [
                        'total' => $broadsheet->total,
                        'bf' => $broadsheet->bf,
                        'cum' => $broadsheet->cum,
                        'grade' => $broadsheet->grade,
                        'remark' => $broadsheet->remark,
                    ],
                    'new_values' => [
                        'total' => $totalRaw,
                        'bf' => $newBf,
                        'cum' => $newCum,
                        'grade' => $newGrade,
                        'remark' => $newRemark,
                    ],
                ]);
                $broadsheet->total = $totalRaw;
                $broadsheet->bf = $newBf;
                $broadsheet->cum = $newCum;
                $broadsheet->grade = $newGrade;
                $broadsheet->remark = $newRemark;
                $broadsheet->save();
            }
        }
    }
    protected function getBroadsheets($staffId, $termId, $sessionId, $schoolClassId = null, $subjectClassId = null)
    {
        $query = Broadsheets::query()
            ->where('broadsheets.staff_id', $staffId)
            ->where('broadsheets.term_id', $termId)
            ->with(['assessmentScores', 'subAssessmentScores']) // Updated to load both
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->join('subjectclass', function ($join) use ($subjectClassId) {
                $join->on('subjectclass.id', '=', 'broadsheets.subjectclass_id')
                    ->on('broadsheet_records.subject_id', '=', 'subjectclass.subjectid')
                    ->on('broadsheet_records.schoolclass_id', '=', 'subjectclass.schoolclassid');
                if ($subjectClassId) {
                    $join->where('subjectclass.id', $subjectClassId);
                }
            })
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->where('broadsheet_records.session_id', $sessionId);
        if ($schoolClassId) {
            $query->where('schoolclass.id', $schoolClassId);
        }
        // Log the raw SQL query for debugging
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        Log::debug('getBroadsheets: Raw SQL query', [
            'sql' => $sql,
            'bindings' => $bindings,
        ]);
        $results = $query->get([
            'broadsheets.id',
            'studentRegistration.admissionNO as admissionno',
            'broadsheet_records.student_id as student_id',
            'studentRegistration.firstname as fname',
            'studentRegistration.lastname as lname',
            'studentRegistration.othername as mname',
            'subject.subject as subject',
            'subject.subject_code as subject_code',
            'broadsheet_records.subject_id',
            'schoolclass.schoolclass',
            'schoolclass.id as schoolclass_id',
            'schoolarm.arm',
            'schoolterm.term',
            'schoolsession.session',
            'subjectclass.id as subjectclid',
            'broadsheets.staff_id',
            'broadsheets.term_id',
            'broadsheet_records.session_id as sessionid',
            // Remove fixed CA and exam fields
            'studentpicture.picture',
            'broadsheets.total',
            'broadsheets.bf',
            'broadsheets.cum',
            'broadsheets.grade',
            'broadsheets.subject_position_class as position',
            'broadsheets.remark',
            'broadsheets.vettedstatus', // Added vettedstatus
        ])->sortBy('lastname');
        // No longer compute fixed CA totals here; done dynamically in subjectscoresheet
        Log::debug('getBroadsheets: Retrieved broadsheets', [
            'staff_id' => $staffId,
            'term_id' => $termId,
            'session_id' => $sessionId,
            'schoolclass_id' => $schoolClassId,
            'subjectclass_id' => $subjectClassId,
            'result_count' => $results->count(),
            'students' => $results->map(function ($item) {
                return [
                    'admissionno' => $item->admissionno,
                    'student_id' => $item->student_id,
                    'subject' => $item->subject,
                    'subject_id' => $item->subject_id,
                    'subjectclass_id' => $item->subjectclid,
                    'position' => $item->position,
                    'vettedstatus' => $item->vettedstatus, // Added to log
                ];
            })->toArray(),
            'subjects' => $results->pluck('subject')->unique()->values()->toArray(),
        ]);
        return $results;
    }
    /**
     * Fetch broadsheets scoped to a specific subassessment.
     */
    protected function getSubassessmentBroadsheets($staffId, $termId, $sessionId, $schoolClassId = null, $subjectClassId = null, $subassessmentId)
    {
        $query = Broadsheets::query()
            ->where('broadsheets.staff_id', $staffId)
            ->where('broadsheets.term_id', $termId)
            ->with(['subAssessmentScores']) // Load all for compute, filter in view if needed
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->join('subjectclass', function ($join) use ($subjectClassId) {
                $join->on('subjectclass.id', '=', 'broadsheets.subjectclass_id')
                    ->on('broadsheet_records.subject_id', '=', 'subjectclass.subjectid')
                    ->on('broadsheet_records.schoolclass_id', '=', 'subjectclass.schoolclassid');
                if ($subjectClassId) {
                    $join->where('subjectclass.id', $subjectClassId);
                }
            })
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->where('broadsheet_records.session_id', $sessionId);
        if ($schoolClassId) {
            $query->where('schoolclass.id', $schoolClassId);
        }
        // Select fields (reuse from your getBroadsheets method)
        $results = $query->get([
            'broadsheets.id',
            'studentRegistration.admissionNO as admissionno',
            'broadsheet_records.student_id as student_id',
            'studentRegistration.firstname as fname',
            'studentRegistration.lastname as lname',
            'studentRegistration.othername as mname',
            'subject.subject as subject',
            'subject.subject_code as subject_code',
            'broadsheet_records.subject_id',
            'schoolclass.schoolclass',
            'schoolclass.id as schoolclass_id',
            'schoolarm.arm',
            'schoolterm.term',
            'schoolsession.session',
            'subjectclass.id as subjectclid',
            'broadsheets.staff_id',
            'broadsheets.term_id',
            'broadsheet_records.session_id as sessionid',
            'studentpicture.picture',
            'broadsheets.total',
            'broadsheets.bf',
            'broadsheets.cum',
            'broadsheets.grade',
            'broadsheets.subject_position_class as position',
            'broadsheets.remark',
            'broadsheets.vettedstatus',
        ])->sortBy('lastname');
        Log::debug('getSubassessmentBroadsheets: Retrieved', [
            'count' => $results->count(),
            'subassessment_id' => $subassessmentId,
        ]);
        return $results;
    }
   
  
    /**
     * Handle single assessment score update (updated for sub-assessments with normalization).
     */
    public function singleUpdateScore(Request $request)
    {
        $validated = $request->validate([
            'broadsheet_id' => 'required|exists:broadsheets,id',
            'assessment_id' => 'required|exists:assessments,id', // Parent for sub
            'score' => 'required|numeric|min:0',
            'is_sub' => 'boolean',
            'sub_assessment_id' => 'nullable|exists:sub_assessments,id', // For single sub update
            'total' => 'nullable|numeric', // Optional raw total from JS
            'raw_total' => 'nullable|numeric', // Explicit raw total for direct saving
        ]);
        $broadsheetId = $validated['broadsheet_id'];
        $assessmentId = $validated['assessment_id'];
        $score = $validated['score'];
        $isSub = $validated['is_sub'] ?? false;
        $subAssessmentId = $validated['sub_assessment_id'] ?? null;
        $rawTotal = $validated['raw_total'] ?? $validated['total'] ?? null; // From JS if provided
        if ($isSub && !$subAssessmentId) {
            return response()->json([
                'success' => false,
                'message' => 'Sub-assessment ID required for sub-assessment updates.',
            ], 422);
        }
        // Fetch broadsheet and related data
        $broadsheet = Broadsheets::findOrFail($broadsheetId);
        $model = $isSub ? SubAssessment::findOrFail($subAssessmentId) : Assessment::findOrFail($assessmentId);
        if ($score > $model->max_score) {
            return response()->json([
                'success' => false,
                'message' => "Score cannot exceed maximum of {$model->max_score}.",
            ], 422);
        }
        $broadsheetRecord = BroadsheetRecord::find($broadsheet->broadSheet_record_id);
        $schoolclassId = $broadsheetRecord->schoolclass_id ?? 0;
        $termId = $broadsheet->term_id;
        $sessionId = $broadsheetRecord->session_id;
        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassId);
        $isSenior = $schoolclass && $schoolclass->classcategories->isNotEmpty() ? $schoolclass->classcategories->first()->is_senior ?? false : false;
        DB::transaction(function () use ($broadsheetId, $assessmentId, $score, $broadsheet, $isSub, $subAssessmentId, $broadsheetRecord) {
            // Update or create the assessment score
            if ($isSub) {
                BroadsheetSubAssessmentScore::updateOrCreate(
                    [
                        'broadsheet_id' => $broadsheetId,
                        'sub_assessment_id' => $subAssessmentId,
                        'assessment_id' => $assessmentId, // Link to parent
                    ],
                    ['score' => $score]
                );
                // Compute normalized parent score
                $assessment = Assessment::with('subAssessments')->find($assessmentId);
                if ($assessment) {
                    $subMaxSum = $assessment->subAssessments->sum('max_score');
                    $subTotal = BroadsheetSubAssessmentScore::where('broadsheet_id', $broadsheetId)
                        ->where('assessment_id', $assessmentId)
                        ->sum('score');
                    $normalized = $subMaxSum > 0 ? ($subTotal / $subMaxSum) * $assessment->max_score : 0;
                    $clampedNormalized = max(0, min($normalized, $assessment->max_score));
                    BroadsheetAssessmentScore::updateOrCreate(
                        [
                            'broadsheet_id' => $broadsheetId,
                            'assessment_id' => $assessmentId,
                        ],
                        ['score' => $clampedNormalized]
                    );
                    Log::info('Normalized parent score updated for single sub save', [
                        'broadsheet_id' => $broadsheetId,
                        'assessment_id' => $assessmentId,
                        'sub_assessment_id' => $subAssessmentId,
                        'sub_total' => $subTotal,
                        'sub_max_sum' => $subMaxSum,
                        'normalized_score' => $clampedNormalized,
                        'raw_total_from_js' => $rawTotal ?? 'not provided',
                    ]);
                }
            } else {
                BroadsheetAssessmentScore::updateOrCreate(
                    [
                        'broadsheet_id' => $broadsheetId,
                        'assessment_id' => $assessmentId,
                    ],
                    ['score' => $score]
                );
            }
            // Recompute totals, cum, grade, remark for this broadsheet
            $assessments = collect();
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $schoolclass->classcategories->pluck('id');
                $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                    ->with('subAssessments')
                    ->get();
            }
            $broadsheet->load(['assessmentScores', 'subAssessmentScores']);
            $this->computeDynamicTotals(collect([$broadsheet]), $assessments, $schoolclass, $broadsheet->term_id, $broadsheetRecord->session_id);
        });
        // Update positions and metrics (full class refresh)
        $this->updateClassMetrics($broadsheet->subjectclass_id, $broadsheet->staff_id, $termId, $sessionId);
        $this->updateSubjectPositions($broadsheet->subjectclass_id, $broadsheet->staff_id, $termId, $sessionId);
        $this->updateClassPositions($schoolclassId, $termId, $sessionId);
        // Compute overall GPA and CGPA for this student
        $gpaCgpaData = $this->computeOverallForStudent(
            $broadsheet->student_id,
            $schoolclass,
            $termId,
            $sessionId,
            $isSenior
        );
        $gpa = round($gpaCgpaData['gpa'], 2);
        $cgpa = round($gpaCgpaData['cgpa'], 2);
        $gpa_grade = $gpaCgpaData['gpa_grade'] ?? 'F';
        $num_subjects = $gpaCgpaData['num_subjects'] ?? 0;
        $total_grade_points = $gpaCgpaData['total_grade_points'] ?? 0.0;
        $broadsheet->refresh();
        Log::info('Single score updated', [
            'broadsheet_id' => $broadsheetId,
            'assessment_id' => $assessmentId,
            'is_sub' => $isSub,
            'sub_assessment_id' => $subAssessmentId,
            'score' => $score,
            'new_total' => $broadsheet->total,
            'new_cum' => $broadsheet->cum,
            'new_bf' => $broadsheet->bf,
            'gpa' => $gpa,
            'cgpa' => $cgpa,
            'gpa_grade' => $gpa_grade,
            'num_subjects' => $num_subjects,
            'total_grade_points' => $total_grade_points,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Score updated successfully!',
            'data' => [
                'total' => $broadsheet->total,
                'cum' => $broadsheet->cum,
                'bf' => $broadsheet->bf,
                'grade' => $broadsheet->grade,
                'remark' => $broadsheet->remark,
                'gpa' => $gpa,
                'gpa_grade' => $gpa_grade,
                'cgpa' => $cgpa,
                'num_subjects' => $num_subjects,
                'total_grade_points' => $total_grade_points,
            ],
        ]);
    }
    public function bulkUpdateScores(Request $request)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.id' => 'required|exists:broadsheets,id',
            'scores.*.assessments' => 'sometimes|array',
            'scores.*.total' => 'nullable|numeric', // Raw total from JS
            'scores.*.raw_total' => 'nullable|numeric', // Explicit raw total
            'term_id' => 'required|exists:schoolterm,id',
            'session_id' => 'required|exists:schoolsession,id',
            'subjectclass_id' => 'required|exists:subjectclass,id',
            'staff_id' => 'required|exists:users,id',
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'assessment_id' => 'required_if:is_sub,true|exists:assessments,id',
            'is_sub' => 'boolean',
        ]);
        $scores = $validated['scores'];
        $term_id = $validated['term_id'];
        $session_id = $validated['session_id'];
        $subjectclass_id = $validated['subjectclass_id'];
        $staff_id = $validated['staff_id'];
        $schoolclass_id = $validated['schoolclass_id'];
        $assessment_id = $validated['assessment_id'] ?? null;
        $is_sub = $validated['is_sub'] ?? false;
        // Fetch the school class and its class category once
        $schoolclass = Schoolclass::with('classcategories')->find($schoolclass_id);
        if (!$schoolclass) {
            Log::error('Schoolclass not found', ['schoolclass_id' => $schoolclass_id]);
            return response()->json([
                'success' => false,
                'message' => 'School class not found',
            ], 404);
        }
        $assessments = collect();
        if ($schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->get();
        }
        $updatedCount = 0;
        $errors = [];
        DB::transaction(function () use ($scores, $term_id, $session_id, $subjectclass_id, $staff_id, $schoolclass_id, $schoolclass, $assessments, $is_sub, $assessment_id, &$updatedCount, &$errors) {
            foreach ($scores as $scoreData) {
                $broadsheetId = $scoreData['id'];
                $broadsheet = Broadsheets::find($broadsheetId);
                if (!$broadsheet) {
                    $errors[] = "Broadsheet ID {$broadsheetId} not found.";
                    continue;
                }
                $assessmentsData = $scoreData['assessments'] ?? [];
                $rawTotal = $scoreData['raw_total'] ?? $scoreData['total'] ?? null;
                if (empty($assessmentsData)) {
                    // Skip if no assessments data
                    continue;
                }
                $localErrors = [];
                // Validate and update each assessment score
                foreach ($assessmentsData as $componentId => $inputScore) {
                    $componentId = (int) $componentId; // Ensure integer
                    if ($is_sub) {
                        $model = SubAssessment::find($componentId);
                        if (!$model) {
                            $localErrors[] = "SubAssessment ID {$componentId} invalid.";
                            continue;
                        }
                        if ($model->assessment_id != $assessment_id) {
                            $localErrors[] = "SubAssessment ID {$componentId} does not belong to assessment {$assessment_id}.";
                            continue;
                        }
                        $clampedScore = max(0, min($inputScore, $model->max_score));
                        if ($inputScore != $clampedScore) {
                            $localErrors[] = "Score for {$model->name} clamped to {$clampedScore} (max: {$model->max_score}).";
                        }
                        BroadsheetSubAssessmentScore::updateOrCreate(
                            [
                                'broadsheet_id' => $broadsheetId,
                                'sub_assessment_id' => $componentId,
                                'assessment_id' => $assessment_id,
                            ],
                            ['score' => $clampedScore]
                        );
                    } else {
                        $model = $assessments->where('id', $componentId)->first();
                        if (!$model) {
                            $localErrors[] = "Assessment ID {$componentId} invalid.";
                            continue;
                        }
                        $clampedScore = max(0, min($inputScore, $model->max_score));
                        if ($inputScore != $clampedScore) {
                            $localErrors[] = "Score for {$model->name} clamped to {$clampedScore} (max: {$model->max_score}).";
                        }
                        BroadsheetAssessmentScore::updateOrCreate(
                            [
                                'broadsheet_id' => $broadsheetId,
                                'assessment_id' => $componentId,
                            ],
                            ['score' => $clampedScore]
                        );
                    }
                }
                if (!empty($localErrors)) {
                    $errors[] = "Broadsheet {$broadsheetId}: " . implode(', ', $localErrors);
                    continue;
                }
                // If sub-assessments, compute normalized parent score
                if ($is_sub && $assessment_id) {
                    $assessment = $assessments->where('id', $assessment_id)->first();
                    if ($assessment) {
                        $subMaxSum = $assessment->subAssessments->sum('max_score');
                        $subTotal = BroadsheetSubAssessmentScore::where('broadsheet_id', $broadsheetId)
                            ->where('assessment_id', $assessment_id)
                            ->sum('score');
                        $normalized = $subMaxSum > 0 ? ($subTotal / $subMaxSum) * $assessment->max_score : 0;
                        $clampedNormalized = max(0, min($normalized, $assessment->max_score));
                        BroadsheetAssessmentScore::updateOrCreate(
                            [
                                'broadsheet_id' => $broadsheetId,
                                'assessment_id' => $assessment_id,
                            ],
                            ['score' => $clampedNormalized]
                        );
                        Log::info('Normalized parent score updated for bulk sub save', [
                            'broadsheet_id' => $broadsheetId,
                            'assessment_id' => $assessment_id,
                            'sub_total' => $subTotal,
                            'sub_max_sum' => $subMaxSum,
                            'normalized_score' => $clampedNormalized,
                            'raw_total_from_js' => $rawTotal ?? 'not provided',
                        ]);
                    }
                }
                // Recompute totals
                $broadsheetRecord = BroadsheetRecord::find($broadsheet->broadSheet_record_id);
                $broadsheet->load(['assessmentScores', 'subAssessmentScores']);
                $this->computeDynamicTotals(collect([$broadsheet]), $assessments, $schoolclass, $term_id, $session_id);
                $updatedCount++;
                Log::info('Updated broadsheet in bulk', [
                    'id' => $broadsheet->id,
                    'admissionno' => optional(optional($broadsheet->broadsheetRecord)->student)->admissionNO ?? 'N/A',
                    'total' => $broadsheet->total,
                    'bf' => $broadsheet->bf,
                    'cum' => $broadsheet->cum,
                    'grade' => $broadsheet->grade,
                    'remark' => $broadsheet->remark,
                ]);
            }
            // Update metrics and positions
            $this->updateClassMetrics($subjectclass_id, $staff_id, $term_id, $session_id);
            $this->updateSubjectPositions($subjectclass_id, $staff_id, $term_id, $session_id);
            $this->updateClassPositions($schoolclass_id, $term_id, $session_id);
        });
        // Fetch updated broadsheets
        $updatedBroadsheets = $this->getBroadsheets($staff_id, $term_id, $session_id, $schoolclass_id, $subjectclass_id);
        // Compute overall GPA and CGPA for updated broadsheets
        $this->computeOverallGPAAndCGPA($updatedBroadsheets, $schoolclass, $term_id, $session_id);
        $responseData = [
            'success' => true,
            'message' => "{$updatedCount} score(s) updated successfully!",
            'data' => [
                'broadsheets' => $updatedBroadsheets,
                'assessments' => $assessments,
            ],
        ];
        if (!empty($errors)) {
            $responseData['warnings'] = $errors;
        }
        Log::info('bulkUpdateScores completed', ['updated_count' => $updatedCount, 'errors' => $errors]);
        return response()->json($responseData, 200);
    }
   
    public function results()
    {
        try {
            $subjectclass_id = session('subjectclass_id');
            $schoolclass_id = session('schoolclass_id');
            $term_id = session('term_id');
            $session_id = session('session_id');
            if (!$subjectclass_id || !$schoolclass_id || !$term_id || !$session_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required session data',
                    'scores' => [],
                ], 400);
            }
            $schoolclass = Schoolclass::with('classcategories')->find($schoolclass_id);
            $assessments = collect();
            if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
                $categoryIds = $schoolclass->classcategories->pluck('id');
                $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                    ->with('subAssessments')
                    ->orderBy('id')->get();
            }
            $broadsheets = Broadsheets::where([
                'subjectclass_id' => $subjectclass_id,
                'term_id' => $term_id,
            ])
                ->with(['assessmentScores', 'subAssessmentScores'])
                ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
                ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
                ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
                ->where('broadsheet_records.session_id', $session_id)
                ->get([
                    'broadsheets.id',
                    'studentRegistration.admissionNO as admissionno',
                    'studentRegistration.firstname as fname',
                    'studentRegistration.lastname as lname',
                    'broadsheets.total',
                    'broadsheets.bf',
                    'broadsheets.cum',
                    'broadsheets.grade',
                    'broadsheets.subject_position_class as position',
                    'broadsheets.term_id',
                ]);
            // Compute overall GPA and CGPA
            $this->computeOverallGPAAndCGPA($broadsheets, $schoolclass, $term_id, $session_id);
            // Compute dynamic data for response
            $scoresData = $broadsheets->map(function ($broadsheet) use ($assessments) {
                $assessmentData = [];
                foreach ($assessments as $assessment) {
                    $scoreObj = $broadsheet->assessmentScores->where('assessment_id', $assessment->id)->first();
                    $assessmentData[$assessment->id] = [
                        'name' => $assessment->name,
                        'max_score' => $assessment->max_score,
                        'score' => $scoreObj ? $scoreObj->score : 0,
                    ];
                }
                return [
                    'id' => $broadsheet->id,
                    'admissionno' => $broadsheet->admissionno,
                    'fname' => $broadsheet->fname,
                    'lname' => $broadsheet->lname,
                    'assessments' => $assessmentData,
                    'total' => $broadsheet->total,
                    'bf' => $broadsheet->bf,
                    'cum' => $broadsheet->cum,
                    'gpa' => $broadsheet->gpa,
                    'gpa_grade' => $broadsheet->gpa_grade ?? 'F',
                    'cgpa' => $broadsheet->cgpa,
                    'grade' => $broadsheet->grade,
                    'position' => $broadsheet->position,
                    'num_subjects' => $broadsheet->num_subjects ?? 0,
                    'total_grade_points' => $broadsheet->total_grade_points ?? 0.0,
                ];
            });
            return response()->json([
                'success' => true,
                'assessments' => $assessments,
                'scores' => $scoresData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in results endpoint: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
    protected function updateClassMetrics($subjectclassid, $staffid, $termid, $sessionid)
    {
        // Fetch the subjectclass to get the subject_id
        $subjectClass = DB::table('subjectclass')
            ->where('id', $subjectclassid)
            ->first(['subjectteacherid']);
        if (!$subjectClass) {
            Log::warning('Subjectclass not found', ['subjectclass_id' => $subjectclassid]);
            return;
        }
        $subjectTeacher = DB::table('subjectteacher')
            ->where('id', $subjectClass->subjectteacherid)
            ->first(['subjectid']);
        if (!$subjectTeacher) {
            Log::warning('Subjectteacher not found', ['subjectteacherid' => $subjectClass->subjectteacherid]);
            return;
        }
        $subjectId = $subjectTeacher->subjectid;
        // Calculate class metrics (min, max, avg) for the subject across all students
        $metrics = Broadsheets::where('broadsheets.subjectclass_id', $subjectclassid)
            ->where('broadsheets.staff_id', $staffid)
            ->where('broadsheets.term_id', $termid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->where('broadsheet_records.session_id', $sessionid)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->select([
                DB::raw('MIN(broadsheets.cum) as class_min'),
                DB::raw('MAX(broadsheets.cum) as class_max'),
                DB::raw('SUM(broadsheets.cum) as cum_sum'),
                DB::raw('COUNT(broadsheets.id) as student_count')
            ])
            ->first();
        $classMin = $metrics->class_min ?? 0;
        $classMax = $metrics->class_max ?? 0;
        $classAvg = $metrics->student_count > 0 ? round($metrics->cum_sum / $metrics->student_count, 1) : 0;
        Log::info('Calculated class metrics', [
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
            'subject_id' => $subjectId,
            'class_min' => $classMin,
            'class_max' => $classMax,
            'class_avg' => $classAvg,
            'student_count' => $metrics->student_count,
            'cum_sum' => $metrics->cum_sum,
        ]);
        // Update all relevant broadsheet records with the calculated metrics
        Broadsheets::where('subjectclass_id', $subjectclassid)
            ->where('staff_id', $staffid)
            ->where('term_id', $termid)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->where('broadsheet_records.session_id', $sessionid)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->update([
                'cmin' => $classMin,
                'cmax' => $classMax,
                'avg' => $classAvg,
            ]);
        Log::info('Updated class metrics for broadsheets', [
            'subjectclass_id' => $subjectclassid,
            'staff_id' => $staffid,
            'term_id' => $termid,
            'session_id' => $sessionid,
            'subject_id' => $subjectId,
        ]);
    }
    protected function updateSubjectPositions($subjectclass_id, $staff_id, $term_id, $session_id)
    {
        Log::info('updateSubjectPositions called', compact('subjectclass_id', 'staff_id', 'term_id', 'session_id'));
        $broadsheets = Broadsheets::where('subjectclass_id', $subjectclass_id)
            ->where('staff_id', $staff_id)
            ->where('term_id', $term_id)
            ->where('broadsheet_records.session_id', $session_id)
            ->join('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->orderByDesc('broadsheets.cum')
            ->orderBy('broadsheets.id')
            ->get();
        if ($broadsheets->isEmpty()) {
            Log::warning('No broadsheets found for position update', compact('subjectclass_id', 'staff_id', 'term_id', 'session_id'));
            return;
        }
        $rank = 0;
        $lastCum = null;
        $lastPosition = 0;
        foreach ($broadsheets as $broadsheet) {
            $rank++;
            if ($lastCum !== null && $broadsheet->cum == $lastCum) {
                // Tied rank
            } else {
                $lastPosition = $rank;
                $lastCum = $broadsheet->cum;
            }
            if ($broadsheet->subject_position_class != $lastPosition) {
                $broadsheet->subject_position_class = $lastPosition;
                $broadsheet->save();
                Log::info('Updated position', [
                    'broadsheet_id' => $broadsheet->id,
                    'student_id' => $broadsheet->student_id,
                    'admissionno' => $broadsheet->admissionno,
                    'cum' => $broadsheet->cum,
                    'subject_position_class' => $lastPosition,
                ]);
            }
        }
        Log::info('Subject positions updated', ['total_records' => $broadsheets->count()]);
    }
    protected function updateClassPositions($schoolclassid, $termid, $sessionid)
    {
        $rank = 0;
        $lastScore = null;
        $rows = 0;
        $pos = PromotionStatus::where('schoolclassid', $schoolclassid)
            ->where('termid', $termid)
            ->where('sessionid', $sessionid)
            ->orderBy('subjectstotalscores', 'DESC')
            ->get();
        foreach ($pos as $row) {
            $rows++;
            if ($lastScore !== $row->subjectstotalscores) {
                $lastScore = $row->subjectstotalscores;
                $rank = $rows;
            }
            $position = match ($rank) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
            $rankPos = $rank . $position;
            PromotionStatus::where('id', $row->id)
                ->update(['position' => $rankPos]);
        }
    }
    public function edit($id)
    {
        $broadsheet = Broadsheets::where('broadsheets.id', $id)
            ->with('assessmentScores')
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->leftJoin('studentRegistration', 'studentRegistration.id', '=', 'broadsheet_records.student_id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('subjectclass', 'subjectclass.id', '=', 'broadsheets.subjectclass_id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'broadsheet_records.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('classcategories', 'classcategories.id', '=', 'schoolclass.classcategoryid')
            ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
            ->leftJoin('subject', 'subject.id', '=', 'broadsheet_records.subject_id')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'broadsheets.term_id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'broadsheet_records.session_id')
            ->first([
                'broadsheets.id as bid',
                'studentRegistration.admissionNO as admissionno',
                'studentRegistration.title',
                'studentRegistration.firstname as fname',
                'studentRegistration.lastname as lname',
                'studentpicture.picture',
                'broadsheets.total',
                'broadsheets.bf',
                'broadsheets.cum',
                'broadsheets.grade',
                'schoolterm.term',
                'schoolsession.session',
                'subject.subject',
                'subject.subject_code',
                'schoolclass.schoolclass',
                'schoolarm.id',
                'broadsheets.subject_position_class as position',
                'broadsheets.remark',
                'broadsheet_records.student_id',
                'broadsheets.staff_id',
                'broadsheets.term_id',
                'broadsheet_records.session_id as sessionid',
                'schoolclass.classcategoryid',
            ]);
        if (!$broadsheet) {
            return view('error', [
                'id' => $id,
                'title' => 'Not Found',
                'message' => 'Score not found.',
            ]);
        }
        $schoolclass = Schoolclass::with('classcategories')->find($broadsheet->schoolclass_id ?? 0); // From join
        $assessments = collect();
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->orderBy('id')->get();
        }
        // Compute overall GPA and CGPA
        $isSenior = $schoolclass && $schoolclass->classcategories->isNotEmpty() ? $schoolclass->classcategories->first()->is_senior ?? false : false;
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $gpaCgpaData = $this->computeOverallForStudent(
                $broadsheet->student_id,
                $schoolclass,
                $broadsheet->term_id,
                $broadsheet->sessionid,
                $isSenior
            );
            $broadsheet->gpa = round($gpaCgpaData['gpa'], 2);
            $broadsheet->cgpa = round($gpaCgpaData['cgpa'], 2);
            $broadsheet->gpa_grade = $gpaCgpaData['gpa_grade'] ?? 'F';
            $broadsheet->num_subjects = $gpaCgpaData['num_subjects'] ?? 0;
            $broadsheet->total_grade_points = $gpaCgpaData['total_grade_points'] ?? 0.0;
        }
        $pagetitle = sprintf(
            'Edit Score for %s %s - %s (%s)',
            $broadsheet->fname,
            $broadsheet->lname,
            $broadsheet->subject,
            $id
        );
        return view('scoresheet.edit', compact('broadsheet', 'pagetitle', 'assessments'));
    }
    public function update(Request $request, $id)
    {
        $broadsheet = Broadsheets::findOrFail($id);
        $termId = $broadsheet->term_id;
        $broadsheetRecordId = $broadsheet->broadSheet_record_id;
        $broadsheetRecord = BroadsheetRecord::find($broadsheetRecordId); // Fetch the record
        $schoolclassId = $broadsheetRecord->schoolclass_id ?? 0; // Assume from record
        // Validate dynamic assessments
        $schoolclass = Schoolclass::with('classcategories')->find($schoolclassId);
        $assessments = collect();
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $categoryIds = $schoolclass->classcategories->pluck('id');
            $assessments = Assessment::whereIn('classcategory_id', $categoryIds)
                ->with('subAssessments')
                ->get();
        }
        $validationRules = [];
        foreach ($assessments as $assessment) {
            $field = 'assessment_' . $assessment->id;
            $validationRules[$field] = 'nullable|numeric|min:0|max:' . $assessment->max_score;
        }
        $request->validate($validationRules);
        // Update assessment scores
        foreach ($assessments as $assessment) {
            $field = 'assessment_' . $assessment->id;
            $score = $request->input($field, 0);
            BroadsheetAssessmentScore::updateOrCreate(
                [
                    'broadsheet_id' => $id,
                    'assessment_id' => $assessment->id,
                ],
                ['score' => $score]
            );
        }
        // Recompute total, cum, grade, remark
        $broadsheet->load('assessmentScores');
        $this->computeDynamicTotals(collect([$broadsheet]), $assessments, $schoolclass, $termId, $broadsheetRecord->session_id);
        // Compute overall GPA and CGPA
        $isSenior = $schoolclass && $schoolclass->classcategories->isNotEmpty() ? $schoolclass->classcategories->first()->is_senior ?? false : false;
        if ($schoolclass && $schoolclass->classcategories->isNotEmpty()) {
            $gpaCgpaData = $this->computeOverallForStudent(
                $broadsheet->student_id,
                $schoolclass,
                $termId,
                $broadsheetRecord->session_id,
                $isSenior
            );
            $broadsheet->gpa = round($gpaCgpaData['gpa'], 2);
            $broadsheet->cgpa = round($gpaCgpaData['cgpa'], 2);
            $broadsheet->gpa_grade = $gpaCgpaData['gpa_grade'] ?? 'F';
            $broadsheet->num_subjects = $gpaCgpaData['num_subjects'] ?? 0;
            $broadsheet->total_grade_points = $gpaCgpaData['total_grade_points'] ?? 0.0;
        }
        $this->updateClassMetrics($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
        $this->updateSubjectPositions($broadsheet->subjectclass_id, $broadsheet->staff_id, $broadsheet->term_id, $broadsheetRecord->session_id);
        $this->updateClassPositions($schoolclassId, $broadsheet->term_id, $broadsheetRecord->session_id);
        return redirect()->action(
            [self::class, 'subjectscoresheet'],
            [
                'schoolclassid' => $schoolclassId,
                'subjectclassid' => $broadsheet->subjectclass_id,
                'staffid' => $broadsheet->staff_id,
                'termid' => $termId,
                'sessionid' => $broadsheetRecord->session_id,
            ]
        )->with('success', 'Score updated successfully!');
    }
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $broadsheet = Broadsheets::findOrFail($id);
        $subjectclassid = $broadsheet->subjectclass_id;
        $staffid = $broadsheet->staff_id;
        $termid = $broadsheet->term_id;
        $broadsheetRecord = DB::table('broadsheet_records')
            ->where('id', $broadsheet->broadSheet_record_id)
            ->first();
        // Delete assessment scores
        BroadsheetAssessmentScore::where('broadsheet_id', $id)->delete();
        BroadsheetSubAssessmentScore::where('broadsheet_id', $id)->delete();
        $broadsheet->delete();
        if ($broadsheetRecord) {
            $this->updateClassMetrics($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
            $this->updateSubjectPositions($subjectclassid, $staffid, $termid, $broadsheetRecord->session_id);
            $this->updateClassPositions($broadsheetRecord->schoolclass_id, $termid, $broadsheetRecord->session_id);
        }
        return response()->json([
            'success' => true,
            'message' => 'Score deleted successfully!',
        ]);
    }
    protected function calculateJuniorGrade($score)
    {
        if ($score >= 70 && $score <= 100) {
            return 'A';
        } elseif ($score >= 60) {
            return 'B';
        } elseif ($score >= 50) {
            return 'C';
        } elseif ($score >= 40) {
            return 'D';
        }
        return 'F';
    }
    /**
     * Fallback grading logic when class category is not available
     */
    protected function getDefaultGrade($score)
    {
        if ($score >= 70 && $score <= 100) {
            return 'A';
        } elseif ($score >= 60) {
            return 'B';
        } elseif ($score >= 50) {
            return 'C';
        } elseif ($score >= 40) {
            return 'D';
        }
        return 'F';
    }
    protected function getRemark($grade)
    {
        $remarks = [
            'A' => 'Excellent',
            'B' => 'Very Good',
            'C' => 'Good',
            'D' => 'Pass',
            'F' => 'Fail',
            'A1' => 'Excellent',
            'B2' => 'Very Good',
            'B3' => 'Good',
            'C4' => 'Credit',
            'C5' => 'Credit',
            'C6' => 'Credit',
            'D7' => 'Pass',
            'E8' => 'Pass',
            'F9' => 'Fail',
        ];
        return $remarks[$grade] ?? 'Unknown';
    }
    protected function getPreviousTermCum($studentId, $subjectId, $termId, $sessionId)
    {
        if ($termId == 1) {
            Log::debug('getBroadsheets: Term 1, bf set to 0', [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
            ]);
            return 0;
        }
        $previousTerm = Broadsheets::where('broadsheet_records.student_id', $studentId)
            ->where('broadsheet_records.subject_id', $subjectId)
            ->where('broadsheets.term_id', $termId - 1)
            ->where('broadsheet_records.session_id', $sessionId)
            ->leftJoin('broadsheet_records', 'broadsheet_records.id', '=', 'broadsheets.broadSheet_record_id')
            ->value('broadsheets.cum');
        if (is_null($previousTerm)) {
            Log::warning('getBroadsheets: No previous term cum found', [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'term_id' => $termId - 1,
                'session_id' => $sessionId,
            ]);
            return 0;
        }
        $cum = round($previousTerm, 2);
        Log::debug('getBroadsheets: Fetched previous cum', [
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'term_id' => $termId - 1,
            'cum' => $cum,
        ]);
        return $cum;
    }
 
    public function import(Request $request)
    {
        // Note: ScoresheetImport needs modification to handle dynamic columns based on assessment names/ids
        // For now, assume it's updated externally
        // ... existing code
    }
    public function calculateGradePreview(Request $request)
    {
        $request->validate([
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'cum' => 'required|numeric|min:0|max:100',
        ]);
        $schoolclass = Schoolclass::with('classcategories')->findOrFail($request->schoolclass_id);
        $grade = $schoolclass->classcategories->isNotEmpty()
            ? $schoolclass->classcategories->first()->calculateGrade($request->cum)
            : $this->getDefaultGrade($request->cum);
        return response()->json(['grade' => $grade]);
    }
}