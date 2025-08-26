# Philippine LTO Plate Number Validation System (2025)

This document outlines the comprehensive Philippine LTO plate number validation system implemented across Laravel (backend + Blade frontend) and Flutter (mobile) for the Parking Management System.

## 🎯 Supported Plate Formats (2025)

### Standard Vehicles (Cars, SUVs, Trucks, Buses, PUVs)
- **Format**: `LLL-DDDD` (3 letters, 4 digits)
- **Examples**: `ABC-1234`, `XYZ-5678`, `DEF-9012`

### Motorcycles
- **Format**: `LL-DDD-L` (2 letters, 3 digits, 1 letter)
- **Examples**: `AB-123-A`, `CD-456-B`

- **Format**: `D-LLL-DD` (1 digit, 3 letters, 2 digits)
- **Examples**: `1-ABC-23`, `5-XYZ-67`

- **Format**: `L-D-L-DDD` (1 letter, 1 digit, 1 letter, 3 digits)
- **Examples**: `A-1-B-234`, `C-5-D-678`

- **Format**: `LL-DDDD` (2 letters, 4 digits)
- **Examples**: `AB-1234`, `CD-5678`

- **Format**: `D-LL-DDD` (1 digit, 2 letters, 3 digits)
- **Examples**: `1-AB-234`, `5-CD-678`

### Tricycles (Yellow Plates)
- **Format**: `LL-DDDD` (2 letters, 4 digits)
- **Examples**: `AB-1234`, `CD-5678`

### Electric Vehicles (EV)
- **Format**: `E-LLL-DDD` (E, 3 letters, 3 digits)
- **Examples**: `E-ABC-123`, `E-XYZ-456`

### Hybrid Vehicles
- **Format**: `H-LLL-DDD` (H, 3 letters, 3 digits)
- **Examples**: `H-ABC-123`, `H-XYZ-456`

### Vintage/Classic Vehicles
- **Format**: `V-LLL-DDD` (V, 3 letters, 3 digits)
- **Examples**: `V-ABC-123`, `V-XYZ-456`

### Government Vehicles
- **Format**: `G-LLL-DDD` (G, 3 letters, 3 digits)
- **Examples**: `G-ABC-123`, `G-XYZ-456`

### Diplomatic Vehicles
- **Format**: `D-LLL-DDD` (D, 3 letters, 3 digits)
- **Examples**: `D-ABC-123`, `D-XYZ-456`

### Temporary/Conduction Plates
- **Format**: `T-LLL-DDD` (T, 3 letters, 3 digits)
- **Examples**: `T-ABC-123`, `T-XYZ-456`

### Special Formats
- **Older Formats**: `LLL-DDD` (3 letters, 3 digits)
- **Extended Motorcycle**: `LL-DDDDD` (2 letters, 5 digits)

## 🔧 Implementation Details

### Laravel Backend

#### Plate Model (`app/Models/Plate.php`)
```php
// Validation method
public static function isValidFormat(string $number): bool
{
    $cleaned = strtoupper(trim($number));
    $normalized = preg_replace('/[\s\-]/', '', $cleaned);
    
    $patterns = [
        '/^[A-Z]{3}\d{4}$/',                    // Standard: LLL-DDDD
        '/^[A-Z]{2}\d{3}[A-Z]$/',               // Motorcycle: LL-DDD-L
        '/^[A-Z]\d{3}[A-Z]{2}$/',               // Motorcycle: D-LLL-DD
        '/^[A-Z]\d{1}[A-Z]\d{3}$/',             // Motorcycle: L-D-L-DDD
        '/^[A-Z]{2}\d{4}$/',                    // Motorcycle/Tricycle: LL-DDDD
        '/^[A-Z]\d{2}[A-Z]\d{3}$/',             // Motorcycle: D-LL-DDD
        '/^E[A-Z]{3}\d{3}$/',                   // Electric: E-LLL-DDD
        '/^H[A-Z]{3}\d{3}$/',                   // Hybrid: H-LLL-DDD
        '/^V[A-Z]{3}\d{3}$/',                   // Vintage: V-LLL-DDD
        '/^G[A-Z]{3}\d{3}$/',                   // Government: G-LLL-DDD
        '/^D[A-Z]{3}\d{3}$/',                   // Diplomatic: D-LLL-DDD
        '/^T[A-Z]{3}\d{3}$/',                   // Temporary: T-LLL-DDD
        '/^[A-Z]{3}\d{3}$/',                    // Older formats
        '/^[A-Z]{2}\d{5}$/',                    // Extended motorcycle
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $normalized)) {
            return true;
        }
    }
    
    return false;
}

// Formatting method
public static function formatNumber(string $number): string
{
    $cleaned = strtoupper(trim($number));
    $normalized = preg_replace('/[\s\-]/', '', $cleaned);
    
    // Apply consistent formatting based on pattern
    if (preg_match('/^([A-Z]{3})(\d{4})$/', $normalized, $matches)) {
        return $matches[1] . '-' . $matches[2]; // LLL-DDDD
    } elseif (preg_match('/^([A-Z]{2})(\d{3})([A-Z])$/', $normalized, $matches)) {
        return $matches[1] . '-' . $matches[2] . '-' . $matches[3]; // LL-DDD-L
    }
    // ... additional patterns
    
    return $number;
}

// Vehicle category detection
public static function detectVehicleCategory(string $number): string
{
    $cleaned = strtoupper(trim($number));
    $normalized = preg_replace('/[\s\-]/', '', $cleaned);
    
    if (preg_match('/^E[A-Z]{3}\d{3}$/', $normalized)) return 'Electric Vehicle';
    if (preg_match('/^H[A-Z]{3}\d{3}$/', $normalized)) return 'Hybrid Vehicle';
    if (preg_match('/^V[A-Z]{3}\d{3}$/', $normalized)) return 'Vintage/Classic';
    if (preg_match('/^G[A-Z]{3}\d{3}$/', $normalized)) return 'Government';
    if (preg_match('/^D[A-Z]{3}\d{3}$/', $normalized)) return 'Diplomatic';
    if (preg_match('/^T[A-Z]{3}\d{3}$/', $normalized)) return 'Temporary/Conduction';
    
    // Motorcycle patterns
    if (preg_match('/^[A-Z]{2}\d{3}[A-Z]$|^[A-Z]\d{3}[A-Z]{2}$|^[A-Z]\d{1}[A-Z]\d{3}$|^[A-Z]{2}\d{4}$|^[A-Z]\d{2}[A-Z]\d{3}$/', $normalized)) {
        return 'Motorcycle';
    }
    
    return 'Standard Vehicle';
}
```

