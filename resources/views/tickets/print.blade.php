<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Ticket - {{ $ticket->ticket_number }}</title>
    <style>
        /* POS Printer styles - 58mm or 80mm thermal printer */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background: white;
            color: black;
        }

        .ticket-container {
            width: 100%;
            text-align: center;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px dashed #000;
            padding-bottom: 8px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
            letter-spacing: 1px;
        }

        .header h2 {
            font-size: 13px;
            margin-bottom: 2px;
            letter-spacing: 1px;
        }

        .ticket-info {
            text-align: left;
            margin: 10px 0;
        }

        .ticket-info .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            border-bottom: 1px dotted #888;
            padding-bottom: 2px;
        }

        .ticket-info .label {
            font-weight: bold;
            width: 45%;
        }

        .ticket-info .value {
            text-align: right;
            width: 50%;
        }

        .ticket-number {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0 5px 0;
            padding: 5px 0;
            border: 2px solid #000;
            background: #fff;
        }

        .barcode {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 15px 0 0 0;
            font-family: 'Courier New', monospace;
            font-size: 24px;
            letter-spacing: 1px;
            text-align: center;
        }

        .qr-placeholder {
            text-align: center;
            margin: 10px 0;
            font-size: 10px;
            border: 1px solid #ccc;
            padding: 20px;
            background: #f9f9f9;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 2px dashed #000;
            padding-top: 8px;
            font-size: 10px;
            color: #222;
        }

        .terms {
            font-size: 9px;
            text-align: center;
            margin-top: 10px;
            color: #444;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white;
                -webkit-print-color-adjust: exact;
            }
            .print-btn {
                display: none !important;
            }
        }

        .print-btn {
            background: #222;
            color: #fff;
            border: none;
            padding: 10px 20px;
            margin: 20px 0;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <!-- Header -->
        <div class="header">
            @if(!empty($ticketConfig['logo']))
                <img src="{{ $ticketConfig['logo'] }}" alt="Logo" style="max-height: 60px; margin-bottom: 4px;">
            @else
                <div style="height: 60px; margin-bottom: 4px;"></div>
            @endif
            <h1>{{ config('app.name') }}</h1>
            <h2>PARKING TICKET</h2>
            @if(!empty($ticketConfig['location_address']))
                <div style="font-size: 11px; margin-bottom: 2px;">{{ $ticketConfig['location_address'] }}</div>
            @endif
            <div>{{ now()->format('Y-m-d H:i:s') }}</div>
        </div>

        <!-- Ticket Number -->
        <div class="ticket-number">
            {{ $ticket->ticket_number }}
        </div>

        <!-- Ticket Information -->
        <div class="ticket-info">
            <div class="row">
                <span class="label">Plate Number:</span>
                <span class="value">{{ $ticket->plate_number }}</span>
            </div>

            <div class="row">
                <span class="label">Time In:</span>
                <span class="value">{{ $ticket->time_in->format('M j, Y g:i A') }}</span>
            </div>

            @if($ticket->time_out)
            <div class="row">
                <span class="label">Time Out:</span>
                <span class="value">{{ $ticket->time_out->format('M j, Y g:i A') }}</span>
            </div>

            <div class="row">
                <span class="label">Duration:</span>
                <span class="value">{{ $ticket->duration }}</span>
            </div>

            <div class="row">
                <span class="label">Total Fee:</span>
                <span class="value">₱{{ number_format($ticket->total_fee, 2) }}</span>
            </div>
            @endif

            <div class="row">
                <span class="label">Rate:</span>
                <span class="value">{{ $ticket->formatted_rate }}</span>
            </div>

            @if($ticket->parking_slot)
            <div class="row">
                <span class="label">Parking Slot:</span>
                <span class="value">{{ $ticket->parking_slot }}</span>
            </div>
            @endif

            @if($ticket->parkingSession && $ticket->parkingSession->creator)
            <div class="row">
                <span class="label">Attendant:</span>
                <span class="value">{{ $ticket->parkingSession->creator->name }}</span>
            </div>
            @endif
        </div>

        <!-- QR Code -->
        <div class="barcode">
            <div id="qrcode"></div>
        </div>

        <!-- QR Code Caption -->
        <div class="qr-caption" style="text-align:center; font-size:10px; margin-bottom:10px;">
            <small>{{ $ticket->ticket_number }}</small>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>KEEP THIS TICKET</div>
            <div>Present when exiting</div>
        </div>

        <!-- Terms -->
        <div class="terms">
            Lost tickets subject to maximum daily rate.<br>
            Ticket valid for current session only.
        </div>

        <!-- Print Button (hidden when printing) -->
        <div class="no-print">
            <button class="print-btn" onclick="window.print(); markAsPrinted();">Print Ticket</button>
            <button class="print-btn" onclick="window.close();">Close</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        function markAsPrinted() {
            @if(auth()->user()->hasRole('admin'))
                const route = '{{ route('admin.tickets.mark-printed', $ticket) }}';
            @else
                // For non-admins, do not attempt to mark as printed
                return;
            @endif

            fetch(route, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Ticket marked as printed');
                }
            })
            .catch(error => {
                console.error('Error marking ticket as printed:', error);
            });
        }

        // Generate QR code for the ticket number
        document.addEventListener('DOMContentLoaded', function() {
            var qrcode = new QRCode(document.getElementById('qrcode'), {
                text: "{{ $ticket->ticket_number }}",
                width: 80,
                height: 80,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        });
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>
