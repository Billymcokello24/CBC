<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;
use App\Models\Tenant;

class TestSidebarLinks extends Command
{
    protected $signature = 'test:sidebar-links {--base-url=http://localhost:8000} {--file=resources/js/components/AppSidebar.vue} {--output=storage/logs/sidebar_test_results.json}';

    protected $description = 'Extract hrefs from AppSidebar.vue and test responses';

    public function handle()
    {
        $baseUrl = rtrim($this->option('base-url'), '/');
        $file = base_path($this->option('file'));
        $output = $this->option('output');

        if (!file_exists($file)) {
            $this->error("Sidebar file not found: $file");
            return 1;
        }

        $content = file_get_contents($file);

        // Match patterns: href: '/path' and Link href="/path"
        preg_match_all('/href\s*:\s*["\'\`]([^"\'\`]+)["\'\`]/', $content, $m1);
        preg_match_all('/<Link\s+href=\"([^\"]+)\"/', $content, $m2);

        $urls = array_merge($m1[1] ?? [], $m2[1] ?? []);
        $urls = array_values(array_unique(array_filter($urls, fn($u) => str_starts_with($u, '/'))));

        if (empty($urls)) {
            $this->warn('No sidebar URLs found.');
            return 0;
        }

        $this->info('Found ' . count($urls) . ' sidebar URLs.');

        $client = new Client(['base_uri' => $baseUrl, 'http_errors' => false, 'timeout' => 10]);
        $results = [];

        // Build route map to determine tenant vs central
        $routes = Route::getRoutes();

        foreach ($urls as $u) {
            $path = ltrim($u, '/');
            $this->line("Testing: $u");

            // Find matching route by URI (simple equality or parameterized match)
            $matchedRoute = null;
            foreach ($routes as $route) {
                $routeUri = $route->uri();
                // Normalize both
                if ($routeUri === $path || ('/' . $routeUri) === $u) {
                    $matchedRoute = $route;
                    break;
                }
                // Try parameterized match: replace {param} with wildcard
                $pattern = '#^' . preg_replace('#\{[^}]+\}#', '[^/]+', $routeUri) . '$#';
                if (preg_match($pattern, $path)) {
                    $matchedRoute = $route;
                    break;
                }
            }

            $isTenant = false;
            if ($matchedRoute) {
                $middleware = $matchedRoute->gatherMiddleware();
                foreach ([\Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class, \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class, \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class] as $m) {
                    if (in_array($m, $middleware, true)) {
                        $isTenant = true;
                        break;
                    }
                }
            }

            if ($isTenant) {
                // Test against each tenant domain (first domain only to keep it quick)
                $tenants = Tenant::all();
                foreach ($tenants as $tenant) {
                    $domains = $tenant->domains()->pluck('domain')->toArray();
                    if (empty($domains)) continue;
                    $domain = $domains[0];

                    try {
                        $start = microtime(true);
                        $resp = $client->get('/' . $path, [
                            'headers' => [
                                'Host' => $domain,
                                'Accept' => 'text/html,application/json',
                            ],
                        ]);
                        $time = round((microtime(true) - $start) * 1000);
                        $status = $resp->getStatusCode();
                        $length = strlen((string) $resp->getBody());
                        $this->line("  [tenant={$tenant->id}] -> $status ({$length} bytes)");
                        $results[] = ['url' => $u, 'tenant' => $tenant->id, 'domain' => $domain, 'status' => $status, 'time_ms' => $time, 'length' => $length];
                    } catch (\Exception $e) {
                        $this->error("  Error: " . $e->getMessage());
                        $results[] = ['url' => $u, 'tenant' => $tenant->id, 'domain' => $domain, 'status' => 'error', 'error' => $e->getMessage()];
                    }
                    break; // only test first domain per tenant to speed up
                }
            } else {
                try {
                    $resp = $client->get('/' . $path, ['headers' => ['Accept' => 'text/html,application/json']]);
                    $status = $resp->getStatusCode();
                    $length = strlen((string) $resp->getBody());
                    $this->line("  -> $status ({$length} bytes)");
                    $results[] = ['url' => $u, 'status' => $status, 'length' => $length];
                } catch (\Exception $e) {
                    $this->error("  Error: " . $e->getMessage());
                    $results[] = ['url' => $u, 'status' => 'error', 'error' => $e->getMessage()];
                }
            }
        }

        @mkdir(dirname($output), 0755, true);
        file_put_contents($output, json_encode($results, JSON_PRETTY_PRINT));
        $this->info('Results written to ' . $output);

        // Print a summary of 404s
        $notFound = array_filter($results, fn($r) => $r['status'] === 404);
        if ($notFound) {
            $this->warn('The following sidebar URLs returned 404:');
            foreach ($notFound as $r) {
                $this->line(' - ' . $r['url']);
            }
        }

        return 0;
    }
}
