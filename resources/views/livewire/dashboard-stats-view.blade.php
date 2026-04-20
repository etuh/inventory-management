<div class="space-y-6">
    <!-- Summary KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Assets -->
        <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center text-indigo-600 dark:text-indigo-400">
                <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="text-sm font-semibold uppercase tracking-wide">Total Assets</h3>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalAssets }}</p>
        </div>

        <!-- Available Assets -->
        <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center text-green-600 dark:text-green-400">
                <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <h3 class="text-sm font-semibold uppercase tracking-wide">Available (Ready)</h3>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $availableAssets }}</p>
        </div>

        <!-- Assigned -->
        <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center text-blue-600 dark:text-blue-400">
                <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <h3 class="text-sm font-semibold uppercase tracking-wide">Assigned (In Use)</h3>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $assignedAssets }}</p>
        </div>

        <!-- Maintenance -->
        <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center text-yellow-600 dark:text-yellow-400">
                <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h3 class="text-sm font-semibold uppercase tracking-wide">In Maintenance (Repair/Lost)</h3>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $maintenanceAssets }}</p>
        </div>
    </div>

    <!-- Analytics Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Assigned Assets per Department -->
        <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 border-b border-gray-200 dark:border-zinc-700 pb-2">Assigned Devices by Department</h3>
            <div class="space-y-4">
                @forelse($assetsByDepartment as $dept)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $dept->department }}</span>
                            <span class="text-gray-500 font-bold dark:text-gray-400">{{ $dept->total }} Items</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-zinc-700 rounded-full h-2.5">
                            @php
                                $percentage = ($dept->total / $assignedAssets) * 100;
                            @endphp
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No department data found.</p>
                @endforelse
            </div>
        </div>

        <!-- System Architecture (Device Types Breakdown) -->
        <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 border-b border-gray-200 dark:border-zinc-700 pb-2">Assets by Device Category</h3>
            <div class="space-y-4">
               @forelse($assetsByDeviceType as $deviceStats)
                   <div>
                       <div class="flex justify-between text-sm mb-1">
                           <span class="font-medium text-gray-700 dark:text-gray-300">{{ $deviceStats->device_type }}</span>
                           <span class="text-gray-500 font-bold dark:text-gray-400">{{ $deviceStats->total }}</span>
                       </div>
                   </div>
               @empty
                   <p class="text-gray-500 dark:text-gray-400 text-sm">No device data found.</p>
               @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Insights: Recently Added & Attention Needed -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Attention Needed -->
        <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6 border border-red-200 dark:border-red-900">
            <h3 class="text-lg font-bold text-red-600 dark:text-red-400 flex items-center mb-4 border-b border-red-200 dark:border-red-900/50 pb-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Needs Attention
            </h3>
            <ul class="divide-y divide-gray-200 dark:divide-zinc-700">
                @forelse($needsAttention as $asset)
                    <li class="py-3 flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $asset->name }} <span class="text-xs text-gray-500">({{ $asset->serial_number }})</span></p>
                            <p class="text-xs capitalize text-gray-500 dark:text-gray-400">Status: <span class="font-bold {{ $asset->status == 'retired' ? 'text-red-500' : 'text-yellow-500' }}">{{ $asset->status }}</span> | Condition: {{ $asset->condition }}</p>
                        </div>
                        <a href="{{ route('inventory.edit', $asset->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                    </li>
                @empty
                    <li class="py-3 text-sm text-gray-500">All primary assets look clean.</li>
                @endforelse
            </ul>
        </div>

        <!-- Recently Added -->
        <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center mb-4 border-b border-gray-200 dark:border-zinc-700 pb-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Recently Added
            </h3>
            <ul class="divide-y divide-gray-200 dark:divide-zinc-700">
                @forelse($recentlyAdded as $asset)
                    <li class="py-3 flex justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $asset->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Type: <span class="font-semibold">{{ $asset->device->name ?? 'Unknown' }}</span> | Added: {{ $asset->created_at->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('inventory.edit', $asset->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">Edit</a>
                    </li>
                @empty
                    <li class="py-3 text-sm text-gray-500">No assets have been added recently.</li>
                @endforelse
            </ul>
            <div class="mt-4 text-center">
                 <a href="/inventory/create" wire:navigate class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Add New Asset &rarr;</a>
            </div>
        </div>
    </div>
</div>
