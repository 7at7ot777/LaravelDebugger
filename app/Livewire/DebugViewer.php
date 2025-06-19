<?php

namespace App\Livewire;

use App\Models\Debug;
use App\Models\Text;
use App\Models\Number;
use App\Models\Json;
use Livewire\Component;
use Livewire\Attributes\On;

class DebugViewer extends Component
{
    public $search = '';
    public $filterByType = '';
    public $filterByFile = '';
    public $debugs = [];
    public $files = [];
    public $types = ['text', 'number', 'json'];
    public $autoRefresh = true;
    public $selectedDebugId = null;

    public function mount()
    {
        $this->loadDebugData();
        $this->loadFiles();
    }

    public function loadDebugData()
    {
        $query = Debug::with('debugable')
            ->orderBy('id', config('debugger.sort'));

        if ($this->search) {
            $search = '%' . $this->search . '%';

            $query->where(function ($q) use ($search) {
                $q->where('class_name', 'like', $search)
                    ->orWhere('line_number', 'like', $search)
                    ->orWhereHasMorph('debugable', [Text::class, Json::class, Number::class], function ($subQuery, $type) use ($search) {
                        switch ($type) {
                            case Text::class:
                                $subQuery->where('text', 'like', $search);
                                break;
                            case Json::class:
                                $subQuery->where('json', 'like', $search);
                                break;
                            case Number::class:
                                $subQuery->whereRaw('CAST(number AS CHAR) LIKE ?', [$search]);
                                break;
                        }
                    });
            });
        }

        if ($this->filterByType) {
            $query->where('debug_type', $this->filterByType);
        }

        if ($this->filterByFile) {
            $query->where('class_name', 'like', '%' . $this->filterByFile . '%');
        }

        $this->debugs = $query->get()->map(function ($debug) {
            return [
                'id' => $debug->id,
                'class_name' => $debug->class_name,
                'line_number' => $debug->line_number,
                'debug_type' => $debug->debug_type,
                'created_at' => $debug->created_at,
                'value' => $this->formatDebugValue($debug),
                'raw_value' => $this->getRawValue($debug),
            ];
        })->toArray();
    }

    public function loadFiles()
    {
        $this->files = Debug::select('class_name')
            ->distinct()
            ->pluck('class_name')
            ->filter()
            ->values()
            ->toArray();
    }

    private function formatDebugValue($debug)
    {
        if (!$debug->debugable) {
            return 'N/A';
        }

        switch ($debug->debug_type) {
            case 'text':
                return $debug->debugable->text;
            case 'number':
                return $debug->debugable->is_int ?
                    (int) $debug->debugable->number :
                    (float) $debug->debugable->number;
            case 'json':
                return json_decode($debug->debugable->json, true);
            default:
                return 'Unknown type';
        }
    }

    private function getRawValue($debug)
    {
        if (!$debug->debugable) {
            return '';
        }

        switch ($debug->debug_type) {
            case 'text':
                return $debug->debugable->text;
            case 'number':
                return (string) $debug->debugable->number;
            case 'json':
                return $debug->debugable->json;
            default:
                return '';
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'filterByType', 'filterByFile', 'autoRefresh'])) {
            $this->loadDebugData();
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterByType = '';
        $this->filterByFile = '';
        $this->loadDebugData();
    }

    public function clearAllDebugData()
    {
        Text::truncate();
        Json::truncate();
        Number::truncate();
        Debug::truncate();

        $this->loadDebugData();
        $this->loadFiles();

        session()->flash('message', 'All debug data cleared successfully!');
    }

    public function refreshData()
    {
        $this->loadDebugData();
        $this->loadFiles();
        session()->flash('message', 'Debug data refreshed!');
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function selectDebug($debugId)
    {
        $this->selectedDebugId = $this->selectedDebugId === $debugId ? null : $debugId;
    }

    #[On('refresh-debug-data')]
    public function handleRefresh()
    {
        $this->loadDebugData();
        $this->loadFiles();
    }

    public function render()
    {
        return view('livewire.debug-viewer')
            ->layout('layouts.debug');
    }
}
