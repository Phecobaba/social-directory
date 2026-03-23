@extends('layouts.app')

@section('title', 'Members List')

@section('content')
    @if ($errors->has('bulk_actions'))
        <div class="alert alert-danger">
            {{ $errors->first('bulk_actions') }}
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-12 col-lg-8">
            <form action="{{ route('members.index') }}" method="GET" class="w-100">
                <div class="input-group w-100">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search by full name or phone number"
                        value="{{ old('search', request('search', $search)) }}"
                    >
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        @if ($search)
                            <a href="{{ route('members.index') }}" class="btn btn-default">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        <div class="col-12 col-lg-4 text-lg-right mt-3 mt-lg-0">
            <div class="d-flex flex-column flex-lg-row justify-content-lg-end">
                <a href="{{ route('members.backup') }}" class="btn btn-outline-dark btn-block mb-2 mb-lg-0 mr-lg-2">
                    <i class="fas fa-download"></i> Full Backup
                </a>
                <a href="{{ route('members.create') }}" class="btn btn-success btn-block btn-lg-inline">
                <i class="fas fa-user-plus"></i> Add Member
                </a>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Export Members</h3>
        </div>
        <form action="{{ route('members.export') }}" method="GET">
            <input type="hidden" name="search" value="{{ request('search', $search) }}">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-lg-7 mb-3 mb-lg-0">
                        <label class="d-block">Core Fields</label>
                        <div class="d-flex flex-wrap">
                            @foreach (['full_name' => 'Full Name', 'phone_number' => 'Phone Number', 'address' => 'Address', 'photo' => 'Photo Path', 'created_at' => 'Created At', 'updated_at' => 'Updated At'] as $field => $label)
                                <div class="custom-control custom-checkbox mr-4 mb-2">
                                    <input class="custom-control-input" type="checkbox" id="field_{{ $field }}" name="fields[]" value="{{ $field }}" {{ in_array($field, ['full_name', 'phone_number', 'address'], true) ? 'checked' : '' }}>
                                    <label for="field_{{ $field }}" class="custom-control-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 col-lg-5">
                        <label class="d-block">Dynamic Fields</label>
                        @if ($availableDynamicFields)
                            <div class="d-flex flex-wrap">
                                @foreach ($availableDynamicFields as $dynamicField)
                                    <div class="custom-control custom-checkbox mr-4 mb-2">
                                        <input class="custom-control-input" type="checkbox" id="dynamic_field_{{ md5($dynamicField) }}" name="dynamic_fields[]" value="{{ $dynamicField }}">
                                        <label for="dynamic_field_{{ md5($dynamicField) }}" class="custom-control-label">{{ $dynamicField }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No dynamic fields available yet.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex flex-column flex-md-row align-items-md-center">
                <button type="submit" class="btn btn-success mr-md-2 mb-2 mb-md-0">
                    <i class="fas fa-file-export mr-1"></i>Export CSV
                </button>
                <button type="submit" class="btn btn-outline-success" formaction="{{ route('members.export.excel') }}">
                    <i class="fas fa-file-excel mr-1"></i>Export Excel
                </button>
            </div>
        </form>
    </div>

    <form id="bulk-action-form" method="POST">
        @csrf
        <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <h3 class="card-title mb-2 mb-lg-0">Member Directory</h3>
                <div class="d-flex flex-column flex-sm-row">
                    <button type="submit" class="btn btn-info btn-sm mb-2 mb-sm-0 mr-sm-2" formaction="{{ route('members.bulk-edit') }}">
                        <i class="fas fa-edit mr-1"></i>Bulk Edit
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm" formaction="{{ route('members.bulk-delete') }}" formmethod="POST" onclick="return confirm('Are you sure you want to delete the selected members?');">
                        <i class="fas fa-trash mr-1"></i>Bulk Delete
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">
                            <input type="checkbox" id="select-all-members">
                        </th>
                        {{-- Photo column changes begin --}}
                        <th style="width: 90px;">Photo</th>
                        {{-- Photo column changes end --}}
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="member-checkbox">
                            </td>
                            {{-- Photo column changes begin --}}
                            <td class="text-center align-middle">
                                @if ($member->photo)
                                    <img
                                        src="{{ asset('storage/' . $member->photo) }}"
                                        alt="{{ $member->full_name }}"
                                        style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;"
                                    >
                                @else
                                    <div
                                        style="width: 40px; height: 40px; border-radius: 50%; background: #d6d8db; color: #6c757d; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; text-transform: uppercase;"
                                        title="{{ $member->full_name }}"
                                    >
                                        {{ \Illuminate\Support\Str::substr(trim($member->full_name), 0, 1) }}
                                    </div>
                                @endif
                            </td>
                            {{-- Photo column changes end --}}
                            <td>{{ $member->full_name }}</td>
                            <td>{{ $member->phone_number }}</td>
                            <td>{{ $member->address ?: 'N/A' }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('members.show', $member->id) }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('members.edit', $member->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <form action="{{ route('members.destroy', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer clearfix">
            {{ $members->appends(request()->query())->links() }}
        </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('select-all-members');
            const memberCheckboxes = document.querySelectorAll('.member-checkbox');

            if (!selectAllCheckbox || memberCheckboxes.length === 0) {
                return;
            }

            selectAllCheckbox.addEventListener('change', function () {
                memberCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
        });
    </script>
@endsection
