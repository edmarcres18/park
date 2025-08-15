<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Ticket {{ $ticket->ticket_number }}</title>
	<style>
		/* 58mm thermal width ~ 203px at 203dpi; we target screen-friendly 300px */
		:root { --paper-width: 300px; }
		body { background:#f4f6f8; margin:0; padding:20px; font-family: Arial, Helvetica, sans-serif; }
		.ticket { width: var(--paper-width); margin: 0 auto; background: #fff; color:#000; padding: 10px 12px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
		.header { text-align:center; }
		.header img { max-width: 48px; max-height: 48px; display:block; margin: 0 auto 6px; }
		.header .brand { font-weight: 800; font-size: 14px; }
		.header .addr { font-size: 11px; color:#444; }
		.section { margin: 8px 0; border-top: 1px dashed #999; padding-top: 8px; }
		.row { display:flex; justify-content:space-between; font-size: 12px; }
		.center { text-align:center; }
		.big { font-size: 16px; font-weight: 800; }
		.barcode { margin-top: 8px; text-align:center; font-family: monospace; font-size: 14px; }
		.footer { margin-top: 8px; font-size: 11px; text-align:center; }
		hr { border: none; border-top: 1px dashed #999; margin: 8px 0; }
		@media print { body { background:#fff; padding:0 } .ticket { box-shadow:none; } }
	</style>
</head>
<body>
	<div class="ticket">
        <div class="header">
            @php $logo = $brandLogo ?? ($config['logo'] ?? null); @endphp
            @if($logo)
                <img src="{{ $logo }}" alt="Logo">
            @endif
            <div class="brand">{{ $appName }}@if(!empty($locationName)) — {{ $locationName }}@endif</div>
			@if(($config['location_address'] ?? null))
				<div class="addr">{{ $config['location_address'] }}</div>
			@endif
		</div>
		<div class="section center">
            <div class="big">{{ $ticket->time_out ? 'PAID PARKING' : 'CHECK FOR PARKING' }}</div>
            <div>{{ $ticket->time_in?->format('F d, Y h:i:s A') }}</div>
		</div>
		<div class="section">
            <div class="row"><span>Started:</span><span>{{ $ticket->time_in?->format('F d, Y h:i:s A') }}</span></div>
            <div class="row"><span>Ended:</span><span>{{ $ticket->time_out?->format('F d, Y h:i:s A') ?? '—' }}</span></div>
			<div class="row"><span>Plate:</span><span>{{ $ticket->plate_number }}</span></div>
			<div class="row"><span>Slot:</span><span>{{ $ticket->parking_slot ?? '—' }}</span></div>
		</div>
		<div class="section">
			<div class="row"><strong>Paid :</strong><strong>₱{{ number_format($ticket->parkingSession?->amount_paid ?? $ticket->rate ?? 0, 2) }}</strong></div>
		</div>
		<div class="section center">
			<canvas id="qr-canvas" width="120" height="120" style="margin:0 auto; display:block;"></canvas>
		</div>
		<div class="barcode">{{ $ticket->ticket_number }}</div>
		<div class="footer">THANK YOU AND LUCKY ROAD!</div>
	</div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js" integrity="sha512-+z+8m0H3N6g8o4uC1cQ0Zk0L6u6+fT3Zf0yYb3m3p1t2Y0Ewq5yffmGvO5k3i7UqCkQyJ1U3sRZQkqOuyk7Q3A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script>
		(function(){
			try {
				var qr = new QRious({
					element: document.getElementById('qr-canvas'),
					value: @json($ticket->ticket_number),
					size: 120,
					level: 'M'
				});
			} catch (e) { /* noop */ }
		})();
	</script>
</body>
</html>


