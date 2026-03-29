<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;
use App\Models\Tenant;

class TestApis extends Command
{
    protected $signature = 'test:apis {--base-url=http://localhost:8000} {--tenant=*} {--output=storage/logs/api_test_results.json} {--include-central}';

    protected $description = 'Discover and smoke-test central and tenant API routes';

    public function handle()
    {
        $baseUrl = $this->option('base-url');
        $output = $this->option('output');
        $includeCentral = (bool) $this->option('include-central');
        $tenantFilters = $this->option('tenant');

        $this->info("Base URL: $baseUrl");

        $routes = Route::getRoutes();

        $tenantMiddlewareClasses = [
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class,
            \Stancl\Tenancy\Middleware\InitializeTenancyByRequestData::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        ];

        $client = new Client(['base_uri' => $baseUrl, 'http_errors' => false, 'timeout' => 10]);

        $results = [];

        // Collect tenant routes and central routes
        $tenantRoutes = [];
        $centralRoutes = [];

        foreach ($routes as $route) {
            $methods = $route->methods();
            $uri = $route->uri();
            $name = $route->getName();
            $middleware = $route->gatherMiddleware();

            $isTenant = false;
            foreach ($tenantMiddlewareClasses as $m) {
                if (in_array($m, $middleware, true)) {
                    $isTenant = true;
                    break;
                }
            }

            $entry = [
                'methods' => $methods,
                'uri' => $uri,
                'name' => $name,
                'middleware' => $middleware,
            ];

            if ($isTenant) {
                $tenantRoutes[] = $entry;
            } else {
                $centralRoutes[] = $entry;
            }
        }

        $this->info('Found ' . count($centralRoutes) . ' central routes and ' . count($tenantRoutes) . ' tenant routes.');

        // Test central routes (if requested)
        if ($includeCentral) {
            $this->info('Testing central routes...');
            foreach ($centralRoutes as $r) {
                if (!in_array('GET', $r['methods']) && !in_array('HEAD', $r['methods'])) {
                    continue;
                }

                $url = $this->buildUrl($r['uri']);
                $start = microtime(true);
                $resp = $client->get($url, ['headers' => ['Accept' => 'application/json']]);
                $time = round((microtime(true) - $start) * 1000);

                $results[] = [
                    'scope' => 'central',
                    'uri' => $r['uri'],
                    'name' => $r['name'],
                    'status' => $resp->getStatusCode(),
                    'time_ms' => $time,
                    'length' => strlen((string) $resp->getBody()),
                ];
                $this->line("[central] {$r['uri']} -> {$resp->getStatusCode()} ({$time}ms)");
            }
        }

        // Test tenant routes per tenant
        $tenants = Tenant::all();
        if ($tenantFilters) {
            $tenants = $tenants->filter(fn($t) => in_array($t->id, $tenantFilters) || in_array($t->school_id ?? '', $tenantFilters));
        }

        foreach ($tenants as $tenant) {
            $domains = $tenant->domains()->pluck('domain')->toArray();
            if (empty($domains)) {
                $this->warn("Tenant {$tenant->id} has no domains, skipping.");
                continue;
            }

            $domain = $domains[0];
            $this->info("Testing tenant {$tenant->id} on domain {$domain}");

            foreach ($tenantRoutes as $r) {
                if (!in_array('GET', $r['methods']) && !in_array('HEAD', $r['methods'])) {
                    continue;
                }

                $url = $this->buildUrl($r['uri']);

                try {
                    $start = microtime(true);
                    $resp = $client->get($url, [
                        'headers' => [
                            'Host' => $domain,
                            'Accept' => 'application/json',
                        ],
                    ]);
                    $time = round((microtime(true) - $start) * 1000);

                    $results[] = [
                        'scope' => 'tenant',
                        'tenant_id' => $tenant->id,
                        'domain' => $domain,
                        'uri' => $r['uri'],
                        'name' => $r['name'],
                        'status' => $resp->getStatusCode(),
                        'time_ms' => $time,
                        'length' => strlen((string) $resp->getBody()),
                    ];

                    $this->line("[tenant:{$tenant->id}] {$r['uri']} -> {$resp->getStatusCode()} ({$time}ms)");
                } catch (\Exception $e) {
                    $this->error("Error requesting {$r['uri']} for tenant {$tenant->id}: " . $e->getMessage());
                    $results[] = [
                        'scope' => 'tenant',
                        'tenant_id' => $tenant->id,
                        'domain' => $domain,
                        'uri' => $r['uri'],
                        'name' => $r['name'],
                        'status' => 'error',
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        // Save results
        @mkdir(dirname($output), 0755, true);
        file_put_contents($output, json_encode($results, JSON_PRETTY_PRINT));

        $this->info('Results written to ' . $output);

        return 0;
    }

    protected function buildUrl(string $uri): string
    {
        // Replace Laravel route parameters like {id} with sample values
        return preg_replace_callback('/\{([^}]+)\}/', function ($m) {
            $name = $m[1];
            // if contains ?, optional param -> supply 1
            if (str_ends_with($name, '?')) {
                $name = substr($name, 0, -1);
            }

            // choose sample value
            if (stripos($name, 'id') !== false) {
                return '1';
            }

            return 'test';
        }, '/' . ltrim($uri, '/'));
    }
}
