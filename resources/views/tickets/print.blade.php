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
        }
        
        .header h2 {
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .ticket-info {
            text-align: left;
            margin: 10px 0;
        }
        
        .ticket-info .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            border-bottom: 1px dotted #ccc;
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
            margin: 10px 0;
            padding: 5px;
            border: 2px solid #000;
        }
        
        .barcode {
            text-align: center;
            margin: 15px 0;
            font-family: 'Libre Barcode 128', monospace;
            font-size: 24px;
            letter-spacing: 1px;
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
        }
        
        .terms {
            font-size: 9px;
            text-align: center;
            margin-top: 10px;
            color: #666;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                background: white;
                -webkit-print-color-adjust: exact;
            }
        }
        
        .print-btn {
            background: #007bff;
            color: white;
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
            <h1>PARKING SYSTEM</h1>
            <h2>PARKING TICKET</h2>
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
        
        <!-- Barcode -->
        <div class="barcode">
            {{ $ticket->barcode }}
        </div>
        
        <!-- QR Code Placeholder -->
        <div class="qr-placeholder">
            QR CODE<br>
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
    
    <script>
        function markAsPrinted() {
            fetch('{{ route('tickets.mark-printed', $ticket) }}', {
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
        
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>
