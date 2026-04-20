<?php

use Livewire\Component;
use App\Models\Asset;
use App\Models\Inventory;
use App\Models\Device;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $filterInventory = '';
    public $filterDevice = '';
    public $perPage = 15;

    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $expandedAssetId = null;

    public function toggleExpand($id)
    {
        if ($this->expandedAssetId === $id) {
            $this->expandedAssetId = null;
        } else {
            $this->expandedAssetId = $id;
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'filterInventory', 'filterDevice', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function inventories() {
        return Inventory::orderBy('name')->get();
    }

    #[Computed]
    public function devices() {
        $query = Device::orderBy('name');
        if ($this->filterInventory) {
            $query->where('inventory_id', $this->filterInventory);
        }
        return $query->get();
    }

    #[Computed]
    public function assets() {
        $query = Asset::with(['device', 'inventory']);

        // Filtering
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterInventory) {
            $query->where('inventory_id', $this->filterInventory);
        }

        if ($this->filterDevice) {
            $query->where('device_id', $this->filterDevice);
        }

        // Sorting
        if (in_array($this->sortField, ['name', 'serial_number', 'created_at'])) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            $query->latest(); // fallback
        }

        $perPageCount = $this->perPage === 'all' ? ($query->count() > 0 ? $query->count() : 1) : $this->perPage;
        return $query->paginate($perPageCount);
    }
};
?>

