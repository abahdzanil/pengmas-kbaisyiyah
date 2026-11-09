<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact');
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $message = ContactMessage::query()->create($request->validated());

        // The message is already safe in the database and visible under
        // "Pesan Masuk", so a mail transport problem (bad SMTP credentials,
        // provider down) must not lose the enquiry or show the visitor an
        // error for something they can't act on. Log it for the admin.
        try {
            Mail::send(new ContactMessageReceived($message));
        } catch (Throwable $e) {
            Log::error('Gagal mengirim notifikasi pesan kontak.', [
                'contact_message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'Pesan Anda telah terkirim. Terima kasih telah menghubungi kami.');
    }
}
