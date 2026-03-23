@extends('layouts.app')

@section('title', 'Edit Member')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Edit Member</h3>
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

                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name', $member->full_name) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $member->phone_number) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="4" required>{{ old('address', $member->address) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="photo">Photo</label>
                            <div class="mb-3">
                                @if ($member->photo)
                                    <img
                                        id="photo-preview"
                                        src="{{ asset('storage/' . $member->photo) }}"
                                        alt="{{ $member->full_name }}"
                                        style="display: block; max-width: 150px; width: 100%; height: 150px; object-fit: cover; border-radius: 8px;"
                                    >
                                    <div
                                        id="photo-placeholder"
                                        style="display: none; width: 150px; height: 150px; background: #e9ecef; color: #6c757d; border-radius: 8px; align-items: center; justify-content: center; font-weight: 600;"
                                    >
                                        No Photo
                                    </div>
                                @else
                                    <img
                                        id="photo-preview"
                                        src=""
                                        alt="Selected photo preview"
                                        style="display: none; max-width: 150px; width: 100%; height: 150px; object-fit: cover; border-radius: 8px;"
                                    >
                                    <div
                                        id="photo-placeholder"
                                        style="display: flex; width: 150px; height: 150px; background: #e9ecef; color: #6c757d; border-radius: 8px; align-items: center; justify-content: center; font-weight: 600;"
                                    >
                                        No Photo
                                    </div>
                                @endif
                            </div>
                            <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
                            <small class="form-text text-muted">Optional. Upload a new image to replace the current photo.</small>
                        </div>

                        {{-- Custom fields section begin --}}
                        @php
                            $customFieldRows = old('dynamic_fields');

                            if ($customFieldRows === null) {
                                $customFieldRows = collect($member->dynamic_fields ?? [])
                                    ->map(fn ($value, $key) => ['key' => $key, 'value' => $value])
                                    ->values()
                                    ->all();
                            }
                        @endphp
                        <div class="card card-outline card-secondary">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Custom Fields</h3>
                                <button type="button" class="btn btn-sm btn-primary" id="add-custom-field">
                                    Add Field
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="custom-fields-container">
                                    @foreach ($customFieldRows as $index => $customField)
                                        <div class="form-row align-items-end mb-3 custom-field-row">
                                            <div class="col-md-5">
                                                <label>Field Name</label>
                                                <input
                                                    type="text"
                                                    name="dynamic_fields[{{ $index }}][key]"
                                                    class="form-control"
                                                    value="{{ $customField['key'] ?? '' }}"
                                                >
                                            </div>
                                            <div class="col-md-5">
                                                <label>Field Value</label>
                                                <input
                                                    type="text"
                                                    name="dynamic_fields[{{ $index }}][value]"
                                                    class="form-control"
                                                    value="{{ $customField['value'] ?? '' }}"
                                                >
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-block remove-custom-field">Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-muted mb-0">Add optional key-value details for this member.</p>
                            </div>
                        </div>
                        {{-- Custom fields section end --}}
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

            if (!photoInput || !photoPreview || !photoPlaceholder) {
                return;
            }

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
