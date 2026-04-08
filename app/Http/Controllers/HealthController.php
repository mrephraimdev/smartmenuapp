<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    /**
     * Health check endpoint for monitoring.
     *
     * Checks:
     * - Application status
     * - Database connectivity
     * - Cache functionality
     * - Queue status
     * - Storage availability
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $checks = [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
        ];

        // Determine overall health status
        $allOk = !in_array('error', array_column($checks, 'status'), true);
        $httpStatus = $allOk ? 200 : 503;

        // Add summary
        $checks['healthy'] = $allOk;
        $checks['message'] = $allOk
            ? 'All systems operational'
            : 'Some systems are experiencing issues';

        return response()->json($checks, $httpStatus);
    }

    /**
     * Ping endpoint for simple uptime monitoring.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Check database connectivity and performance.
     *
     * @return array
     */
    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);

            // Try to execute a simple query
            DB::connection()->getPdo();
            $result = DB::selectOne('SELECT 1 as success');

            $duration = round((microtime(true) - $start) * 1000, 2);

            if ($result && $result->success === 1) {
                return [
                    'status' => 'ok',
                    'response_time_ms' => $duration,
                    'driver' => config('database.default'),
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Database query failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache functionality.
     *
     * @return array
     */
    protected function checkCache(): array
    {
        try {
            $start = microtime(true);

            $key = 'health_check_' . time();
            $value = uniqid('test_', true);

            // Test cache set
            Cache::put($key, $value, 10);

            // Test cache get
            $retrieved = Cache::get($key);

            // Clean up
            Cache::forget($key);

            $duration = round((microtime(true) - $start) * 1000, 2);

            if ($retrieved === $value) {
                return [
                    'status' => 'ok',
                    'response_time_ms' => $duration,
                    'driver' => config('cache.default'),
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Cache read/write mismatch',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue status.
     *
     * @return array
     */
    protected function checkQueue(): array
    {
        try {
            $driver = config('queue.default');

            // Get queue size (for database driver)
            if ($driver === 'database') {
                $pendingJobs = DB::table('jobs')->count();

                return [
                    'status' => 'ok',
                    'driver' => $driver,
                    'pending_jobs' => $pendingJobs,
                ];
            }

            // For Redis/SQS, we just verify connection
            if ($driver === 'redis') {
                Queue::connection($driver);

                return [
                    'status' => 'ok',
                    'driver' => $driver,
                ];
            }

            return [
                'status' => 'ok',
                'driver' => $driver,
                'message' => 'Queue driver detected',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage availability and disk space.
     *
     * @return array
     */
    protected function checkStorage(): array
    {
        try {
            $disk = Storage::disk(config('filesystems.default'));

            // Test write
            $testFile = 'health_check_' . time() . '.txt';
            $testContent = 'Health check test';

            $disk->put($testFile, $testContent);

            // Test read
            $retrieved = $disk->get($testFile);

            // Clean up
            $disk->delete($testFile);

            // Get disk space (for local driver)
            $diskSpace = null;
            if (config('filesystems.default') === 'local') {
                $path = storage_path('app');
                $totalSpace = disk_total_space($path);
                $freeSpace = disk_free_space($path);

                $diskSpace = [
                    'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
                    'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                    'used_percent' => round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2),
                ];
            }

            if ($retrieved === $testContent) {
                $result = [
                    'status' => 'ok',
                    'driver' => config('filesystems.default'),
                ];

                if ($diskSpace) {
                    $result['disk_space'] = $diskSpace;

                    // Warn if disk usage > 90%
                    if ($diskSpace['used_percent'] > 90) {
                        $result['warning'] = 'Disk usage above 90%';
                    }
                }

                return $result;
            }

            return [
                'status' => 'error',
                'message' => 'Storage read/write mismatch',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed: ' . $e->getMessage(),
            ];
        }
    }
}
