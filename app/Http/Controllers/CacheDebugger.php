<?php

namespace App\Http\Controllers;

use App\DebuggerInterface;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use function Webmozart\Assert\Tests\StaticAnalysis\uuid;

class CacheDebugger implements DebuggerInterface
{
    private const CACHE_KEY_PREFIX = 'debugger:';
    private const CACHE_COUNTER_KEY = 'debugger:counter';
    private const CACHE_INDEX_KEY = 'debugger:index';
    private const CACHE_FILES_KEY = 'debugger:files';
    private const CACHE_TTL = 3600; // 1 hour
    public function __construct()
    {
        Cache::put(self::CACHE_COUNTER_KEY, 0, self::CACHE_TTL);
    }

    public function display($variable): void
    {
        $debug_backtrace = debug_backtrace()[1];
        $class_name = str_replace(base_path(), '', $debug_backtrace['file']);
        $line = $debug_backtrace['line'];

        $debug_entry = [
            'id' => $this->getNextId(),
            'class_name' => $class_name,
            'line_number' => $line,
            'debug_type' => $this->getVariableType($variable),
            'created_at' => Carbon::now(),
            'value' => $this->formatValue($variable),
            'raw_value' => $this->getRawValue($variable),
        ];

        $this->storeDebugEntry($debug_entry);
        $this->updateIndex($debug_entry);
        $this->updateFilesList($class_name);
    }

    public function displayQuery(Builder $query): void
    {
        $debug_backtrace = debug_backtrace()[1];
        $class_name = str_replace(base_path(), '', $debug_backtrace['file']);
        $line = $debug_backtrace['line'];

        $sql = $query->toSql();
        foreach ($query->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'$binding'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }

        $debug_entry = [
            'id' => $this->getNextId(),
            'class_name' => $class_name,
            'line_number' => $line,
            'debug_type' => 'text',
            'created_at' => Carbon::now(),
            'value' => $sql,
            'raw_value' => $sql,
        ];

        $this->storeDebugEntry($debug_entry);
        $this->updateIndex($debug_entry);
        $this->updateFilesList($class_name);
    }

    public function loadDebugData($search = null, $filterByType = null, $filterByFile = null): array
    {
        $index = Cache::get(self::CACHE_INDEX_KEY, []);
        $results = [];

        foreach ($index as $id) {
            $entry = Cache::get(self::CACHE_KEY_PREFIX . $id);
            if (!$entry) continue;

            // Apply filters
            if ($search && !$this->matchesSearch($entry, $search)) {
                continue;
            }

            if ($filterByType && $entry['debug_type'] !== $filterByType) {
                continue;
            }

            if ($filterByFile) {
                if (strpos($entry['class_name'], $filterByFile) === false) {
                    continue;
                }
            }
            $results[] = $entry;
        }

        // Sort by ID (ascending or descending based on config)
        $sortOrder = config('debugger.sort', 'desc');
        usort($results, function ($a, $b) use ($sortOrder) {
            return $sortOrder === 'desc' ? $b['id'] - $a['id'] : $a['id'] - $b['id'];
        });

        return $results;
    }

    public function loadFiles(): array
    {
        return Cache::get(self::CACHE_FILES_KEY, []);
    }

    public function clearAllDebugData(): void
    {
        $index = Cache::get(self::CACHE_INDEX_KEY, []);

        // Remove all debug entries
        foreach ($index as $id) {
            Cache::forget(self::CACHE_KEY_PREFIX . $id);
        }

        // Clear index and metadata
        Cache::forget(self::CACHE_INDEX_KEY);
        Cache::forget(self::CACHE_COUNTER_KEY);
        Cache::forget(self::CACHE_FILES_KEY);
    }

    private function getNextId(): int
    {
        return Cache::increment(self::CACHE_COUNTER_KEY, 1);
    }

    private function getVariableType($variable): string
    {
        if (is_array($variable) || is_object($variable)) {
            return 'json';
        } elseif (is_int($variable) || is_float($variable) || is_numeric($variable) || is_bool($variable)) {
            return 'number';
        } elseif (is_string($variable)) {
            return 'text';
        } else {
            return 'unknown';
        }
    }

    private function formatValue($variable)
    {
        if (is_array($variable) || is_object($variable)) {
            $encoded = json_encode($variable);
            if ($encoded === false) {
                return ['error' => 'Invalid JSON or Array'];
            }
            return json_decode($encoded, true);
        } elseif (is_int($variable) || is_float($variable) || is_numeric($variable) || is_bool($variable)) {
            if (is_bool($variable)) {
                return $variable ? 1 : 0;
            } elseif (is_float($variable)) {
                return (float)$variable;
            }
            return $variable;
        } elseif (is_string($variable)) {
            return $variable;
        } else {
            return 'N/A';
        }
    }

    private function getRawValue($variable): string
    {
        if (is_array($variable) || is_object($variable)) {
            $encoded = json_encode($variable);
            if ($encoded === false) {
                return json_encode(['error' => 'Invalid JSON or Array']);
            }
            return $encoded;
        } elseif (is_int($variable) || is_float($variable) || is_numeric($variable) || is_bool($variable)) {
            if (is_bool($variable)) {
                return $variable ? '1' : '0';
            }
            return (string)$variable;
        } elseif (is_string($variable)) {
            return $variable;
        } else {
            return '';
        }
    }

    private function storeDebugEntry(array $entry): void
    {
        Cache::put(
            self::CACHE_KEY_PREFIX . $entry['id'],
            $entry,
            self::CACHE_TTL
        );
    }

    private function updateIndex(array $entry): void
    {
        $index = Cache::get(self::CACHE_INDEX_KEY, []);
        $index[] = $entry['id'];

        // Keep only the last 1000 entries to prevent memory issues
        if (count($index) > 1000) {
            $oldId = array_shift($index);
            Cache::forget(self::CACHE_KEY_PREFIX . $oldId);
        }

        Cache::put(self::CACHE_INDEX_KEY, $index, self::CACHE_TTL);
    }

    private function updateFilesList(string $className): void
    {
        $files = Cache::get(self::CACHE_FILES_KEY, []);

        if (!in_array($className, $files)) {
            $files[] = $className;
            Cache::put(self::CACHE_FILES_KEY, array_values($files), self::CACHE_TTL);
        }
    }

    private function matchesSearch(array $entry, string $search): bool
    {
        $search = strtolower($search);

        // Search in class name
        if (strpos(strtolower($entry['class_name']), $search) !== false) {
            return true;
        }

        // Search in line number
        if (strpos((string)$entry['line_number'], $search) !== false) {
            return true;
        }

        // Search in value content
        $searchValue = strtolower($entry['raw_value']);
        if (strpos($searchValue, $search) !== false) {
            return true;
        }

        return false;
    }
}
