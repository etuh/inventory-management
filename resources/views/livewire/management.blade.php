<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Inventory;
use App\Models\Device;
use App\Models\Category;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $activeTab = 'users'; // 'users', 'inventories', 'devices', 'departments'

    public $assignees = [];
    public $systems_users = [];
    public $editingAssignee = null;
    public $editingAssigneeNewName = '';
    public $newAssigneeName = '';

    // Inventory management
    public $inventories = [];
    public $newInventoryName = '';
    public $editingInventoryId = null;
    public $editingInventoryName = '';

    public $editingDepartmentId = null;
    public $editingDepartmentName = '';

    // Department management
    public $departments = [];
    public $newDepartmentName = '';

    // Device management
    public $devices = [];
    public $newDeviceName = '';
    public $editingDeviceId = null;
    public $editingDeviceName = '';

    public $editingSystemUserId = null;
    public $editingSystemUserName = '';
    public $editingSystemUserEmail = '';
    public $newDeviceInventoryId = '';
    public $newDeviceCategories = '';
    public $newDeviceIncludeOS = false;
    public $newDeviceIncludeOtherData = false;
    public $newDeviceFields = [
        ['name' => '', 'type' => 'text', 'options' => '']
    ];
    public $newOtherDataFields = [
        ['name' => '', 'type' => 'text', 'options' => '']
    ];

    public function mount() {
        $this->loadData();
    }

    public function loadData() {
        $this->systems_users = User::all();
        // Get unique assigned_to from assets and explicitly created external users
        $assetAssignees = Asset::whereNotNull('assigned_to')
            ->where('assigned_to', '!=', '')
            ->distinct()
            ->pluck('assigned_to')->toArray();
        $externalUsers = \App\Models\ExternalUser::pluck('name')->toArray();
        $allAssignees = array_unique(array_merge($assetAssignees, $externalUsers));
        sort($allAssignees, SORT_STRING | SORT_FLAG_CASE);
        $this->assignees = $allAssignees;

        $this->inventories = Inventory::withCount(['assets', 'devices'])->get();
        $this->departments = \App\Models\Department::orderBy('name')->get();
        $this->devices = Device::with('inventory')->withCount(['assets', 'categories'])->get();
    }

    public function switchTab($tab) {
        $this->activeTab = $tab;
        $this->loadData();
    }

    public function createInventory() {
        $this->validate(['newInventoryName' => 'required|string|max:255']);
        Inventory::create(['name' => $this->newInventoryName]);
        $this->newInventoryName = '';
        $this->loadData();
        session()->flash('inventory_status', 'Inventory list successfully created!');
    }

    public function deleteInventory($id) {
        Inventory::findOrFail($id)->delete();
        $this->loadData();
        session()->flash('inventory_status', 'Inventory list successfully deleted!');
    }

    public function editInventory($id, $name) {
        $this->editingInventoryId = $id;
        $this->editingInventoryName = $name;
    }

    public function cancelEditInventory() {
        $this->editingInventoryId = null;
        $this->editingInventoryName = '';
    }

    public function updateInventory() {
        $this->validate(['editingInventoryName' => 'required|string|max:255']);
        if ($this->editingInventoryId) {
            Inventory::findOrFail($this->editingInventoryId)->update(['name' => $this->editingInventoryName]);
            $this->cancelEditInventory();
            $this->loadData();
            session()->flash('inventory_status', 'Inventory successfully updated!');
        }
    }

    public function editAssignee($name) {
        $this->editingAssignee = $name;
        $this->editingAssigneeNewName = $name;
    }

    public function cancelEditAssignee() {
        $this->editingAssignee = null;
        $this->editingAssigneeNewName = '';
    }

    public function createAssignee() {
        $this->validate(['newAssigneeName' => 'required|string|max:255']);
        \App\Models\ExternalUser::firstOrCreate(['name' => $this->newAssigneeName]);
        $this->newAssigneeName = '';
        $this->loadData();
        session()->flash('user_status', 'External / Non-User successfully created!');
    }

    public function updateAssignee() {
        $this->validate(['editingAssigneeNewName' => 'required|string|max:255']);
        if ($this->editingAssignee && $this->editingAssigneeNewName !== $this->editingAssignee) {
            Asset::where('assigned_to', $this->editingAssignee)
                 ->update(['assigned_to' => $this->editingAssigneeNewName]);
            \App\Models\ExternalUser::where('name', $this->editingAssignee)
                 ->update(['name' => $this->editingAssigneeNewName]);
        }
        $this->cancelEditAssignee();
        $this->loadData();
        session()->flash('user_status', 'Non-user successfully updated!');
    }

    public function deleteAssignee($name) {
        if ($name) {
            Asset::where('assigned_to', $name)
                 ->update(['assigned_to' => null]);
            \App\Models\ExternalUser::where('name', $name)->delete();
            $this->loadData();
            session()->flash('user_status', 'Non-user successfully removed!');
        }
    }

    public function createDepartment() {
        $this->validate(['newDepartmentName' => 'required|string|max:255']);
        \App\Models\Department::create(['name' => $this->newDepartmentName]);
        $this->newDepartmentName = '';
        $this->loadData();
        session()->flash('department_status', 'Department successfully created!');
    }

    public function deleteDepartment($id) {
        \App\Models\Department::findOrFail($id)->delete();
        $this->loadData();
        session()->flash('department_status', 'Department successfully deleted!');
    }

    public function editDepartment($id, $name) {
        $this->editingDepartmentId = $id;
        $this->editingDepartmentName = $name;
    }

    public function cancelEditDepartment() {
        $this->editingDepartmentId = null;
        $this->editingDepartmentName = '';
    }

    public function updateDepartment() {
        $this->validate(['editingDepartmentName' => 'required|string|max:255']);
        if ($this->editingDepartmentId) {
            \App\Models\Department::findOrFail($this->editingDepartmentId)->update(['name' => $this->editingDepartmentName]);
            $this->cancelEditDepartment();
            $this->loadData();
            session()->flash('department_status', 'Department successfully updated!');
        }
    }

    public function saveDevice() {
        $this->validate([
            'newDeviceName' => 'required|string|max:255',
            'newDeviceInventoryId' => 'required|exists:inventories,id'
        ]);

        $data = [
            'name' => $this->newDeviceName,
            'inventory_id' => $this->newDeviceInventoryId,
            'specification_fields' => $this->newDeviceFields,
            'include_operating_system' => $this->newDeviceIncludeOS,
            'other_data_fields' => $this->newDeviceIncludeOtherData ? $this->newOtherDataFields : [],
        ];

        $isUpdating = !empty($this->editingDeviceId);

        if ($isUpdating) {
            $device = Device::findOrFail($this->editingDeviceId);
            $device->update($data);

            // Re-create categories for simplicity
            Category::where('device_id', $device->id)->delete();
        } else {
            $device = Device::create($data);
        }

        if (trim($this->newDeviceCategories) !== '') {
            $catNames = array_filter(array_map('trim', explode(',', $this->newDeviceCategories)));
            foreach ($catNames as $cName) {
                if (!empty($cName)) {
                    Category::create(['device_id' => $device->id, 'name' => $cName]);
                }
            }
        }

        $this->cancelEditDevice();
        $this->loadData();
        session()->flash('device_status', $isUpdating ? 'Device type successfully updated!' : 'Device type successfully created!');
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

    public function deleteDevice($id) {
        Device::findOrFail($id)->delete();
        $this->loadData();
        session()->flash('device_status', 'Device type successfully deleted!');
    }

    public function editDevice($id) {
        $device = Device::with('categories')->findOrFail($id);
        $this->editingDeviceId = $device->id;
        $this->newDeviceName = $device->name;
        $this->newDeviceInventoryId = $device->inventory_id;

        $this->newDeviceCategories = $device->categories->pluck('name')->implode(', ');
        $this->newDeviceIncludeOS = $device->include_operating_system ?? false;

        $specFields = is_string($device->specification_fields) ? json_decode($device->specification_fields, true) : ($device->specification_fields ?? []);
        $this->newDeviceFields = count($specFields) > 0 ? $specFields : [['name' => '', 'type' => 'text', 'options' => '']];

        $otherDataFields = is_string($device->other_data_fields) ? json_decode($device->other_data_fields, true) : ($device->other_data_fields ?? []);
        $this->newDeviceIncludeOtherData = !empty($otherDataFields);
        $this->newOtherDataFields = count($otherDataFields) > 0 ? $otherDataFields : [['name' => '', 'type' => 'text', 'options' => '']];

        // Scroll to top to see form (optional since we don't have JS here but helps concept)
    }

    public function cancelEditDevice() {
        $this->editingDeviceId = null;
        $this->newDeviceName = '';
        $this->newDeviceInventoryId = '';
        $this->newDeviceCategories = '';
        $this->newDeviceIncludeOS = false;
        $this->newDeviceIncludeOtherData = false;
        $this->newDeviceFields = [['name' => '', 'type' => 'text', 'options' => '']];
        $this->newOtherDataFields = [['name' => '', 'type' => 'text', 'options' => '']];
    }



    public function deleteSystemUser($id) {
        $user = User::findOrFail($id);
        if ($user->id !== auth()->id()) {
            $user->delete();
            $this->loadData();
            session()->flash('user_status', 'System user successfully deleted!');
        } else {
            session()->flash('user_status', 'You cannot delete yourself!');
        }
    }

    public function editSystemUser($id, $name, $email) {
        $this->editingSystemUserId = $id;
        $this->editingSystemUserName = $name;
        $this->editingSystemUserEmail = $email;
    }

    public function cancelEditSystemUser() {
        $this->editingSystemUserId = null;
        $this->editingSystemUserName = '';
        $this->editingSystemUserEmail = '';
    }

    public function updateSystemUser() {
        $this->validate([
            'editingSystemUserName' => 'required|string|max:255',
            'editingSystemUserEmail' => 'required|email|max:255|unique:users,email,' . $this->editingSystemUserId
        ]);
        if ($this->editingSystemUserId) {
            User::findOrFail($this->editingSystemUserId)->update([
                'name' => $this->editingSystemUserName,
                'email' => $this->editingSystemUserEmail
            ]);
            $this->cancelEditSystemUser();
            $this->loadData();
            session()->flash('user_status', 'System user successfully updated!');
        }
    }
};
?>

