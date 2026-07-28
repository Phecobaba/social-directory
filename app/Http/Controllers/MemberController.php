<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Models\Branch;
use App\Models\Member;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberController extends Controller
{
    private const IMPORT_PREVIEW_SESSION_KEY = 'member_import_preview';

    private const MEMBER_FIELDS = [
        'branch_id',
        'title',
        'surname',
        'other_names',
        'date_of_birth',
        'place_of_birth',
        'town_of_origin',
        'village',
        'local_government_of_origin',
        'state_of_origin',
        'occupation',
        'height',
        'phone_number',
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_phone',
        'father_name',
        'mother_name',
        'wife_name',
        'house_address',
        'office_address',
        'photo',
    ];

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $selectedBranchId = $request->integer('branch_id') ?: null;

        $memberQuery = $this->memberSearchQuery($search, $selectedBranchId);
        $availableDynamicFields = $this->availableDynamicFields(clone $memberQuery);

        $members = $memberQuery
            ->with('branch')
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);

        return view('members.index', compact(
            'members',
            'search',
            'selectedBranchId',
            'branches',
            'availableDynamicFields'
        ));
    }

    public function backup(): StreamedResponse
    {
        $members = Member::query()->with('branch')->latest()->get();

        return response()->streamDownload(function () use ($members) {
            echo json_encode([
                'club' => 'Divine Source Friends Club of Nigeria',
                'abbreviation' => 'D.S.F.C',
                'generated_at' => now()->toIso8601String(),
                'member_count' => $members->count(),
                'members' => $members->map(function (Member $member) {
                    return [
                        'id' => $member->id,
                        'branch_id' => $member->branch_id,
                        'branch' => $member->branch?->name,
                        'title' => $member->title,
                        'surname' => $member->surname,
                        'other_names' => $member->other_names,
                        'full_name' => $member->full_name,
                        'date_of_birth' => $member->date_of_birth?->toDateString(),
                        'place_of_birth' => $member->place_of_birth,
                        'town_of_origin' => $member->town_of_origin,
                        'village' => $member->village,
                        'local_government_of_origin' => $member->local_government_of_origin,
                        'state_of_origin' => $member->state_of_origin,
                        'occupation' => $member->occupation,
                        'height' => $member->height,
                        'phone_number' => $member->phone_number,
                        'next_of_kin_name' => $member->next_of_kin_name,
                        'next_of_kin_relationship' => $member->next_of_kin_relationship,
                        'next_of_kin_phone' => $member->next_of_kin_phone,
                        'father_name' => $member->father_name,
                        'mother_name' => $member->mother_name,
                        'wife_name' => $member->wife_name,
                        'house_address' => $member->house_address,
                        'office_address' => $member->office_address,
                        'photo' => $member->photo,
                        'dynamic_fields' => $member->dynamic_fields,
                        'created_at' => $member->created_at?->toIso8601String(),
                        'updated_at' => $member->updated_at?->toIso8601String(),
                    ];
                })->all(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, 'dsfc-members-backup.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function create(): View
    {
        $importPreview = session(self::IMPORT_PREVIEW_SESSION_KEY);
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('members.create', compact('importPreview', 'branches'));
    }

    public function show(Member $member): View
    {
        $member->load('branch');

        return view('members.show', compact('member'));
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $validated = $this->normalizeLegacyPayload($request->validated());
        $validated['dynamic_fields'] = $this->extractDynamicFields($request->input('dynamic_fields', []));
        $payload = $this->memberPayload($validated);

        if ($request->hasFile('photo')) {
            $payload['photo'] = $request->file('photo')->store('members', 'public');
        }

        Member::create($payload);

        return redirect()
            ->route('members.index')
            ->with('status', 'Member created successfully.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $importPreview = $this->buildImportPreview($request->file('csv_file')->getRealPath());

        if (isset($importPreview['error'])) {
            return back()->withErrors([
                'csv_file' => $importPreview['error'],
            ])->withInput();
        }

        $request->session()->put(self::IMPORT_PREVIEW_SESSION_KEY, $importPreview);

        return redirect()
            ->route('members.create')
            ->with('status', 'CSV processed. Review the preview below before confirming import.');
    }

    public function confirmImport(Request $request): RedirectResponse
    {
        $importPreview = $request->session()->get(self::IMPORT_PREVIEW_SESSION_KEY);

        if (! $importPreview || empty($importPreview['rows'])) {
            return redirect()
                ->route('members.create')
                ->withErrors([
                    'csv_file' => 'No CSV import preview is available. Upload a CSV file first.',
                ]);
        }

        $importableRows = collect($importPreview['rows'])
            ->filter(fn ($row) => ($row['status'] ?? '') === 'ready')
            ->values();

        if ($importableRows->isEmpty()) {
            return redirect()
                ->route('members.create')
                ->withErrors([
                    'csv_file' => 'There are no valid rows available to import. Resolve the preview issues and upload again.',
                ]);
        }

        foreach ($importableRows as $row) {
            Member::create($this->memberPayload($row['member_data']));
        }

        $request->session()->forget(self::IMPORT_PREVIEW_SESSION_KEY);

        return redirect()
            ->route('members.index')
            ->with('status', 'Members imported successfully from CSV.');
    }

    public function clearImportPreview(Request $request): RedirectResponse
    {
        $request->session()->forget(self::IMPORT_PREVIEW_SESSION_KEY);

        return redirect()
            ->route('members.create')
            ->with('status', 'CSV import preview cleared.');
    }

    public function export(Request $request): StreamedResponse
    {
        [$selectedCoreFields, $selectedDynamicFields, $members, $headers] = $this->prepareExportData($request);

        return response()->streamDownload(function () use ($members, $selectedCoreFields, $selectedDynamicFields, $headers) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headers);

            foreach ($members as $member) {
                $row = [];

                foreach ($selectedCoreFields as $field) {
                    $row[] = $this->resolveExportValue($member, $field);
                }

                foreach ($selectedDynamicFields as $field) {
                    $row[] = $member->dynamic_fields[$field] ?? '';
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'dsfc-members-export.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        [$selectedCoreFields, $selectedDynamicFields, $members, $headers] = $this->prepareExportData($request);

        return response()->streamDownload(function () use ($members, $selectedCoreFields, $selectedDynamicFields, $headers) {
            echo '<?xml version="1.0"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
            echo 'xmlns:o="urn:schemas-microsoft-com:office:office" ';
            echo 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
            echo 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
            echo '<Worksheet ss:Name="Members"><Table>';

            $this->outputExcelRow($headers);

            foreach ($members as $member) {
                $row = [];

                foreach ($selectedCoreFields as $field) {
                    $row[] = (string) $this->resolveExportValue($member, $field);
                }

                foreach ($selectedDynamicFields as $field) {
                    $row[] = (string) ($member->dynamic_fields[$field] ?? '');
                }

                $this->outputExcelRow($row);
            }

            echo '</Table></Worksheet></Workbook>';
        }, 'dsfc-members-export.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="dsfc-members-template.csv"',
        ];

        $columns = [
            'branch',
            'title',
            'surname',
            'other_names',
            'DOB',
            'place_of_birth',
            'town_of_origin',
            'village',
            'local_government_of_origin',
            'state_of_origin',
            'occupation',
            'height',
            'phone_number',
            'next_of_kin_phone_number',
            'next_of_kin_name',
            'relationship_of_next_of_kin',
            'father_name',
            'mother_name',
            'wife_name',
            'house_address',
            'office_address',
        ];
        $sampleRow = [
            'Lagos Branch',
            'Mrs',
            'Doe',
            'Jane Amara',
            '1992-04-15',
            'Awka',
            'Nnewi',
            'Uruagu',
            'Nnewi North',
            'Anambra',
            'Engineer',
            '170cm',
            '08012345678',
            '08098765432',
            'John Doe',
            'Brother',
            'Peter Doe',
            'Martha Doe',
            '',
            '12 Palm Street',
            '3 Tech Close',
        ];

        return response()->streamDownload(function () use ($columns, $sampleRow) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $columns);
            fputcsv($handle, $sampleRow);

            fclose($handle);
        }, 'dsfc-members-template.csv', $headers);
    }

    public function edit(Member $member): View
    {
        $member->load('branch');
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('members.edit', compact('member', 'branches'));
    }

    public function bulkEdit(Request $request): View|RedirectResponse
    {
        $memberIds = collect($request->input('member_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($memberIds->isEmpty()) {
            return redirect()
                ->route('members.index')
                ->withErrors(['bulk_actions' => 'Select at least one member to bulk edit.']);
        }

        $members = Member::query()
            ->with('branch')
            ->whereIn('id', $memberIds)
            ->orderBy('surname')
            ->orderBy('other_names')
            ->get();

        if ($members->isEmpty()) {
            return redirect()
                ->route('members.index')
                ->withErrors(['bulk_actions' => 'The selected members could not be found.']);
        }

        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('members.bulk-edit', compact('members', 'branches'));
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:members,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'house_address' => ['nullable', 'string'],
            'office_address' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'dynamic_fields' => ['nullable', 'array'],
        ]);

        $members = Member::query()
            ->whereIn('id', $validated['member_ids'])
            ->get();

        $bulkDynamicFields = $this->extractDynamicFields($request->input('dynamic_fields', [])) ?? [];

        foreach ($members as $member) {
            $currentDynamicFields = $member->dynamic_fields ?? [];
            $updateData = [
                'dynamic_fields' => array_replace($currentDynamicFields, $bulkDynamicFields) ?: null,
            ];

            if (array_key_exists('branch_id', $validated) && $validated['branch_id']) {
                $updateData['branch_id'] = (int) $validated['branch_id'];
            }

            $address = $validated['house_address'] ?? $validated['address'] ?? '';

            if ($address !== '') {
                $updateData['house_address'] = $address;
                $updateData['address'] = $address;
            }

            if (($validated['office_address'] ?? '') !== '') {
                $updateData['office_address'] = $validated['office_address'];
            }

            $member->update($updateData);
        }

        return redirect()
            ->route('members.index')
            ->with('status', 'Selected members updated successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:members,id'],
        ]);

        $members = Member::query()
            ->whereIn('id', $validated['member_ids'])
            ->get();

        foreach ($members as $member) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }

            $member->delete();
        }

        return redirect()
            ->route('members.index', $this->memberIndexRedirectQuery($request))
            ->with('status', 'Selected members deleted successfully.');
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $validated = $this->normalizeLegacyPayload($request->validate($this->memberRules()));
        $validated['dynamic_fields'] = $this->extractDynamicFields($request->input('dynamic_fields', []));
        $payload = $this->memberPayload($validated);

        if ($request->hasFile('photo')) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }

            $payload['photo'] = $request->file('photo')->store('members', 'public');
        }

        $member->update($payload);

        return redirect()
            ->route('members.index')
            ->with('status', 'Member updated successfully.');
    }

    public function destroy(Request $request, Member $member): RedirectResponse
    {
        $this->deleteMember($member);

        return redirect()
            ->route('members.index', $this->memberIndexRedirectQuery($request))
            ->with('status', 'Member deleted successfully.');
    }

    public function destroyFromRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
        ]);

        $member = Member::findOrFail($validated['member_id']);

        $this->deleteMember($member);

        return redirect()
            ->route('members.index', $this->memberIndexRedirectQuery($request))
            ->with('status', 'Member deleted successfully.');
    }

    private function deleteMember(Member $member): void
    {
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }

        $member->delete();
    }

    private function memberIndexRedirectQuery(Request $request): array
    {
        return collect($request->only(['search', 'branch_id']))
            ->filter(fn ($value) => filled($value))
            ->all();
    }

    private function memberRules(): array
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'title' => ['nullable', 'string', 'max:30'],
            'surname' => ['required_without:full_name', 'nullable', 'string', 'max:120'],
            'other_names' => ['required_without:full_name', 'nullable', 'string', 'max:160'],
            'full_name' => ['required_without_all:surname,other_names', 'nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'town_of_origin' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255'],
            'local_government_of_origin' => ['nullable', 'string', 'max:255'],
            'state_of_origin' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'height' => ['nullable', 'string', 'max:50'],
            'phone_number' => ['required', 'string', 'max:50'],
            'next_of_kin_name' => ['nullable', 'string', 'max:255'],
            'next_of_kin_relationship' => ['nullable', 'string', 'max:120'],
            'next_of_kin_phone' => ['nullable', 'string', 'max:50'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'wife_name' => ['nullable', 'string', 'max:255'],
            'house_address' => ['nullable', 'string'],
            'office_address' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'dynamic_fields' => ['nullable', 'array'],
        ];
    }

    private function normalizeLegacyPayload(array $validated): array
    {
        if ((! ($validated['surname'] ?? null) || ! ($validated['other_names'] ?? null)) && ! empty($validated['full_name'])) {
            $parts = preg_split('/\s+/', trim((string) $validated['full_name']), 2);
            $validated['surname'] = $validated['surname'] ?? ($parts[0] ?? null);
            $validated['other_names'] = $validated['other_names'] ?? ($parts[1] ?? null);
        }

        if (empty($validated['house_address']) && ! empty($validated['address'])) {
            $validated['house_address'] = $validated['address'];
        }

        if (empty($validated['branch_id'])) {
            $validated['branch_id'] = Branch::query()->value('id');
        }

        if (empty($validated['full_name']) && (! empty($validated['surname']) || ! empty($validated['other_names']))) {
            $validated['full_name'] = trim(($validated['surname'] ?? '').' '.($validated['other_names'] ?? ''));
        }

        if (empty($validated['address']) && ! empty($validated['house_address'])) {
            $validated['address'] = $validated['house_address'];
        }

        return $validated;
    }

    private function memberPayload(array $validated): array
    {
        $payload = collect(self::MEMBER_FIELDS)
            ->filter(fn ($field) => array_key_exists($field, $validated))
            ->mapWithKeys(function ($field) use ($validated) {
                $value = $validated[$field];

                return [$field => $value === '' ? null : $value];
            })
            ->all();

        if (array_key_exists('full_name', $validated)) {
            $payload['full_name'] = $validated['full_name'];
        }

        if (array_key_exists('address', $validated)) {
            $payload['address'] = $validated['address'];
        }

        if (array_key_exists('dynamic_fields', $validated)) {
            $payload['dynamic_fields'] = $validated['dynamic_fields'];
        }

        if (empty($payload['full_name']) && (! empty($payload['surname']) || ! empty($payload['other_names']))) {
            $payload['full_name'] = trim(($payload['surname'] ?? '').' '.($payload['other_names'] ?? ''));
        }

        if (empty($payload['address']) && ! empty($payload['house_address'])) {
            $payload['address'] = $payload['house_address'];
        }

        return $payload;
    }

    private function extractDynamicFields(array $dynamicFieldRows): ?array
    {
        $dynamicFields = [];

        foreach ($dynamicFieldRows as $dynamicField) {
            $key = trim($dynamicField['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $dynamicFields[$key] = $dynamicField['value'] ?? '';
        }

        return $dynamicFields ?: null;
    }

    private function normalizeCsvHeader(string $header): string
    {
        $normalized = strtolower(trim($header));
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? '';

        return trim($normalized, '_');
    }

    private function canonicalCsvField(string $normalizedHeader): string
    {
        return match ($normalizedHeader) {
            'dob' => 'date_of_birth',
            'local_government_of_oigin' => 'local_government_of_origin',
            'lga_of_origin', 'l_g_a_of_origin' => 'local_government_of_origin',
            'relationship_of_next_of_kin' => 'next_of_kin_relationship',
            'next_of_kin_phone_number', 'next_of_kin_phone_no' => 'next_of_kin_phone',
            'next_of_kin' => 'next_of_kin_name',
            'father_s_name', 'fathers_name' => 'father_name',
            'mother_s_name', 'mothers_name' => 'mother_name',
            'wife_s_name', 'wifes_name' => 'wife_name',
            default => $normalizedHeader,
        };
    }

    private function isEmptyCsvRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function mapCsvRowToMemberData(array $preparedHeaders, array $row): array
    {
        $memberData = [
            'branch_name' => null,
            'title' => null,
            'surname' => null,
            'other_names' => null,
            'date_of_birth' => null,
            'place_of_birth' => null,
            'town_of_origin' => null,
            'village' => null,
            'local_government_of_origin' => null,
            'state_of_origin' => null,
            'occupation' => null,
            'height' => null,
            'phone_number' => null,
            'next_of_kin_name' => null,
            'next_of_kin_relationship' => null,
            'next_of_kin_phone' => null,
            'father_name' => null,
            'mother_name' => null,
            'wife_name' => null,
            'house_address' => null,
            'office_address' => null,
            'photo' => null,
            'dynamic_fields' => [],
        ];

        $coreFields = [
            'title',
            'surname',
            'other_names',
            'date_of_birth',
            'place_of_birth',
            'town_of_origin',
            'village',
            'local_government_of_origin',
            'state_of_origin',
            'occupation',
            'height',
            'phone_number',
            'next_of_kin_name',
            'next_of_kin_relationship',
            'next_of_kin_phone',
            'father_name',
            'mother_name',
            'wife_name',
            'house_address',
            'office_address',
            'photo',
        ];

        foreach ($preparedHeaders as $index => $header) {
            $value = isset($row[$index]) ? trim((string) $row[$index]) : '';
            $canonicalField = $this->canonicalCsvField($header['normalized']);

            if ($header['normalized'] === '') {
                continue;
            }

            if (in_array($canonicalField, ['branch', 'branch_name'], true)) {
                $memberData['branch_name'] = $value !== '' ? $value : null;

                continue;
            }

            if ($canonicalField === 'address') {
                $memberData['house_address'] = $value !== '' ? $value : null;

                continue;
            }

            if (in_array($canonicalField, $coreFields, true)) {
                $memberData[$canonicalField] = $value !== '' ? $value : null;

                if (in_array($canonicalField, ['occupation', 'state_of_origin'], true) && $value !== '') {
                    $memberData['dynamic_fields'][$canonicalField] = $value;
                }

                continue;
            }

            $memberData['dynamic_fields'][$header['original']] = $value;
        }

        if ($memberData['dynamic_fields'] === []) {
            $memberData['dynamic_fields'] = null;
        }

        return $memberData;
    }

    private function buildImportPreview(string $filePath): array
    {
        $file = fopen($filePath, 'r');

        if ($file === false) {
            return ['error' => 'Unable to read the uploaded CSV file.'];
        }

        $headers = fgetcsv($file);

        if ($headers === false || count(array_filter($headers, fn ($header) => trim((string) $header) !== '')) === 0) {
            fclose($file);

            return ['error' => 'The CSV file must contain a header row.'];
        }

        $preparedHeaders = collect($headers)->map(function ($header) {
            $original = trim((string) $header);

            return [
                'original' => $original,
                'normalized' => $this->normalizeCsvHeader($original),
            ];
        })->values()->all();

        $rows = [];
        $rowNumber = 1;

        while (($csvRow = fgetcsv($file)) !== false) {
            $rowNumber++;

            if ($this->isEmptyCsvRow($csvRow)) {
                continue;
            }

            $rows[] = [
                'row_number' => $rowNumber,
                'data' => $csvRow,
            ];
        }

        fclose($file);

        if ($rows === []) {
            return ['error' => 'The CSV file does not contain any member rows to import.'];
        }

        $branchMap = Branch::query()
            ->get(['id', 'name'])
            ->mapWithKeys(function (Branch $branch) {
                return [strtolower(trim($branch->name)) => $branch->id];
            });
        $defaultBranchId = Branch::query()->value('id');

        $existingPhoneNumbers = Member::query()
            ->pluck('phone_number')
            ->filter()
            ->map(fn ($value) => trim((string) $value))
            ->flip();

        $existingEmails = $this->existingDynamicEmailValues();
        $seenPhones = [];
        $seenEmails = [];
        $previewRows = [];
        $summary = [
            'ready' => 0,
            'duplicates' => 0,
            'invalid' => 0,
            'total' => 0,
        ];

        foreach ($rows as $row) {
            $memberData = $this->mapCsvRowToMemberData($preparedHeaders, $row['data']);
            $issues = [];

            if (($memberData['branch_name'] ?? null) === null) {
                if ($defaultBranchId) {
                    $memberData['branch_id'] = $defaultBranchId;
                }
            } elseif (! $branchMap->has(strtolower(trim((string) $memberData['branch_name'])))) {
                $issues[] = 'Branch was not found. Create the branch first, then import again.';
            } else {
                $memberData['branch_id'] = $branchMap[strtolower(trim((string) $memberData['branch_name']))];
            }

            if (($memberData['surname'] ?? null) === null) {
                $issues[] = 'Surname is required.';
            }

            if (($memberData['other_names'] ?? null) === null) {
                $issues[] = 'Other names are required.';
            }

            if (($memberData['phone_number'] ?? null) === null) {
                $issues[] = 'Phone number is required.';
            }

            if (($memberData['phone_number'] ?? null) !== null) {
                $phone = trim((string) $memberData['phone_number']);

                if (isset($existingPhoneNumbers[$phone])) {
                    $issues[] = 'Duplicate phone number already exists.';
                }

                if (isset($seenPhones[$phone])) {
                    $issues[] = 'Duplicate phone number also appears in row '.$seenPhones[$phone].'.';
                } else {
                    $seenPhones[$phone] = $row['row_number'];
                }
            }

            $email = $this->extractEmailFromDynamicFields($memberData['dynamic_fields'] ?? []);

            if ($email !== null) {
                if (isset($existingEmails[$email])) {
                    $issues[] = 'Duplicate email already exists in imported member data.';
                }

                if (isset($seenEmails[$email])) {
                    $issues[] = 'Duplicate email also appears in row '.$seenEmails[$email].'.';
                } else {
                    $seenEmails[$email] = $row['row_number'];
                }
            }

            $status = 'ready';

            if ($issues !== []) {
                $hasDuplicateIssue = collect($issues)->contains(fn ($issue) => str_contains(strtolower($issue), 'duplicate'));
                $status = $hasDuplicateIssue ? 'duplicate' : 'invalid';
            }

            $summary['total']++;
            $summary[$status === 'ready' ? 'ready' : ($status === 'duplicate' ? 'duplicates' : 'invalid')]++;

            $previewRows[] = [
                'row_number' => $row['row_number'],
                'status' => $status,
                'issues' => $issues,
                'member_data' => $memberData,
                'dynamic_field_count' => count($memberData['dynamic_fields'] ?? []),
            ];
        }

        return [
            'headers' => $preparedHeaders,
            'rows' => $previewRows,
            'summary' => $summary,
        ];
    }

    private function existingDynamicEmailValues(): array
    {
        $emails = [];

        Member::query()
            ->whereNotNull('dynamic_fields')
            ->get(['dynamic_fields'])
            ->each(function (Member $member) use (&$emails) {
                foreach ($member->dynamic_fields ?? [] as $key => $value) {
                    if ($this->normalizeCsvHeader((string) $key) !== 'email') {
                        continue;
                    }

                    $email = strtolower(trim((string) $value));

                    if ($email !== '') {
                        $emails[$email] = true;
                    }
                }
            });

        return $emails;
    }

    private function extractEmailFromDynamicFields(?array $dynamicFields): ?string
    {
        foreach ($dynamicFields ?? [] as $key => $value) {
            if ($this->normalizeCsvHeader((string) $key) !== 'email') {
                continue;
            }

            $email = strtolower(trim((string) $value));

            return $email !== '' ? $email : null;
        }

        return null;
    }

    private function memberSearchQuery(?string $search, ?int $branchId = null): Builder
    {
        return Member::query()
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->when($search, function ($query, $searchTerm) {
                $query->where(function ($memberQuery) use ($searchTerm) {
                    $memberQuery->where('surname', 'like', '%'.$searchTerm.'%')
                        ->orWhere('other_names', 'like', '%'.$searchTerm.'%')
                        ->orWhere('phone_number', 'like', '%'.$searchTerm.'%');
                });
            });
    }

    private function availableDynamicFields(Builder $query): array
    {
        return $query->get(['dynamic_fields'])
            ->flatMap(fn (Member $member) => array_keys($member->dynamic_fields ?? []))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function prepareExportData(Request $request): array
    {
        $search = $request->input('search');
        $selectedBranchId = $request->integer('branch_id') ?: null;

        $allowedCoreFields = [
            'full_name',
            'address',
            'branch',
            'title',
            'surname',
            'other_names',
            'date_of_birth',
            'place_of_birth',
            'town_of_origin',
            'village',
            'local_government_of_origin',
            'state_of_origin',
            'occupation',
            'height',
            'phone_number',
            'next_of_kin_name',
            'next_of_kin_relationship',
            'next_of_kin_phone',
            'father_name',
            'mother_name',
            'wife_name',
            'house_address',
            'office_address',
            'photo',
            'created_at',
            'updated_at',
        ];

        $selectedCoreFields = collect($request->input('fields', ['branch', 'surname', 'other_names', 'phone_number', 'house_address']))
            ->filter(fn ($field) => in_array($field, $allowedCoreFields, true))
            ->values();

        $selectedDynamicFields = collect($request->input('dynamic_fields', []))
            ->map(fn ($field) => trim((string) $field))
            ->filter()
            ->values();

        if ($selectedCoreFields->isEmpty() && $selectedDynamicFields->isEmpty()) {
            $selectedCoreFields = collect(['branch', 'surname', 'other_names', 'phone_number', 'house_address']);
        }

        $members = $this->memberSearchQuery($search, $selectedBranchId)
            ->with('branch')
            ->latest()
            ->get();

        $headers = $selectedCoreFields
            ->merge($selectedDynamicFields)
            ->values()
            ->all();

        return [$selectedCoreFields, $selectedDynamicFields, $members, $headers];
    }

    private function resolveExportValue(Member $member, string $field): string
    {
        if ($field === 'branch') {
            return (string) ($member->branch?->name ?? '');
        }

        if ($field === 'full_name') {
            return $member->full_name;
        }

        if ($field === 'address') {
            return (string) ($member->address ?? $member->house_address ?? '');
        }

        $value = $member->{$field};

        if ($value instanceof CarbonInterface) {
            return $value->toDateTimeString();
        }

        return (string) ($value ?? '');
    }

    private function outputExcelRow(array|Collection $cells): void
    {
        echo '<Row>';

        foreach ($cells as $cell) {
            echo '<Cell><Data ss:Type="String">'.$this->escapeExcelValue((string) $cell).'</Data></Cell>';
        }

        echo '</Row>';
    }

    private function escapeExcelValue(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
