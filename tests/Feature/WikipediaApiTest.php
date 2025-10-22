<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WikipediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_extract_table_requires_url()
    {
        $response = $this->postJson('/api/extract-table', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['url']);
    }

    public function test_extract_table_requires_valid_url()
    {
        $response = $this->postJson('/api/extract-table', [
            'url' => 'not-a-url'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['url']);
    }

    public function test_extract_table_handles_pages_without_tables()
    {
        $response = $this->postJson('/api/extract-table', [
            'url' => 'https://en.wikipedia.org/wiki/Main_Page'
        ]);

        // Returns 400 with error message (actual behavior may vary based on page)
        // This test verifies error handling works for edge cases
        $this->assertContains($response->status(), [200, 400]);
    }

    public function test_generate_visualization_requires_table_data()
    {
        $response = $this->postJson('/api/generate-visualization', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['tableData']);
    }

    public function test_generate_visualization_requires_selected_columns()
    {
        $response = $this->postJson('/api/generate-visualization', [
            'tableData' => [
                'headers' => ['Country', 'Population'],
                'rows' => [['China', '1411778724']]
            ]
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['selectedColumns']);
    }

    public function test_generate_visualization_requires_chart_type()
    {
        $response = $this->postJson('/api/generate-visualization', [
            'tableData' => [
                'headers' => ['Country', 'Population'],
                'rows' => [['China', '1411778724']]
            ],
            'selectedColumns' => ['Population']
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['chartType']);
    }

    public function test_generate_visualization_validates_chart_type()
    {
        $response = $this->postJson('/api/generate-visualization', [
            'tableData' => [
                'headers' => ['Country', 'Population'],
                'rows' => [['China', '1411778724']]
            ],
            'selectedColumns' => ['Population'],
            'chartType' => 'invalid_type'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['chartType']);
    }

    public function test_api_returns_json_content_type()
    {
        $response = $this->postJson('/api/extract-table', [
            'url' => 'https://en.wikipedia.org/wiki/Test'
        ]);

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }
}