<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="flex border-b border-gray-200 dark:border-zinc-700">
        <button wire:click="switchTab('users')" class="py-2 px-4 border-b-2 font-medium text-sm {{ $activeTab === 'users' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}">
            Manage Users
        </button>
        <button wire:click="switchTab('departments')" class="py-2 px-4 border-b-2 font-medium text-sm {{ $activeTab === 'departments' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}">
            Manage Departments
        </button>
        <button wire:click="switchTab('inventories')" class="py-2 px-4 border-b-2 font-medium text-sm {{ $activeTab === 'inventories' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}">
            Manage Inventories
        </button>
        <button wire:click="switchTab('devices')" class="py-2 px-4 border-b-2 font-medium text-sm {{ $activeTab === 'devices' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}">
            Manage Devices
        </button>
    </div>

    <div class="mt-8">
        @if ($activeTab === 'users')
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Users</h2>
                <p class="text-sm text-gray-500 mb-6">Manage internal system users and external/non-account assignees.</p>

                @if (session()->has('user_status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('user_status') }}
                    </div>
                @endif

                <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mt-6 mb-3 border-b pb-2">Assigned To (External / Non-Users)</h3>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">External users are listed below. Users are automatically added to this list when an asset is assigned to them, or you can add them explicitly here. Editing their names updates all their active assets, and removing them deletes their record and un-assigns their assets.</div>

                <form wire:submit.prevent="createAssignee" class="mb-6 flex gap-4 items-end bg-gray-50 dark:bg-zinc-900 p-4 rounded-md border border-gray-200 dark:border-zinc-700">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Add New External User</label>
                        <input type="text" wire:model="newAssigneeName" placeholder="e.g. John Smith (Contractor)" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-300">
                        @error('newAssigneeName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Add</button>
                </form>

                <div class="flex flex-wrap gap-2 mb-8 items-center">
                    @forelse($assignees as $assignee)
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                            @if($editingAssignee === $assignee)
                                <input type="text" wire:model="editingAssigneeNewName" wire:keydown.enter="updateAssignee" wire:keydown.escape="cancelEditAssignee" class="text-sm px-2 py-0.5 border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-6 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-300 text-black">
                                <button type="button" wire:click.prevent="updateAssignee" class="ml-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200 font-bold">&#10003;</button>
                                <button type="button" wire:click.prevent="cancelEditAssignee" class="ml-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-bold">&times;</button>
                            @else
                                {{ $assignee }}
                                <button type="button" wire:click="editAssignee('{{ addslashes($assignee) }}')" class="ml-2 text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 font-bold" title="Edit">
                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button type="button" wire:click="deleteAssignee('{{ addslashes($assignee) }}')" wire:confirm="Are you sure? This will set 'assigned to' to empty for all assets assigned to '{{ addslashes($assignee) }}'." class="ml-1 text-red-500 hover:text-red-700 font-bold" title="Remove from all">
                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No external users currently assigned to assets.</p>
                    @endforelse
                </div>

                <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mt-6 mb-3 border-b pb-2">System Users</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-700">
                            @foreach($systems_users as $user)
                                <tr wire:key="user-{{ $user->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if($editingSystemUserId === $user->id)
                                            <input type="text" wire:model="editingSystemUserName" wire:keydown.enter="updateSystemUser" wire:keydown.escape="cancelEditSystemUser" class="text-sm px-2 py-1 border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 w-full">
                                        @else
                                            {{ $user->name }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($editingSystemUserId === $user->id)
                                            <input type="email" wire:model="editingSystemUserEmail" wire:keydown.enter="updateSystemUser" wire:keydown.escape="cancelEditSystemUser" class="text-sm px-2 py-1 border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 w-full">
                                        @else
                                            {{ $user->email }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        @if($editingSystemUserId === $user->id)
                                            <button type="button" wire:key="btn-save-user-{{ $user->id }}" wire:click.prevent="updateSystemUser" class="text-indigo-600 hover:text-indigo-900 font-bold mr-2">Save</button>
                                            <button type="button" wire:key="btn-cancel-user-{{ $user->id }}" wire:click.prevent="cancelEditSystemUser" class="text-gray-500 hover:text-gray-700 font-bold mr-2">Cancel</button>
                                        @else
                                            <button type="button" wire:key="btn-edit-user-{{ $user->id }}" wire:click="editSystemUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            @if($user->id !== auth()->id())
                                                <button type="button" wire:key="btn-delete-user-{{ $user->id }}" wire:click="deleteSystemUser({{ $user->id }})" wire:confirm="Are you sure you want to delete user '{{ $user->name }}'? This cannot be undone." class="text-red-600 hover:text-red-900">Delete</button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($activeTab === 'departments')
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Departments</h2>
                </div>

                @if (session()->has('department_status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('department_status') }}
                    </div>
                @endif

                <form wire:submit.prevent="createDepartment" class="mb-8 flex gap-4 items-end bg-gray-50 dark:bg-zinc-900 p-4 rounded-md border border-gray-200 dark:border-zinc-700">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">New Department Name</label>
                        <input type="text" wire:model="newDepartmentName" placeholder="e.g. IT, HR" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-300">
                        @error('newDepartmentName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Create</button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department Name</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-700">
                            @foreach($departments as $dept)
                                <tr wire:key="dept-{{ $dept->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if($editingDepartmentId === $dept->id)
                                            <input type="text" wire:model="editingDepartmentName" wire:keydown.enter="updateDepartment" wire:keydown.escape="cancelEditDepartment" class="text-sm px-2 py-1 border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300">
                                        @else
                                            {{ $dept->name }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        @if($editingDepartmentId === $dept->id)
                                            <button type="button" wire:key="btn-save-dept-{{ $dept->id }}" wire:click.prevent="updateDepartment" class="text-indigo-600 hover:text-indigo-900 font-bold mr-2">Save</button>
                                            <button type="button" wire:key="btn-cancel-dept-{{ $dept->id }}" wire:click.prevent="cancelEditDepartment" class="text-gray-500 hover:text-gray-700 font-bold mr-2">Cancel</button>
                                        @else
                                            <button type="button" wire:key="btn-edit-dept-{{ $dept->id }}" wire:click="editDepartment({{ $dept->id }}, '{{ addslashes($dept->name) }}')" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            <button type="button" wire:key="btn-delete-dept-{{ $dept->id }}" wire:click="deleteDepartment({{ $dept->id }})" wire:confirm="Are you sure you want to delete '{{ $dept->name }}'? This cannot be undone." class="text-red-600 hover:text-red-900">Delete</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($departments) === 0)
                                <tr><td colspan="2" class="px-6 py-4 text-center text-gray-500">No departments found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($activeTab === 'inventories')
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Inventory Lists</h2>
                </div>

                @if (session()->has('inventory_status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('inventory_status') }}
                    </div>
                @endif

                <!-- Create new Inventory -->
                <form wire:submit.prevent="createInventory" class="mb-8 flex gap-4 items-end bg-gray-50 dark:bg-zinc-900 p-4 rounded-md border border-gray-200 dark:border-zinc-700">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">New Inventory Name</label>
                        <input type="text" wire:model="newInventoryName" placeholder="e.g. Main Office, Warehouse A" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-300">
                        @error('newInventoryName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Create</button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inventory Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assets Count</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-700">
                            @foreach($inventories as $inv)
                                <tr wire:key="inv-{{ $inv->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if($editingInventoryId === $inv->id)
                                            <input type="text" wire:model="editingInventoryName" wire:keydown.enter="updateInventory" wire:keydown.escape="cancelEditInventory" class="text-sm px-2 py-1 border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300">
                                        @else
                                            {{ $inv->name }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $inv->assets_count }} assets, {{ $inv->devices_count }} device types</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        @if($editingInventoryId === $inv->id)
                                            <button type="button" wire:key="btn-save-inv-{{ $inv->id }}" wire:click.prevent="updateInventory" class="text-indigo-600 hover:text-indigo-900 font-bold mr-2">Save</button>
                                            <button type="button" wire:key="btn-cancel-inv-{{ $inv->id }}" wire:click.prevent="cancelEditInventory" class="text-gray-500 hover:text-gray-700 font-bold mr-2">Cancel</button>
                                        @else
                                            <button type="button" wire:key="btn-edit-inv-{{ $inv->id }}" wire:click="editInventory({{ $inv->id }}, '{{ addslashes($inv->name) }}')" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            <button type="button" wire:key="btn-delete-inv-{{ $inv->id }}" wire:click="deleteInventory({{ $inv->id }})" wire:confirm="Are you sure you want to delete '{{ $inv->name }}'? WARNING: You will lose {{ $inv->devices_count }} device types and {{ $inv->assets_count }} assets associated with it! This cannot be undone." class="text-red-600 hover:text-red-900">Delete</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($inventories) === 0)
                                <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No inventory lists found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($activeTab === 'devices')
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Device Types</h2>

                @if (session()->has('device_status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('device_status') }}
                    </div>
                @endif

                <!-- Create new Device (Simplified for basic CRUD here. Full schema edit remains in asset form) -->
                <form wire:submit.prevent="saveDevice" class="mb-8 bg-gray-50 dark:bg-zinc-900 p-6 rounded-md border border-gray-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">{{ $editingDeviceId ? 'Edit Device Schema' : 'Create New Device Schema' }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">New Device Name</label>
                            <input type="text" wire:model="newDeviceName" placeholder="e.g. Laptop, Keyboard" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-300">
                            @error('newDeviceName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Target Inventory</label>
                            <select wire:model="newDeviceInventoryId" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-300">
                                <option value="">-- Select Inventory --</option>
                                @foreach($inventories as $inv)
                                    <option value="{{ $inv->id }}">{{ $inv->name }}</option>
                                @endforeach
                            </select>
                            @error('newDeviceInventoryId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">Categories (Optional, comma-separated)</label>
                        <input type="text" wire:model="newDeviceCategories" placeholder="e.g. Standard, Premium" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-300">
                    </div>

                    <h4 class="font-medium text-gray-700 dark:text-zinc-300 mb-2 mt-6">Specification Fields for this Device</h4>
                    <div class="bg-white dark:bg-zinc-800 p-4 border rounded-md dark:border-zinc-700">
                        @foreach($newDeviceFields as $index => $field)
                            <div class="flex gap-3 mb-3 items-start">
                                <div class="flex-1">
                                    <input type="text" wire:model="newDeviceFields.{{ $index }}.name" placeholder="Field Name (e.g. Screen Size)" required class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-600 dark:text-zinc-300">
                                </div>
                                <div class="w-32">
                                    <select wire:model.live="newDeviceFields.{{ $index }}.type" class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-600 dark:text-zinc-300">
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                        <option value="select">Dropdown</option>
                                    </select>
                                </div>
                                @if($newDeviceFields[$index]['type'] === 'select')
                                    <div class="flex-1">
                                        <input type="text" wire:model="newDeviceFields.{{ $index }}.options" placeholder="Comma separated (e.g. Red, Blue)" required class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-600 dark:text-zinc-300">
                                    </div>
                                @endif
                                <button type="button" wire:click="removeSpecificationFieldRow({{ $index }})" class="mt-1 text-red-500 hover:text-red-700 dark:hover:text-red-400 font-bold">&times;</button>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addSpecificationFieldRow" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 font-medium hover:text-indigo-800 dark:hover:text-indigo-300">+ Add another field</button>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-zinc-700 space-y-3 mb-4">
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="newDeviceIncludeOS" id="mgmt_newDevice_show_os" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600">
                            <label for="mgmt_newDevice_show_os" class="ml-2 block text-sm text-gray-700 dark:text-zinc-300 cursor-pointer">Include Operating System Information</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" wire:model.live="newDeviceIncludeOtherData" id="mgmt_newDevice_show_other" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:border-zinc-600">
                            <label for="mgmt_newDevice_show_other" class="ml-2 block text-sm text-gray-700 dark:text-zinc-300 cursor-pointer">Include Other Data Properties</label>
                        </div>
                    </div>

                    @if($newDeviceIncludeOtherData)
                    <div class="mb-6 bg-white dark:bg-zinc-800 p-4 border rounded-md dark:border-zinc-700">
                        <h5 class="font-medium text-gray-700 dark:text-zinc-300 mb-3">Define Other Data Fields</h5>
                        @foreach($newOtherDataFields as $index => $field)
                            <div class="flex gap-3 mb-3 items-start">
                                <div class="flex-1">
                                    <input type="text" wire:model="newOtherDataFields.{{ $index }}.name" placeholder="Field Name" required class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-600 dark:text-zinc-300">
                                </div>
                                <div class="w-32">
                                    <select wire:model.live="newOtherDataFields.{{ $index }}.type" class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-600 dark:text-zinc-300">
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                        <option value="select">Dropdown</option>
                                    </select>
                                </div>
                                @if($newOtherDataFields[$index]['type'] === 'select')
                                    <div class="flex-1">
                                        <input type="text" wire:model="newOtherDataFields.{{ $index }}.options" placeholder="Comma separated" required class="block w-full rounded-md border-gray-300 shadow-sm text-sm dark:bg-zinc-900 dark:border-zinc-600 dark:text-zinc-300">
                                    </div>
                                @endif
                                <button type="button" wire:click="removeOtherDataFieldRow({{ $index }})" class="mt-1 text-red-500 hover:text-red-700 dark:hover:text-red-400 font-bold">&times;</button>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addOtherDataFieldRow" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 font-medium hover:text-indigo-800 dark:hover:text-indigo-300">+ Add another field</button>
                    </div>
                    @endif

                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-zinc-700 flex justify-end gap-2">
                        @if($editingDeviceId)
                            <button type="button" wire:click="cancelEditDevice" class="bg-gray-300 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-400 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600">Cancel</button>
                        @endif
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">{{ $editingDeviceId ? 'Update Device Type' : 'Create Device Type' }}</button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Belongs to Inventory</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assets Count</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-700">
                            @foreach($devices as $dev)
                                <tr wire:key="dev-{{ $dev->id }}" class="{{ $editingDeviceId === $dev->id ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $dev->name }}
                                        @if($editingDeviceId === $dev->id)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-200">Editing</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ optional($dev->inventory)->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $dev->assets_count }} assets</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <button type="button" wire:key="btn-edit-dev-{{ $dev->id }}" wire:click="editDevice({{ $dev->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                        <button type="button" wire:key="btn-delete-dev-{{ $dev->id }}" wire:click="deleteDevice({{ $dev->id }})" wire:confirm="Are you sure you want to delete this device? This will also delete any categories and assets associated with it." class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($devices) === 0)
                                <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No device types found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
