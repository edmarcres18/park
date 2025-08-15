<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Services\TicketTemplateConfigService;
use App\Models\SiteSetting;

class TicketPrintController extends Controller
{
    public function __construct(protected TicketTemplateConfigService $configService)
    {
        // Web routes will handle their own middleware; API handled separately
    }

    /**
     * Render Blade view for 58mm ticket print.
     */
    public function web(Ticket $ticket)
    {
        $ticket->load(['parkingSession.creator', 'parkingSession.parkingRate']);
        // Prefer site settings like admin layout
        $appName = SiteSetting::getValue('app_name', config('app.name', 'Parking System'));
        $brandLogo = SiteSetting::getValue('brand_logo');
        $locationName = SiteSetting::getValue('location_name');
        if ($brandLogo) {
            if (!str_starts_with($brandLogo, 'http')) {
                $brandLogo = asset('storage/' . ltrim($brandLogo, '/'));
            }
        }
        $config = $this->configService->getConfig();
        return view('tickets.print_58mm', [
            'ticket' => $ticket,
            'appName' => $appName,
            'config' => $config,
            'brandLogo' => $brandLogo,
            'locationName' => $locationName,
        ]);
    }

    /**
     * API: return normalized print data for Flutter.
     */
    public function api(Ticket $ticket)
    {
        // Ownership guard for attendants. Admins can access via role-based route groups.
        if (auth()->check() && auth()->user()->hasRole('attendant')) {
            if (!$ticket->parkingSession || $ticket->parkingSession->created_by !== auth()->id()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        $ticket->load(['parkingSession.parkingRate']);
        $appName = SiteSetting::getValue('app_name', config('app.name', 'Parking System'));
        $brandLogo = SiteSetting::getValue('brand_logo');
        $locationName = SiteSetting::getValue('location_name');
        if ($brandLogo) {
            if (!str_starts_with($brandLogo, 'http')) {
                $brandLogo = asset('storage/' . ltrim($brandLogo, '/'));
            }
        }
        $cfg = $this->configService->getConfig();

        $session = $ticket->parkingSession;
        $isPaid = (bool) ($session && $session->end_time);
        $paidAmount = $session?->amount_paid ?? $ticket->rate ?? 0;

        return response()->json([
            'app_name' => $appName,
            'brand_logo' => $brandLogo,
            'logo' => $brandLogo ?? ($cfg['logo'] ?? null),
            'location_name' => $locationName,
            'address' => $cfg['location_address'] ?? null,
            'ticket' => [
                'id' => $ticket->id,
                'number' => $ticket->ticket_number,
                'plate' => $ticket->plate_number,
                'time_in' => optional($ticket->time_in)->format('F d, Y h:i:s A'),
                'time_out' => optional($ticket->time_out)->format('F d, Y h:i:s A'),
                'paid' => $isPaid,
                'amount' => (float) $paidAmount,
                'formatted_amount' => '₱' . number_format((float) $paidAmount, 2),
                'barcode' => $ticket->barcode,
            ],
        ]);
    }
}


