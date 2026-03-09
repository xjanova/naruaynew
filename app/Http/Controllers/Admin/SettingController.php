<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ModuleStatus;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()
            ->groupBy('group');
        $modules = ModuleStatus::orderBy('module_name')->get();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'modules' => $modules,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);

        foreach ($request->settings as $item) {
            SettingService::set($item['key'], $item['value']);
        }

        Cache::flush();

        return back()->with('success', 'Settings updated.');
    }

    public function toggleModule(ModuleStatus $module)
    {
        $module->update(['is_active' => !$module->is_active]);
        Cache::flush();
        return back()->with('success', "Module {$module->module_name} " . ($module->is_active ? 'enabled' : 'disabled') . ".");
    }
}
