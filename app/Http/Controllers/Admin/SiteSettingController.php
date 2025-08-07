<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteSettingController extends Controller
{
    /**
     * Display a listing of site settings.
     */
    public function index()
    {
        $settings = SiteSetting::orderBy('group')->orderBy('key')->get();
        $groups = $settings->groupBy('group');

        return view('admin.settings.index', compact('settings', 'groups'));
    }

    /**
     * Show the form for creating a new setting.
     */
    public function create()
    {
        $groups = ['general', 'ticket', 'email', 'location', 'system'];
        $types = ['string', 'integer', 'boolean', 'json', 'text'];

        return view('admin.settings.create', compact('groups', 'types'));
    }

    /**
     * Store a newly created setting.
     */
    public function store(Request $request)
    {
        $rules = [
            'key' => 'required|string|unique:site_settings,key',
            'value' => 'nullable|string',
            'type' => 'required|in:string,integer,boolean,json,text',
            'group' => 'required|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ];

        // If creating brand_logo, allow file upload
        if ($request->input('key') === 'brand_logo') {
            $rules['logo_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048';
        }

        $validated = $request->validate($rules);

        // Handle logo upload
        if ($request->input('key') === 'brand_logo' && $request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $filename = 'logo_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename, 'public');
            $validated['value'] = $path;
        }

        SiteSetting::create($validated);

        // Clear cache
        \Cache::forget("site_setting_{$request->key}");

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Setting created successfully.');
    }

    /**
     * Show the form for editing the specified setting.
     */
    public function edit(SiteSetting $setting)
    {
        $groups = ['general', 'ticket', 'email', 'location', 'system'];
        $types = ['string', 'integer', 'boolean', 'json', 'text'];

        return view('admin.settings.edit', compact('setting', 'groups', 'types'));
    }

    /**
     * Update the specified setting.
     */
    public function update(Request $request, SiteSetting $setting)
    {
        $rules = [
            'key' => 'required|string|unique:site_settings,key,' . $setting->id,
            'value' => 'nullable|string',
            'type' => 'required|in:string,integer,boolean,json,text',
            'group' => 'required|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ];

        // If editing brand_logo, allow file upload
        if ($setting->key === 'brand_logo') {
            $rules['logo_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048';
        }

        $validated = $request->validate($rules);

        // Handle logo upload
        if ($setting->key === 'brand_logo' && $request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $filename = 'logo_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename, 'public');

            // Delete old logo if exists and is different
            if ($setting->value && $setting->value !== $path && \Storage::disk('public')->exists($setting->value)) {
                \Storage::disk('public')->delete($setting->value);
            }

            $validated['value'] = $path;
        }

        $setting->update($validated);

        // Clear cache
        \Cache::forget("site_setting_{$setting->key}");

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Setting updated successfully.');
    }

    /**
     * Remove the specified setting.
     */
    public function destroy(SiteSetting $setting)
    {
        $setting->delete();

        // Clear cache
        Cache::forget("site_setting_{$setting->key}");

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Setting deleted successfully.');
    }

    /**
     * Bulk update settings.
     */
    public function bulkUpdate(Request $request)
    {
        $settings = $request->input('settings', []);

        foreach ($settings as $key => $value) {
            $setting = SiteSetting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
                Cache::forget("site_setting_{$key}");
            }
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Settings updated successfully.');
    }

    /**
     * Clear all settings cache.
     */
    public function clearCache()
    {
        SiteSetting::clearCache();

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Settings cache cleared successfully.');
    }
}
