@extends('layouts.app')

@section('title', 'Branches')

@section('content')
    @if ($errors->has('branch'))
        <div class="alert alert-danger">
            {{ $errors->first('branch') }}
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-xl-5 mb-4 mb-xl-0">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Add Branch</h3>
                </div>
                <form action="{{ route('branches.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Branch Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Lagos Branch" required>
                        </div>
                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="is_active">Active Branch</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create Branch</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Branch Directory</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Status</th>
                                <th>Members</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($branches as $branch)
                                <tr>
                                    <td>{{ $branch->name }}</td>
                                    <td>
                                        @if ($branch->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $branch->members_count }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                        <a href="{{ route('members.index', ['branch_id' => $branch->id]) }}" class="btn btn-sm btn-outline-primary mb-2 align-self-start">
                                            <i class="fas fa-users mr-1"></i>View Members
                                        </a>
                                        <form action="{{ route('branches.update', $branch) }}" method="POST" class="mb-2">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-row align-items-center">
                                                <div class="col-12 col-md-6 mb-2 mb-md-0">
                                                    <input type="text" name="name" class="form-control form-control-sm" value="{{ $branch->name }}" required>
                                                </div>
                                                <div class="col-6 col-md-auto mb-2 mb-md-0">
                                            <input type="hidden" name="is_active" value="0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="active_{{ $branch->id }}" name="is_active" value="1" {{ $branch->is_active ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="active_{{ $branch->id }}"></label>
                                            </div>
                                                </div>
                                                <div class="col-6 col-md-auto">
                                                    <button type="submit" class="btn btn-sm btn-info btn-block">Save</button>
                                                </div>
                                            </div>
                                        </form>
                                        <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="align-self-start" onsubmit="return confirm('Delete this branch?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No branches added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
