@php
    $waNumber = $settings->phone ? preg_replace('/[^0-9]/', '', $settings->phone) : null;
    $waNumber = $waNumber && str_starts_with($waNumber, '0') ? '62'.substr($waNumber, 1) : $waNumber;
@endphp
<x-layouts.app title="Contact Us">

<section class="pt-40 pb-16 lg:pt-48 lg:pb-20 border-b border-sand-200">
  <div class="max-w-6xl mx-auto px-6 lg:px-8">
    <p class="text-xs text-ink/45 mb-5"><a href="{{ route('home') }}" class="hover:text-forest-700">Home</a> / <span class="text-forest-700">Contact Us</span></p>
    <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Contact Us</p>
    <h1 class="font-extrabold text-3xl sm:text-5xl text-forest-800 max-w-2xl">Mari Berkenalan Lebih Dekat</h1>
  </div>
</section>

<section class="py-24">
  <div class="max-w-6xl mx-auto px-6 lg:px-8 grid lg:grid-cols-12 gap-14">

    <div class="lg:col-span-5">
      <x-ecms.text section="contact" key="intro_text" tag="p" class="text-[15px] leading-relaxed text-ink/70 mb-10" />

      <ul class="space-y-6 mb-10">
        <li class="flex gap-4">
          <x-icon name="location" :size="20" stroke="#2f5d40" :stroke-width="'1.6'" class="shrink-0 mt-0.5" />
          <div>
            <p class="text-sm font-semibold text-forest-800 mb-0.5">Alamat</p>
            <p class="text-sm text-ink/70">{{ $settings->address }}</p>
          </div>
        </li>
        <li class="flex gap-4">
          <x-icon name="phone" :size="20" stroke="#2f5d40" :stroke-width="'1.6'" class="shrink-0 mt-0.5" />
          <div>
            <p class="text-sm font-semibold text-forest-800 mb-0.5">WhatsApp / Telepon</p>
            <p class="text-sm text-ink/70">{{ $settings->phone }}</p>
          </div>
        </li>
        <li class="flex gap-4">
          <x-icon name="email" :size="20" stroke="#2f5d40" :stroke-width="'1.6'" class="shrink-0 mt-0.5" />
          <div>
            <p class="text-sm font-semibold text-forest-800 mb-0.5">Email</p>
            <p class="text-sm text-ink/70">{{ $settings->email }}</p>
          </div>
        </li>
        <li class="flex gap-4">
          <x-icon name="instagram" :size="20" stroke="#2f5d40" :stroke-width="'1.6'" class="shrink-0 mt-0.5" />
          <div>
            <p class="text-sm font-semibold text-forest-800 mb-0.5">Instagram</p>
            <a href="{{ $settings->instagram_url }}" target="_blank" rel="noopener" class="text-sm text-forest-700 underline underline-offset-2 hover:text-forest-500">{{ $settings->instagram_handle }}</a>
          </div>
        </li>
        <li class="flex gap-4">
          <x-icon name="clock" :size="20" stroke="#2f5d40" :stroke-width="'1.6'" class="shrink-0 mt-0.5" />
          <div>
            <p class="text-sm font-semibold text-forest-800 mb-0.5">Jam Operasional</p>
            <p class="text-sm text-ink/70">{{ $settings->operational_hours }}</p>
          </div>
        </li>
      </ul>

      <!-- @if ($waNumber)
        <div class="flex flex-wrap gap-3">
          <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-6 py-3 rounded-sm transition-colors">
            Chat via WhatsApp
          </a>
        </div>
      @endif -->
    </div>

    <div class="lg:col-span-7">
      <div class="border border-sand-300 bg-white p-8 sm:p-10 mb-8">
        <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-6">Kirim Pesan</p>

        @if (session('status'))
          <div class="mb-6 border border-forest-300 bg-forest-50 text-forest-800 text-sm px-4 py-3">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}" class="space-y-5">
          @csrf
          <div>
            <label for="cf-name" class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Nama</label>
            <input id="cf-name" name="name" type="text" required value="{{ old('name') }}" placeholder="Nama lengkap"
              class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm focus:outline-none focus:border-forest-600">
            @error('name') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="cf-email" class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Email</label>
            <input id="cf-email" name="email" type="email" required value="{{ old('email') }}" placeholder="nama@email.com"
              class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm focus:outline-none focus:border-forest-600">
            @error('email') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="cf-phone" class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">No. WhatsApp</label>
            <input id="cf-phone" name="phone" type="tel" required value="{{ old('phone') }}" placeholder="08xx-xxxx-xxxx"
              class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm focus:outline-none focus:border-forest-600">
            @error('phone') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="cf-message" class="block text-xs font-semibold uppercase tracking-wide text-forest-800 mb-2">Pesan</label>
            <textarea id="cf-message" name="message" rows="4" required placeholder="Tuliskan pertanyaan atau kebutuhan Anda"
              class="w-full border border-sand-300 bg-sand-50 px-4 py-3 text-sm focus:outline-none focus:border-forest-600">{{ old('message') }}</textarea>
            @error('message') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
          </div>
          <button type="submit" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
            Kirim Pesan
          </button>
        </form>
      </div>

      @if ($settings->maps_embed_url)
        <div class="border border-sand-300 h-[320px] bg-sand-100">
          <iframe
            title="Peta lokasi {{ $settings->site_name }}"
            class="w-full h-full grayscale-[15%]"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            src="{{ $settings->maps_embed_url }}">
          </iframe>
        </div>
      @endif
    </div>

  </div>
</section>

</x-layouts.app>
