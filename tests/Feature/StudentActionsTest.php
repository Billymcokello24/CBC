<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Academic\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_bulk_delete_students()
    {
        $students = Student::factory()->count(3)->create(['school_id' => 1]);
        $ids = $students->pluck('id')->toArray();

        $response = $this->actingAs($this->user)
            ->post(route('students.bulk-delete'), [
                'student_ids' => $ids
            ]);

        $response->assertRedirect();
        $this->assertEquals(0, Student::whereIn('id', $ids)->count());
    }

    public function test_can_export_students_pdf()
    {
        Student::factory()->count(2)->create(['school_id' => 1]);

        $response = $this->actingAs($this->user)
            ->get(route('students.export-pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
