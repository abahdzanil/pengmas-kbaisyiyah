<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\EducationHistory;
use App\Models\Feature;
use App\Models\SectionContent;
use App\Models\SiteSetting;
use App\Models\StaffMember;
use App\Models\User;
use Database\Seeders\EcmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_view_dashboard(): void
    {
        // Seed real content (not just an empty schema) so the dashboard's
        // "Terakhir Diperbarui" stat and every collection-manager panel
        // render against realistic data, not all-null edge cases.
        $this->seed(EcmsSeeder::class);
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Kelola Home');
        $response->assertSee('Kelola Contact');
        $response->assertSee('data-collection-manager', false);
    }

    public function test_every_collection_with_an_image_field_renders_an_upload_control(): void
    {
        // The API side has always accepted an 'image' upload, so API tests
        // passed while the form rendered no file input at all — gallery
        // photos and article covers both shipped that way. This asserts the
        // rendered form schema itself, which is where the gap was.
        $this->seed(EcmsSeeder::class);
        $admin = User::factory()->create(['is_admin' => true]);

        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

        $withImages = collect(config('ecms.collections'))
            ->filter(fn ($config) => ! empty($config['image_field']));

        $this->assertNotEmpty($withImages, 'Expected at least one collection to declare an image_field.');

        foreach ($withImages as $resource => $config) {
            preg_match(
                '/data-resource="'.preg_quote($resource, '/').'"(.*?)data-fields=\'(.*?)\'/s',
                $html,
                $matches
            );

            $this->assertNotEmpty($matches, "No collection manager rendered for [{$resource}].");

            $fields = json_decode(htmlspecialchars_decode($matches[2], ENT_QUOTES), true);
            $types = collect($fields)->pluck('type');

            $this->assertTrue(
                $types->contains('image'),
                "Collection [{$resource}] declares image_field '{$config['image_field']}' but its form renders no image field (types: {$types->implode(', ')})."
            );
        }
    }

    public function test_admin_can_update_single_instance_content(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        SectionContent::query()->create(['section' => 'home', 'key' => 'hero_title', 'value' => 'Old title', 'type' => 'text']);

        $response = $this->actingAs($admin)->patchJson('/admin/content/home/hero_title', [
            'value' => 'New title',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertSame('New title', SectionContent::forSection('home')['hero_title']);
    }

    public function test_admin_can_crud_a_collection_item(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $create = $this->actingAs($admin)->postJson('/admin/features', [
            'title' => 'Fitur Baru',
            'description' => 'Deskripsi fitur baru.',
        ]);
        $create->assertCreated()->assertJson(['success' => true]);
        $id = $create->json('data.id');

        $this->actingAs($admin)->putJson("/admin/features/{$id}", [
            'title' => 'Fitur Diperbarui',
            'description' => 'Deskripsi diperbarui.',
        ])->assertOk();
        $this->assertSame('Fitur Diperbarui', Feature::find($id)->title);

        $this->actingAs($admin)->patchJson("/admin/features/{$id}/status", [
            'status' => 'published',
        ])->assertOk();
        $this->assertSame('published', Feature::find($id)->status);

        $this->actingAs($admin)->deleteJson("/admin/features/{$id}")->assertOk();
        $this->assertModelMissing(Feature::find($id) ?? new Feature(['id' => $id]));
    }

    public function test_admin_can_create_article_with_auto_slug(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson('/admin/articles', [
            'title' => 'Kunjungan Dinas Pendidikan',
            'excerpt' => 'Ringkasan kunjungan.',
            'body' => '<p>Isi artikel.</p>',
            'published_at' => now()->toDateString(),
        ]);

        $response->assertCreated();
        $this->assertSame('kunjungan-dinas-pendidikan', $response->json('data.slug'));
        $this->assertFalse((bool) $response->json('data.is_highlight'));
    }

    public function test_admin_can_upload_and_replace_a_collection_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $create = $this->actingAs($admin)->post('/admin/staff', [
            'name' => 'Ustadzah Fulanah',
            'role' => 'Pendidik Kelompok A',
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ]);
        $create->assertCreated();
        $id = $create->json('data.id');
        $firstPath = StaffMember::find($id)->photo_path;
        $this->assertNotNull($firstPath);
        Storage::disk('public')->assertExists($firstPath);

        $this->actingAs($admin)->post("/admin/staff/{$id}", [
            '_method' => 'PUT',
            'name' => 'Ustadzah Fulanah',
            'role' => 'Pendidik Kelompok A',
            'image' => UploadedFile::fake()->image('foto-baru.jpg'),
        ])->assertOk();

        Storage::disk('public')->assertMissing($firstPath);
        $this->assertNotNull(StaffMember::find($id)->photo_path);
    }

    public function test_admin_can_reorder_a_collection(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $first = Feature::factory()->create(['sort_order' => 1]);
        $second = Feature::factory()->create(['sort_order' => 2]);
        $third = Feature::factory()->create(['sort_order' => 3]);

        $this->actingAs($admin)->postJson('/admin/features/reorder', [
            'ids' => [$third->id, $first->id, $second->id],
        ])->assertOk();

        $this->assertSame(1, $third->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);
        $this->assertSame(3, $second->fresh()->sort_order);
    }

    public function test_reorder_rejects_collections_without_sort_support(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // 'articles' has has_sort => false in config/ecms.php (ordered by
        // published_at instead).
        $this->actingAs($admin)->postJson('/admin/articles/reorder', [
            'ids' => [1, 2],
        ])->assertStatus(422);
    }

    public function test_staff_initials_are_auto_derived_and_not_admin_editable(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // No 'initials' field is sent — config/ecms.php no longer exposes
        // one; the admin form uploads a photo instead.
        $response = $this->actingAs($admin)->postJson('/admin/staff', [
            'name' => 'Budi Santoso',
            'role' => 'Pendidik Kelompok A',
        ]);

        $response->assertCreated();
        $this->assertSame('BS', StaffMember::findOrFail($response->json('data.id'))->initials);
    }

    public function test_staff_initials_recompute_when_name_changes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = StaffMember::factory()->create(['name' => 'Budi Santoso', 'initials' => 'BS']);

        $this->actingAs($admin)->putJson("/admin/staff/{$staff->id}", [
            'name' => 'Citra Dewi', 'role' => $staff->role,
        ])->assertOk();

        $this->assertSame('CD', $staff->fresh()->initials);
    }

    public function test_admin_can_add_education_history_while_creating_a_staff_member(): void
    {
        // Education history is a 'repeater' field edited inline on the staff
        // form itself (config/ecms.php), not a separate top-level collection
        // — one POST /admin/staff creates both.
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson('/admin/staff', [
            'name' => 'Budi Santoso',
            'role' => 'Pendidik Kelompok A',
            'education_histories' => [
                ['degree' => 'S1 Pendidikan Anak Usia Dini', 'institution' => 'Universitas Negeri Malang', 'year' => '2018'],
                ['degree' => 'S2 Manajemen Pendidikan', 'institution' => 'Universitas Negeri Malang', 'year' => '2021'],
            ],
        ]);

        $response->assertCreated();
        $staff = StaffMember::findOrFail($response->json('data.id'));
        $this->assertCount(2, $staff->educationHistories);
        $this->assertSame('S1 Pendidikan Anak Usia Dini', $staff->educationHistories->first()->degree);
    }

    public function test_updating_a_staff_member_resyncs_their_education_history(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = StaffMember::factory()->create();
        $existing = EducationHistory::factory()->create(['staff_member_id' => $staff->id, 'degree' => 'Riwayat Lama']);

        $this->actingAs($admin)->putJson("/admin/staff/{$staff->id}", [
            'name' => $staff->name,
            'role' => $staff->role,
            'education_histories' => [
                ['degree' => 'Riwayat Baru', 'institution' => 'Institusi Baru', 'year' => '2023'],
            ],
        ])->assertOk();

        $this->assertDatabaseMissing('education_histories', ['id' => $existing->id]);
        $this->assertDatabaseHas('education_histories', ['staff_member_id' => $staff->id, 'degree' => 'Riwayat Baru']);
    }

    public function test_submitting_no_education_rows_clears_existing_history(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = StaffMember::factory()->create();
        EducationHistory::factory()->create(['staff_member_id' => $staff->id]);

        $this->actingAs($admin)->putJson("/admin/staff/{$staff->id}", [
            'name' => $staff->name,
            'role' => $staff->role,
        ])->assertOk();

        $this->assertSame(0, EducationHistory::where('staff_member_id', $staff->id)->count());
    }

    public function test_an_education_row_missing_a_required_child_field_fails_validation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson('/admin/staff', [
            'name' => 'Budi Santoso',
            'role' => 'Pendidik Kelompok A',
            'education_histories' => [
                ['degree' => 'S1 Pendidikan Anak Usia Dini'], // institution missing
            ],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('education_histories.0.institution');
    }

    public function test_soft_deleting_a_staff_member_keeps_their_education_history(): void
    {
        // StaffMember uses SoftDeletes, so the admin "Hapus" button never
        // issues a real DELETE against staff_members — the FK's
        // cascadeOnDelete() (a DB-level ON DELETE trigger) never fires, and
        // the education rows are deliberately left intact (recoverable, and
        // still correct if the staff member is ever restored).
        $admin = User::factory()->create(['is_admin' => true]);
        $staff = StaffMember::factory()->create();
        $education = EducationHistory::factory()->create(['staff_member_id' => $staff->id]);

        $this->actingAs($admin)->deleteJson("/admin/staff/{$staff->id}")->assertOk();

        $this->assertSoftDeleted($staff);
        $this->assertDatabaseHas('education_histories', ['id' => $education->id]);
    }

    public function test_force_deleting_a_staff_member_cascades_their_education_history(): void
    {
        $staff = StaffMember::factory()->create();
        $education = EducationHistory::factory()->create(['staff_member_id' => $staff->id]);

        $staff->forceDelete();

        $this->assertDatabaseMissing('education_histories', ['id' => $education->id]);
    }

    public function test_unknown_collection_returns_404(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->getJson('/admin/not-a-real-collection')->assertNotFound();
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        SiteSetting::current();

        $this->actingAs($admin)->putJson('/admin/settings', [
            'site_name' => "KB 'Aisyiyah 2 Dau Baru",
            'phone' => '0812-0000-0000',
        ])->assertOk();

        $this->assertSame("KB 'Aisyiyah 2 Dau Baru", SiteSetting::current()->site_name);
    }

    public function test_updating_contact_fields_only_does_not_clobber_site_name(): void
    {
        // Regression guard: the admin dashboard splits site_settings across
        // two forms ("Kelola Contact" and "Pengaturan Situs"); without
        // 'sometimes' on every rule, a contact-only submission would
        // validate the absent 'site_name' as null (nullable allows it) and
        // Eloquent's update() would overwrite the real value with null.
        $admin = User::factory()->create(['is_admin' => true]);
        SiteSetting::current()->update(['site_name' => "KB 'Aisyiyah 2 Dau"]);

        $this->actingAs($admin)->putJson('/admin/settings', [
            'address' => 'Alamat Baru',
            'phone' => '0812-1111-1111',
        ])->assertOk();

        $settings = SiteSetting::current();
        $this->assertSame("KB 'Aisyiyah 2 Dau", $settings->site_name);
        $this->assertSame('Alamat Baru', $settings->address);
    }

    public function test_admin_can_manage_contact_messages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $message = ContactMessage::query()->create([
            'name' => 'Wali Murid',
            'phone' => '0812xxxx',
            'message' => 'Tanya info pendaftaran',
            'status' => 'new',
        ]);

        $this->actingAs($admin)->getJson('/admin/messages')->assertOk();

        $this->actingAs($admin)->patchJson("/admin/messages/{$message->id}/read")->assertOk();
        $this->assertSame('read', $message->fresh()->status);

        $this->actingAs($admin)->deleteJson("/admin/messages/{$message->id}")->assertOk();
        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }
}