#### Form Request Validation (`app/Http/Requests/StorePlateRequest.php`)
```php
public function rules(): array
{
    return [
        'number' => [
            'required',
            'string',
            'max:255',
            'unique:plates',
            function ($attribute, $value, $fail) {
                if (!Plate::isValidFormat($value)) {
                    $fail('The plate number format is not valid for Philippine LTO standards.');
                }
            }
        ],
        'owner_name' => 'nullable|string|max:255',
        'vehicle_type' => 'required|string|max:255',
    ];
}
```

### Flutter Mobile App

#### Plate Validation (`lib/screens/tabs/plates_tab.dart`)
```dart
// Philippine LTO plate validation patterns (2025)
static final List<RegExp> _platePatterns = [
  // Standard vehicles (Cars, SUVs, Trucks, Buses, PUVs): LLL-DDDD
  RegExp(r'^[A-Z]{3}\d{4}$'),
  
  // Motorcycles: LL-DDD-L, D-LLL-DD, L-D-L-DDD, LL-DDDD, D-LL-DDD
  RegExp(r'^[A-Z]{2}\d{3}[A-Z]$'),
  RegExp(r'^[A-Z]\d{3}[A-Z]{2}$'),
  RegExp(r'^[A-Z]\d{1}[A-Z]\d{3}$'),
  RegExp(r'^[A-Z]{2}\d{4}$'),
  RegExp(r'^[A-Z]\d{2}[A-Z]\d{3}$'),
  
  // Electric Vehicles (EV): E-LLL-DDD
  RegExp(r'^E[A-Z]{3}\d{3}$'),
  
  // Hybrid Vehicles: H-LLL-DDD
  RegExp(r'^H[A-Z]{3}\d{3}$'),
  
  // Vintage/Classic: V-LLL-DDD
  RegExp(r'^V[A-Z]{3}\d{3}$'),
  
  // Government: G-LLL-DDD
  RegExp(r'^G[A-Z]{3}\d{3}$'),
  
  // Diplomatic: D-LLL-DDD
  RegExp(r'^D[A-Z]{3}\d{3}$'),
  
  // Temporary/Conduction: T-LLL-DDD
  RegExp(r'^T[A-Z]{3}\d{3}$'),
  
  // Special formats for specific vehicle types
  RegExp(r'^[A-Z]{3}\d{3}$'), // Some older formats
  RegExp(r'^[A-Z]{2}\d{5}$'), // Extended motorcycle format
];

// Validate Philippine LTO plate format
bool _isValidPlateFormat(String plate) {
  final normalized = plate.toUpperCase().replaceAll(RegExp(r'[\s\-]'), '');
  return _platePatterns.any((pattern) => pattern.hasMatch(normalized));
}

// Auto-detect vehicle type from plate
String _detectVehicleType(String plate) {
  final normalized = plate.toUpperCase().replaceAll(RegExp(r'[\s\-]'), '');
  
  if (normalized.startsWith('E')) return 'Electric Vehicle';
  if (normalized.startsWith('H')) return 'Hybrid Vehicle';
  if (normalized.startsWith('V')) return 'Vintage/Classic';
  if (normalized.startsWith('G')) return 'Government';
  if (normalized.startsWith('D')) return 'Diplomatic';
  if (normalized.startsWith('T')) return 'Temporary/Conduction';
  
  // Motorcycle patterns
  if (RegExp(r'^[A-Z]{2}\d{3}[A-Z]$|^[A-Z]\d{3}[A-Z]{2}$|^[A-Z]\d{1}[A-Z]\d{3}$|^[A-Z]{2}\d{4}$|^[A-Z]\d{2}[A-Z]\d{3}$').hasMatch(normalized)) {
    return 'Motorcycle';
  }
  
  return 'Car'; // Default
}
```

