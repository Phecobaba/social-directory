@extends('layouts.app')

@section('title', 'Member Profile')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
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
                            <img src="{{ asset('storage/' . $member->photo) }}" alt="{{ $member->full_name }}" style="display:inline-block;max-width:200px;width:100%;height:auto;object-fit:cover;border-radius:8px;">
                        @else
                            <div style="width:150px;height:150px;margin:0 auto;background:#e9ecef;color:#6c757d;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:600;">No Photo</div>
                        @endif
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-4">Branch</dt>
                        <dd class="col-sm-8">{{ $member->branch?->name ?: 'N/A' }}</dd>

                        <dt class="col-sm-4">Title</dt>
                        <dd class="col-sm-8">{{ $member->title ?: 'N/A' }}</dd>

                        <dt class="col-sm-4">Surname</dt>
                        <dd class="col-sm-8">{{ $member->surname }}</dd>

                        <dt class="col-sm-4">Other Names</dt>
                        <dd class="col-sm-8">{{ $member->other_names }}</dd>

                        <dt class="col-sm-4">Phone Number</dt>
                        <dd class="col-sm-8">{{ $member->phone_number }}</dd>

                        <dt class="col-sm-4">Date of Birth</dt>
                        <dd class="col-sm-8">{{ $member->date_of_birth?->format('M d, Y') ?: 'N/A' }}</dd>

                        <dt class="col-sm-4">Place of Birth</dt>
                        <dd class="col-sm-8">{{ $member->place_of_birth ?: 'N/A' }}</dd>

                        <dt class="col-sm-4">Town/Village/LGA/State</dt>
                        <dd class="col-sm-8">
                            {{ $member->town_of_origin ?: 'N/A' }} /
                            {{ $member->village ?: 'N/A' }} /
                            {{ $member->local_government_of_origin ?: 'N/A' }} /
                            {{ $member->state_of_origin ?: 'N/A' }}
                        </dd>

                        <dt class="col-sm-4">Occupation</dt>
                        <dd class="col-sm-8">{{ $member->occupation ?: 'N/A' }}</dd>

                        <dt class="col-sm-4">Height</dt>
                        <dd class="col-sm-8">{{ $member->height ?: 'N/A' }}</dd>

                        <dt class="col-sm-4">Next of Kin</dt>
                        <dd class="col-sm-8">
                            {{ $member->next_of_kin_name ?: 'N/A' }}
                            ({{ $member->next_of_kin_relationship ?: 'N/A' }})
                            - Phone: {{ $member->next_of_kin_phone ?: 'N/A' }}
                        </dd>

                        <dt class="col-sm-4">Parents / Spouse</dt>
                        <dd class="col-sm-8">Father: {{ $member->father_name ?: 'N/A' }}, Mother: {{ $member->mother_name ?: 'N/A' }}, Wife: {{ $member->wife_name ?: 'N/A' }}</dd>

                        <dt class="col-sm-4">House Address</dt>
                        <dd class="col-sm-8">{{ $member->house_address ?: 'N/A' }}</dd>

                        <dt class="col-sm-4">Office Address</dt>
                        <dd class="col-sm-8">{{ $member->office_address ?: 'N/A' }}</dd>
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
