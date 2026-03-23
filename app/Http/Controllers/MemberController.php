<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class MemberController extends Controller
{
    private const IMPORT_PREVIEW_SESSION_KEY = 'member_import_preview';

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $memberQuery = $this->memberSearchQuery($search);
        $availableDynamicFields = $this->availableDynamicFields(clone $memberQuery);

        $members = $memberQuery
            ->latest()
            ->paginate(10);

        return view('members.index', compact('members', 'search', 'availableDynamicFields'));
    }

    public function backup(): StreamedResponse
    {
        $members = Member::query()->latest()->get();

        return response()->streamDownload(function () use ($members) {
            echo json_encode([
                'club' => 'Divine Source Friends Club of Nigeria',
                'abbreviation' => 'D.S.F.C',
                'generated_at' => now()->toIso8601String(),
                'member_count' => $members->count(),
                'members' => $members->map(function (Member $member) {
                    return [
                        'id' => $member->id,
                        'full_name' => $member->full_name,
                        'phone_number' => $member->phone_number,
                        'address' => $member->address,
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

        return view('members.create', compact('importPreview'));
    }

    public function show(int $id): View
    {
        $member = Member::findOrFail($id);

        return view('members.show', compact('member'));
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['dynamic_fields'] = $this->extractDynamicFields($request->input('dynamic_fields', []));

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('members', 'public');
        }

        Member::create($validated);

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
            Member::create($row['member_data']);
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
                    $value = $member->{$field};
                    $row[] = $value instanceof \Carbon\CarbonInterface ? $value->toDateTimeString() : $value;
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
                    $value = $member->{$field};
                    $row[] = $value instanceof \Carbon\CarbonInterface ? $value->toDateTimeString() : (string) ($value ?? '');
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

        $columns = ['full_name', 'phone_number', 'address', 'occupation', 'state_of_origin'];
        $sampleRow = ['Jane Doe', '08012345678', '12 Palm Street', 'Engineer', 'Delta'];

        return response()->streamDownload(function () use ($columns, $sampleRow) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $columns);
            fputcsv($handle, $sampleRow);

            fclose($handle);
        }, 'dsfc-members-template.csv', $headers);
    }

    public function edit(int $id): View
    {
        $member = Member::findOrFail($id);

        return view('members.edit', compact('member'));
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
            ->whereIn('id', $memberIds)
            ->orderBy('full_name')
            ->get();

        if ($members->isEmpty()) {
            return redirect()
                ->route('members.index')
                ->withErrors(['bulk_actions' => 'The selected members could not be found.']);
        }

        return view('members.bulk-edit', compact('members'));
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:members,id'],
            'address' => ['nullable', 'string'],
            'dynamic_fields' => ['nullable', 'array'],
        ]);

        $members = Member::query()
            ->whereIn('id', $validated['member_ids'])
            ->get();

        $bulkDynamicFields = $this->extractDynamicFields($request->input('dynamic_fields', [])) ?? [];
        $address = $validated['address'] ?? null;

        foreach ($members as $member) {
            $currentDynamicFields = $member->dynamic_fields ?? [];
            $member->update([
                'address' => $address !== null && $address !== '' ? $address : $member->address,
                'dynamic_fields' => array_replace($currentDynamicFields, $bulkDynamicFields) ?: null,
            ]);
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
            ->route('members.index')
            ->with('status', 'Selected members deleted successfully.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $member = Member::findOrFail($id);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'dynamic_fields' => ['nullable', 'array'],
        ]);
        $validated['dynamic_fields'] = $this->extractDynamicFields($request->input('dynamic_fields', []));

        if ($request->hasFile('photo')) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }

            $validated['photo'] = $request->file('photo')->store('members', 'public');
        }

        $member->update($validated);

        return redirect()
            ->route('members.index')
            ->with('status', 'Member updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $member = Member::findOrFail($id);

        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }

        $member->delete();

        return redirect()
            ->route('members.index')
            ->with('status', 'Member deleted successfully.');
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
            'full_name' => null,
            'phone_number' => null,
            'address' => null,
            'photo' => null,
            'dynamic_fields' => [],
        ];

        foreach ($preparedHeaders as $index => $header) {
            $value = isset($row[$index]) ? trim((string) $row[$index]) : '';

            if ($header['normalized'] === '') {
                continue;
            }

            if (in_array($header['normalized'], ['full_name', 'phone_number', 'address', 'photo'], true)) {
                $memberData[$header['normalized']] = $value !== '' ? $value : null;
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

            if (($memberData['full_name'] ?? null) === null) {
                $issues[] = 'Full name is required.';
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
                    $issues[] = 'Duplicate phone number also appears in row ' . $seenPhones[$phone] . '.';
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
                    $issues[] = 'Duplicate email also appears in row ' . $seenEmails[$email] . '.';
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

    private function memberSearchQuery(?string $search): Builder
    {
        return Member::query()
            ->when($search, function ($query, $searchTerm) {
                $query->where(function ($memberQuery) use ($searchTerm) {
                    $memberQuery->where('full_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('phone_number', 'like', '%' . $searchTerm . '%');
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
        $selectedCoreFields = collect($request->input('fields', ['full_name', 'phone_number', 'address']))
            ->filter(fn ($field) => in_array($field, ['full_name', 'phone_number', 'address', 'photo', 'created_at', 'updated_at'], true))
            ->values();

        $selectedDynamicFields = collect($request->input('dynamic_fields', []))
            ->map(fn ($field) => trim((string) $field))
            ->filter()
            ->values();

        if ($selectedCoreFields->isEmpty() && $selectedDynamicFields->isEmpty()) {
            $selectedCoreFields = collect(['full_name', 'phone_number', 'address']);
        }

        $members = $this->memberSearchQuery($search)
            ->latest()
            ->get();

        $headers = $selectedCoreFields
            ->merge($selectedDynamicFields)
            ->values()
            ->all();

        return [$selectedCoreFields, $selectedDynamicFields, $members, $headers];
    }

    private function outputExcelRow(array|Collection $cells): void
    {
        echo '<Row>';

        foreach ($cells as $cell) {
            echo '<Cell><Data ss:Type="String">' . $this->escapeExcelValue((string) $cell) . '</Data></Cell>';
        }

        echo '</Row>';
    }

    private function escapeExcelValue(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
