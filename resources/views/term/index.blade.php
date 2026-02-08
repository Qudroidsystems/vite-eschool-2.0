@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Term Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Terms</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Whoops!</strong> Please check the errors below.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Add Button + Search -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                                <div class="search-box w-50">
                                    <input type="text" class="form-control search" placeholder="Search terms...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                                @can('Create term')
                                    <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addTermModal">
                                        <i class="bi bi-plus-circle me-1"></i> Add Term
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms Table -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">School Terms <span class="badge bg-dark-subtle text-dark">{{ $terms->total() }}</span></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle table-nowrap table-hover" id="termTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Term</th>
                                            <th>Status</th>
                                            <th>Updated</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @forelse ($terms as $term)
                                            <tr data-id="{{ $term->id }}">
                                                <td>{{ $loop->iteration + ($terms->currentPage() - 1) * $terms->perPage() }}</td>
                                                <td class="term">{{ $term->term }}</td>
                                                <td>
                                                    <span class="badge {{ $term->status ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $term->status ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>{{ $term->updated_at->format('d M Y') }}</td>
                                                <td class="text-end">
                                                    @can('Update term')
                                                        <button class="btn btn-sm btn-soft-secondary edit-btn" title="Edit">
                                                            <i class="ri-pencil-line"></i>
                                                        </button>
                                                    @endcan
                                                    @can('Delete term')
                                                        <button class="btn btn-sm btn-soft-danger remove-btn" title="Delete">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No terms found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $terms->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Modal -->
            <div class="modal fade" id="addTermModal" tabindex="-1" aria-labelledby="addTermModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addTermModalLabel">Add New Term</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="addTermForm">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Term Name</label>
                                    <input type="text" name="term" class="form-control" required placeholder="e.g. First Term 2025/2026">
                                </div>
                                <div class="mb-3 form-check form-switch">
                                    <input type="checkbox" name="status" class="form-check-input" id="addStatus" checked>
                                    <label class="form-check-label" for="addStatus">Active</label>
                                </div>
                                <div class="alert alert-danger d-none" id="addError"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Term</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editTermModal" tabindex="-1" aria-labelledby="editTermModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editTermModalLabel">Edit Term</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="editTermForm">
                            <input type="hidden" name="id" id="editId">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Term Name</label>
                                    <input type="text" name="term" id="editTerm" class="form-control" required>
                                </div>
                                <div class="mb-3 form-check form-switch">
                                    <input type="checkbox" name="status" class="form-check-input" id="editStatus">
                                    <label class="form-check-label" for="editStatus">Active</label>
                                </div>
                                <div class="alert alert-danger d-none" id="editError"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update Term</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center py-4">
                            <h4>Delete this term?</h4>
                            <p class="text-muted">This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDelete">Yes, Delete</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
