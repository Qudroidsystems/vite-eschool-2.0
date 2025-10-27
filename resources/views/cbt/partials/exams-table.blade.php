<div class="table-responsive">
    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_exams_table">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th class="min-w-125px sort cursor-pointer" data-sort="sn">SN</th>
                <th class="min-w-125px sort cursor-pointer" data-sort="title">Title</th>
                <th class="min-w-125px sort cursor-pointer" data-sort="description">Description</th>
                <th class="min-w-125px sort cursor-pointer" data-sort="duration">Duration</th>
                <th class="min-w-125px sort cursor-pointer" data-sort="start_time">Start Time</th>
                <th class="min-w-125px sort cursor-pointer" data-sort="end_time">End Time</th>
                <th class="min-w-125px sort cursor-pointer" data-sort="status">Status</th>
                <th class="min-w-100px">Actions</th>
            </tr>
        </thead>
        <tbody class="fw-semibold text-gray-600 list">
            @php $i = ($exams->currentPage() - 1) * $exams->perPage() @endphp
            @forelse ($exams as $exam)
                @php
                    $now = now();
                    $rawStart = $exam->start_time ?? 'NULL';
                    $rawEnd = $exam->end_time ?? 'NULL';
                    $start = $rawStart ? \Carbon\Carbon::parse($rawStart) : null;
                    $end = $rawEnd ? \Carbon\Carbon::parse($rawEnd) : null;
                    $hasAttempted = in_array($exam->id, $attempts ?? []);
                    $status = 'Unknown'; // Default
                    if ($start && $end) {
                        if ($hasAttempted) {
                            $status = 'Completed';
                        } elseif ($now->lt($start)) {
                            $status = 'Upcoming';
                        } elseif ($now->between($start, $end)) {
                            $status = 'Ongoing';
                        } else {
                            $status = 'Ended';
                        }
                    } elseif (!$start || !$end) {
                        $status = 'Invalid Dates';
                    }
                @endphp
                <tr>
                    <td class="sn">{{ ++$i }}</td>
                    <td class="title">{{ $exam->title }}</td>
                    <td class="description">{{ Str::limit($exam->description ?? '', 50) }}</td>
                    <td class="duration">{{ $exam->duration }} mins</td>
                    <td class="start_time">{{ $rawStart }}</td>
                    <td class="end_time">{{ $rawEnd }}</td>
                    <td class="status">
                        @if ($hasAttempted)
                            <span style="color: #0dcaf0; background-color: #cff4fc; padding: 0.25em 0.5em; border-radius: 0.25rem; font-size: 0.75em;">Completed</span>
                        @elseif ($status === 'Upcoming')
                            <span style="color: #ffc107; background-color: #fff3cd; padding: 0.25em 0.5em; border-radius: 0.25rem; font-size: 0.75em;">Upcoming</span>
                        @elseif ($status === 'Ongoing')
                            <span style="color: #198754; background-color: #d1e7dd; padding: 0.25em 0.5em; border-radius: 0.25rem; font-size: 0.75em;">Ongoing</span>
                        @elseif ($status === 'Ended')
                            <span style="color: #dc3545; background-color: #f8d7da; padding: 0.25em 0.5em; border-radius: 0.25rem; font-size: 0.75em;">Ended</span>
                        @else
                            <span style="color: #6c757d; background-color: #e2e3e5; padding: 0.25em 0.5em; border-radius: 0.25rem; font-size: 0.75em;">{{ $status }}</span>
                        @endif
                    </td>
                    <td class="actions">
                        @if ($hasAttempted)
                            <span style="color: #0dcaf0; background-color: #cff4fc; padding: 0.25em 0.5em; border-radius: 0.25rem; font-size: 0.75em;">Exam Taken</span>
                        @elseif ($status === 'Ongoing')
                            @can('Take cbt-exam')
                                <a href="{{ route('cbt.take', $exam->id) }}" class="btn btn-sm btn-primary">Take Exam</a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endcan
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="noresult" style="display: block;">No exams available for your registered subjects at this time.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row mt-3 align-items-center" id="pagination-element">
    <div class="col-sm">
        <div class="text-muted text-center text-sm-start">
            Showing <span class="fw-semibold">{{ $exams->count() }}</span> of <span class="fw-semibold">{{ $exams->total() }}</span> Results
        </div>
    </div>
    <div class="col-sm-auto mt-3 mt-sm-0">
        <div class="pagination-wrap hstack gap-2 justify-content-center">
            <a class="page-item pagination-prev {{ $exams->onFirstPage() ? 'disabled' : '' }}" href="javascript:void(0);" data-url="{{ $exams->previousPageUrl() }}">
                <i class="mdi mdi-chevron-left align-middle"></i>
            </a>
            <ul class="pagination listjs-pagination mb-0">
                @foreach ($exams->links()->elements[0] as $page => $url)
                    @if(is_string($page))
                        <li class="page-item disabled"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item {{ $exams->currentPage() == $page ? 'active' : '' }}">
                            <a class="page-link" href="javascript:void(0);" data-url="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
            <a class="page-item pagination-next {{ $exams->hasMorePages() ? '' : 'disabled' }}" href="javascript:void(0);" data-url="{{ $exams->nextPageUrl() }}">
                <i class="mdi mdi-chevron-right align-middle"></i>
            </a>
        </div>
    </div>
</div>