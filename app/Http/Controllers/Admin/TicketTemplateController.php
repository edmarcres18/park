<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketTemplateController extends Controller
{
    /**
     * Display a listing of ticket templates.
     */
    public function index()
    {
        $templates = TicketTemplate::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.ticket-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        $variables = [
            'ticket_number' => 'Ticket Number',
            'plate_number' => 'Plate Number',
            'time_in' => 'Time In',
            'time_out' => 'Time Out',
            'duration' => 'Duration',
            'rate' => 'Rate',
            'parking_slot' => 'Parking Slot',
            'location' => 'Location',
            'attendant' => 'Attendant',
            'qr_code' => 'QR Code',
            'barcode' => 'Barcode',
        ];

        return view('admin.ticket-templates.create', compact('variables'));
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'html_template' => 'required|string',
            'css_styles' => 'nullable|string',
            'template_variables' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        // If this is set as default, remove default from others
        if ($request->is_default) {
            TicketTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        TicketTemplate::create($data);

        return redirect()->route('admin.ticket-templates.index')
                        ->with('success', 'Template created successfully.');
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(TicketTemplate $template)
    {
        $variables = [
            'ticket_number' => 'Ticket Number',
            'plate_number' => 'Plate Number',
            'time_in' => 'Time In',
            'time_out' => 'Time Out',
            'duration' => 'Duration',
            'rate' => 'Rate',
            'parking_slot' => 'Parking Slot',
            'location' => 'Location',
            'attendant' => 'Attendant',
            'qr_code' => 'QR Code',
            'barcode' => 'Barcode',
        ];

        return view('admin.ticket-templates.edit', compact('template', 'variables'));
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, TicketTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'html_template' => 'required|string',
            'css_styles' => 'nullable|string',
            'template_variables' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();

        // If this is set as default, remove default from others
        if ($request->is_default) {
            TicketTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        $template->update($data);

        return redirect()->route('admin.ticket-templates.index')
                        ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template.
     */
    public function destroy(TicketTemplate $template)
    {
        if ($template->is_default) {
            return redirect()->route('admin.ticket-templates.index')
                            ->with('error', 'Cannot delete default template.');
        }

        $template->delete();

        return redirect()->route('admin.ticket-templates.index')
                        ->with('success', 'Template deleted successfully.');
    }

    /**
     * Set template as default.
     */
    public function setDefault(TicketTemplate $template)
    {
        // Remove default from other templates
        TicketTemplate::where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        $template->update(['is_default' => true]);

        return redirect()->route('admin.ticket-templates.index')
                        ->with('success', 'Template set as default successfully.');
    }

    /**
     * Preview template.
     */
    public function preview(TicketTemplate $template)
    {
        // Create a sample ticket for preview
        $sampleTicket = new \App\Models\Ticket([
            'ticket_number' => 'SAMPLE001',
            'plate_number' => 'ABC123',
            'time_in' => now(),
            'rate' => 50.00,
            'parking_slot' => 'A1',
            'location' => 'Sample Location, City, State',
            'attendant' => 'John Doe',
        ]);

        $data = $sampleTicket->template_data;
        $rendered = $template->render($data);

        return view('admin.ticket-templates.preview', compact('template', 'rendered', 'sampleTicket'));
    }

    /**
     * Duplicate template.
     */
    public function duplicate(TicketTemplate $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->slug = Str::slug($newTemplate->name);
        $newTemplate->is_default = false;
        $newTemplate->is_active = false;
        $newTemplate->save();

        return redirect()->route('admin.ticket-templates.index')
                        ->with('success', 'Template duplicated successfully.');
    }
}
