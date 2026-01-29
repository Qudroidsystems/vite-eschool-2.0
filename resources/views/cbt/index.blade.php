@extends('layouts.master')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">My Exams</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('cbt.index') }}">Exams</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error / Success Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @can('View cbt-exam')

            <!-- Term & Session Selection Modal -->
            <div class="modal fade" id="termSessionModal" tabindex="-1" aria-labelledby="termSessionModalLabel" aria-hidden="true"
                 data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="termSessionModalLabel">Select Academic Period</h5>
                        </div>
                        <div class="modal-body">
                            <form id="termSessionForm">
                                <div class="mb-3">
                                    <label for="sessionSelect" class="form-label fw-bold">Session</label>
                                    <select class="form-select" id="sessionSelect" name="session" required>
                                        <option value="">-- Choose Session --</option>
                                        @foreach($sessions as $s)
                                            <option value="{{ $s->id }}"
                                                {{ $selectedSessionId == $s->id ? 'selected' : '' }}>
                                                {{ $s->session }}
                                                @if($s->status) ({{ $s->status }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="termSelect" class="form-label fw-bold">Term</label>
                                    <select class="form-select" id="termSelect" name="term" required>
                                        <option value="">-- Choose Term --</option>
                                        @foreach($terms as $t)
                                            <option value="{{ $t->id }}"
                                                {{ $selectedTermId == $t->id ? 'selected' : '' }}>
                                                {{ $t->term }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mt-3" id="loadExamsBtn">
                                    <i class="ri-search-line me-2"></i> Load My Exams
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exams List (hidden until term & session are selected) -->
            <div id="examsList" style="{{ (!$selectedTermId || !$selectedSessionId) ? 'display:none;' : '' }}">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">
                                        Available Exams
                                        <span class="badge bg-dark-subtle text-dark ms-1">{{ $exams->total() }}</span>
                                    </h5>
                                    <p class="text-muted mb-0 fs-6">
                                        For: <strong>{{ $student->firstname }} {{ $student->lastname }}</strong>
                                        <span class="fs-7">
                                            ({{ $class->schoolclass ?? 'N/A' }} -
                                             {{ $termObj->term ?? 'N/A' }} -
                                             {{ $sessionObj->session ?? 'N/A' }})
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    @include('cbt.partials.exams-table')
                                </div>

                                <!-- Pagination -->
                                <div class="row mt-3 align-items-center">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold">{{ $exams->firstItem() ?? 0 }}</span> to
                                            <span class="fw-semibold">{{ $exams->lastItem() ?? 0 }}</span> of
                                            <span class="fw-semibold">{{ $exams->total() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        {{ $exams->appends(request()->query())->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="row mt-3">
                    <div class="col-12">
                        <p class="text-muted">
                            Total Subjects Offered: <strong>{{ $totalreg }}</strong> |
                            Subjects Registered: <strong>{{ $reg }}</strong>
                        </p>
                    </div>
                </div>

            </div>

            @endcan

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('termSessionModal');
    const modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });

    // Show modal only if no term/session selected
    @if(!$selectedTermId || !$selectedSessionId)
        modal.show();
    @endif

    // Handle form submission
    document.getElementById('termSessionForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const term    = document.getElementById('termSelect').value;
        const session = document.getElementById('sessionSelect').value;

        if (!term || !session) {
            alert('Please select both Term and Session.');
            return;
        }

        // Reload page with selected filters
        window.location.href = `{{ route('cbt.index') }}?term=${term}&session=${session}`;
    });
});
</script>
@endsection
