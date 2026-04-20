<?php

use Livewire\Component;
use App\Models\Device;
use App\Models\Inventory;
use App\Models\Asset;
use App\Models\User;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Validation\ValidationException;

new class extends Component
{
    public $inventories;
    public $devices;
    public $categories;
    public $departments;
    public $users;

    public $assetId = null;

    public $inventory_id = '';
    public $device_id = '';
    public $category_id = null;
    public $name = '';
    public $serial_number = '';
    public $status = 'available';
    public $condition = 'new';

    public $assigned_user_id = null;
    public $assigned_to = '';
    public $existing_assignees = [];
    public $department = '';
    public $brand_model = '';
    public $model_no = '';
    public $accessory = [];
    public $operating_system = ['name' => '', 'version' => '', 'status' => '', 'partial_key' => ''];
    public $other_data = [];
    public $purchase_date = '';

    public $specifications = [];

    public $show_operating_system = false;
    public $show_other_data = false;

    public $newOtherDataFields = [
        ['name' => '', 'type' => 'text', 'options' => '']
    ];

    public $isNewDevice = false;
    public $newDeviceName = '';
    public $newDeviceCategories = '';
    public $newDeviceIncludeOS = false;
    public $newDeviceIncludeOtherData = false;
    public $newDeviceFields = [
        ['name' => '', 'type' => 'text', 'options' => '']
    ];

    public function mount($assetId = null) {
        $this->assetId = $assetId;
        $this->inventories = Inventory::all();
        $this->devices = collect();
        $this->categories = collect();
        $this->users = User::all();
        $this->departments = Department::all();
        $assetAssignees = Asset::whereNotNull('assigned_to')->where('assigned_to', '!=', '')->distinct()->pluck('assigned_to')->toArray();
        $externalUsers = \App\Models\ExternalUser::pluck('name')->toArray();
        $allAssignees = array_unique(array_merge($assetAssignees, $externalUsers));
        sort($allAssignees, SORT_STRING | SORT_FLAG_CASE);
        $this->existing_assignees = $allAssignees;

        if ($this->assetId) {
            $asset = Asset::findOrFail($this->assetId);
            $this->inventory_id = $asset->inventory_id;
            $this->devices = Device::where('inventory_id', $this->inventory_id)->get();

            $this->device_id = $asset->device_id;
$this->categories = Category::where('device_id', $this->device_id)->get();
            $this->category_id = $asset->category_id;
$this->categories = Category::where('device_id', $this->device_id)->get();
            $this->category_id = $asset->category_id;
            $this->name = $asset->name;
            $this->serial_number = $asset->serial_number;
            $this->status = $asset->status ?? 'available';
                        $this->condition = $asset->condition ?? 'new';
            $this->assigned_user_id = $asset->assigned_user_id;
            $this->assigned_to = $asset->assigned_to;
            $this->department = $asset->department;
            $this->brand_model = $asset->brand_model;
            $this->model_no = $asset->model_no;
            $this->accessory = is_string($asset->accessory) ? json_decode($asset->accessory, true) : ($asset->accessory ?? []);
            $os = is_array($asset->operating_system) ? $asset->operating_system : (is_string($asset->operating_system) ? json_decode($asset->operating_system, true) : []);
            $this->operating_system = [
                'name' => $os['name'] ?? ($os[0] ?? ''),
                'version' => $os['version'] ?? '',
                'status' => $os['status'] ?? '',
                'partial_key' => $os['partial_key'] ?? ''
            ];
            $this->purchase_date = $asset->purchase_date;
            $this->specifications = is_string($asset->specifications) ? json_decode($asset->specifications, true) : ($asset->specifications ?? []);
            $this->other_data = is_string($asset->other_data) ? json_decode($asset->other_data, true) : ($asset->other_data ?? []);

            $device = Device::find($this->device_id);
            if ($device) {
                $this->show_operating_system = $device->include_operating_system ?? false;
                $this->show_other_data = !empty($device->other_data_fields);
                $this->newOtherDataFields = $device->other_data_fields ?? [];

                // Initialize default empty values for other_data to bind properly
                foreach ($this->newOtherDataFields as $field) {
                    if (!isset($this->other_data[$field['name']])) {
                        $this->other_data[$field['name']] = '';
                    }
                }
            } else {
                $this->show_operating_system = !empty($asset->operating_system);
                $this->show_other_data = !empty($asset->other_data);
            }
        }
    }

    public function updatedInventoryId($inventoryId) {
        $this->devices = Device::where('inventory_id', $inventoryId)->get();
        $this->device_id = '';
        $this->category_id = null;
        $this->categories = collect();
        $this->category_id = null;
        $this->categories = collect();
        $this->specifications = [];
        $this->isNewDevice = false;
        $this->newDeviceCategories = '';
    }

    public function updatedDeviceId($catId) {
        if ($catId === 'new') {
            $this->isNewDevice = true;
            $this->specifications = [];
        } else {
            $this->isNewDevice = false;
            $device = Device::find($catId);
$this->categories = Category::where('device_id', $catId)->get();
            $fields = $device->specification_fields ?? [];
            foreach ($fields as $field) {
                $this->specifications[$field['name']] = '';
            }

            // Set OS visibility based on device configuration
            $this->show_operating_system = $device->include_operating_system ?? false;

            // Restore other_data fields from device configuration
            $this->show_other_data = !empty($device->other_data_fields);
            if ($device->other_data_fields ?? false) {
                $this->newOtherDataFields = $device->other_data_fields;
            }
        }
    }


    public function saveDeviceTypeOnly() {

        $this->validate([
            'newDeviceName' => 'required|string|max:255',
        ]);

        $device = Device::create([
            'inventory_id' => $this->inventory_id,
            'name' => $this->newDeviceName,
            'specification_fields' => $this->newDeviceFields,
            'include_operating_system' => $this->newDeviceIncludeOS,
            'other_data_fields' => $this->newDeviceIncludeOtherData ? $this->newOtherDataFields : [],
        ]);

        if (trim($this->newDeviceCategories) !== '') {
            $catNames = array_filter(array_map('trim', explode(',', $this->newDeviceCategories)));
            foreach ($catNames as $cName) {
                if (!empty($cName)) {
                    Category::create(['device_id' => $device->id, 'name' => $cName]);
                }
            }
        }

        $this->devices = Device::where('inventory_id', $this->inventory_id)->get();
        $this->device_id = $device->id;
        $this->categories = Category::where('device_id', $this->device_id)->get();
        $this->category_id = null;
        $this->isNewDevice = false;
        $this->newDeviceName = '';
        $this->newDeviceCategories = '';
        $this->newDeviceIncludeOS = false;
        $this->newDeviceIncludeOtherData = false;
        $this->newDeviceFields = [['name' => '', 'type' => 'text', 'options' => '']];
        $this->newOtherDataFields = [['name' => '', 'type' => 'text', 'options' => '']];

        session()->flash('status', 'Device type successfully created!');
    }

    public function addSpecificationFieldRow() {
        $this->newDeviceFields[] = ['name' => '', 'type' => 'text', 'options' => ''];
    }

    public function removeSpecificationFieldRow($index) {
        unset($this->newDeviceFields[$index]);
        $this->newDeviceFields = array_values($this->newDeviceFields);
    }

    public function addOtherDataFieldRow() {
        $this->newOtherDataFields[] = ['name' => '', 'type' => 'text', 'options' => ''];
    }

    public function removeOtherDataFieldRow($index) {
        unset($this->newOtherDataFields[$index]);
        $this->newOtherDataFields = array_values($this->newOtherDataFields);
    }

    public function submit() {
        $this->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|unique:assets,serial_number,' . $this->assetId,
        ]);

        if ($this->isNewDevice) {

        $this->validate([
            'newDeviceName' => 'required|string|max:255',
        ]);

        $device = Device::create([
            'inventory_id' => $this->inventory_id,
            'name' => $this->newDeviceName,
            'specification_fields' => $this->newDeviceFields,
            'include_operating_system' => $this->newDeviceIncludeOS,
            'other_data_fields' => $this->newDeviceIncludeOtherData ? $this->newOtherDataFields : [],
        ]);

        if (trim($this->newDeviceCategories) !== '') {
            $catNames = array_filter(array_map('trim', explode(',', $this->newDeviceCategories)));
            foreach ($catNames as $cName) {
                if (!empty($cName)) {
                    Category::create(['device_id' => $device->id, 'name' => $cName]);
                }
            }
        }
            $this->device_id = $device->id;
            $this->show_operating_system = $this->newDeviceIncludeOS;
            $this->show_other_data = $this->newDeviceIncludeOtherData;

        } else {
            $this->validate([
                'device_id' => 'required|exists:devices,id',
                'category_id' => 'nullable|exists:categories,id',
            ]);
        }

        // Validate Specifications based on schema
        $device = Device::find($this->device_id);
        $schema = $device->specification_fields ?? [];
        $hasOS = $this->isNewDevice ? $this->newDeviceIncludeOS : ($device->include_operating_system ?? false);
        $hasOtherData = $this->isNewDevice ? $this->newDeviceIncludeOtherData : !empty($device->other_data_fields);

        foreach ($schema as $field) {
            if ($field['type'] === 'number' && !empty($this->specifications[$field['name']])) {
                if (!is_numeric($this->specifications[$field['name']])) {
                    throw ValidationException::withMessages([
                        'specifications.'.$field['name'] => 'The '.$field['name'].' must be a number.'
                    ]);
                }
            }
        }

        Asset::updateOrCreate(
            ['id' => $this->assetId],
            [
                'inventory_id' => $this->inventory_id,
                'device_id' => $this->device_id,
                'category_id' => $this->category_id ?: null,
                'name' => $this->name,
                'serial_number' => $this->serial_number,
                'status' => $this->status,
                                'condition' => $this->condition,
                'assigned_user_id' => $this->assigned_user_id ?: null,
                'assigned_to' => $this->assigned_to ?: null,
                'department' => $this->department,
                'brand_model' => $this->brand_model,
                'model_no' => $this->model_no,
                'accessory' => !empty($this->accessory) ? $this->accessory : null,
                'operating_system' => $hasOS ? $this->operating_system : null,
                'other_data' => $hasOtherData ? $this->other_data : null,
                'specifications' => $this->specifications,
                'purchase_date' => $this->purchase_date ?: null,
            ]
        );

                session()->flash('status', $this->assetId ? 'Asset successfully updated!' : 'Asset successfully saved!');

        return $this->redirect('/inventory');
    }

    public function deleteAsset() {
        if ($this->assetId) {
            Asset::findOrFail($this->assetId)->delete();
            session()->flash('status', 'Asset successfully deleted!');
            return $this->redirect('/inventory');
        }
    }
};
?>

