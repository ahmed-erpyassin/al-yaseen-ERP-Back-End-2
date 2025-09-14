<?php

namespace Modules\ProjectsManagment\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProjectsManagment\Models\ProjectFinancial;
use Modules\ProjectsManagment\Models\Project;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Users\Models\User;
use Modules\Companies\Models\Company;

class ProjectFinancialTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $project;
    protected $currency;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->currency = Currency::factory()->create();
        $this->project = Project::factory()->create([
            'company_id' => $this->company->id,
            'currency_id' => $this->currency->id
        ]);
    }

    /** @test */
    public function it_can_create_project_financial()
    {
        $data = [
            'project_id' => $this->project->id,
            'currency_id' => $this->currency->id,
            'exchange_rate' => 1.0000,
            'reference_type' => 'Invoice',
            'reference_id' => 'INV-001',
            'amount' => 1000.00,
            'date' => '2024-01-15',
            'description' => 'Test project financial entry'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/project-financials', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Project financial created successfully'
            ]);

        $this->assertDatabaseHas('project_financials', [
            'project_id' => $this->project->id,
            'reference_type' => 'Invoice',
            'reference_id' => 'INV-001',
            'amount' => 1000.00
        ]);
    }

    /** @test */
    public function it_can_list_project_financials()
    {
        // Create test project financials
        ProjectFinancial::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'currency_id' => $this->currency->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-financials');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project financials retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'project_id',
                            'currency_id',
                            'reference_type',
                            'reference_id',
                            'amount',
                            'date'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_show_project_financial()
    {
        $projectFinancial = ProjectFinancial::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'currency_id' => $this->currency->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/project-financials/{$projectFinancial->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project financial retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'project_id',
                    'currency_id',
                    'reference_type',
                    'reference_id',
                    'amount',
                    'date'
                ]
            ]);
    }

    /** @test */
    public function it_can_update_project_financial()
    {
        $projectFinancial = ProjectFinancial::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'currency_id' => $this->currency->id,
            'amount' => 1000.00
        ]);

        $updateData = [
            'amount' => 1500.00,
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/project-financials/{$projectFinancial->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project financial updated successfully'
            ]);

        $this->assertDatabaseHas('project_financials', [
            'id' => $projectFinancial->id,
            'amount' => 1500.00,
            'description' => 'Updated description'
        ]);
    }

    /** @test */
    public function it_can_soft_delete_project_financial()
    {
        $projectFinancial = ProjectFinancial::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'currency_id' => $this->currency->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/project-financials/{$projectFinancial->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project financial deleted successfully'
            ]);

        $this->assertSoftDeleted('project_financials', [
            'id' => $projectFinancial->id
        ]);
    }

    /** @test */
    public function it_can_search_project_financials()
    {
        ProjectFinancial::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'currency_id' => $this->currency->id,
            'reference_type' => 'Invoice',
            'reference_id' => 'INV-001'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/project-financials/search', [
                'search' => 'Invoice'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project financials search completed successfully'
            ]);
    }

    /** @test */
    public function it_can_filter_by_field()
    {
        ProjectFinancial::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'currency_id' => $this->currency->id,
            'reference_type' => 'Invoice'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-financials/filter/by-field?field=reference_type&value=Invoice');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project financials filtered successfully'
            ]);
    }

    /** @test */
    public function it_can_get_sortable_fields()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-financials/fields/sortable');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sortable fields retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    /** @test */
    public function it_can_get_projects_for_dropdown()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-financials/projects/list');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Projects retrieved successfully'
            ]);
    }

    /** @test */
    public function it_can_get_currencies_for_dropdown()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/project-financials/currencies/list');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Currencies retrieved successfully'
            ]);
    }
}
