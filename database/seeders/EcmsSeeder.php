<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\DevelopmentAspect;
use App\Models\EducationHistory;
use App\Models\Facility;
use App\Models\Feature;
use App\Models\GalleryCategory;
use App\Models\MissionPoint;
use App\Models\SectionContent;
use App\Models\SiteSetting;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EcmsSeeder extends Seeder
{
    public function run(): void
    {
        // The per-section seeders truncate before re-inserting so the seeder
        // stays re-runnable. MySQL refuses to TRUNCATE a table referenced by
        // a foreign key (staff_members <- education_histories), unlike
        // SQLite where Laravel compiles truncate to a plain DELETE — so FK
        // checks are lifted for the duration of the seed.
        Schema::withoutForeignKeyConstraints(function () {
            $this->seedAdmin();
            $this->seedSiteSettings();
            $this->seedHomeContent();
            $this->seedAboutContent();
            $this->seedStrukturOrganisasiContent();
            $this->seedSdmContent();
            $this->seedGalleryContent();
            $this->seedArtikelContent();
            $this->seedContactContent();
        });
    }

    private function seedAdmin(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@dau.com'],
            [
                'name' => 'Admin KB Aisyiyah 2 Dau',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedSiteSettings(): void
    {
        SiteSetting::query()->updateOrCreate([], [
            'site_name' => "KB 'Aisyiyah 2 Dau",
            'tagline' => 'Kelompok Bermain',
            'address' => 'Kecamatan Dau, Kabupaten Malang, Jawa Timur',
            'phone' => '0812-xxxx-xxxx',
            'email' => 'kbaisyiyah2dau@email.com',
            'instagram_handle' => '@kbaisyiyah2dau',
            'instagram_url' => 'https://www.instagram.com/kbaisyiyah2dau/',
            'maps_embed_url' => 'https://www.google.com/maps?q=KB%20Aisyiyah%202%20Dau%20Malang&output=embed',
            'operational_hours' => 'Senin – Jumat',
        ]);
    }

    /**
     * @param  array<string, string>  $values
     */
    private function putSection(string $section, array $values): void
    {
        foreach ($values as $key => [$value, $type]) {
            SectionContent::query()->updateOrCreate(
                ['section' => $section, 'key' => $key],
                ['value' => $value, 'type' => $type]
            );
        }
    }

    private function seedHomeContent(): void
    {
        $this->putSection('home', [
            'hero_eyebrow' => ['Pendidikan Anak Usia Dini · Dau, Kabupaten Malang', 'text'],
            'hero_title' => ['Menumbuhkan Iman, Karakter, dan Kegembiraan Belajar Sejak Usia Dini', 'text'],
            'hero_subtitle' => ["KB 'Aisyiyah 2 Dau adalah Kelompok Bermain di bawah naungan Pimpinan Cabang 'Aisyiyah Dau, Kabupaten Malang. Kami mendampingi anak usia 2–4 tahun tumbuh melalui bermain, kebiasaan islami, dan pembelajaran yang hangat bersama keluarga.", 'richtext'],
            'hero_button1_label' => ['Hubungi Kami', 'text'],
            'hero_button2_label' => ['Tentang Kami', 'text'],
            'hero_image' => [null, 'image'],
            'stat_usia_value' => ['2–4 Tahun', 'text'],
            'stat_naungan_value' => ["'Aisyiyah", 'text'],
            'stat_lokasi_value' => ['Dau, Malang', 'text'],
            'keunggulan_eyebrow' => ["Mengapa KB 'Aisyiyah 2 Dau", 'text'],
            'keunggulan_title' => ['Pendampingan Tumbuh Kembang yang Islami dan Menyeluruh', 'text'],
            'about_teaser_eyebrow' => ['Tentang Kami', 'text'],
            'about_teaser_title' => ["Bagian dari Gerakan 'Aisyiyah untuk Pendidikan Anak Usia Dini", 'text'],
            'about_teaser_body' => ["KB 'Aisyiyah 2 Dau adalah lembaga PAUD jalur Kelompok Bermain di bawah naungan Pimpinan Cabang 'Aisyiyah Dau, Kabupaten Malang — organisasi otonom perempuan Muhammadiyah yang telah bergerak di bidang pendidikan sejak 1917.", 'richtext'],
            'about_teaser_visi' => ['Terwujudnya generasi usia dini yang beriman, berakhlak mulia, cerdas, dan mandiri.', 'text'],
            'gallery_teaser_eyebrow' => ['Galeri Kegiatan', 'text'],
            'gallery_teaser_title' => ['Momen Belajar dan Bermain Sehari-hari', 'text'],
            'contact_cta_eyebrow' => ['Hubungi Kami', 'text'],
            'contact_cta_title' => ['Tertarik Mendaftarkan Putra-Putri Anda?', 'text'],
            'contact_cta_body' => ['Silakan hubungi kami untuk informasi pendaftaran, kunjungan, atau konsultasi lebih lanjut.', 'text'],
        ]);

        Feature::query()->truncate();
        $features = [
            ['icon' => 'book', 'title' => 'Kurikulum Islami Terpadu', 'description' => 'Pembiasaan akhlak dan nilai keislaman dipadukan dengan capaian perkembangan anak usia dini.'],
            ['icon' => 'people', 'title' => 'Pendidik yang Peduli', 'description' => 'Guru memahami tahap tumbuh kembang anak usia 2–4 tahun dan mendampingi dengan sabar.'],
            ['icon' => 'play', 'title' => 'Belajar Melalui Bermain', 'description' => 'Kegiatan edukatif menstimulasi motorik, bahasa, kognitif, dan sosial-emosional anak.'],
            ['icon' => 'shield', 'title' => 'Lingkungan Terpercaya', 'description' => "Berada di bawah naungan Pimpinan Cabang 'Aisyiyah Dau dengan pengawasan penuh setiap kegiatan."],
        ];
        foreach ($features as $i => $feature) {
            Feature::query()->create($feature + ['sort_order' => $i + 1, 'status' => 'published']);
        }
    }

    private function seedAboutContent(): void
    {
        $this->putSection('about', [
            'page_title' => ["Tentang KB 'Aisyiyah 2 Dau", 'text'],
            'sejarah_eyebrow' => ['Sejarah & Naungan', 'text'],
            'sejarah_title' => ["Bagian dari Gerakan 'Aisyiyah untuk Pendidikan Anak Usia Dini", 'text'],
            'sejarah_paragraf_1' => ["KB 'Aisyiyah 2 Dau adalah lembaga Pendidikan Anak Usia Dini (PAUD) jalur Kelompok Bermain yang berada di bawah naungan Pimpinan Cabang 'Aisyiyah Dau, Kabupaten Malang — organisasi otonom perempuan Muhammadiyah yang telah bergerak di bidang pendidikan, kesehatan, dan kesejahteraan sosial sejak tahun 1917.", 'richtext'],
            'sejarah_paragraf_2' => ['Meneruskan amanah tersebut, kami hadir untuk memberikan layanan pendidikan usia dini yang islami, hangat, dan terjangkau bagi keluarga di wilayah Kecamatan Dau dan sekitarnya, guna menumbuhkan generasi yang berakhlak mulia, cerdas, dan mandiri sejak dini.', 'richtext'],
            'sejarah_paragraf_3' => ["Sebagai bagian dari amal usaha 'Aisyiyah, seluruh kegiatan belajar-mengajar berada dalam pengawasan dan pembinaan Pimpinan Cabang 'Aisyiyah Dau, sejalan dengan semangat gerakan 'Aisyiyah dalam mencerdaskan dan memberdayakan masyarakat melalui pendidikan.", 'richtext'],
            'visi' => ['Terwujudnya generasi usia dini yang beriman, berakhlak mulia, cerdas, dan mandiri.', 'text'],
            'perkembangan_eyebrow' => ['Program Pembelajaran', 'text'],
            'perkembangan_title' => ['Aspek Perkembangan yang Kami Dampingi', 'text'],
            'fasilitas_eyebrow' => ['Fasilitas', 'text'],
            'fasilitas_title' => ['Lingkungan Belajar yang Ramah Anak', 'text'],
            'cta_title' => ['Ingin Tahu Lebih Banyak?', 'text'],
            'cta_body' => ['Kunjungi kami langsung atau hubungi melalui kontak resmi untuk konsultasi pendaftaran.', 'text'],
        ]);

        MissionPoint::query()->truncate();
        $missions = [
            'Menanamkan nilai-nilai keislaman sejak usia dini melalui pembiasaan sehari-hari.',
            'Menyelenggarakan pembelajaran yang menyenangkan sesuai tahap tumbuh kembang anak.',
            'Membangun kerja sama yang erat antara pendidik, orang tua, dan lingkungan sekitar.',
            'Menyiapkan anak secara mental dan sosial untuk melanjutkan ke jenjang TK/RA.',
        ];
        foreach ($missions as $i => $content) {
            MissionPoint::query()->create(['content' => $content, 'sort_order' => $i + 1, 'status' => 'published']);
        }

        DevelopmentAspect::query()->truncate();
        $aspects = [
            ['title' => 'Nilai Agama & Moral', 'description' => 'Pembiasaan doa harian, akhlak, dan adab sesuai tuntunan islami.'],
            ['title' => 'Fisik Motorik', 'description' => 'Motorik kasar dan halus melalui permainan aktif dan terarah.'],
            ['title' => 'Kognitif', 'description' => 'Mengenal bentuk, warna, angka, dan hubungan sebab-akibat sederhana.'],
            ['title' => 'Bahasa', 'description' => 'Memperkaya kosakata, kemampuan mendengarkan, dan bercerita sederhana.'],
            ['title' => 'Sosial Emosional', 'description' => 'Berbagi, sabar menunggu giliran, dan membangun rasa percaya diri.'],
            ['title' => 'Seni', 'description' => 'Mewarnai, musik, dan gerak sebagai media ekspresi diri anak.'],
        ];
        foreach ($aspects as $i => $aspect) {
            DevelopmentAspect::query()->create($aspect + ['sort_order' => $i + 1, 'status' => 'published']);
        }

        Facility::query()->truncate();
        $facilities = [
            ['title' => 'Ruang Bermain Indoor', 'description' => 'Ruang kelas nyaman dengan alat permainan edukatif (APE).'],
            ['title' => 'Area Bermain Outdoor', 'description' => 'Ruang terbuka untuk kegiatan motorik kasar dan bermain bersama.'],
            ['title' => 'Ruang Mengaji', 'description' => 'Tempat kegiatan pembiasaan mengaji dan doa harian.'],
            ['title' => 'Alat Permainan Edukatif', 'description' => 'Media belajar yang mendukung stimulasi tumbuh kembang anak.'],
        ];
        foreach ($facilities as $i => $facility) {
            Facility::query()->create($facility + ['sort_order' => $i + 1, 'status' => 'published']);
        }
    }

    private function seedStrukturOrganisasiContent(): void
    {
        $this->putSection('struktur_organisasi', [
            'page_title' => ['Struktur Organisasi', 'text'],
            'intro_text' => ["Struktur kepengurusan KB 'Aisyiyah 2 Dau di bawah pembinaan Pimpinan Cabang 'Aisyiyah Dau, Kabupaten Malang.", 'richtext'],
            'struktur_image' => [null, 'image'],
            'image_caption' => ['Bagan struktur organisasi — lengkapi dengan dokumen resmi sebelum situs dipublikasikan.', 'text'],
        ]);
    }

    private function seedSdmContent(): void
    {
        $this->putSection('sdm', [
            'page_title' => ['Sumber Daya Manusia', 'text'],
            'intro_text' => ['Tenaga pendidik kami telah mengikuti pelatihan PAUD dan memahami tahap tumbuh kembang anak usia 2–4 tahun.', 'richtext'],
        ]);

        EducationHistory::query()->truncate();
        StaffMember::query()->truncate();
        $staff = [
            [
                'name' => 'Nama — lengkapi', 'role' => 'Kepala KB',
                'education' => [['degree' => 'S1 Pendidikan Guru PAUD', 'institution' => 'Institusi — lengkapi', 'year' => null]],
            ],
            [
                'name' => 'Nama — lengkapi', 'role' => 'Pendidik Kelompok A',
                'education' => [['degree' => 'S1 Pendidikan Anak Usia Dini', 'institution' => 'Institusi — lengkapi', 'year' => null]],
            ],
            [
                'name' => 'Nama — lengkapi', 'role' => 'Pendidik Kelompok B',
                'education' => [['degree' => 'D3 Kebidanan', 'institution' => 'Institusi — lengkapi', 'year' => null]],
            ],
        ];
        foreach ($staff as $i => $member) {
            $created = StaffMember::query()->create([
                'name' => $member['name'],
                'role' => $member['role'],
                'photo_path' => null,
                'sort_order' => $i + 1,
                'status' => 'published',
            ]);

            foreach ($member['education'] as $education) {
                $created->educationHistories()->create($education);
            }
        }
    }

    private function seedGalleryContent(): void
    {
        $this->putSection('gallery', [
            'page_title' => ['Gallery', 'text'],
            'intro_text' => ['Dokumentasi lengkap kegiatan dapat dilihat di akun Instagram resmi kami.', 'text'],
        ]);

        GalleryCategory::query()->truncate();
        $categories = [
            'harian' => 'Kegiatan Harian',
            'perayaan' => 'Acara & Perayaan',
            'kreativitas' => 'Kreativitas',
            'outdoor' => 'Outdoor',
        ];
        $i = 1;
        foreach ($categories as $slug => $name) {
            GalleryCategory::query()->create([
                'name' => $name,
                'slug' => $slug,
                'sort_order' => $i++,
            ]);
        }

        // Tidak ada foto contoh: galeri diisi lewat panel admin (upload
        // gambar sungguhan). Placeholder tanpa gambar hanya menghasilkan
        // kotak kosong di halaman publik.
    }

    private function seedArtikelContent(): void
    {
        $this->putSection('artikel', [
            'page_title' => ['Artikel', 'text'],
            'intro_text' => ["Kabar dan dokumentasi kegiatan terbaru dari KB 'Aisyiyah 2 Dau.", 'text'],
        ]);

        Article::query()->truncate();
        $articles = [
            [
                'title' => 'Kegiatan Belajar Sambil Bermain di Bulan Ini',
                'slug' => 'kegiatan-belajar-sambil-bermain-bulan-ini',
                'excerpt' => 'Dokumentasi singkat kegiatan belajar sambil bermain anak-anak KB \'Aisyiyah 2 Dau sepanjang bulan ini. (Contoh berita — lengkapi dengan dokumentasi asli.)',
                'body' => "<p>Sepanjang bulan ini, anak-anak KB 'Aisyiyah 2 Dau mengikuti berbagai kegiatan belajar sambil bermain yang dirancang untuk menstimulasi motorik, bahasa, dan sosial-emosional mereka.</p><p>(Contoh isi artikel — silakan lengkapi dengan berita dan dokumentasi kegiatan yang sebenarnya melalui panel admin.)</p>",
                'published_at' => now()->subDays(3)->toDateString(),
                'is_highlight' => true,
            ],
            [
                'title' => 'Perayaan Hari Besar Islam Bersama Wali Murid',
                'slug' => 'perayaan-hari-besar-islam-bersama-wali-murid',
                'excerpt' => 'Perayaan hari besar Islam tahun ini diikuti dengan antusias oleh anak-anak dan wali murid. (Contoh berita — lengkapi dengan dokumentasi asli.)',
                'body' => "<p>Perayaan hari besar Islam menjadi salah satu momen istimewa bagi anak-anak KB 'Aisyiyah 2 Dau untuk mengenal dan mempraktikkan nilai-nilai keislaman bersama keluarga.</p><p>(Contoh isi artikel — silakan lengkapi dengan berita dan dokumentasi kegiatan yang sebenarnya melalui panel admin.)</p>",
                'published_at' => now()->subDays(10)->toDateString(),
                'is_highlight' => false,
            ],
            [
                'title' => "Kunjungan dan Silaturahmi Pimpinan Cabang 'Aisyiyah Dau",
                'slug' => 'kunjungan-silaturahmi-pimpinan-cabang-aisyiyah-dau',
                'excerpt' => "Pimpinan Cabang 'Aisyiyah Dau berkunjung untuk bersilaturahmi dan meninjau kegiatan belajar-mengajar. (Contoh berita — lengkapi dengan dokumentasi asli.)",
                'body' => "<p>Kunjungan ini merupakan bagian dari pembinaan rutin Pimpinan Cabang 'Aisyiyah Dau terhadap amal usaha di bidang pendidikan anak usia dini.</p><p>(Contoh isi artikel — silakan lengkapi dengan berita dan dokumentasi kegiatan yang sebenarnya melalui panel admin.)</p>",
                'published_at' => now()->subDays(20)->toDateString(),
                'is_highlight' => false,
            ],
        ];
        foreach ($articles as $article) {
            Article::query()->create($article + ['cover_image' => null, 'status' => 'published']);
        }
    }

    private function seedContactContent(): void
    {
        $this->putSection('contact', [
            'intro_text' => ['Untuk informasi pendaftaran, kunjungan, atau kerja sama, silakan hubungi kami melalui kontak berikut atau isi formulir di samping.', 'text'],
        ]);
    }
}
