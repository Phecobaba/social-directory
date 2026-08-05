@extends('layouts.app')

@section('title', 'Edit Member')

@section('content')
    <div class="row">
        <div class="col-lg-10">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Edit Member</h3>
                    <p class="mb-0 mt-1 text-sm">{{ $member->full_name }}</p>
                </div>

                <form action="{{ route('members.update', $member->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Please fix the following issues:</strong>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="branch_id">Branch</label>
                                <select id="branch_id" name="branch_id" class="form-control" required>
                                    <option value="">Select branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ (string) old('branch_id', $member->branch_id) === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $member->title) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="surname">Surname</label>
                                <input type="text" class="form-control" id="surname" name="surname" value="{{ old('surname', $member->surname) }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="other_names">Other Names</label>
                                <input type="text" class="form-control" id="other_names" name="other_names" value="{{ old('other_names', $member->other_names) }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $member->date_of_birth?->toDateString()) }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="place_of_birth">Place of Birth</label>
                                <input type="text" class="form-control" id="place_of_birth" name="place_of_birth" value="{{ old('place_of_birth', $member->place_of_birth) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="town_of_origin">Town of Origin</label>
                                <input type="text" class="form-control" id="town_of_origin" name="town_of_origin" value="{{ old('town_of_origin', $member->town_of_origin) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="village">Village</label>
                                <input type="text" class="form-control" id="village" name="village" value="{{ old('village', $member->village) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="local_government_of_origin">L.G.A of Origin</label>
                                <input type="text" class="form-control" id="local_government_of_origin" name="local_government_of_origin" value="{{ old('local_government_of_origin', $member->local_government_of_origin) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="state_of_origin">State of Origin</label>
                                <input type="text" class="form-control" id="state_of_origin" name="state_of_origin" value="{{ old('state_of_origin', $member->state_of_origin) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="occupation">Occupation</label>
                                <input type="text" class="form-control" id="occupation" name="occupation" value="{{ old('occupation', $member->occupation) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="height">Height</label>
                                <input type="text" class="form-control" id="height" name="height" value="{{ old('height', $member->height) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $member->phone_number) }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="next_of_kin_phone">Next of Kin Phone</label>
                                <input type="text" class="form-control" id="next_of_kin_phone" name="next_of_kin_phone" value="{{ old('next_of_kin_phone', $member->next_of_kin_phone) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="next_of_kin_name">Next of Kin</label>
                                <input type="text" class="form-control" id="next_of_kin_name" name="next_of_kin_name" value="{{ old('next_of_kin_name', $member->next_of_kin_name) }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="next_of_kin_relationship">Relationship of Next of Kin</label>
                                <input type="text" class="form-control" id="next_of_kin_relationship" name="next_of_kin_relationship" value="{{ old('next_of_kin_relationship', $member->next_of_kin_relationship) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="father_name">Father's Name</label>
                                <input type="text" class="form-control" id="father_name" name="father_name" value="{{ old('father_name', $member->father_name) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="mother_name">Mother's Name</label>
                                <input type="text" class="form-control" id="mother_name" name="mother_name" value="{{ old('mother_name', $member->mother_name) }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="wife_name">Wife's Name</label>
                                <input type="text" class="form-control" id="wife_name" name="wife_name" value="{{ old('wife_name', $member->wife_name) }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="house_address">House Address</label>
                            <textarea class="form-control" id="house_address" name="house_address" rows="3">{{ old('house_address', $member->house_address) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="office_address">Office Address</label>
                            <textarea class="form-control" id="office_address" name="office_address" rows="3">{{ old('office_address', $member->office_address) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="photo">Photo</label>
                            <div class="mb-3">
                                @if ($member->photo)
                                    <img id="photo-preview" src="{{ asset('storage/' . $member->photo) }}" alt="{{ $member->full_name }}" style="display:block;max-width:150px;width:100%;height:150px;object-fit:cover;border-radius:8px;">
                                @else
                                    <img id="photo-preview" src="" alt="Selected photo preview" style="display:none;max-width:150px;width:100%;height:150px;object-fit:cover;border-radius:8px;">
                                @endif
                                <div id="photo-placeholder" style="{{ $member->photo ? 'display:none;' : 'display:flex;' }}width:150px;height:150px;background:#e9ecef;color:#6c757d;border-radius:8px;align-items:center;justify-content:center;font-weight:600;">No Photo</div>
                            </div>
                            <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
                        </div>

                        @php
                            $customFieldRows = old('dynamic_fields');
                            if ($customFieldRows === null) {
                                $customFieldRows = collect($member->dynamic_fields ?? [])->map(fn ($value, $key) => ['key' => $key, 'value' => $value])->values()->all();
                            }
                        @endphp
                        <div class="card card-outline card-secondary">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Custom Fields</h3>
                                <button type="button" class="btn btn-sm btn-primary" id="add-custom-field">Add Field</button>
                            </div>
                            <div class="card-body">
                                <div id="custom-fields-container">
                                    @foreach ($customFieldRows as $index => $customField)
                                        <div class="form-row align-items-end mb-3 custom-field-row">
                                            <div class="col-md-5">
                                                <label>Field Name</label>
                                                <input type="text" name="dynamic_fields[{{ $index }}][key]" class="form-control" value="{{ $customField['key'] ?? '' }}">
                                            </div>
                                            <div class="col-md-5">
                                                <label>Field Value</label>
                                                <input type="text" name="dynamic_fields[{{ $index }}][value]" class="form-control" value="{{ $customField['value'] ?? '' }}">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-block remove-custom-field">Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Member</button>
                        <a href="{{ route('members.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const photoInput = document.getElementById('photo');
            const photoPreview = document.getElementById('photo-preview');
            const photoPlaceholder = document.getElementById('photo-placeholder');
            const addCustomFieldButton = document.getElementById('add-custom-field');
            const customFieldsContainer = document.getElementById('custom-fields-container');

            if (photoInput && photoPreview && photoPlaceholder) {
                photoInput.addEventListener('change', function (event) {
                    const [file] = event.target.files || [];

                    if (!file) {
                        if (photoPreview.getAttribute('src')) {
                            photoPreview.style.display = 'block';
                        } else {
                            photoPreview.style.display = 'none';
                            photoPlaceholder.style.display = 'flex';
                        }

                        return;
                    }

                    const objectUrl = URL.createObjectURL(file);
                    photoPreview.src = objectUrl;
                    photoPreview.style.display = 'block';
                    photoPlaceholder.style.display = 'none';

                    photoPreview.onload = function () {
                        URL.revokeObjectURL(objectUrl);
                    };
                });
            }

            if (!addCustomFieldButton || !customFieldsContainer) {
                return;
            }

            let customFieldIndex = customFieldsContainer.children.length;

            function buildCustomFieldRow(index, key = '', value = '') {
                const wrapper = document.createElement('div');
                wrapper.className = 'form-row align-items-end mb-3 custom-field-row';
                wrapper.innerHTML = `
                    <div class="col-md-5">
                        <label>Field Name</label>
                        <input type="text" name="dynamic_fields[${index}][key]" class="form-control" value="${key}">
                    </div>
                    <div class="col-md-5">
                        <label>Field Value</label>
                        <input type="text" name="dynamic_fields[${index}][value]" class="form-control" value="${value}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-block remove-custom-field">Remove</button>
                    </div>
                `;

                return wrapper;
            }

            addCustomFieldButton.addEventListener('click', function () {
                customFieldsContainer.appendChild(buildCustomFieldRow(customFieldIndex));
                customFieldIndex += 1;
            });

            customFieldsContainer.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-custom-field')) {
                    event.target.closest('.custom-field-row').remove();
                }
            });
        });
    </script>
@endsection
