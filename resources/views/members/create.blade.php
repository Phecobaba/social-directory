@extends('layouts.app')

@section('title', 'Add Member')

@section('content')
    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Add Member</h3>
                </div>

                <form action="{{ route('members.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                        @if ($errors->any() && ! $errors->has('csv_file'))
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
                            <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="4">{{ old('address') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="photo">Photo</label>
                            <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
                            <small class="form-text text-muted">Optional. Upload a member profile image.</small>
                        </div>

                        {{-- Custom fields section begin --}}
                        @php
                            $customFieldRows = old('dynamic_fields', []);
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
                        <button type="submit" class="btn btn-primary">Save Member</button>
                        <a href="{{ route('members.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Bulk Import via CSV</h3>
                </div>
                <form action="{{ route('members.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                        @if ($errors->has('csv_file'))
                            <div class="alert alert-danger">
                                {{ $errors->first('csv_file') }}
                            </div>
                        @endif

                        <p class="text-muted">
                            Upload a CSV file to add multiple members at once. Standard columns such as <code>full_name</code>, <code>phone_number</code>, and <code>address</code> map directly to member fields.
                        </p>
                        <p class="text-muted">
                            Any extra CSV columns are automatically stored as each member's custom fields.
                        </p>

                        <div class="form-group">
                            <label for="csv_file">CSV File</label>
                            <input type="file" class="form-control-file" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
                        </div>

                        <a href="{{ route('members.import.template') }}" class="btn btn-outline-primary btn-sm mb-3">
                            <i class="fas fa-download mr-1"></i>Download CSV Template
                        </a>

                        <div class="alert alert-light border mb-0">
                            <strong>Example header row:</strong><br>
                            <code>full_name,phone_number,address,occupation,state_of_origin</code>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-search-plus mr-1"></i>Preview Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (! empty($importPreview))
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">CSV Import Preview</h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="border rounded py-3">
                                    <div class="h4 mb-1">{{ $importPreview['summary']['total'] }}</div>
                                    <div class="text-muted">Rows Found</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3 mb-md-0">
                                <div class="border rounded py-3">
                                    <div class="h4 mb-1 text-success">{{ $importPreview['summary']['ready'] }}</div>
                                    <div class="text-muted">Ready to Import</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded py-3">
                                    <div class="h4 mb-1 text-warning">{{ $importPreview['summary']['duplicates'] }}</div>
                                    <div class="text-muted">Duplicate Rows</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded py-3">
                                    <div class="h4 mb-1 text-danger">{{ $importPreview['summary']['invalid'] }}</div>
                                    <div class="text-muted">Invalid Rows</div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Row</th>
                                        <th>Status</th>
                                        <th>Full Name</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Custom Fields</th>
                                        <th>Issues</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($importPreview['rows'] as $previewRow)
                                        <tr>
                                            <td>{{ $previewRow['row_number'] }}</td>
                                            <td>
                                                @if ($previewRow['status'] === 'ready')
                                                    <span class="badge badge-success">Ready</span>
                                                @elseif ($previewRow['status'] === 'duplicate')
                                                    <span class="badge badge-warning">Duplicate</span>
                                                @else
                                                    <span class="badge badge-danger">Invalid</span>
                                                @endif
                                            </td>
                                            <td>{{ $previewRow['member_data']['full_name'] ?: 'N/A' }}</td>
                                            <td>{{ $previewRow['member_data']['phone_number'] ?: 'N/A' }}</td>
                                            <td>{{ $previewRow['member_data']['address'] ?: 'N/A' }}</td>
                                            <td>{{ $previewRow['dynamic_field_count'] }}</td>
                                            <td>
                                                @if ($previewRow['issues'])
                                                    <ul class="mb-0 pl-3">
                                                        @foreach ($previewRow['issues'] as $issue)
                                                            <li>{{ $issue }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-success">No issues</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <p class="text-muted mb-3 mb-md-0">
                            Only rows marked <strong>Ready</strong> will be imported.
                        </p>
                        <div class="d-flex flex-column flex-sm-row">
                            <form action="{{ route('members.import.clear') }}" method="POST" class="mr-sm-2 mb-2 mb-sm-0">
                                @csrf
                                <button type="submit" class="btn btn-default btn-block">
                                    Clear Preview
                                </button>
                            </form>
                            <form action="{{ route('members.import.confirm') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block" {{ $importPreview['summary']['ready'] === 0 ? 'disabled' : '' }}>
                                    Confirm Import
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Custom fields section begin --}}
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
    {{-- Custom fields section end --}}
@endsection
