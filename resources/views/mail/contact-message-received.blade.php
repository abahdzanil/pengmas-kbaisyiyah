<x-mail::message>
# Pesan Baru dari Website

Ada pesan masuk melalui formulir Contact Us {{ $contactMessage->created_at->translatedFormat('d F Y, H:i') }} WIB.

**Nama:** {{ $contactMessage->name }}
**Email:** {{ $contactMessage->email }}
**No. WhatsApp:** {{ $contactMessage->phone }}

<x-mail::panel>
{{ $contactMessage->message }}
</x-mail::panel>

Balas email ini untuk menjawab langsung ke pengirim.

<x-mail::button :url="route('admin.dashboard')">
Buka Panel Admin
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
