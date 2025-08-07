<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TicketTemplateConfigService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketConfigController extends Controller
{
    protected $configService;

    public function __construct(TicketTemplateConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Show the config form for ticket template settings.
     */
    public function edit()
    {
        $config = $this->configService->getConfig();
        return view('admin.settings.ticket-config', compact('config'));
    }

    /**
     * Update the ticket template config (logo and location_address only).
     */
    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'location_address' => 'nullable|string|max:255',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $this->configService->setLogo($filename);
        }

        // Handle location address
        if ($request->filled('location_address')) {
            $this->configService->setLocationAddress($request->location_address);
        }

        return redirect()->route('admin.ticket-config.edit')->with('success', 'Ticket configuration updated successfully.');
    }
}
