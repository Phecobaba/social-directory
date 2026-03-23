@extends('layouts.app')

@section('title', 'Member Profile')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <h3 class="card-title mb-2 mb-md-0">{{ $member->full_name }}</h3>
                        <div>
                            <a href="{{ route('members.index') }}" class="btn btn-sm btn-default">Back to Members</a>
                            <a href="{{ route('members.edit', $member->id) }}" class="btn btn-sm btn-primary">Edit Member</a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="text-center mb-4">
                        @if ($member->photo)
                            <img
                                src="{{ asset('storage/' . $member->photo) }}"
                                alt="{{ $member->full_name }}"
                                style="display: inline-block; max-width: 200px; width: 100%; height: auto; object-fit: cover; border-radius: 8px;"
                            >
                        @else
                            <div
                                style="width: 150px; height: 150px; margin: 0 auto; background: #e9ecef; color: #6c757d; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 600;"
                            >
                                No Photo
                            </div>
                        @endif
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-4">Full Name</dt>
                        <dd class="col-sm-8">{{ $member->full_name }}</dd>

                        <dt class="col-sm-4">Phone Number</dt>
                        <dd class="col-sm-8">{{ $member->phone_number }}</dd>

                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">{{ $member->address ?: 'N/A' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Custom Fields</h3>
                </div>
                <div class="card-body">
                    @if (! empty($member->dynamic_fields))
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($member->dynamic_fields as $field => $value)
                                        <tr>
                                            <td>{{ $field }}</td>
                                            <td>{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No custom fields added.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
