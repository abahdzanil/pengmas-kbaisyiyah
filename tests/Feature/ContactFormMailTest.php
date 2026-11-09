<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormMailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Wali Murid',
            'email' => 'waliMurid@example.com',
            'phone' => '0812-3456-7890',
            'message' => 'Saya ingin bertanya soal pendaftaran.',
        ], $overrides);
    }

    public function test_submitting_the_form_emails_the_school_address_from_site_settings(): void
    {
        Mail::fake();
        SiteSetting::current()->update(['email' => 'sekolah@kbaisyiyah2dau.sch.id']);

        $this->post('/contact', $this->payload())->assertRedirect();

        Mail::assertSent(ContactMessageReceived::class, function (ContactMessageReceived $mail) {
            $envelope = $mail->envelope();

            // Goes to the admin-editable address, and replying in the inbox
            // reaches the visitor rather than the school itself.
            return $envelope->to[0]->address === 'sekolah@kbaisyiyah2dau.sch.id'
                && $envelope->replyTo[0]->address === 'waliMurid@example.com';
        });
    }

    public function test_email_is_required_and_must_be_valid(): void
    {
        Mail::fake();

        $this->post('/contact', $this->payload(['email' => '']))->assertSessionHasErrors('email');
        $this->post('/contact', $this->payload(['email' => 'bukan-email']))->assertSessionHasErrors('email');

        $this->assertSame(0, ContactMessage::query()->count());
        Mail::assertNothingSent();
    }

    public function test_the_submitted_email_is_stored_with_the_message(): void
    {
        Mail::fake();

        $this->post('/contact', $this->payload());

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'waliMurid@example.com',
            'name' => 'Wali Murid',
            'status' => 'new',
        ]);
    }

    public function test_a_failing_mail_transport_does_not_lose_the_message(): void
    {
        // Bad SMTP credentials or a provider outage must not cost the school
        // the enquiry, nor show the visitor an error they can't act on.
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP connection refused'));
        Log::shouldReceive('error')->once();

        $response = $this->post('/contact', $this->payload());

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('contact_messages', ['email' => 'waliMurid@example.com']);
    }

    public function test_admin_inbox_shows_the_sender_email(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        ContactMessage::factory()->create(['email' => 'pengirim@example.com']);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('pengirim@example.com')
            ->assertSee('Balas Email');
    }
}
