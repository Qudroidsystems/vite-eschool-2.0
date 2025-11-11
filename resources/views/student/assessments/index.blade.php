@extends('layouts.master')

@section('content')
<style>
.assessment-card {
    border-left: 4px solid #007bff;
}
.sub-assessment {
    background-color: #f8f9fa;
    border-radius: 5px;
    padding: 10px;
    margin: 5px 0;
}
.score-badge {
    font-size: 0.875em;
}
.progress-report {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.chart-container {
    position: relative;
    height: 300px;
    margin: 20px 0;
}
.vibrant-colors .chartjs-render-monitor {
    background: transparent;
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <form method="GET" action="{{ route('assessments') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="term_id" class="form-label">Term</label>
                                <select name="term_id" id="term_id" class="form-select">
                                    <option value="">All Terms</option>
                                    @foreach($terms as $t)
                                        <option value="{{ $t->id }}" {{ $userSelectedTermId == $t->id ? 'selected' : '' }}>{{ $t->term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="session_id" class="form-label">Session</label>
                                <select name="session_id" id="session_id" class="form-select">
                                    <option value="">All Sessions</option>
                                    @foreach($sessions as $s)
                                        <option value="{{ $s->id }}" {{ $selectedSessionId == $s->id ? 'selected' : '' }}>{{ $s->session }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @php
                $selectedTermObj = $userSelectedTermId ? $terms->firstWhere('id', $userSelectedTermId) : null;
                $selectedTermName = $selectedTermObj ? $selectedTermObj->term : 'All Terms';
                $selectedSessionObj = $selectedSessionId ? $sessions->firstWhere('id', $selectedSessionId) : null;
                $selectedSessionName = $selectedSessionObj ? $selectedSessionObj->session : 'All Sessions';
            @endphp

            <!-- Student Info Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <h6><i class="bi bi-person-circle me-2"></i>Student</h6>
                                    <p class="text-muted mb-0">{{ $student->firstname }} {{ $student->lastname }}</p>
                                    <small class="text-primary">Admission No: {{ $student->admissionNo }}</small>
                                </div>
                                <div class="col-md-3">
                                    <h6><i class="bi bi-building me-2"></i>Class</h6>
                                    <p class="text-muted mb-0">{{ $class->schoolclass ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6><i class="bi bi-calendar3 me-2"></i>Term</h6>
                                    <p class="text-muted mb-0">{{ $selectedTermName }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6><i class="bi bi-calendar4-event me-2"></i>Session</h6>
                                    <p class="text-muted mb-0">{{ $selectedSessionName }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Report Summary -->
            @if(isset($subjectsWithAssessments) && $subjectsWithAssessments->isNotEmpty())
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card progress-report">
                        <div class="card-body">
                            <h5 class="card-title text-white mb-3">Progress Report</h5>
                            <div class="row text-center">
                                <div class="col-md-2">
                                    <h4 class="text-white">{{ $overallProgress['total_subjects'] ?? 0 }}</h4>
                                    <p class="mb-0">Total Subjects</p>
                                </div>
                                <div class="col-md-2">
                                    <h4 class="text-white">{{ $overallProgress['completed_subjects'] ?? 0 }}</h4>
                                    <p class="mb-0">Completed</p>
                                </div>
                                <div class="col-md-2">
                                    <h4 class="text-white">{{ $overallProgress['average_cum'] ?? 0 }}</h4>
                                    <p class="mb-0">Avg Score</p>
                                </div>
                                <div class="col-md-2">
                                    <h4 class="text-white">{{ $overallProgress['gpa'] ?? '-' }}</h4>
                                    <p class="mb-0">GPA</p>
                                </div>
                                <div class="col-md-2">
                                    <h4 class="text-white">{{ $overallProgress['cgpa'] ?? '-' }}</h4>
                                    <p class="mb-0">CGPA</p>
                                </div>
                                <div class="col-md-2">
                                    <h4 class="text-white">{{ $overallProgress['gpa_grade'] ?? '-' }}</h4>
                                    <p class="mb-0">GPA Grade</p>
                                </div>
                            </div>
                            <div class="row text-center mt-3">
                                <div class="col-md-3">
                                    <h6 class="text-white-50 mb-1">Total Grade Points</h6>
                                    <h5 class="text-white">{{ number_format($overallProgress['total_grade_points'] ?? 0, 1) }}</h5>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-white-50 mb-1">Calculated GPA</h6>
                                    <h5 class="text-white">{{ $overallProgress['calculated_gpa'] ?? 0 }}</h5>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-white-50 mb-1">Subjects Count</h6>
                                    <h5 class="text-white">{{ $overallProgress['num_subjects'] ?? 0 }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GPA Trend Line Chart -->
            @if(isset($gpaTrend) && !empty($gpaTrend))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">GPA Trend</h5>
                            <div class="chart-container">
                                <canvas id="gpaTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endif

            @if (session('error') || isset($error))
                <div class="alert alert-warning">{{ session('error') ?? $error }}</div>
            @endif

            @if(!isset($subjectsWithAssessments) || $subjectsWithAssessments->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No assessments available for the selected term and session. Check back later or contact your teacher.
                </div>
            @else
                <!-- Subjects Accordions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">My Subjects and Assessments for {{ $selectedTermName }}- {{ $selectedSessionName }}</h5>
                                <div class="accordion" id="subjectsAccordion">
                                    @foreach($subjectsWithAssessments as $index => $subject)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading{{ $index }}">
                                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                                    <i class="bi bi-book me-2"></i>{{ $subject['subject_name'] ?? 'N/A' }} ({{ $subject['subject_code'] ?? '' }})
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#subjectsAccordion">
                                                <div class="accordion-body">
                                                    <!-- Overall Performance -->
                                                    <div class="row mb-3 p-3 bg-light rounded">
                                                        <div class="col-md-2">
                                                            <h6>Total Score</h6>
                                                            <span class="badge bg-primary fs-6">{{ number_format($subject['total'] ?? 0, 2) }}</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h6>Brought Forward</h6>
                                                            <span class="badge bg-secondary fs-6">{{ number_format($subject['bf'] ?? 0, 2) }}</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h6>Cumulative</h6>
                                                            <span class="badge bg-info fs-6">{{ number_format($subject['cum'] ?? 0, 2) }}</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h6>Subject GPA</h6>
                                                            <span class="badge bg-warning fs-6">{{ number_format($subject['subject_gpa'] ?? 0, 1) }}</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h6>Grade</h6>
                                                            <span class="badge bg-success fs-6">{{ $subject['grade'] ?? '-' }}</span>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h6>Overall GPA</h6>
                                                            <span class="badge bg-primary fs-6">{{ $overallProgress['gpa'] ?? '-' }}</span>
                                                        </div>
                                                    </div>

                                                    @if(!empty($subject['remark'] ?? ''))
                                                        <div class="alert alert-info mb-3">
                                                            <strong>Remark:</strong> {{ $subject['remark'] }}
                                                        </div>
                                                    @endif

                                                    @if(($subject['position'] ?? '-') !== '-')
                                                        <div class="alert alert-secondary mb-3">
                                                            <strong>Position in Class:</strong> {{ $subject['position'] }}
                                                        </div>
                                                    @endif

                                                    <!-- Assessment Chart Visualization -->
                                                    @if(isset($subject['assessments']) && $subject['assessments']->isNotEmpty())
                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <h6 class="mb-2">Assessments Visualization</h6>
                                                            <div class="chart-container">
                                                                <canvas id="assessmentChart{{ $index }}"></canvas>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <!-- Assessments Table -->
                                                    <h6 class="mb-2">Assessments</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Assessment</th>
                                                                    <th>Max Score</th>
                                                                    <th>Your Score</th>
                                                                    <th>Percentage</th>
                                                                    <th>Sub-Assessments</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if(isset($subject['assessments']) && $subject['assessments']->isNotEmpty())
                                                                    @foreach($subject['assessments'] as $assessment)
                                                                        <tr>
                                                                            <td>{{ $assessment['name'] ?? 'N/A' }}</td>
                                                                            <td>{{ $assessment['max_score'] ?? 0 }}</td>
                                                                            <td><span class="score-badge bg-primary">{{ number_format($assessment['score'] ?? 0, 2) }}</span></td>
                                                                            <td>
                                                                                <div class="progress" style="height: 20px;">
                                                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($assessment['percentage'] ?? 0) }}%" aria-valuenow="{{ $assessment['percentage'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                                                                        {{ ($assessment['percentage'] ?? 0) }}%
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                @if(isset($assessment['sub_assessments']) && $assessment['sub_assessments']->isNotEmpty())
                                                                                    <div class="sub-assessments-list">
                                                                                        @foreach($assessment['sub_assessments'] as $sub)
                                                                                            <div class="sub-assessment d-flex justify-content-between">
                                                                                                <small>{{ $sub['name'] ?? 'N/A' }}</small>
                                                                                                <small class="text-muted">{{ number_format($sub['score'] ?? 0, 2) }} / {{ $sub['max_score'] ?? 0 }} ({{ ($sub['percentage'] ?? 0) }}%)</small>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                @else
                                                                                    <span class="text-muted">None</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @else
                                                                    <tr>
                                                                        <td colspan="5" class="text-center text-muted">No assessments data available</td>
                                                                    </tr>
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(isset($subjectsWithAssessments) && $subjectsWithAssessments->isNotEmpty())
<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // GPA Trend Line Chart
    @if(isset($gpaTrend) && !empty($gpaTrend))
    const gpaCtx = document.getElementById('gpaTrendChart').getContext('2d');
    const gpaChart = new Chart(gpaCtx, {
        type: 'line',
        data: {
            labels: @json(array_keys($gpaTrend)),
            datasets: [{
                label: 'GPA',
                data: @json(array_values($gpaTrend)),
                borderColor: '#ff6b6b',
                backgroundColor: 'rgba(255, 107, 107, 0.2)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#333'
                    }
                }
            }
        }
    });
    @endif

    // Assessment Charts for each subject
    @foreach($subjectsWithAssessments as $index => $subject)
        @if(isset($subject['assessments']) && $subject['assessments']->isNotEmpty())
        const assessmentCtx{{ $index }} = document.getElementById('assessmentChart{{ $index }}').getContext('2d');
        const assessmentChart{{ $index }} = new Chart(assessmentCtx{{ $index }}, {
            type: 'bar',
            data: {
                labels: @json($subject['assessments']->pluck('name')->toArray()),
                datasets: [{
                    label: 'Your Score',
                    data: @json($subject['assessments']->pluck('score')->toArray()),
                    backgroundColor: [
                        '#ff6384',
                        '#36a2eb',
                        '#ffce56',
                        '#4bc0c0',
                        '#9966ff',
                        '#ff9f40',
                        '#ff6384'
                    ],
                    borderColor: [
                        '#ff6384',
                        '#36a2eb',
                        '#ffce56',
                        '#4bc0c0',
                        '#9966ff',
                        '#ff9f40',
                        '#ff6384'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#333'
                        }
                    }
                }
            }
        });
        @endif
    @endforeach
</script>
@endif
@endsection