@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
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
            <!-- End page title -->

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
            <div id="examsList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search exams">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Available Exams <span class="badge bg-dark-subtle text-dark ms-1">{{ $exams->total() }}</span></h5>
                                    <p class="text-muted mb-0 fs-6">Exams for {{ $student->firstname }} {{ $student->lastname }} <span class="fs-7">({{ $class->schoolclass }} - {{ $term->term }} - {{ $session->session }})</span></p>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- NEW: Wrapper for dynamic updates -->
                                <div id="exams-container">
                                    @include('cbt.partials.exams-table', [
                                        'exams' => $exams,
                                        'attempts' => $attempts,
                                        'student' => $student,
                                        'class' => $class,
                                        'term' => $term,
                                        'session' => $session,
                                        'totalreg' => $totalreg,
                                        'reg' => $reg,
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="row mt-3">
                <div class="col-12">
                    <p class="text-muted">Total Subjects Offered: {{ $totalreg }} | Subjects Registered: {{ $reg }}</p>
                </div>
            </div>
            @endcan
        </div>
        <!-- End Page-content -->
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script> {{-- Optional: For advanced search/sort; remove if unused --}}
<script>
$(document).ready(function() {
    let examList; // For List.js if used

    // Initialize List.js for search/sort (optional)
    function initListJs() {
        examList = new List('kt_exams_table', {
            valueNames: ['title', 'description', 'duration', 'start_time', 'end_time', 'status'],
            page: 15, // Matches your pagination
            pagination: true,
            plugins: [ListPagination({})] // Requires ListPagination plugin; skip if not installed
        });
    }
    // Uncomment if using List.js: initListJs();

    // Handle search (client-side filter; works with/without List.js)
    $('.search').on('keyup', debounce(function() {
        var searchValue = $(this).val().toLowerCase();
        $('#kt_exams_table tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.includes(searchValue));
        });
        updateResultText(); // Custom function below
    }, 300));

    // Handle sort clicks (client-side; for server-side, add query params to AJAX)
    $('.sort').on('click', function(e) {
        e.preventDefault();
        var sortBy = $(this).data('sort');
        var isAsc = $(this).hasClass('asc') ? false : true;
        $('.sort').removeClass('asc desc');
        $(this).addClass(isAsc ? 'asc' : 'desc');

        // Simple client-side sort (array of rows, sort, re-append)
        var $rows = $('#kt_exams_table tbody tr').get();
        $rows.sort(function(a, b) {
            var A = $(a).find('.' + sortBy).text().toUpperCase();
            var B = $(b).find('.' + sortBy).text().toUpperCase();
            if (isAsc) {
                return A < B ? -1 : A > B ? 1 : 0;
            } else {
                return A > B ? -1 : A < B ? 1 : 0;
            }
        });
        $.each($rows, function(index, row) {
            $('#kt_exams_table tbody').append(row);
        });
    });

    // Handle pagination clicks (AJAX)
    $(document).on('click', '.pagination-prev, .pagination-next, .page-link', function(e) {
        e.preventDefault();
        
        var $this = $(this);
        if ($this.hasClass('disabled') || !$this.data('url')) return;
        
        var url = $this.data('url');
        
        // Show loading
        $('#kt_exams_table tbody').html('<tr><td colspan="8" class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
        
        // AJAX fetch
        $.get(url, function(data) {
            var $newContent = $(data).find('#exams-container').html();
            $('#exams-container').html($newContent);
            
            // Re-init events after update
            initTableEvents();
            
        }).fail(function(xhr) {
            console.error('AJAX Error:', xhr);
            $('#kt_exams_table tbody').html('<tr><td colspan="8" class="text-center text-danger p-4">Error loading data. Please refresh the page.</td></tr>');
            alert('Failed to load page. Check console for details.');
        });
    });

    // Re-bind events after AJAX updates
    function initTableEvents() {
        // Re-attach sort if needed (already delegated above)
        // If using List.js, re-init: initListJs();
    }

    // Update "Showing X of Y" text (call after search/paginate)
    function updateResultText() {
        var visibleRows = $('#kt_exams_table tbody tr:visible').length;
        var total = {{ $exams->total() }}; // Initial total; update via AJAX if needed
        $('.text-muted.text-center .fw-semibold:first').text(visibleRows);
        $('.text-muted.text-center .fw-semibold:last').text(total);
    }

    // Utility: Debounce for search
    function debounce(func, wait) {
        var timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Initial setup
    initTableEvents();
    updateResultText();
});
</script>
@endpush

@endsection