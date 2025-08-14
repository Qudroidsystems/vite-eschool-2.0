@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('school-information.index') }}">School Management</a></li>
                                <li class="breadcrumb-item active">School Overview</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">School Information Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> {{ $school->school_name }}</p>
                                    <p><strong>Address:</strong> {{ $school->school_address }}</p>
                                    <p><strong>Phone:</strong> {{ $school->school_phone }}</p>
                                    <p><strong>Email:</strong> {{ $school->school_email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Motto:</strong> {{ $school->school_motto ?? 'N/A' }}</p>
                                    <p><strong>Website:</strong> {{ $school->school_website ? '<a href="' . $school->school_website . '" target="_blank">' . $school->school_website . '</a>' : 'N/A' }}</p>
                                    <p><strong>Status:</strong> <span class="badge bg-{{ $school->is_active ? 'success' : 'secondary' }}">{{ $school->is_active ? 'Active' : 'Inactive' }}</span></p>
                                    <p><strong>Logo:</strong> 
                                        @if ($school->school_logo)
                                            <img src="{{ $school->logo_url }}" alt="{{ $school->school_name }} Logo" style="max-width: 100px;">
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('school-information.index') }}" class="btn btn-light">Back to List</a>
                                {{-- @can('Update schoolinformation')
                                    <a href="{{ route('school-information.edit', $school->id) }}" class="btn btn-secondary">Edit</a>
                                @endcan --}}
                                @can('Delete schoolinformation')
                                    <form action="{{ route('school-information.destroy', $school->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this school?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection