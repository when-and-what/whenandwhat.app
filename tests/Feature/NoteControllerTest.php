<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoteControllerTest extends TestCase
{
    use RefreshDatabase;

    public User $user;
    public User $user2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user2 = User::factory()->create();
    }

    public function test_view_notes()
    {
        $notes = Note::factory(6)->create(['user_id' => $this->user->id]);
        $notes2 = Note::factory(8)->create(['user_id' => $this->user2->id]);
        Note::factory(15)->create();

        $response = $this->get(route('notes.index'));
        $response->assertRedirect('login');

        $response = $this->actingAs($this->user)->get(route('notes.index'));
        $response->assertViewIs('notes.index');
        $response->assertStatus(200);
        $response->assertViewHas(
            'notes',
            Note::whereBelongsTo($this->user)
                ->orderBy('published_at', 'desc')
                ->get()
        );
        foreach ($notes as $note) {
            $response->assertSeeText($note->title);
        }

        $response = $this->actingAs($this->user2)->get(route('notes.index'));
        $response->assertViewIs('notes.index');
        $response->assertStatus(200);
        $response->assertViewHas(
            'notes',
            Note::whereBelongsTo($this->user2)
                ->orderBy('published_at', 'desc')
                ->get()
        );
        foreach ($notes2 as $note) {
            $response->assertSeeText($note->title);
        }
    }

    public function test_create_note()
    {
        $response = $this->get(route('notes.create'));
        $response->assertRedirect('login');

        $response = $this->actingAs($this->user)->get(route('notes.create'));
        $response->assertStatus(200);
        $response->assertSeeText('New Note');

        $response = $this->actingAs($this->user)->post(route('notes.store'), [
            'title' => 'my title',
        ]);
        $response->assertRedirect(route('notes.index'));
        $this->assertDatabaseHas('notes', [
            'user_id' => $this->user->id,
            'title' => 'my title',
        ]);
    }

    public function test_edit_note()
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user2)->get(route('notes.edit', $note));
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->get(route('notes.edit', $note));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user2)->put(route('notes.update', $note), []);
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->put(route('notes.update', $note), [
            'title' => 'new title',
            'sub_title' => $note->sub_title,
            'icon' => $note->icon,
            'published_at' => $note->published_at->format('Y-m-d\TH:i'),
        ]);
        $response->assertRedirect(route('notes.index'));
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'user_id' => $this->user->id,
            'title' => 'new title',
            'sub_title' => $note->sub_title,
        ]);
    }

    public function test_delete_note()
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user2)->delete(route('notes.destroy', $note), []);
        $response->assertStatus(403);
        $this->assertNotSoftDeleted($note);

        $response = $this->actingAs($this->user)->delete(route('notes.destroy', $note), []);
        $response->assertRedirect(route('notes.index'));
        $this->assertSoftDeleted($note);
    }
}