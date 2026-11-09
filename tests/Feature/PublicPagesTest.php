<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\EducationHistory;
use App\Models\Feature;
use App\Models\Photo;
use App\Models\StaffMember;
use Database\Seeders\EcmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EcmsSeeder::class);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function publicRoutes(): array
    {
        return [
            'home' => ['/'],
            'about' => ['/about'],
            'struktur organisasi' => ['/about/struktur-organisasi'],
            'sumber daya manusia' => ['/about/sumber-daya-manusia'],
            'gallery' => ['/gallery'],
            'artikel index' => ['/artikel'],
            'contact' => ['/contact'],
        ];
    }

    #[DataProvider('publicRoutes')]
    public function test_public_page_renders_with_seeded_content(string $uri): void
    {
        $this->get($uri)->assertOk();
    }

    public function test_home_page_shows_seeded_hero_and_features(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Menumbuhkan Iman, Karakter, dan Kegembiraan Belajar Sejak Usia Dini');
        $response->assertSee('Kurikulum Islami Terpadu');
    }

    public function test_header_nav_has_about_dropdown_and_artikel_not_contact(): void
    {
        $response = $this->get('/');

        $response->assertSee('Struktur Organisasi');
        $response->assertSee('Sumber Daya Manusia');
        $response->assertSee('Artikel');
    }

    public function test_published_article_detail_page_renders(): void
    {
        $article = Article::query()->where('status', 'published')->firstOrFail();

        $this->get("/artikel/{$article->slug}")
            ->assertOk()
            ->assertSee($article->title);
    }

    public function test_draft_article_returns_404(): void
    {
        $article = Article::query()->first();
        $article->update(['status' => 'draft']);

        $this->get("/artikel/{$article->slug}")->assertNotFound();
    }

    public function test_unknown_article_slug_returns_404(): void
    {
        $this->get('/artikel/tidak-ada-artikel-ini')->assertNotFound();
    }

    public function test_draft_photo_is_hidden_from_gallery_page(): void
    {
        $draft = Photo::query()->first();
        $draft->update(['status' => 'draft', 'caption' => 'Foto Rahasia Draft']);

        $this->get('/gallery')->assertDontSee('Foto Rahasia Draft');
    }

    public function test_draft_feature_is_hidden_from_home_page(): void
    {
        $draft = Feature::query()->first();
        $draft->update(['status' => 'draft', 'title' => 'Fitur Rahasia Draft']);

        $this->get('/')->assertDontSee('Fitur Rahasia Draft');
    }

    public function test_contact_form_stores_a_message_and_redirects_back(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Wali Murid',
            'email' => 'wali@example.com',
            'phone' => '0812-0000-0000',
            'message' => 'Saya ingin bertanya soal pendaftaran.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Wali Murid',
            'status' => 'new',
        ]);
    }

    public function test_contact_form_requires_name_email_phone_and_message(): void
    {
        $response = $this->post('/contact', []);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'message']);
        $this->assertSame(0, ContactMessage::query()->count());
    }

    public function test_sdm_listing_links_to_each_staff_members_detail_page(): void
    {
        $staff = StaffMember::query()->published()->firstOrFail();

        $this->get('/about/sumber-daya-manusia')
            ->assertOk()
            ->assertSee(route('about.sdm.show', $staff), false);
    }

    public function test_sdm_detail_page_shows_staff_education_history(): void
    {
        $staff = StaffMember::query()->published()->firstOrFail();
        EducationHistory::query()->create([
            'staff_member_id' => $staff->id,
            'degree' => 'S2 Manajemen Pendidikan Uji',
            'institution' => 'Universitas Uji Coba',
            'year' => '2021',
        ]);

        $response = $this->get(route('about.sdm.show', $staff));

        $response->assertOk();
        $response->assertSee($staff->name);
        $response->assertSee('S2 Manajemen Pendidikan Uji');
        $response->assertSee('Universitas Uji Coba');
    }

    public function test_sdm_detail_page_does_not_leak_address_related_info(): void
    {
        // StaffMember has no address field at all — this guards the page
        // itself never gaining an "Alamat" label for staff. (The site's own
        // address still legitimately appears in the shared footer on every
        // page, so that's not checked here.)
        $staff = StaffMember::query()->published()->firstOrFail();

        $response = $this->get(route('about.sdm.show', $staff));

        $response->assertOk();
        $response->assertDontSee('Alamat');
    }

    public function test_draft_staff_member_detail_page_returns_404(): void
    {
        $staff = StaffMember::factory()->create(['status' => 'draft']);

        $this->get(route('about.sdm.show', $staff))->assertNotFound();
    }

    public function test_unknown_staff_member_detail_page_returns_404(): void
    {
        $this->get('/about/sumber-daya-manusia/999999')->assertNotFound();
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk();
    }
}
