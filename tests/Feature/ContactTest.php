<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_contact_page(): void
    {
        $this->get(route('contact.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Marketing/Contact'));
    }

    public function test_guest_can_submit_a_contact_message(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'General question',
            'message' => 'Is the markdown editor included?',
        ])->assertRedirect();

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'jane@example.com',
            'subject' => 'General question',
        ]);
    }

    public function test_contact_message_requires_valid_input(): void
    {
        $this->post(route('contact.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'subject' => '',
            'message' => '',
        ])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->assertSame(0, ContactMessage::count());
    }
}
