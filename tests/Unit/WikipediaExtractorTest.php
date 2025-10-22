<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\WikipediaExtractor;
use Illuminate\Support\Facades\Http;

class WikipediaExtractorTest extends TestCase
{
    private WikipediaExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new WikipediaExtractor();
    }

    public function test_extracts_table_headers_from_thead()
    {
        $html = '
            <table class="wikitable">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Population</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>China</td>
                        <td>1411778724</td>
                    </tr>
                </tbody>
            </table>
        ';

        Http::fake([
            '*' => Http::response($html, 200),
        ]);

        $result = $this->extractor->extractTable('https://en.wikipedia.org/wiki/Test');

        $this->assertEquals(['Country', 'Population'], $result['headers']);
        $this->assertCount(1, $result['rows']);
        $this->assertEquals(['China', '1411778724'], $result['rows'][0]);
    }

    public function test_identifies_numeric_columns()
    {
        $html = '
            <table class="wikitable">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Population</th>
                        <th>GDP</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>China</td><td>1411778724</td><td>14720000</td></tr>
                    <tr><td>India</td><td>1393409038</td><td>2875000</td></tr>
                    <tr><td>USA</td><td>331893745</td><td>20940000</td></tr>
                </tbody>
            </table>
        ';

        Http::fake([
            '*' => Http::response($html, 200),
        ]);

        $result = $this->extractor->extractTable('https://en.wikipedia.org/wiki/Test');

        $this->assertContains('Population', $result['numericColumns']);
        $this->assertContains('GDP', $result['numericColumns']);
        $this->assertNotContains('Country', $result['numericColumns']);
    }

    public function test_handles_table_without_thead()
    {
        $html = '
            <table class="wikitable">
                <tbody>
                    <tr>
                        <th>Country</th>
                        <th>Population</th>
                    </tr>
                    <tr>
                        <td>China</td>
                        <td>1411778724</td>
                    </tr>
                </tbody>
            </table>
        ';

        Http::fake([
            '*' => Http::response($html, 200),
        ]);

        $result = $this->extractor->extractTable('https://en.wikipedia.org/wiki/Test');

        $this->assertEquals(['Country', 'Population'], $result['headers']);
        $this->assertCount(1, $result['rows']);
    }

    public function test_preserves_data_as_extracted()
    {
        $html = '
            <table class="wikitable">
                <thead>
                    <tr><th>Country</th><th>Population</th></tr>
                </thead>
                <tbody>
                    <tr><td>China</td><td>1411778724</td></tr>
                </tbody>
            </table>
        ';

        Http::fake([
            '*' => Http::response($html, 200),
        ]);

        $result = $this->extractor->extractTable('https://en.wikipedia.org/wiki/Test');

        $this->assertEquals('1411778724', $result['rows'][0][1]);
        $this->assertEquals('China', $result['rows'][0][0]);
    }

    public function test_throws_exception_when_no_table_found()
    {
        $html = '<html><body><p>No tables here</p></body></html>';

        Http::fake([
            '*' => Http::response($html, 200),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No tables found on this Wikipedia page');

        $this->extractor->extractTable('https://en.wikipedia.org/wiki/Test');
    }

    public function test_throws_exception_on_http_error()
    {
        Http::fake([
            '*' => Http::response('', 404),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch Wikipedia page');

        $this->extractor->extractTable('https://en.wikipedia.org/wiki/Test');
    }
}
