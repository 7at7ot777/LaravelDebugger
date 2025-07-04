<?php

namespace App\Livewire;

use App\Debugger;
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
        $this->debugs = Debugger::loadDebugData($this->search, $this->filterByType, $this->filterByFile);;
    }

    public function loadFiles()
    {
        $this->files = Debugger::loadFiles();
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
        Debugger::clearAllDebugData();

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
