<?php

use Livewire\Component;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public function render()
    {
        // High-Level KPIs
        $totalAssets = Asset::count();
        $availableAssets = Asset::where('status', 'available')->count();
        $assignedAssets = Asset::where('status', 'assigned')->count();
        $maintenanceAssets = Asset::where('status', 'maintenance')->count();

        // Assets by Department
        $assetsByDepartment = Asset::select('department', DB::raw('count(*) as total'))
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->where('status', 'assigned')
            ->groupBy('department')
            ->orderBy('total', 'desc')
            ->get();

        // Assets by Device Type (Assuming devices table relation exists natively on the model)
        $assetsByDeviceType = Asset::select('devices.name as device_type', DB::raw('count(assets.id) as total'))
            ->join('devices', 'assets.device_id', '=', 'devices.id')
            ->groupBy('devices.name')
            ->orderBy('total', 'desc')
            ->get();

        // Needs Attention
        $needsAttention = Asset::whereIn('status', ['maintenance', 'retired'])
            ->orWhere('condition', 'poor')
            ->take(5)
            ->get();

        // Recently Added
        $recentlyAdded = Asset::latest()
            ->take(5)
            ->with(['device'])
            ->get();

        return view('livewire.dashboard-stats-view', [
            'totalAssets' => $totalAssets,
            'availableAssets' => $availableAssets,
            'assignedAssets' => $assignedAssets,
            'maintenanceAssets' => $maintenanceAssets,
            'assetsByDepartment' => $assetsByDepartment,
            'assetsByDeviceType' => $assetsByDeviceType,
            'needsAttention' => $needsAttention,
            'recentlyAdded' => $recentlyAdded,
        ]);
    }
};
?>
</div>
