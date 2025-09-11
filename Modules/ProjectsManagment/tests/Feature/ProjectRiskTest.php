<?php

namespace Modules\ProjectsManagment\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProjectsManagment\Models\ProjectRisk;
use Modules\ProjectsManagment\Models\Project;
use Modules\HumanResources\Models\Employee;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;

class ProjectRiskTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $project;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->project = Project::factory()->create(['company_id' => $this->company->id]);
        $this->employee = Employee::factory()->create(['company_id' => $this->company->id]);
    }

    /** @test */
    public function it_can_create_project_risk()
    {
        $data = [
            'project_id' => $this->project->id,
            'title' => 'Test Risk Title',
            'description' => 'Test risk description',
            'impact' => 'high',
            'probability' => 'medium',
            'mitigation_plan' => 'Test mitigation plan',
            'status' => 'open',
            'assigned_to' => $this->employee->id
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/project-risks', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Project risk created successfully'
            ]);

        $this->assertDatabaseHas('project_risks', [
            'project_id' => $this->project->id,
            'title' => 'Test Risk Title',
            'impact' => 'high',
            'probability' => 'medium',
            'status' => 'open',
            'assigned_to' => $this->employee->id
        ]);
    }

    /** @test */
    public function it_can_list_project_risks()
    {
        // Create test project risks
        ProjectRisk::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'assigned_to' => $this->employee->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-risks');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project risks retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'project_id',
                            'title',
                            'impact',
                            'probability',
                            'status',
                            'assigned_to'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_show_project_risk()
    {
        $projectRisk = ProjectRisk::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'assigned_to' => $this->employee->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/project-risks/{$projectRisk->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project risk retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'project_id',
                    'title',
                    'impact',
                    'probability',
                    'status',
                    'assigned_to'
                ]
            ]);
    }

    /** @test */
    public function it_can_update_project_risk()
    {
        $projectRisk = ProjectRisk::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'title' => 'Original Title',
            'status' => 'open'
        ]);

        $updateData = [
            'title' => 'Updated Risk Title',
            'status' => 'mitigated'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/project-risks/{$projectRisk->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project risk updated successfully'
            ]);

        $this->assertDatabaseHas('project_risks', [
            'id' => $projectRisk->id,
            'title' => 'Updated Risk Title',
            'status' => 'mitigated'
        ]);
    }

    /** @test */
    public function it_can_soft_delete_project_risk()
    {
        $projectRisk = ProjectRisk::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/project-risks/{$projectRisk->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project risk deleted successfully'
            ]);

        $this->assertSoftDeleted('project_risks', [
            'id' => $projectRisk->id
        ]);
    }

    /** @test */
    public function it_can_get_projects_for_dropdown()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-risks/projects/list');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Projects retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'project_number',
                        'name',
                        'display_name'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_get_employees_for_dropdown()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-risks/employees/list');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Employees retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'employee_number',
                        'full_name',
                        'display_name'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_get_impact_options()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-risks/impact/options');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Impact options retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'value',
                        'label'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_get_probability_options()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-risks/probability/options');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Probability options retrieved successfully'
            ]);
    }

    /** @test */
    public function it_can_get_status_options()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-risks/status/options');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Status options retrieved successfully'
            ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/project-risks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id', 'title', 'impact', 'probability', 'status']);
    }

    /** @test */
    public function it_validates_enum_values()
    {
        $data = [
            'project_id' => $this->project->id,
            'title' => 'Test Risk',
            'impact' => 'invalid_impact',
            'probability' => 'invalid_probability',
            'status' => 'invalid_status'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/project-risks', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['impact', 'probability', 'status']);
    }

    /** @test */
    public function it_can_search_project_risks()
    {
        ProjectRisk::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'title' => 'Database Security Risk',
            'description' => 'Risk related to database security'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/project-risks/search', [
                'search' => 'Database'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project risks search completed successfully'
            ]);
    }

    /** @test */
    public function it_can_filter_by_field()
    {
        ProjectRisk::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'impact' => 'high'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-risks/filter/by-field?field=impact&value=high');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project risks filtered successfully'
            ]);
    }

    /** @test */
    public function it_can_sort_project_risks()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/project-risks/sort', [
                'sort_by' => 'title',
                'sort_direction' => 'asc'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project risks sorted successfully'
            ]);
    }

    /** @test */
    public function it_can_restore_soft_deleted_project_risk()
    {
        $projectRisk = ProjectRisk::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id
        ]);

        // First delete it
        $projectRisk->delete();

        // Then restore it
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/project-risks/{$projectRisk->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project risk restored successfully'
            ]);
    }

    /** @test */
    public function it_can_get_trashed_project_risks()
    {
        $projectRisk = ProjectRisk::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id
        ]);

        $projectRisk->delete();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-risks/trashed/list');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Trashed project risks retrieved successfully'
            ]);
    }

    /** @test */
    public function it_can_get_field_values()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-risks/fields/values?field=impact');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Field values retrieved successfully'
            ]);
    }

    /** @test */
    public function it_can_get_sortable_fields()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-risks/fields/sortable');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sortable fields retrieved successfully'
            ]);
    }
}