<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Hardware and Software Inventory Tracker</h2>
        <a href="/inventory/create" wire:navigate class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800">
            + New Asset
        </a>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg mb-6 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 border-b border-gray-200 dark:border-zinc-700 pb-4 mb-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name or Serial Number..." class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm py-3 px-4 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-500 dark:text-zinc-300 text-base">
            </div>

            <!-- Inventory Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Inventory List</label>
                <select wire:model.live="filterInventory" class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm py-3 px-4 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-500 dark:text-zinc-300 text-base">
                    <option value="">All Inventories</option>
                    @foreach($this->inventories as $inventory)
                        <option value="{{ $inventory->id }}">{{ $inventory->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Device Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Device</label>
                <select wire:model.live="filterDevice" class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm py-3 px-4 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-500 dark:text-zinc-300 text-base">
                    <option value="">All Device Types</option>
                    @foreach($this->devices as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Per Page Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Per Page</label>
                <select wire:model.live="perPage" class="mt-1 block w-full rounded-md border-2 border-gray-400 shadow-sm py-3 px-4 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-500 dark:text-zinc-300 text-base">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-zinc-800 shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead class="bg-gray-50 dark:bg-zinc-900/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800" wire:click="sortBy('name')">
                            Asset Item
                            @if($sortField === 'name')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Inventory
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Device
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            OS
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/4 min-w-[250px]">
                            Specifications
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Condition
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-zinc-700">
                    @forelse($this->assets as $asset)
                        <tr wire:click="toggleExpand({{ $asset->id }})" wire:key="row-{{ $asset->id }}" class="cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $asset->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">SN: {{ $asset->serial_number ?: 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border border-transparent dark:border-blue-800/50">
                                    {{ $asset->inventory->name ?? 'None' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800 dark:bg-zinc-700 dark:text-zinc-200">
                                    {{ $asset->device->name ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                @php $osData = is_string($asset->operating_system) ? json_decode($asset->operating_system, true) : $asset->operating_system; @endphp
                                @if($osData && is_array($osData) && count($osData) > 0)
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $osData['name'] ?? '' }}</span>
                                @else
                                    <em class="text-gray-400 dark:text-gray-500">None</em>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @php $specData = is_string($asset->specifications) ? json_decode($asset->specifications, true) : $asset->specifications; @endphp
                                @if($specData && is_array($specData) && count($specData) > 0)
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                                        @foreach($specData as $key => $value)
                                            @if(!empty($value))
                                                <div><span class="font-medium text-gray-700 dark:text-gray-300">{{ $key }}:</span> <span class="text-gray-600 dark:text-gray-400">{{ is_array($value) ? implode(',', $value) : $value }}</span></div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <em class="text-gray-400 dark:text-gray-500">No extra details.</em>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-zinc-900/30 dark:text-zinc-300 border border-transparent dark:border-zinc-800/50 capitalize">
                                    {{ $asset->condition }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('inventory.edit', $asset->id) }}" wire:click.stop wire:navigate class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                                    Edit
                                </a>
                            </td>
                        </tr>
                        @if($expandedAssetId === $asset->id)
                        <tr wire:key="expanded-{{ $asset->id }}" class="bg-gray-50 dark:bg-zinc-800/50 border-b border-gray-200 dark:border-zinc-700">
                            <td colspan="7" class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2 border-b border-gray-200 dark:border-zinc-600 pb-1">Hardware Info</h4>
                                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            <li>
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                                                @php
                                                    $statusClasses = match($asset->status) {
                                                        'available' => 'text-green-600 dark:text-green-400',
                                                        'assigned' => 'text-blue-600 dark:text-blue-400',
                                                        'maintenance' => 'text-yellow-600 dark:text-yellow-400',
                                                        'retired' => 'text-red-600 dark:text-red-400',
                                                        default => 'text-gray-600 dark:text-gray-400',
                                                    };
                                                @endphp
                                                <span class="capitalize font-semibold {{ $statusClasses }}">{{ $asset->status }}</span>
                                            </li>
                                            <li><span class="font-medium text-gray-700 dark:text-gray-300">Brand / Model:</span> {{ $asset->brand_model ?: 'N/A' }}</li>
                                            <li><span class="font-medium text-gray-700 dark:text-gray-300">Model No:</span> {{ $asset->model_no ?: 'N/A' }}</li>
                                            <li><span class="font-medium text-gray-700 dark:text-gray-300">Purchase Date:</span> {{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('d/M/Y') : 'N/A' }}</li>
                                            @php
                                                $accessories = is_string($asset->accessory) ? json_decode($asset->accessory, true) : $asset->accessory;
                                            @endphp
                                            <li><span class="font-medium text-gray-700 dark:text-gray-300">Accessories:</span> {{ is_array($accessories) ? implode(', ', $accessories) : ($accessories ?: 'None') }}</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2 border-b border-gray-200 dark:border-zinc-600 pb-1">Assignment</h4>
                                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            <li><span class="font-medium text-gray-700 dark:text-gray-300">Assigned User (System):</span> {{ $asset->assignedUser->name ?? 'Unassigned' }}</li>
                                            <li><span class="font-medium text-gray-700 dark:text-gray-300">Assigned To (External):</span> {{ $asset->assigned_to ?: 'Unassigned' }}</li>
                                            <li><span class="font-medium text-gray-700 dark:text-gray-300">Department / Area:</span> {{ $asset->department ?: 'N/A' }}</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2 border-b border-gray-200 dark:border-zinc-600 pb-1">Operating System</h4>
                                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            @php $osExpandedData = is_string($asset->operating_system) ? json_decode($asset->operating_system, true) : $asset->operating_system; @endphp
                                            @if($osExpandedData && is_array($osExpandedData) && count($osExpandedData) > 0)
                                                @foreach($osExpandedData as $key => $value)
                                                    <li><span class="font-medium text-gray-700 dark:text-gray-300 capitalize">{{ str_replace('_', ' ', $key) }}:</span> {{ is_array($value) ? implode(',', $value) : $value }}</li>
                                                @endforeach
                                            @else
                                                <li>No OS information</li>
                                            @endif
                                        </ul>
                                    </div>
                                    @php $otherDataExpanded = is_string($asset->other_data) ? json_decode($asset->other_data, true) : $asset->other_data; @endphp
                                    @if($otherDataExpanded && is_array($otherDataExpanded) && count($otherDataExpanded) > 0)
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2 border-b border-gray-200 dark:border-zinc-600 pb-1">Other Data</h4>
                                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            @foreach($otherDataExpanded as $key => $value)
                                                <li><span class="font-medium text-gray-700 dark:text-gray-300 capitalize">{{ str_replace('_', ' ', $key) }}:</span> {{ is_array($value) ? implode(',', $value) : $value }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <svg class="h-10 w-10 text-gray-400 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span class="text-lg font-medium">No assets found matching your criteria.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 bg-gray-50 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700">
            {{ $this->assets->links() }}
        </div>
    </div>
</div>
