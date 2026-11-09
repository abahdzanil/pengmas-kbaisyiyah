<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Dashboard Admin — KB &apos;Aisyiyah 2 Dau</title>
@include('partials.head-assets')
</head>
<body class="bg-sand-50 text-ink font-sans antialiased">

<div class="lg:flex min-h-screen">

  <!-- ============ SIDEBAR ============ -->
  <aside id="dashSidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-72 bg-forest-800 text-sand-50 flex flex-col transition-transform -translate-x-full lg:translate-x-0">
    <div class="h-20 flex items-center gap-3 px-6 border-b border-sand-50/10 shrink-0">
      <span class="w-9 h-9 rounded-full bg-sand-50/10 flex items-center justify-center shrink-0">
        <x-icon name="logo" :size="16" stroke="#f4eee0" />
      </span>
      <span class="leading-tight">
        <span class="block font-bold text-[14px]">KB &apos;Aisyiyah 2 Dau</span>
        <span class="block text-[10px] uppercase tracking-[0.14em] text-sand-300">Panel Admin</span>
      </span>
    </div>

    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
      <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-sand-100/40 mb-2">Menu</p>
      @php
        $navItems = [
            'overview' => 'Dashboard',
            'home' => 'Kelola Home',
            'about' => 'Kelola About Us',
            'struktur' => 'Kelola Struktur Organisasi',
            'sdm' => 'Kelola Sumber Daya Manusia',
            'gallery' => 'Kelola Gallery',
            'artikel' => 'Kelola Artikel',
            'contact' => 'Kelola Contact',
            'messages' => 'Pesan Masuk',
            'settings' => 'Pengaturan Situs',
        ];
      @endphp
      @foreach ($navItems as $panel => $label)
        <button type="button" data-panel="{{ $panel }}" class="dash-nav-link w-full text-left px-3 py-3 text-sm font-semibold flex items-center gap-3 {{ $loop->first ? 'bg-sand-50/10 text-sand-50' : 'text-sand-100/70' }}">
          {{ $label }}
          @if ($panel === 'messages' && $stats['unread_messages'] > 0)
            <span class="ml-auto bg-forest-600 text-sand-50 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $stats['unread_messages'] }}</span>
          @endif
        </button>
      @endforeach

      <div class="pt-6 mt-6 border-t border-sand-50/10 space-y-1">
        <a href="{{ route('home') }}" target="_blank" class="w-full text-left px-3 py-3 text-sm font-semibold flex items-center gap-3 text-sand-100/70 hover:text-sand-50">
          Lihat Situs
        </a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full text-left px-3 py-3 text-sm font-semibold flex items-center gap-3 text-sand-100/70 hover:text-sand-50">
            Keluar
          </button>
        </form>
      </div>
    </nav>
  </aside>

  <!-- ============ MAIN ============ -->
  <div class="flex-1 min-w-0">
    <header class="h-20 bg-white border-b border-sand-200 flex items-center justify-between px-6 lg:px-10 sticky top-0 z-20">
      <div class="flex items-center gap-4">
        <button id="dashMenuBtn" class="lg:hidden w-9 h-9 flex items-center justify-center text-forest-800">
          <x-icon name="menu" :size="22" :stroke-width="'1.8'" />
        </button>
        <h1 id="dashPanelTitle" class="font-extrabold text-lg sm:text-xl text-forest-800">Dashboard</h1>
      </div>
      <div class="flex items-center gap-3">
        <span class="hidden sm:block text-sm text-ink/60">{{ $admin->name }}</span>
        <span class="w-9 h-9 rounded-full bg-forest-700 text-sand-50 flex items-center justify-center text-xs font-bold shrink-0">{{ mb_substr($admin->name, 0, 1) }}</span>
      </div>
    </header>

    <main class="p-6 lg:p-10">

      <!-- ===== PANEL: OVERVIEW ===== -->
      <section class="dash-panel" data-panel="overview">
        <p class="text-sm text-ink/60 mb-8">Ringkasan konten situs KB &apos;Aisyiyah 2 Dau.</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
          <div class="border border-sand-300 bg-white p-6">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink/45 mb-2">Halaman Aktif</p>
            <p class="text-3xl font-extrabold text-forest-800">{{ $stats['active_pages'] }}</p>
          </div>
          <div class="border border-sand-300 bg-white p-6">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink/45 mb-2">Foto Galeri</p>
            <p class="text-3xl font-extrabold text-forest-800">{{ $stats['gallery_photos'] }}</p>
          </div>
          <div class="border border-sand-300 bg-white p-6">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink/45 mb-2">Pesan Masuk (Baru)</p>
            <p class="text-3xl font-extrabold text-forest-800">{{ $stats['unread_messages'] }}</p>
          </div>
          <div class="border border-sand-300 bg-white p-6">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink/45 mb-2">Terakhir Diperbarui</p>
            <p class="text-lg font-extrabold text-forest-800">{{ $stats['last_updated']?->translatedFormat('d M Y') ?? '—' }}</p>
          </div>
        </div>

        <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Akses Cepat</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
          @foreach (array_slice($navItems, 1, 4, true) as $panel => $label)
            <button type="button" data-panel="{{ $panel }}" class="dash-nav-link text-left border border-sand-300 bg-white hover:border-forest-600 p-6 transition-colors">
              <p class="font-bold text-forest-800 text-sm">{{ $label }}</p>
            </button>
          @endforeach
        </div>
      </section>

      <!-- ===== PANEL: KELOLA HOME ===== -->
      <section class="dash-panel hidden" data-panel="home">
        <p class="text-sm text-ink/60 mb-8">Perbarui teks dan gambar yang tampil di halaman Home.</p>
        <div class="border border-sand-300 bg-white p-6 sm:p-8 max-w-3xl space-y-6 mb-10">
          <x-admin.field section="home" key="hero_eyebrow" label="Label Kecil (Eyebrow)" />
          <x-admin.field section="home" key="hero_title" label="Judul Utama (Headline)" type="textarea" />
          <x-admin.field section="home" key="hero_subtitle" label="Sub-judul" type="richtext" />
          <div class="grid sm:grid-cols-2 gap-6">
            <x-admin.field section="home" key="hero_button1_label" label="Label Tombol 1" />
            <x-admin.field section="home" key="hero_button2_label" label="Label Tombol 2" />
          </div>
          <x-admin.field section="home" key="hero_image" label="Gambar Hero" type="image" />
          <div class="grid sm:grid-cols-3 gap-6">
            <x-admin.field section="home" key="stat_usia_value" label="Statistik: Usia Anak" />
            <x-admin.field section="home" key="stat_naungan_value" label="Statistik: Naungan" />
            <x-admin.field section="home" key="stat_lokasi_value" label="Statistik: Lokasi" />
          </div>
          <x-admin.field section="home" key="keunggulan_eyebrow" label="Keunggulan — Label Kecil" />
          <x-admin.field section="home" key="keunggulan_title" label="Keunggulan — Judul" />
          <x-admin.field section="home" key="about_teaser_eyebrow" label="Tentang Kami (Teaser) — Label Kecil" />
          <x-admin.field section="home" key="about_teaser_title" label="Tentang Kami (Teaser) — Judul" />
          <x-admin.field section="home" key="about_teaser_body" label="Tentang Kami (Teaser) — Isi" type="richtext" />
          <x-admin.field section="home" key="about_teaser_visi" label="Kutipan Visi" type="textarea" />
          <x-admin.field section="home" key="gallery_teaser_eyebrow" label="Galeri (Teaser) — Label Kecil" />
          <x-admin.field section="home" key="gallery_teaser_title" label="Galeri (Teaser) — Judul" />
          <x-admin.field section="home" key="contact_cta_eyebrow" label="CTA Kontak — Label Kecil" />
          <x-admin.field section="home" key="contact_cta_title" label="CTA Kontak — Judul" />
          <x-admin.field section="home" key="contact_cta_body" label="CTA Kontak — Isi" type="textarea" />
          <button type="button" data-save-panel class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
            Simpan Perubahan
          </button>
        </div>

        <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Kartu Keunggulan</p>
        <x-admin.collection-manager resource="features" />
      </section>

      <!-- ===== PANEL: KELOLA ABOUT US ===== -->
      <section class="dash-panel hidden" data-panel="about">
        <p class="text-sm text-ink/60 mb-8">Perbarui sejarah, visi-misi, dan konten lain di halaman About Us.</p>
        <div data-save-scope class="border border-sand-300 bg-white p-6 sm:p-8 max-w-3xl space-y-6 mb-10">
          <x-admin.field section="about" key="page_title" label="Judul Halaman" />
          <x-admin.field section="about" key="sejarah_eyebrow" label="Sejarah — Label Kecil" />
          <x-admin.field section="about" key="sejarah_title" label="Sejarah — Judul" />
          <x-admin.field section="about" key="sejarah_paragraf_1" label="Sejarah — Paragraf 1" type="richtext" />
          <x-admin.field section="about" key="sejarah_paragraf_2" label="Sejarah — Paragraf 2" type="richtext" />
          <x-admin.field section="about" key="sejarah_paragraf_3" label="Sejarah — Paragraf 3" type="richtext" />
          <x-admin.field section="about" key="perkembangan_eyebrow" label="Aspek Perkembangan — Label Kecil" />
          <x-admin.field section="about" key="perkembangan_title" label="Aspek Perkembangan — Judul" />
          <x-admin.field section="about" key="fasilitas_eyebrow" label="Fasilitas — Label Kecil" />
          <x-admin.field section="about" key="fasilitas_title" label="Fasilitas — Judul" />
          <x-admin.field section="about" key="cta_title" label="CTA — Judul" />
          <x-admin.field section="about" key="cta_body" label="CTA — Isi" type="textarea" />
          <button type="button" data-save-panel class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
            Simpan Perubahan
          </button>
        </div>

        <div class="space-y-10">
          <div>
            <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Visi &amp; Misi</p>

            <div data-save-scope class="border border-sand-300 bg-white p-6 sm:p-8 max-w-3xl space-y-6 mb-6">
              <x-admin.field section="about" key="visi" label="Visi" type="textarea" />
              <button type="button" data-save-panel class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
                Simpan Visi
              </button>
            </div>

            <p class="text-xs text-ink/50 mb-4">Misi tampil sebagai daftar poin di halaman About Us — tambahkan satu poin per baris.</p>
            <x-admin.collection-manager resource="mission-points" />
          </div>
          <div>
            <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Aspek Perkembangan</p>
            <x-admin.collection-manager resource="development-aspects" />
          </div>
          <div>
            <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Fasilitas</p>
            <x-admin.collection-manager resource="facilities" />
          </div>
        </div>
      </section>

      <!-- ===== PANEL: KELOLA STRUKTUR ORGANISASI ===== -->
      <section class="dash-panel hidden" data-panel="struktur">
        <p class="text-sm text-ink/60 mb-8">Perbarui bagan dan teks halaman Struktur Organisasi.</p>
        <div class="border border-sand-300 bg-white p-6 sm:p-8 max-w-3xl space-y-6">
          <x-admin.field section="struktur_organisasi" key="page_title" label="Judul Halaman" />
          <x-admin.field section="struktur_organisasi" key="intro_text" label="Teks Pengantar" type="richtext" />
          <x-admin.field section="struktur_organisasi" key="struktur_image" label="Gambar Bagan Struktur" type="image" />
          <x-admin.field section="struktur_organisasi" key="image_caption" label="Keterangan Gambar" />
          <button type="button" data-save-panel class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
            Simpan Perubahan
          </button>
        </div>
      </section>

      <!-- ===== PANEL: KELOLA SDM ===== -->
      <section class="dash-panel hidden" data-panel="sdm">
        <p class="text-sm text-ink/60 mb-8">Perbarui teks pengantar dan daftar staf/tenaga pendidik.</p>
        <div class="border border-sand-300 bg-white p-6 sm:p-8 max-w-3xl space-y-6 mb-10">
          <x-admin.field section="sdm" key="page_title" label="Judul Halaman" />
          <x-admin.field section="sdm" key="intro_text" label="Teks Pengantar" type="richtext" />
          <button type="button" data-save-panel class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
            Simpan Perubahan
          </button>
        </div>

        <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Staf &amp; Tenaga Pendidik</p>
        <p class="text-xs text-ink/50 mb-4">Riwayat pendidikan diisi langsung di dalam form tambah/ubah staf di bawah.</p>
        <x-admin.collection-manager resource="staff" />
      </section>

      <!-- ===== PANEL: KELOLA GALLERY ===== -->
      <section class="dash-panel hidden" data-panel="gallery">
        <p class="text-sm text-ink/60 mb-8">Perbarui teks pengantar, kategori, dan foto kegiatan.</p>
        <div class="border border-sand-300 bg-white p-6 sm:p-8 max-w-3xl space-y-6 mb-10">
          <x-admin.field section="gallery" key="page_title" label="Judul Halaman" />
          <x-admin.field section="gallery" key="intro_text" label="Teks Pengantar" type="richtext" />
          <button type="button" data-save-panel class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
            Simpan Perubahan
          </button>
        </div>

        <div class="space-y-10">
          <div>
            <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Kategori</p>
            <x-admin.collection-manager resource="gallery-categories" />
          </div>
          <div>
            <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Foto</p>
            <x-admin.collection-manager resource="photos" />
          </div>
        </div>
      </section>

      <!-- ===== PANEL: KELOLA ARTIKEL ===== -->
      <section class="dash-panel hidden" data-panel="artikel">
        <p class="text-sm text-ink/60 mb-8">Perbarui teks pengantar dan kelola artikel/berita.</p>
        <div class="border border-sand-300 bg-white p-6 sm:p-8 max-w-3xl space-y-6 mb-10">
          <x-admin.field section="artikel" key="page_title" label="Judul Halaman" />
          <x-admin.field section="artikel" key="intro_text" label="Teks Pengantar" />
          <button type="button" data-save-panel class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
            Simpan Perubahan
          </button>
        </div>

        <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Artikel</p>
        <x-admin.collection-manager resource="articles" />
      </section>

      <!-- ===== PANEL: PESAN MASUK ===== -->
      <section class="dash-panel hidden" data-panel="messages">
        <p class="text-sm text-ink/60 mb-8">Pesan yang dikirim pengunjung melalui formulir Contact Us.</p>
        <div class="border border-sand-300 bg-white divide-y divide-sand-200">
          @forelse ($messages as $message)
            <div class="p-6 flex flex-col sm:flex-row sm:items-start gap-4" data-message-row>
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-1">
                  <p class="font-bold text-forest-800 text-sm">{{ $message->name }}</p>
                  @if ($message->status === 'new')
                    <span class="bg-forest-700 text-sand-50 text-[10px] font-semibold uppercase px-2 py-0.5" data-badge-new>Baru</span>
                  @endif
                </div>
                <p class="text-xs text-ink/50 mb-3">
                  @if ($message->email)
                    <a href="mailto:{{ $message->email }}" class="text-forest-700 hover:text-forest-500 underline underline-offset-2">{{ $message->email }}</a>
                    &middot;
                  @endif
                  {{ $message->phone }} &middot; {{ $message->created_at->translatedFormat('d M Y, H:i') }}
                </p>
                <p class="text-sm text-ink/70">{{ $message->message }}</p>
              </div>
              <div class="flex gap-2 shrink-0">
                @if ($message->status === 'new')
                  <button type="button" data-message-read data-id="{{ $message->id }}" class="text-[11px] font-semibold uppercase tracking-wide border border-forest-700 text-forest-700 px-3 py-1.5">Tandai Dibaca</button>
                @endif
                @if ($message->email)
                  <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Balasan dari '.$settings->site_name) }}"
                    class="text-[11px] font-semibold uppercase tracking-wide border border-forest-700 text-forest-700 px-3 py-1.5">Balas Email</a>
                @endif
                <button type="button" data-message-delete data-id="{{ $message->id }}" class="text-[11px] font-semibold uppercase tracking-wide border border-sand-400 text-ink/60 px-3 py-1.5">Hapus</button>
              </div>
            </div>
          @empty
            <p class="p-6 text-sm text-ink/50">Belum ada pesan masuk.</p>
          @endforelse
        </div>
      </section>

      <!-- ===== PANEL: KELOLA CONTACT ===== -->
      <section class="dash-panel hidden" data-panel="contact">
        <p class="text-sm text-ink/60 mb-8">Alamat, kontak, dan jam buka yang tampil di halaman Contact Us (dan footer situs).</p>
        <div class="border border-sand-300 bg-white p-6 sm:p-8 max-w-3xl space-y-6 mb-10">
          <x-admin.field section="contact" key="intro_text" label="Teks Pengantar" />
          <button type="button" data-save-panel class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
            Simpan Perubahan
          </button>
        </div>

        <form data-settings-form class="border border-sand-300 bg-white p-6 sm:p-8 max-w-3xl space-y-6">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Alamat</label>
            <textarea name="address" rows="2" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">{{ $settings->address }}</textarea>
          </div>
          <div class="grid sm:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">WhatsApp / Telepon</label>
              <input type="text" name="phone" value="{{ $settings->phone }}" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Email</label>
              <input type="email" name="email" value="{{ $settings->email }}" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Instagram (handle)</label>
              <input type="text" name="instagram_handle" value="{{ $settings->instagram_handle }}" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Instagram (URL)</label>
              <input type="text" name="instagram_url" value="{{ $settings->instagram_url }}" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Jam Operasional</label>
              <input type="text" name="operational_hours" value="{{ $settings->operational_hours }}" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Link Google Maps (embed)</label>
              <input type="text" name="maps_embed_url" value="{{ $settings->maps_embed_url }}" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">
            </div>
          </div>
          <button type="submit" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
            Simpan Perubahan
          </button>
        </form>
      </section>

      <!-- ===== PANEL: PENGATURAN SITUS ===== -->
      <section class="dash-panel hidden" data-panel="settings">
        <p class="text-sm text-ink/60 mb-8">Identitas situs: nama dan tagline yang tampil di header &amp; footer.</p>
        <form data-settings-form class="border border-sand-300 bg-white p-6 sm:p-8 max-w-3xl space-y-6">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Nama Situs</label>
            <input type="text" name="site_name" value="{{ $settings->site_name }}" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Tagline</label>
            <input type="text" name="tagline" value="{{ $settings->tagline }}" class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm">
          </div>
          <button type="submit" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
            Simpan Perubahan
          </button>
        </form>
      </section>

    </main>
  </div>
</div>

<!-- Toast notifikasi -->
<div id="dashToast" class="fixed bottom-6 right-6 z-50 bg-forest-800 text-sand-50 text-sm px-5 py-4 border border-forest-900 shadow-lg max-w-xs transition-all duration-300 translate-y-24 opacity-0"></div>

<script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>
