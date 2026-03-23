<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_member_pages(): void
    {
        $member = Member::create([
            'full_name' => 'Ada Lovelace',
            'phone_number' => '08012345678',
            'address' => '12 Computer Street',
        ]);

        $this->get(route('members.index'))->assertRedirect(route('login'));
        $this->get(route('members.create'))->assertRedirect(route('login'));
        $this->get(route('members.edit', $member))->assertRedirect(route('login'));
        $this->post(route('members.store'), [])->assertRedirect(route('login'));
        $this->put(route('members.update', $member), [])->assertRedirect(route('login'));
        $this->delete(route('members.destroy', $member))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_members_index_page(): void
    {
        $user = User::factory()->create();
        $member = Member::create([
            'full_name' => 'Ada Lovelace',
            'phone_number' => '08012345678',
            'address' => '12 Computer Street',
        ]);

        $response = $this->actingAs($user)->get(route('members.index'));

        $response->assertOk();
        $response->assertSee('Member Directory');
        $response->assertSee($member->full_name);
    }

    public function test_authenticated_user_can_view_member_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('members.create'));

        $response->assertOk();
        $response->assertSee('Add Member');
    }

    public function test_authenticated_user_can_view_member_profile_page(): void
    {
        $user = User::factory()->create();
        $member = Member::create([
            'full_name' => 'Dorothy Vaughan',
            'phone_number' => '08099990000',
            'address' => 'Computation Avenue',
            'dynamic_fields' => ['Department' => 'Mathematics'],
        ]);

        $response = $this->actingAs($user)->get(route('members.show', $member));

        $response->assertOk();
        $response->assertSee('Member Profile');
        $response->assertSee($member->full_name);
        $response->assertSee('Department');
    }

    public function test_authenticated_user_can_create_member_with_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('members.store'), [
            'full_name' => 'Grace Hopper',
            'phone_number' => '08087654321',
            'address' => '44 Navy Avenue',
            'photo' => $this->fakePngUpload('member.png'),
        ]);

        $response->assertRedirect(route('members.index'));
        $response->assertSessionHas('status', 'Member created successfully.');

        $member = Member::query()->first();

        $this->assertNotNull($member);
        $this->assertSame('Grace Hopper', $member->full_name);
        $this->assertSame('08087654321', $member->phone_number);
        $this->assertSame('44 Navy Avenue', $member->address);
        $this->assertNotNull($member->photo);
        Storage::disk('public')->assertExists($member->photo);
    }

    public function test_authenticated_user_sees_validation_errors_when_creating_invalid_member(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('members.create'))
            ->post(route('members.store'), [
                'full_name' => '',
                'phone_number' => '',
                'address' => 'Missing required fields',
            ]);

        $response->assertRedirect(route('members.create'));
        $response->assertSessionHasErrors(['full_name', 'phone_number']);
        $this->assertDatabaseCount('members', 0);
    }

    public function test_authenticated_user_can_view_member_edit_page(): void
    {
        $user = User::factory()->create();
        $member = Member::create([
            'full_name' => 'Mary Jackson',
            'phone_number' => '08022223333',
            'address' => 'Space Road',
        ]);

        $response = $this->actingAs($user)->get(route('members.edit', $member));

        $response->assertOk();
        $response->assertSee('Edit Member');
        $response->assertSee($member->full_name);
    }

    public function test_authenticated_user_can_update_member_and_replace_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $oldPhoto = $this->fakePngUpload('old-member.png')->store('members', 'public');

        $member = Member::create([
            'full_name' => 'Mary Jackson',
            'phone_number' => '08022223333',
            'address' => 'Space Road',
            'photo' => $oldPhoto,
        ]);

        $response = $this->actingAs($user)
            ->put(route('members.update', $member), [
                'full_name' => 'Mary Winston Jackson',
                'phone_number' => '08033334444',
                'address' => 'Updated Space Road',
                'photo' => $this->fakePngUpload('new-member.png'),
            ]);

        $response->assertRedirect(route('members.index'));
        $response->assertSessionHas('status', 'Member updated successfully.');

        $member->refresh();

        $this->assertSame('Mary Winston Jackson', $member->full_name);
        $this->assertSame('08033334444', $member->phone_number);
        $this->assertSame('Updated Space Road', $member->address);
        $this->assertNotSame($oldPhoto, $member->photo);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($member->photo);
    }

    public function test_authenticated_user_can_delete_member(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $photo = $this->fakePngUpload('delete-member.png')->store('members', 'public');

        $member = Member::create([
            'full_name' => 'Katherine Johnson',
            'phone_number' => '08055556666',
            'address' => 'Orbit Lane',
            'photo' => $photo,
        ]);

        $response = $this->actingAs($user)->delete(route('members.destroy', $member));

        $response->assertRedirect(route('members.index'));
        $response->assertSessionHas('status', 'Member deleted successfully.');
        $this->assertDatabaseMissing('members', ['id' => $member->id]);
        Storage::disk('public')->assertMissing($photo);
    }

    public function test_authenticated_user_can_import_members_from_csv_with_dynamic_fields(): void
    {
        $user = User::factory()->create();
        $csv = implode("\n", [
            'full_name,phone_number,address,occupation,state_of_origin',
            'Jane Doe,08010101010,12 Palm Street,Engineer,Delta',
            'John Smith,08020202020,45 Unity Road,Teacher,Ondo',
        ]);

        $filePath = tempnam(sys_get_temp_dir(), 'members-import-');
        file_put_contents($filePath, $csv);

        $upload = new UploadedFile($filePath, 'members.csv', 'text/csv', null, true);

        $response = $this->actingAs($user)->post(route('members.import'), [
            'csv_file' => $upload,
        ]);

        $response->assertRedirect(route('members.create'));
        $response->assertSessionHas('status', 'CSV processed. Review the preview below before confirming import.');
        $response->assertSessionHas('member_import_preview');

        $confirmResponse = $this->actingAs($user)->post(route('members.import.confirm'));

        $confirmResponse->assertRedirect(route('members.index'));
        $confirmResponse->assertSessionHas('status', 'Members imported successfully from CSV.');

        $this->assertDatabaseHas('members', [
            'full_name' => 'Jane Doe',
            'phone_number' => '08010101010',
            'address' => '12 Palm Street',
        ]);

        $this->assertDatabaseHas('members', [
            'full_name' => 'John Smith',
            'phone_number' => '08020202020',
            'address' => '45 Unity Road',
        ]);

        $member = Member::where('full_name', 'Jane Doe')->firstOrFail();

        $this->assertSame([
            'occupation' => 'Engineer',
            'state_of_origin' => 'Delta',
        ], $member->dynamic_fields);
    }

    public function test_import_preview_flags_duplicate_phone_numbers_and_skips_confirm_import(): void
    {
        $user = User::factory()->create();

        Member::create([
            'full_name' => 'Existing Member',
            'phone_number' => '08010101010',
            'address' => 'Already Stored',
        ]);

        $csv = implode("\n", [
            'full_name,phone_number,address,email',
            'Jane Doe,08010101010,12 Palm Street,jane@example.com',
            'John Smith,08010101010,45 Unity Road,jane@example.com',
        ]);

        $filePath = tempnam(sys_get_temp_dir(), 'members-duplicate-import-');
        file_put_contents($filePath, $csv);

        $upload = new UploadedFile($filePath, 'members-duplicates.csv', 'text/csv', null, true);

        $response = $this->actingAs($user)->post(route('members.import'), [
            'csv_file' => $upload,
        ]);

        $response->assertRedirect(route('members.create'));

        $preview = session('member_import_preview');

        $this->assertNotNull($preview);
        $this->assertSame(0, $preview['summary']['ready']);
        $this->assertSame(2, $preview['summary']['duplicates']);

        $confirmResponse = $this->actingAs($user)->post(route('members.import.confirm'));

        $confirmResponse->assertRedirect(route('members.create'));
        $confirmResponse->assertSessionHasErrors('csv_file');
        $this->assertDatabaseMissing('members', ['full_name' => 'Jane Doe']);
        $this->assertDatabaseMissing('members', ['full_name' => 'John Smith']);
    }

    public function test_authenticated_user_can_download_csv_template(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('members.import.template'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition', 'attachment; filename=dsfc-members-template.csv');
        $this->assertStringContainsString(
            'full_name,phone_number,address,occupation,state_of_origin',
            $response->streamedContent()
        );
    }

    public function test_authenticated_user_can_export_members_with_selected_core_and_dynamic_fields(): void
    {
        $user = User::factory()->create();

        Member::create([
            'full_name' => 'Jane Doe',
            'phone_number' => '08010101010',
            'address' => '12 Palm Street',
            'dynamic_fields' => [
                'occupation' => 'Engineer',
                'state_of_origin' => 'Delta',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('members.export', [
            'fields' => ['full_name', 'phone_number'],
            'dynamic_fields' => ['occupation'],
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'full_name,phone_number,occupation',
            $response->streamedContent()
        );
        $this->assertStringContainsString(
            '"Jane Doe",08010101010,Engineer',
            $response->streamedContent()
        );
    }

    public function test_authenticated_user_can_export_members_to_excel_format(): void
    {
        $user = User::factory()->create();

        Member::create([
            'full_name' => 'Excel User',
            'phone_number' => '08030303030',
            'address' => 'Spreadsheet Avenue',
            'dynamic_fields' => [
                'occupation' => 'Analyst',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('members.export.excel', [
            'fields' => ['full_name', 'phone_number'],
            'dynamic_fields' => ['occupation'],
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.ms-excel');
        $this->assertStringContainsString('Workbook', $response->streamedContent());
        $this->assertStringContainsString('Excel User', $response->streamedContent());
        $this->assertStringContainsString('Analyst', $response->streamedContent());
    }

    public function test_authenticated_user_can_download_full_member_backup(): void
    {
        $user = User::factory()->create();

        Member::create([
            'full_name' => 'Backup User',
            'phone_number' => '08040404040',
            'address' => 'Archive Road',
            'dynamic_fields' => ['occupation' => 'Archivist'],
        ]);

        $response = $this->actingAs($user)->get(route('members.backup'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
        $this->assertStringContainsString('Divine Source Friends Club of Nigeria', $response->streamedContent());
        $this->assertStringContainsString('Backup User', $response->streamedContent());
    }

    public function test_authenticated_user_can_view_bulk_edit_page_for_selected_members(): void
    {
        $user = User::factory()->create();
        $members = Member::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('members.bulk-edit'), [
            'member_ids' => $members->pluck('id')->all(),
        ]);

        $response->assertOk();
        $response->assertSee('Bulk Edit Selected Members');
    }

    public function test_authenticated_user_can_bulk_update_selected_members(): void
    {
        $user = User::factory()->create();
        $members = Member::factory()->count(2)->create([
            'address' => 'Old Address',
            'dynamic_fields' => ['department' => 'Choir'],
        ]);

        $response = $this->actingAs($user)->post(route('members.bulk-update'), [
            'member_ids' => $members->pluck('id')->all(),
            'address' => 'New Address',
            'dynamic_fields' => [
                ['key' => 'state_of_origin', 'value' => 'Lagos'],
            ],
        ]);

        $response->assertRedirect(route('members.index'));
        $response->assertSessionHas('status', 'Selected members updated successfully.');

        foreach ($members as $member) {
            $member->refresh();
            $this->assertSame('New Address', $member->address);
            $this->assertSame('Lagos', $member->dynamic_fields['state_of_origin']);
            $this->assertSame('Choir', $member->dynamic_fields['department']);
        }
    }

    public function test_authenticated_user_can_bulk_delete_selected_members(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $photo = $this->fakePngUpload('bulk-delete.png')->store('members', 'public');
        $members = collect([
            Member::create([
                'full_name' => 'Bulk Delete One',
                'phone_number' => '08050505050',
                'address' => 'One Lane',
                'photo' => $photo,
            ]),
            Member::create([
                'full_name' => 'Bulk Delete Two',
                'phone_number' => '08060606060',
                'address' => 'Two Lane',
            ]),
        ]);

        $response = $this->actingAs($user)->post(route('members.bulk-delete'), [
            'member_ids' => $members->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('members.index'));
        $response->assertSessionHas('status', 'Selected members deleted successfully.');
        $this->assertDatabaseMissing('members', ['id' => $members[0]->id]);
        $this->assertDatabaseMissing('members', ['id' => $members[1]->id]);
        Storage::disk('public')->assertMissing($photo);
    }

    private function fakePngUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'member-photo-');

        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WnKXu0AAAAASUVORK5CYII='));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