<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-100">{{ $this->assetId ? 'Edit Inventory Asset' : 'Add New Inventory Asset' }}</h2>

        @if (session()->has('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-400 px-4 py-3 rounded relative mb-4">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit.prevent="submit" class="space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Inventory</label>
                    <select wire:model.live="inventory_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        <option value="">-- Select Inventory List --</option>
                        @foreach($inventories as $inventory)
                            <option value="{{ $inventory->id }}">{{ $inventory->name }}</option>
                        @endforeach
                    </select>
                    @error('inventory_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Device</label>
                    <select wire:model.live="device_id" required @if(!$inventory_id) disabled @endif class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 dark:disabled:bg-zinc-700 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        <option value="">-- Select Device --</option>
                        <option value="new" class="font-bold text-indigo-600 dark:text-indigo-400">+ Create New Device</option>
                        @foreach($devices as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('device_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Category (optional)</label>
                    <select wire:model.live="category_id" @if(!$device_id || $device_id == 'new') disabled @endif class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 dark:disabled:bg-zinc-700 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        <option value="">-- No Category / Select Category --</option>
                        @if($categories && count($categories) > 0)
                            @foreach($categories as $catOpt)
                                <option value="{{ $catOpt->id }}">{{ $catOpt->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
</div>

            <!-- NEW CATEGORY DEFINITION -->
            @if($isNewDevice)
                <div class="p-5 border-2 border-indigo-200 bg-indigo-50 dark:border-indigo-900 dark:bg-indigo-900/20 rounded-md">
                    <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-300 mb-4">Define New Device Schema</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Device Name</label>
                        <input type="text" wire:model="newDeviceName" placeholder="e.g. Server, Projector" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        @error('newDeviceName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>


                    <div class="mb-4 mt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Categories (Optional, comma-separated)</label>
                        <input type="text" wire:model="newDeviceCategories" placeholder="e.g. Standard, Premium" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                    </div>

                    <h4 class="font-medium text-gray-700 dark:text-zinc-300 mb-2">Specification Fields for this Device</h4>
                    @foreach($newDeviceFields as $index => $field)
                        <div class="flex gap-3 mb-3 items-start">
                            <div class="flex-1">
                                <input type="text" wire:model="newDeviceFields.{{ $index }}.name" placeholder="Field Name (e.g. Screen Size)" required class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                            </div>
                            <div class="w-32">
                                <select wire:model.live="newDeviceFields.{{ $index }}.type" class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                                    <option value="text">Text</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="select">Dropdown</option>
                                </select>
                            </div>
                            @if($newDeviceFields[$index]['type'] === 'select')
                                <div class="flex-1">
                                    <input type="text" wire:model="newDeviceFields.{{ $index }}.options" placeholder="Comma separated (e.g. Red, Blue)" required class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                                </div>
                            @endif
                            <button type="button" wire:click="removeSpecificationFieldRow({{ $index }})" class="mt-1 text-red-500 hover:text-red-700 dark:hover:text-red-400 font-bold">&times;</button>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addSpecificationFieldRow" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 font-medium hover:text-indigo-800 dark:hover:text-indigo-300">+ Add another field</button>

                    <!-- Enable/Disable Optional Fields for Device -->
                    <div class="mt-6 pt-6 border-t border-indigo-200 dark:border-indigo-800 space-y-3">
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="newDeviceIncludeOS" id="newDevice_show_os" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700">
                            <label for="newDevice_show_os" class="ml-2 block text-sm text-gray-700 dark:text-zinc-300 cursor-pointer">Include Operating System Information</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" wire:model.live="newDeviceIncludeOtherData" id="newDevice_show_other" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700">
                            <label for="newDevice_show_other" class="ml-2 block text-sm text-gray-700 dark:text-zinc-300 cursor-pointer">Include Other Data</label>
                        </div>
                    </div>

                    <!-- Other Data Field Schema Definition -->
                    @if($newDeviceIncludeOtherData)
                    <div class="mt-6 p-4 border border-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-800 rounded-md">
                        <h5 class="font-medium text-gray-700 dark:text-zinc-300 mb-3">Define Other Data Fields</h5>
                        @foreach($newOtherDataFields as $index => $field)
                            <div class="flex gap-3 mb-3 items-start">
                                <div class="flex-1">
                                    <input type="text" wire:model="newOtherDataFields.{{ $index }}.name" placeholder="Field Name (e.g. Warranty End Date)" required class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                                </div>
                                <div class="w-32">
                                    <select wire:model.live="newOtherDataFields.{{ $index }}.type" class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                        <option value="select">Dropdown</option>
                                    </select>
                                </div>
                                @if($newOtherDataFields[$index]['type'] === 'select')
                                    <div class="flex-1">
                                        <input type="text" wire:model="newOtherDataFields.{{ $index }}.options" placeholder="Comma separated (e.g. Yes, No)" required class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                                    </div>
                                @endif
                                <button type="button" wire:click="removeOtherDataFieldRow({{ $index }})" class="mt-1 text-red-500 hover:text-red-700 dark:hover:text-red-400 font-bold">&times;</button>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addOtherDataFieldRow" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 font-medium hover:text-indigo-800 dark:hover:text-indigo-300">+ Add another field</button>
                    </div>
                    @endif

                    <div class="mt-4 pt-4 border-t border-indigo-200 dark:border-indigo-800">"
                        <button type="button" wire:click="saveDeviceTypeOnly" class="bg-indigo-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-indigo-700">
                            Save Device Type Only
                        </button>
                        <span class="text-xs text-gray-500 ml-2">Saves the device schema without creating an asset.</span>
                    </div>
                </div>
            @endif

            <hr class="dark:border-zinc-700">

            <!-- ASSET FIXED DETAILS -->
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b dark:border-zinc-700 pb-2">Base Asset Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Asset Name</label>
                    <input type="text" wire:model="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Serial Number / Asset Tag</label>
                    <input type="text" wire:model="serial_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                    @error('serial_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- User (This is the user who has been assigned the device -- dropdown) -->
                                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Assigned User (System)</label>
                    <select wire:model="assigned_user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        <option value="">-- Unassigned --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Assigned To (Non-User / External)</label>
                    <input type="text" list="assignees-list" wire:model="assigned_to" placeholder="e.g. John Doe (Contractor)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                    <datalist id="assignees-list">
                        @foreach($existing_assignees as $assignee)
                            <option value="{{ $assignee }}">
                        @endforeach
                    </datalist>
                    <span class="text-xs text-gray-500">Select an existing person or type a new name to add them to the list.</span>
                    @error('assigned_to') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Department/Area (The department the device is -- dropdown) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Department / Area</label>
                    <select wire:model="department" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        <option value="">-- Select Department/Area --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Brand/Model -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Brand / Model</label>
                    <input type="text" wire:model="brand_model" placeholder="e.g. Dell XPS 15" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                    @error('brand_model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Model no (text). -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Model No.</label>
                    <input type="text" wire:model="model_no" placeholder="e.g. XPS9500" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                    @error('model_no') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Accessories (multiple selection) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Accessories</label>
                    <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        @foreach(['Charger', 'Memory Card', 'Bag/Case', 'Adapter/Hub', 'Stylus', 'Other (please specify)'] as $acc)
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="accessory" value="{{ $acc }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-zinc-300">{{ $acc }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('accessory') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Operating system(Dictionary) - shown if device supports it -->
                @if(!$isNewDevice && $device_id)
                    @php
                        $selectedCat = $devices->firstWhere('id', $device_id);
                        $hasOS = $selectedCat ? ($selectedCat->include_operating_system ?? false) : false;
                    @endphp
                    @if($hasOS)
                <div class="md:col-span-2 p-4 border border-gray-200 rounded-md bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900/50">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Operating System</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-zinc-300">Name / Build</label>
                            <input type="text" wire:model="operating_system.name" placeholder="e.g. Windows 11 Pro 64-bit (10.0.22621, Build 22621)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-zinc-300">Version</label>
                            <input type="text" wire:model="operating_system.version" placeholder="e.g. 22H2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-zinc-300">Status</label>
                            <input type="text" wire:model="operating_system.status" placeholder="e.g. Licensed" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-zinc-300">Partial Key</label>
                            <input type="text" wire:model="operating_system.partial_key" placeholder="e.g. X2FC2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        </div>
                    </div>
                </div>
                @endif
                @endif

                <!-- Other Data - shown if device has other data fields -->
                @if(!$isNewDevice && $device_id)
                    @php
                        $selectedCat = $devices->firstWhere('id', $device_id);
                        $otherDataFields = $selectedCat ? ($selectedCat->other_data_fields ?? []) : [];
                    @endphp
                    @if(!empty($otherDataFields))
                <div class="md:col-span-2 p-5 border border-gray-200 bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900/50 rounded-md">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Other Data</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($otherDataFields as $field)
                            @if(!empty($field['name']))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">{{ $field['name'] }}</label>

                                    @if($field['type'] === 'select')
                                        <select wire:model="other_data.{{ $field['name'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">-- Choose Option --</option>
                                            @foreach(explode(',', $field['options'] ?? '') as $opt)
                                                <option value="{{ trim($opt) }}">{{ trim($opt) }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="{{ $field['type'] }}" wire:model="other_data.{{ $field['name'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @endif

                <!-- purchase date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Purchase Date</label>
                    <input type="date" wire:model="purchase_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                    @error('purchase_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Condition</label>
                    <select wire:model="condition" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        <option value="new">New</option>
                        <option value="good">Good</option>
                        <option value="dead">Dead</option>
                        <option value="outdated">Outdated</option>
                        <option value="old">Old</option>
                        <option value="out_of_order">Out of order</option>
                        <option value="faulty">Faulty</option>
                        <option value="damaged">Damaged</option>
                        <option value="defective">Defective</option>
                    </select>
                    @error('condition') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Status</label>
                    <select wire:model="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300">
                        <option value="available">Available</option>
                        <option value="assigned">Assigned</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="retired">Retired</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- DYNAMIC FIELDS LOOP -->
            @if(!$isNewDevice && $device_id)
                @php
                    $selectedCat = $devices->firstWhere('id', $device_id);
                    $specificationFields = $selectedCat ? ($selectedCat->specification_fields ?? []) : [];
                @endphp

                @if(count($specificationFields) > 0)
                    <div class="p-5 border border-gray-200 bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900/50 rounded-md">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b dark:border-zinc-700 pb-2 mb-4">Specifications: {{ $selectedCat->name }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($specificationFields as $field)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">{{ $field['name'] }}</label>

                                    @if($field['type'] === 'select')
                                        <select wire:model="specifications.{{ $field['name'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">-- Choose Option --</option>
                                            @foreach(explode(',', $field['options'] ?? '') as $opt)
                                                <option value="{{ trim($opt) }}">{{ trim($opt) }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="{{ $field['type'] }}" wire:model="specifications.{{ $field['name'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-300 focus:ring-indigo-500 focus:border-indigo-500">
                                    @endif
                                    @error('specifications.'.$field['name']) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

                        <div class="flex justify-between items-center w-full">
                @if($this->assetId)
                    <button type="button" wire:click="deleteAsset" wire:confirm="Are you sure you want to delete this asset?" class="bg-red-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete Asset
                    </button>
                @else
                    <div></div>
                @endif
                <button type="submit" class="bg-indigo-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800">
                    Save Asset
                </button>
            </div>
        </form>
    </div>
</div>