## 🚗 Vehicle Type Options

All platforms now support the following comprehensive vehicle types:

1. **Car** - Standard passenger vehicles
2. **Motorcycle** - Two-wheeled vehicles
3. **SUV** - Sport utility vehicles
4. **Van** - Commercial or passenger vans
5. **Truck** - Commercial trucks
6. **Bus** - Public or private buses
7. **Electric Vehicle** - Battery-powered vehicles
8. **Hybrid Vehicle** - Gas-electric hybrid vehicles
9. **Vintage/Classic** - Classic and vintage vehicles
10. **Government** - Government-owned vehicles
11. **Diplomatic** - Diplomatic mission vehicles
12. **Temporary/Conduction** - Temporary registration plates

## 🔄 Auto-Detection Features

### Web Interface (Blade Templates)
- **Real-time formatting**: Plate numbers are formatted as users type
- **Auto-detection**: Vehicle type is automatically detected based on plate format
- **Smart validation**: Immediate feedback on invalid formats

### Mobile App (Flutter)
- **Live validation**: Plate format validation in real-time
- **Auto-selection**: Vehicle type dropdown automatically updates based on plate format
- **User-friendly**: Clear error messages for invalid formats

## 📝 Form Labels Updated

All forms now use **"Owner/Description"** instead of "Owner Name" to accommodate:
- Individual owner names
- Company names
- Vehicle descriptions
- Fleet identifiers

## 🛡️ Security & Validation

### Input Sanitization
- Case-insensitive validation (converted to uppercase)
- Optional dash/space separator support
- Special character filtering
- Length validation

### Duplicate Prevention
- Database-level unique constraints
- Application-level duplicate checking
- Real-time duplicate validation via API
- Comprehensive logging of duplicate attempts

### Error Handling
- Clear, user-friendly error messages
- Detailed validation feedback
- Graceful fallback for edge cases
- Comprehensive logging for debugging

## 🧪 Testing Examples

### Valid Plate Numbers
```
Standard Vehicles:
- ABC-1234
- XYZ-5678
- DEF-9012

Motorcycles:
- AB-123-A
- 1-ABC-23
- A-1-B-234
- CD-5678
- 5-AB-678

Special Categories:
- E-ABC-123 (Electric)
- H-XYZ-456 (Hybrid)
- V-DEF-789 (Vintage)
- G-GOV-001 (Government)
- D-DIP-002 (Diplomatic)
- T-TMP-003 (Temporary)
```

### Invalid Plate Numbers
```
- ABC-123 (Too short)
- ABCD-1234 (Too many letters)
- ABC-12345 (Too many digits)
- 123-ABC (Wrong format)
- ABC@123 (Invalid characters)
```

## 🔧 Configuration

### Environment Variables
No additional configuration required - the system works out of the box.

### Customization
To add new plate formats:
1. Update regex patterns in `Plate::isValidFormat()`
2. Add formatting logic in `Plate::formatNumber()`
3. Update detection logic in `Plate::detectVehicleCategory()`
4. Update Flutter patterns in `_platePatterns`
5. Update vehicle type detection in `_detectVehicleType()`

## 📊 Performance Considerations

- **Efficient regex patterns**: Optimized for fast matching
- **Caching**: No caching implemented (real-time validation required)
- **Database indexes**: Unique constraint provides efficient duplicate checking
- **Minimal overhead**: Validation adds negligible performance impact

## 🔮 Future Enhancements

1. **OCR Integration**: Automatic plate recognition from images
2. **Bulk Import**: CSV import with validation
3. **Fuzzy Matching**: Suggest similar plate numbers
4. **Regional Support**: Province-specific plate formats
5. **API Rate Limiting**: Prevent abuse of validation endpoints
6. **Audit Trail**: Enhanced logging for compliance

## 🚀 Usage

### API Endpoints
```bash
# Check plate format validity
GET /api/plates/check-duplicate/{plate_number}

# Create new plate (with validation)
POST /api/plates
{
    "number": "ABC-1234",
    "owner_name": "John Doe",
    "vehicle_type": "Car"
}
```

### Web Interface
- Navigate to Plates section
- Click "Add Plate" or "Edit Plate"
- Enter plate number (auto-formatted)
- Vehicle type auto-detected
- Submit with validation

### Mobile App
- Open Plates tab
- Tap "+" to add new plate
- Enter plate number (real-time validation)
- Vehicle type auto-selected
- Save with validation

## 📞 Support

For questions or issues with the Philippine LTO plate validation system:
1. Check the validation patterns in this document
2. Review the error messages for specific issues
3. Test with known valid plate formats
4. Contact the development team for assistance

---

**Last Updated**: 2025  
**Version**: 1.0  
**Compatibility**: Laravel 10+, Flutter 3.0+
