@extends('layouts.app')

@section('title', 'Bulk Edit Members')

@section('content')
    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Bulk Edit Selected Members</h3>
                </div>

                <form action="{{ route('members.bulk-update') }}" method="POST">
                    @csrf

                    @foreach ($members as $member)
                        <input type="hidden" name="member_ids[]" value="{{ $member->id }}">
                    @endforeach

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 pl-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            Updating <strong>{{ $members->count() }}</strong> selected member(s).
                        </div>

                        <div class="form-group">
                            <label for="address">Replace Address for Selected Members</label>
                            <textarea class="form-control" id="address" name="address" rows="4">{{ old('address') }}</textarea>
                            <small class="form-text text-muted">Leave blank to keep each member's existing address.</small>
                        </div>

                        @php
                            $customFieldRows = old('dynamic_fields', []);
                        @endphp
                        <div class="card card-outline card-secondary">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Dynamic Fields to Add or Update</h3>
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
                                <p class="text-muted mb-0">Any field entered here will be applied to every selected member.</p>
                            </div>
                        </div>

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Phone</th>
                                        <th>Current Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members as $member)
                                        <tr>
                                            <td>{{ $member->full_name }}</td>
                                            <td>{{ $member->phone_number }}</td>
                                            <td>{{ $member->address ?: 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Apply Bulk Update</button>
                        <a href="{{ route('members.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addCustomFieldButton = document.getElementById('add-custom-field');
            const customFieldsContainer = document.getElementById('custom-fields-container');

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
