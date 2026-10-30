<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_users_can_sign_in_and_reach_the_dashboard(): void
    {
        $user = User::factory()->create(['username' => 'staff-user', 'role' => 'teacher']);

        $this->post('/login', [
            'username' => 'staff-user',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee($user->full_name)
            ->assertSee('Teacher overview')
            ->assertSee('My courses')
            ->assertSee('Enter results')
            ->assertSee('staff-stat-grid')
            ->assertSee('portal-sidebar-dashboard', false)
            ->assertSee('portal-sidebar-courses', false)
            ->assertSee('portal-sidebar-results', false)
            ->assertSee('portal-sidebar-profile', false)
            ->assertSee('portal-sidebar-logout', false);
    }

    public function test_admin_dashboard_shows_administration_overview_and_quick_actions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Student Registrar System')
            ->assertSee('favicon.svg')
            ->assertSee('Administration overview')
            ->assertSee('AT A GLANCE')
            ->assertSee('Students')
            ->assertSee('Teachers')
            ->assertSee('Pending results')
            ->assertSee('Add a student')
            ->assertSee('Add a teacher')
            ->assertSee('Manage courses')
            ->assertSee('Review results')
            ->assertSee('admin-sidebar-dashboard', false)
            ->assertSee('admin-sidebar-students', false)
            ->assertSee('admin-sidebar-teachers', false)
            ->assertSee('admin-sidebar-courses', false)
            ->assertSee('admin-sidebar-results', false)
            ->assertSee('admin-tab-students', false)
            ->assertSee('admin-tab-teachers', false)
            ->assertSee('admin-tab-courses', false)
            ->assertSee('admin-tab-results', false);
    }

    public function test_student_dashboard_shows_saved_and_pending_results_after_login(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create([
            'username' => 'academic-student',
            'role' => 'student',
        ]);
        $publishedCourse = Course::create([
            'course_name' => 'Published Course',
            'course_code' => 'PUB101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
            'academic_year' => '2023/24',
            'semester' => 1,
        ]);
        $pendingCourse = Course::create([
            'course_name' => 'Pending Course',
            'course_code' => 'PEN101',
            'credit_hours' => 4,
            'teacher_id' => $teacher->id,
            'academic_year' => '2023/24',
            'semester' => 2,
        ]);
        $student->courses()->attach([$publishedCourse->id, $pendingCourse->id]);
        Result::create([
            'student_id' => $student->id,
            'course_id' => $publishedCourse->id,
            'quiz_score' => 4,
            'attendance_score' => 5,
            'assignment_score' => 9,
            'midterm_score' => 25,
            'final_exam_score' => 40,
            'total' => 83,
            'grade' => 'B',
        ])->forceFill(['status' => Result::STATUS_APPROVED])->save();
        Result::create([
            'student_id' => $student->id,
            'course_id' => $pendingCourse->id,
            'quiz_score' => 2,
            'attendance_score' => 3,
            'assignment_score' => 5,
            'midterm_score' => 15,
            'final_exam_score' => 25,
            'total' => 50,
            'grade' => 'F',
        ])->forceFill(['status' => Result::STATUS_PENDING_REVIEW])->save();

        $this->post('/login', [
            'username' => 'academic-student',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Student Academic Status Summary')
            ->assertSee('Currently you are enrolled in', false)
            ->assertSee('Published Course')
            ->assertSee('83.00')
            ->assertSee('Pending Course')
            ->assertSee('50.00')
            ->assertSee('Pending Review')
            ->assertSee('66.50/100')
            ->assertSee('Academic Year 2023/24 · Semester 1')
            ->assertSee('Academic Year 2023/24 · Semester 2')
            ->assertSee('SCR')
            ->assertSee('SGP')
            ->assertSee('SGPA')
            ->assertSee('CCR')
            ->assertSee('CGP')
            ->assertSee('CGPA')
            ->assertSee('3.00')
            ->assertSee('1.29')
            ->assertSee('Pass')
            ->assertSee('Fail')
            ->assertSee('awaiting administrator review')
            ->assertSee(route('student.results.index'), false)
            ->assertSee('Download CSV')
            ->assertSee(route('student.results.export'), false)
            ->assertSee('Important Notes:')
            ->assertSee('student-important-notes', false)
            ->assertSee('Results awaiting administrator review are visible, but are not yet final.')
            ->assertSee('Once an administrator approves a course result, it is treated as final.')
            ->assertSee('Check “My enrolled courses” below to confirm your current semester courses are listed.');
    }

    public function test_student_can_download_only_their_results_as_a_csv(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => '=HYPERLINK("https://example.test","Open")',
            'course_code' => 'SEC101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
            'academic_year' => '2024/25',
            'semester' => 1,
        ]);
        $otherCourse = Course::create([
            'course_name' => 'Private Course',
            'course_code' => 'PRIVATE101',
            'credit_hours' => 2,
            'teacher_id' => $teacher->id,
        ]);
        Result::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'quiz_score' => 4,
            'attendance_score' => 5,
            'assignment_score' => 9,
            'midterm_score' => 25,
            'final_exam_score' => 40,
            'total' => 83,
            'grade' => 'B',
        ]);
        Result::create([
            'student_id' => $otherStudent->id,
            'course_id' => $otherCourse->id,
            ...Result::SCORE_MAXIMUMS,
            'total' => 100,
            'grade' => 'A',
        ]);

        $response = $this->actingAs($student)->get(route('student.results.export'));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload('student-results.csv');

        $csv = $response->streamedContent();
        $lines = array_filter(explode("\n", substr($csv, 3)), fn (string $line): bool => $line !== '');
        $rows = array_map(fn (string $line): array => str_getcsv($line, ',', '"', ''), $lines);

        $this->assertSame([
            'Course Code',
            'Course Name',
            'Academic Year',
            'Semester',
            'Credit Hours',
            'Quiz / 5',
            'Attendance / 5',
            'Assignment / 10',
            'Midterm / 30',
            'Final / 50',
            'Total / 100',
            'Grade',
        ], $rows[0]);
        $this->assertSame([
            'SEC101',
            '\'=HYPERLINK("https://example.test","Open")',
            '2024/25',
            '1',
            '3',
            '4.00',
            '5.00',
            '9.00',
            '25.00',
            '40.00',
            '83.00',
            'B',
        ], $rows[1]);
        $this->assertStringNotContainsString('Private Course', $csv);
    }

    public function test_only_students_can_download_student_results_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('student.results.export'))
            ->assertRedirect(route('login'));

        $this->actingAs($admin)->get(route('student.results.export'))
            ->assertForbidden();
    }

    public function test_semester_gpa_weights_course_credits_and_uses_the_grade_point_scale(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $courseMarks = [
            ['grade' => 'A', 'credits' => 1, 'total' => 90],
            ['grade' => 'B', 'credits' => 2, 'total' => 80],
            ['grade' => 'C', 'credits' => 3, 'total' => 70],
            ['grade' => 'D', 'credits' => 4, 'total' => 60],
            ['grade' => 'F', 'credits' => 5, 'total' => 59],
        ];

        foreach ($courseMarks as $index => $courseMark) {
            $course = Course::create([
                'course_name' => 'Grade '.$courseMark['grade'].' course',
                'course_code' => 'GPA'.$index,
                'credit_hours' => $courseMark['credits'],
                'teacher_id' => $teacher->id,
                'academic_year' => '2024/25',
                'semester' => 1,
            ]);

            Result::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'quiz_score' => 0,
                'attendance_score' => 0,
                'assignment_score' => 0,
                'midterm_score' => 0,
                'final_exam_score' => 0,
                'total' => $courseMark['total'],
                'grade' => $courseMark['grade'],
            ]);
        }

        $this->actingAs($student)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('SCR')
            ->assertSee('15')
            ->assertSee('SGP')
            ->assertSee('20.00')
            ->assertSee('SGPA')
            ->assertSee('1.33')
            ->assertSee('Status')
            ->assertSee('Fail');
    }

    public function test_guests_can_view_the_student_registration_form(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your student account')
            ->assertSee(route('login'));
    }

    public function test_registration_creates_and_signs_in_a_student_even_if_a_privileged_role_is_submitted(): void
    {
        $this->post(route('register.store'), [
            'full_name' => 'Jordan Student',
            'username' => 'jordan-student',
            'email' => 'jordan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('username', 'jordan-student')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'full_name' => 'Jordan Student',
            'email' => 'jordan@example.com',
            'role' => 'student',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_rejects_mismatched_password_confirmation(): void
    {
        $this->from(route('register'))->post(route('register.store'), [
            'full_name' => 'Jordan Student',
            'username' => 'jordan-student',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ])->assertRedirect(route('register'))
            ->assertInvalid([
                'password' => 'The password field confirmation does not match.',
            ]);

        $this->assertDatabaseMissing('users', ['username' => 'jordan-student']);
    }

    public function test_registration_rejects_a_username_that_is_already_in_use(): void
    {
        User::factory()->create(['username' => 'existing-student']);

        $this->from(route('register'))->post(route('register.store'), [
            'full_name' => 'Jordan Student',
            'username' => 'existing-student',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register'))
            ->assertInvalid([
                'username' => 'The username has already been taken.',
            ]);
    }

    public function test_seeded_administrator_can_sign_in(): void
    {
        $this->seed();

        $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_role_restricted_pages_reject_other_roles(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->get(route('admin.users.edit', User::factory()->create(['role' => 'student'])))
            ->assertForbidden();
        $this->put(route('admin.users.update', User::factory()->create(['role' => 'student'])), [
            'full_name' => 'Unauthorized update',
            'username' => 'unauthorized-update',
            'gender' => 'female',
        ])->assertForbidden();

        $this->get(route('admin.results.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_accounts_and_assign_a_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['student', 'teacher'] as $role) {
            $this->actingAs($admin)->get(route('admin.users.create', ['role' => $role]))
                ->assertOk()
                ->assertSee('<select name="gender"', false)
                ->assertSee('Female')
                ->assertSee('Male');
        }

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'role' => 'teacher',
            'full_name' => 'Taylor Teacher',
            'username' => 'taylor-teacher',
            'email' => 'taylor@example.com',
            'gender' => 'female',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.users.index', ['role' => 'teacher']));

        $teacher = User::where('username', 'taylor-teacher')->firstOrFail();
        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'gender' => 'female',
        ]);

        $courseData = [
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
            'academic_year' => '2026/27',
            'semester' => 1,
        ];

        $this->from(route('admin.courses.index'))->post(route('admin.courses.store'), [
            ...$courseData,
            'academic_year' => '2026',
            'semester' => 3,
        ])->assertRedirect(route('admin.courses.index'))
            ->assertInvalid(['academic_year', 'semester']);

        $this->post(route('admin.courses.store'), $courseData)
            ->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseHas('courses', [
            'course_code' => 'WEB101',
            'teacher_id' => $teacher->id,
            'academic_year' => '2026/27',
            'semester' => 1,
        ]);
    }

    public function test_admin_can_edit_student_and_teacher_profiles_and_gender(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'gender' => 'female',
            'username' => 'edit-student',
            'email' => 'edit-student@example.com',
        ]);
        $teacher = User::factory()->create([
            'role' => 'teacher',
            'gender' => 'male',
            'username' => 'edit-teacher',
            'email' => 'edit-teacher@example.com',
        ]);

        foreach ([$student, $teacher] as $user) {
            $role = $user->role;
            $this->actingAs($admin)->get(route('admin.users.index', ['role' => $role]))
                ->assertOk()
                ->assertSee(route('admin.users.edit', $user), false)
                ->assertSee('Delete');

            $this->get(route('admin.users.edit', $user))
                ->assertOk()
                ->assertSee('<select name="gender"', false)
                ->assertSee('Female')
                ->assertSee('Male');

            $this->put(route('admin.users.update', $user), [
                'full_name' => $user->full_name.' Updated',
                'username' => $user->username,
                'email' => $user->email,
                'phone' => '555-0134',
                'gender' => $role === 'student' ? 'male' : 'female',
                'address' => 'Updated address',
                'experience' => $role === 'teacher' ? 5 : null,
            ])->assertRedirect(route('admin.users.index', ['role' => $role]));

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'full_name' => $user->full_name.' Updated',
                'phone' => '555-0134',
                'gender' => $role === 'student' ? 'male' : 'female',
                'address' => 'Updated address',
                'experience' => $role === 'teacher' ? 5 : null,
            ]);
        }

        $this->from(route('admin.users.edit', $student))->put(route('admin.users.update', $student), [
            'full_name' => $student->full_name,
            'username' => $student->username,
            'email' => $student->email,
            'gender' => 'other',
        ])->assertRedirect(route('admin.users.edit', $student))
            ->assertInvalid(['gender']);

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'gender' => 'male',
        ]);
    }

    public function test_admin_can_assign_an_academic_term_to_an_existing_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'course_name' => 'Existing Course',
            'course_code' => 'OLD101',
            'credit_hours' => 3,
        ]);

        $this->actingAs($admin)->put(route('admin.courses.academic-term.update', $course), [
            'academic_year' => '2025/26',
            'semester' => 2,
        ])->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'academic_year' => '2025/26',
            'semester' => 2,
        ]);
    }

    public function test_students_can_enroll_and_view_their_results(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($student)
            ->post(route('student.courses.enroll', $course))
            ->assertRedirect(route('student.courses.index'));

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->get(route('student.courses.index'))
            ->assertOk()
            ->assertSee('You are enrolled in Web Development.')
            ->assertSee('Enrolled');

        Result::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'quiz_score' => 5,
            'attendance_score' => 5,
            'assignment_score' => 10,
            'midterm_score' => 30,
            'final_exam_score' => 50,
            'total' => 100,
            'grade' => 'A',
        ])->forceFill(['status' => Result::STATUS_APPROVED])->save();

        $this->get(route('student.results.index'))
            ->assertOk()
            ->assertSee($course->course_name)
            ->assertSee('Quiz / 5')
            ->assertSee('scope="col">Quiz / 5</th>', false)
            ->assertSee('Attendance / 5')
            ->assertSee('Assignment / 10')
            ->assertSee('Midterm / 30')
            ->assertSee('Final / 50')
            ->assertSee('5.00')
            ->assertSee('100.00/100')
            ->assertSee('A')
            ->assertDontSee('<th scope="col">Status</th>', false)
            ->assertDontSee('Approved');
    }

    public function test_duplicate_enrollment_shows_an_already_enrolled_notification(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
        ]);
        DB::table('enrollments')->insert([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($student)
            ->post(route('student.courses.enroll', $course))
            ->assertRedirect(route('student.courses.index'))
            ->assertSessionHas('status', 'You are already enrolled in Web Development.');

        $this->get(route('student.courses.index'))
            ->assertOk()
            ->assertSee('You are already enrolled in Web Development.');

        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_teacher_can_save_results_for_an_enrolled_student(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
        ]);
        DB::table('enrollments')->insert([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($teacher)->post(route('teacher.results.store'), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'action' => 'save',
            'quiz_score' => 4.5,
            'attendance_score' => 4.5,
            'assignment_score' => 9.5,
            'midterm_score' => 28.5,
            'final_exam_score' => 48,
        ])->assertRedirect(route('teacher.results.index'));

        $this->assertDatabaseHas('results', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'quiz_score' => 4.5,
            'final_exam_score' => 48,
            'total' => 95,
            'grade' => 'A',
            'status' => Result::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($teacher)
            ->get(route('teacher.results.index'));

        $response
            ->assertOk()
            ->assertSee($student->full_name)
            ->assertSee('Quiz / 5')
            ->assertSee('scope="col">Quiz / 5</th>', false)
            ->assertSee('Final / 50')
            ->assertSee(route('teacher.results.update', Result::where('student_id', $student->id)->firstOrFail()), false)
            ->assertSee('Edit result')
            ->assertSee('Update result')
            ->assertSee('Submit to admin')
            ->assertSee('Save result')
            ->assertSee('Submit to admin');

        $this->actingAs($student)->get(route('dashboard'))
            ->assertOk()
            ->assertSee($course->course_name)
            ->assertSee('95.00')
            ->assertSee('Draft');

        $this->get(route('student.results.index'))
            ->assertOk()
            ->assertSee($course->course_name)
            ->assertSee('95.00/100')
            ->assertDontSee('<th scope="col">Status</th>', false)
            ->assertDontSee('Draft');
    }

    public function test_teacher_can_save_a_result_again_to_update_its_draft(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
        ]);
        DB::table('enrollments')->insert([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $scores = [
            'quiz_score' => 3,
            'attendance_score' => 3,
            'assignment_score' => 6,
            'midterm_score' => 18,
            'final_exam_score' => 30,
        ];

        $this->actingAs($teacher)->post(route('teacher.results.store'), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'action' => 'save',
            ...$scores,
        ])->assertRedirect(route('teacher.results.index'));

        $result = Result::where('student_id', $student->id)->firstOrFail();
        $updatedScores = [...$scores, 'quiz_score' => 4];

        $this->post(route('teacher.results.store'), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'action' => 'save',
            ...$updatedScores,
        ])->assertRedirect(route('teacher.results.index'));

        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'quiz_score' => 4,
            'total' => 61,
            'status' => Result::STATUS_DRAFT,
        ]);
    }

    public function test_submitting_a_result_locks_teacher_edits_until_admin_returns_it(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
        ]);
        DB::table('enrollments')->insert([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $scores = [
            'quiz_score' => 3,
            'attendance_score' => 3,
            'assignment_score' => 6,
            'midterm_score' => 18,
            'final_exam_score' => 30,
        ];

        $this->actingAs($teacher)->post(route('teacher.results.store'), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'action' => 'submit',
            ...$scores,
        ])->assertRedirect(route('teacher.results.index'));

        $result = Result::where('student_id', $student->id)->firstOrFail();
        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'status' => Result::STATUS_PENDING_REVIEW,
            'submitted_at' => $result->submitted_at,
        ]);

        $this->put(route('teacher.results.update', $result), [
            'action' => 'save',
            ...Result::SCORE_MAXIMUMS,
        ])->assertStatus(409);

        $this->post(route('teacher.results.store'), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'action' => 'save',
            ...Result::SCORE_MAXIMUMS,
        ])->assertStatus(409);

        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'quiz_score' => 3,
            'total' => 60,
            'status' => Result::STATUS_PENDING_REVIEW,
        ]);
        $this->actingAs($student)->get(route('student.results.index'))
            ->assertOk()
            ->assertSee($course->course_name)
            ->assertSee('60.00/100')
            ->assertDontSee('Pending Review')
            ->assertDontSee('<th scope="col">Status</th>', false);
    }

    public function test_admin_can_approve_a_submitted_result_for_student_viewing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
        ]);
        DB::table('enrollments')->insert([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $result = Result::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            ...Result::SCORE_MAXIMUMS,
            'total' => 100,
            'grade' => 'A',
        ]);
        $result->forceFill([
            'status' => Result::STATUS_PENDING_REVIEW,
            'submitted_at' => now(),
        ])->save();

        $this->actingAs($teacher)->get(route('admin.results.index'))
            ->assertForbidden();

        $this->post(route('admin.results.approve', $result))
            ->assertForbidden();

        $this->actingAs($admin)->get(route('admin.results.index'))
            ->assertOk()
            ->assertSee($student->full_name)
            ->assertSee($course->course_name)
            ->assertDontSee('Pending Review')
            ->assertDontSee('<th scope="col">Status</th>', false)
            ->assertDontSee('Actions')
            ->assertDontSee(route('admin.results.approve', $result), false)
            ->assertDontSee(route('admin.results.return', $result), false)
            ->assertDontSee('Return for edits');

        $this->post(route('admin.results.approve', $result))
            ->assertRedirect(route('admin.results.index'));

        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'status' => Result::STATUS_APPROVED,
        ]);
        $this->post(route('admin.results.approve', $result))
            ->assertStatus(409);
        $this->post(route('admin.results.return', $result), [
            'feedback' => 'This is already approved.',
        ])->assertStatus(409);

        $this->actingAs($student)->get(route('student.results.index'))
            ->assertOk()
            ->assertSee($course->course_name)
            ->assertSee('100.00/100');

        $this->actingAs($teacher)->put(route('teacher.results.update', $result), [
            'action' => 'save',
            ...Result::SCORE_MAXIMUMS,
        ])->assertStatus(409);
    }

    public function test_admin_result_submissions_group_all_courses_under_each_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $firstStudent = User::factory()->create([
            'role' => 'student',
            'full_name' => 'Aaron Student',
        ]);
        $secondStudent = User::factory()->create([
            'role' => 'student',
            'full_name' => 'Zoe Student',
        ]);

        foreach ([
            [$firstStudent, 'Algebra'],
            [$firstStudent, 'Biology'],
            [$secondStudent, 'Chemistry'],
        ] as [$student, $courseName]) {
            $course = Course::create([
                'course_name' => $courseName,
                'course_code' => strtoupper(substr($courseName, 0, 3)).$student->id,
                'credit_hours' => 3,
                'teacher_id' => $teacher->id,
            ]);
            $result = Result::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                ...Result::SCORE_MAXIMUMS,
                'total' => 100,
                'grade' => 'A',
            ]);
            $result->forceFill([
                'status' => Result::STATUS_PENDING_REVIEW,
                'submitted_at' => now(),
            ])->save();
        }

        $this->actingAs($admin)->get(route('admin.results.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'Aaron Student',
                'Algebra',
                'Biology',
                'Zoe Student',
                'Chemistry',
            ])
            ->assertSee('rowspan="2"', false);
    }

    public function test_admin_can_return_a_result_with_feedback_for_teacher_edits(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
        ]);
        DB::table('enrollments')->insert([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $result = Result::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            ...Result::SCORE_MAXIMUMS,
            'total' => 100,
            'grade' => 'A',
        ]);
        $result->forceFill([
            'status' => Result::STATUS_PENDING_REVIEW,
            'submitted_at' => now(),
        ])->save();

        $this->actingAs($admin)->from(route('admin.results.index'))
            ->post(route('admin.results.return', $result), [
                'feedback' => '',
            ])->assertRedirect(route('admin.results.index'))
            ->assertInvalid(['feedback' => 'The feedback field is required.']);

        $this->post(route('admin.results.return', $result), [
            'feedback' => '<script>Review marks</script>',
        ])->assertRedirect(route('admin.results.index'));

        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'status' => Result::STATUS_RETURNED,
            'admin_feedback' => '<script>Review marks</script>',
        ]);

        $response = $this->actingAs($teacher)->get(route('teacher.results.index'));
        $response->assertOk()
            ->assertSee('&lt;script&gt;Review marks&lt;/script&gt;', false)
            ->assertSee('Edit result')
            ->assertSee('Update result')
            ->assertSee('Submit to admin');

        $this->put(route('teacher.results.update', $result), [
            'action' => 'save',
            ...Result::SCORE_MAXIMUMS,
        ])->assertRedirect(route('teacher.results.index'))
            ->assertSessionHas('status', 'Result updated: 100.00/100 (A).');

        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'status' => Result::STATUS_RETURNED,
            'admin_feedback' => '<script>Review marks</script>',
        ]);

        $this->put(route('teacher.results.update', $result), [
            'action' => 'submit',
            ...Result::SCORE_MAXIMUMS,
        ])->assertRedirect(route('teacher.results.index'))
            ->assertSessionHas('status', 'Result updated and submitted to the administrator: 100.00/100 (A).');

        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'status' => Result::STATUS_PENDING_REVIEW,
            'admin_feedback' => null,
        ]);
    }

    public function test_teacher_cannot_save_a_score_above_its_category_maximum(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
        ]);
        DB::table('enrollments')->insert([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $scores = Result::SCORE_MAXIMUMS;
        $scores['quiz_score'] = 6;

        $this->actingAs($teacher)->from(route('teacher.results.index'))
            ->post(route('teacher.results.store'), [
                'student_id' => $student->id,
                'course_id' => $course->id,
                'action' => 'save',
                ...$scores,
            ])->assertRedirect(route('teacher.results.index'))
            ->assertInvalid(['quiz_score' => 'The quiz score field must be between 0 and 5.']);

        $this->assertDatabaseMissing('results', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_teacher_can_update_an_existing_result_from_the_per_student_form(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
            'teacher_id' => $teacher->id,
        ]);
        DB::table('enrollments')->insert([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $result = Result::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'total' => 60,
            'grade' => 'D',
            'quiz_score' => 3,
            'attendance_score' => 3,
            'assignment_score' => 6,
            'midterm_score' => 18,
            'final_exam_score' => 30,
        ]);

        $this->actingAs($teacher)->put(route('teacher.results.update', $result), [
            'action' => 'save',
            ...Result::SCORE_MAXIMUMS,
        ])->assertRedirect(route('teacher.results.index'));

        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'quiz_score' => 5,
            'final_exam_score' => 50,
            'total' => 100,
            'grade' => 'A',
        ]);
    }

    public function test_teacher_cannot_update_a_result_for_another_teachers_course(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $anotherTeacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'course_name' => 'Web Development',
            'course_code' => 'WEB101',
            'credit_hours' => 3,
            'teacher_id' => $anotherTeacher->id,
        ]);
        DB::table('enrollments')->insert([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $result = Result::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            ...Result::SCORE_MAXIMUMS,
            'total' => 100,
            'grade' => 'A',
        ]);

        $this->actingAs($teacher)->put(route('teacher.results.update', $result), [
            'action' => 'save',
            ...Result::SCORE_MAXIMUMS,
        ])->assertNotFound();

        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'quiz_score' => 5,
            'total' => 100,
            'grade' => 'A',
        ]);
    }
}
