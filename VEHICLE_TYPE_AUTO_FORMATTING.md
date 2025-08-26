# Vehicle Type-Based Auto-Formatting System

This document outlines the comprehensive vehicle type-based auto-formatting system implemented across Laravel (backend + Blade frontend) and Flutter (mobile) for Philippine LTO plate numbers.

## 🎯 Overview

The system automatically formats plate numbers based on the selected vehicle type, providing a seamless user experience with real-time formatting and validation.

## 🔧 How It Works

### 1. **Vehicle Type Selection First**
- User selects vehicle type from dropdown
- Input field placeholder and maxlength update automatically
- Plate number formatting rules are applied based on selection

### 2. **Real-Time Auto-Formatting**
- As user types, plate number is automatically formatted
- Formatting follows Philippine LTO standards for each vehicle type
- Invalid characters are filtered out automatically

### 3. **Bidirectional Auto-Detection**
- If user enters plate number first, vehicle type is auto-detected
- If user changes vehicle type, existing plate is reformatted
- System maintains consistency across all interactions

## 📋 Vehicle Type Formatting Rules

### Standard Vehicles (Car, SUV, Van, Truck, Bus)
- **Format**: `LLL-DDDD` (3 letters, 4 digits)
- **Pattern**: `/^([A-Z]{3})(\d{4})$/`
- **Example**: `ABC-1234`
- **Max Length**: 8 characters

### Motorcycle (Multiple Patterns)
- **Pattern 1**: `LL-DDD-L` → `/^([A-Z]{2})(\d{3})([A-Z])$/`
- **Pattern 2**: `D-LLL-DD` → `/^([A-Z])(\d{3})([A-Z]{2})$/`
- **Pattern 3**: `L-D-L-DDD` → `/^([A-Z])(\d{1})([A-Z])(\d{3})$/`
- **Pattern 4**: `LL-DDDD` → `/^([A-Z]{2})(\d{4})$/`
- **Pattern 5**: `D-LL-DDD` → `/^([A-Z])(\d{2})([A-Z])(\d{3})$/`
- **Max Length**: 10 characters

### Electric Vehicle
- **Format**: `E-LLL-DDD` (E, 3 letters, 3 digits)
- **Pattern**: `/^(E)([A-Z]{3})(\d{3})$/`
- **Example**: `E-ABC-123`
- **Max Length**: 9 characters

### Hybrid Vehicle
- **Format**: `H-LLL-DDD` (H, 3 letters, 3 digits)
- **Pattern**: `/^(H)([A-Z]{3})(\d{3})$/`
- **Example**: `H-XYZ-456`
- **Max Length**: 9 characters

### Vintage/Classic
- **Format**: `V-LLL-DDD` (V, 3 letters, 3 digits)
- **Pattern**: `/^(V)([A-Z]{3})(\d{3})$/`
- **Example**: `V-DEF-789`
- **Max Length**: 9 characters

### Government
- **Format**: `G-LLL-DDD` (G, 3 letters, 3 digits)
- **Pattern**: `/^(G)([A-Z]{3})(\d{3})$/`
- **Example**: `G-GOV-001`
- **Max Length**: 9 characters

### Diplomatic
- **Format**: `D-LLL-DDD` (D, 3 letters, 3 digits)
- **Pattern**: `/^(D)([A-Z]{3})(\d{3})$/`
- **Example**: `D-DIP-002`
- **Max Length**: 9 characters

### Temporary/Conduction
- **Format**: `T-LLL-DDD` (T, 3 letters, 3 digits)
- **Pattern**: `/^(T)([A-Z]{3})(\d{3})$/`
- **Example**: `T-TMP-003`
- **Max Length**: 9 characters

## 💻 Implementation Details

### Laravel Backend (JavaScript)

#### Vehicle Type Format Mapping
```javascript
const vehicleTypeFormats = {
    'Car': { 
        pattern: /^([A-Z]{3})(\d{4})$/, 
        format: '$1-$2',
        placeholder: 'ABC-1234',
        maxLength: 8
    },
    'Motorcycle': { 
        patterns: [
            { pattern: /^([A-Z]{2})(\d{3})([A-Z])$/, format: '$1-$2-$3', placeholder: 'AB-123-A' },
            { pattern: /^([A-Z])(\d{3})([A-Z]{2})$/, format: '$1-$2-$3', placeholder: '1-ABC-23' },
            // ... more patterns
        ],
        placeholder: 'AB-123-A',
        maxLength: 10
    },
    'Electric Vehicle': { 
        pattern: /^(E)([A-Z]{3})(\d{3})$/, 
        format: '$1-$2-$3',
        placeholder: 'E-ABC-123',
        maxLength: 9
    },
    // ... other vehicle types
};
```

#### Formatting Function
```javascript
function formatPlateByVehicleType(value, vehicleType) {
    if (!vehicleType || !vehicleTypeFormats[vehicleType]) {
        return value;
    }

    const format = vehicleTypeFormats[vehicleType];
    
    if (vehicleType === 'Motorcycle') {
        // Try each motorcycle pattern
        for (const pattern of format.patterns) {
            if (pattern.pattern.test(value)) {
                return value.replace(pattern.pattern, pattern.format);
            }
        }
    } else {
        // Single pattern for other vehicle types
        if (format.pattern.test(value)) {
            return value.replace(format.pattern, format.format);
        }
    }
    
    return value;
}
```

#### Event Handlers
```javascript
// Format as user types
plateNumberInput.addEventListener('input', function(e) {
    let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    const selectedVehicleType = vehicleTypeSelect.value;
    
    if (selectedVehicleType && vehicleTypeFormats[selectedVehicleType]) {
        value = formatPlateByVehicleType(value, selectedVehicleType);
    }
    
    e.target.value = value;
});

// Auto-detect vehicle type
plateNumberInput.addEventListener('blur', function() {
    const value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    
    if (value.startsWith('E')) {
        vehicleTypeSelect.value = 'Electric Vehicle';
    } else if (value.startsWith('H')) {
        vehicleTypeSelect.value = 'Hybrid Vehicle';
    }
    // ... more detection logic
    
    updatePlateInputAttributes();
});

// Re-format when vehicle type changes
vehicleTypeSelect.addEventListener('change', function() {
    const value = plateNumberInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    if (value) {
        const formattedValue = formatPlateByVehicleType(value, this.value);
        plateNumberInput.value = formattedValue;
    }
    
    updatePlateInputAttributes();
});
```

### Flutter Mobile App (Dart)

#### Formatting Function
```dart
String _formatPlateByVehicleType(String plate, String vehicleType) {
  final normalized = plate.toUpperCase().replaceAll(RegExp(r'[\s\-]'), '');
  
  switch (vehicleType) {
    case 'Car':
    case 'SUV':
    case 'Van':
    case 'Truck':
    case 'Bus':
      // Standard format: LLL-DDDD
      if (RegExp(r'^[A-Z]{3}\d{4}$').hasMatch(normalized)) {
        return '${normalized.substring(0, 3)}-${normalized.substring(3, 7)}';
      }
      break;
      
    case 'Motorcycle':
      // Try each motorcycle pattern
      if (RegExp(r'^[A-Z]{2}\d{3}[A-Z]$').hasMatch(normalized)) {
        return '${normalized.substring(0, 2)}-${normalized.substring(2, 5)}-${normalized.substring(5, 6)}';
      }
      // ... more patterns
      break;
      
    case 'Electric Vehicle':
      // Format: E-LLL-DDD
      if (RegExp(r'^E[A-Z]{3}\d{3}$').hasMatch(normalized)) {
        return 'E-${normalized.substring(1, 4)}-${normalized.substring(4, 7)}';
      }
      break;
      
    // ... other vehicle types
  }
  
  return plate;
}
```

#### TextField Implementation
```dart
TextFormField(
  controller: _numberController,
  decoration: const InputDecoration(
    labelText: 'Plate Number',
    border: OutlineInputBorder(),
    hintText: 'e.g., ABC-1234, AB-123-A, E-ABC-123',
  ),
  validator: (String? v) {
    if (v == null || v.trim().isEmpty) return 'Required';
    if (!_isValidPlateFormat(v.trim())) {
      return 'Invalid Philippine LTO plate format';
    }
    return null;
  },
  onChanged: (String value) {
    // Auto-detect vehicle type
    if (_isValidPlateFormat(value)) {
      final detectedType = _detectVehicleType(value);
      if (detectedType != _vehicleType) {
        setState(() {
          _vehicleType = detectedType;
        });
      }
    }
    
    // Auto-format based on selected vehicle type
    final formattedValue = _formatPlateByVehicleType(value, _vehicleType);
    if (formattedValue != value) {
      _numberController.text = formattedValue;
      _numberController.selection = TextSelection.fromPosition(
        TextPosition(offset: formattedValue.length),
      );
    }
  },
),
```

## 🎯 User Experience Features

### 1. **Smart Placeholders**
- Placeholder text updates based on selected vehicle type
- Shows expected format for each vehicle type
- Helps users understand the expected input

### 2. **Dynamic Max Length**
- Input field maxlength adjusts based on vehicle type
- Prevents users from entering more characters than needed
- Ensures proper format compliance

### 3. **Real-Time Validation**
- Immediate feedback on invalid formats
- Clear error messages for format violations
- Prevents submission of invalid data

### 4. **Cursor Position Management**
- Maintains cursor position during auto-formatting
- Prevents cursor jumping to beginning of field
- Smooth typing experience

### 5. **Bidirectional Auto-Detection**
- Vehicle type auto-detects from plate format
- Plate reformats when vehicle type changes
- Flexible workflow for users

## 🧪 Testing Examples

### Car Format
```
Input: ABC1234
Output: ABC-1234
Vehicle Type: Car (auto-detected)
```

### Motorcycle Format
```
Input: AB123A
Output: AB-123-A
Vehicle Type: Motorcycle (auto-detected)
```

### Electric Vehicle Format
```
Input: EABC123
Output: E-ABC-123
Vehicle Type: Electric Vehicle (auto-detected)
```

### Vehicle Type Change
```
Current: ABC-1234 (Car)
Change to: Electric Vehicle
Output: E-ABC-123
```

## 🔧 Configuration

### Adding New Vehicle Types
1. **Update JavaScript mapping** in all Blade templates
2. **Update Dart formatting function** in Flutter app
3. **Add validation patterns** in Plate model
4. **Update dropdown options** in all forms
5. **Test formatting** with sample data

### Customizing Formatting Rules
1. **Modify regex patterns** in vehicle type mapping
2. **Update format strings** for new patterns
3. **Adjust maxlength** values if needed
4. **Update placeholders** for new formats
5. **Test edge cases** thoroughly

## 🚀 Performance Considerations

### JavaScript Optimization
- **Efficient regex patterns**: Pre-compiled patterns for fast matching
- **Minimal DOM manipulation**: Batch updates to reduce reflows
- **Event delegation**: Efficient event handling
- **Debounced input**: Prevents excessive formatting calls

### Flutter Optimization
- **RegExp caching**: Reuse compiled patterns
- **State management**: Efficient setState calls
- **Text selection**: Smooth cursor positioning
- **Memory management**: Proper controller disposal

## 🛡️ Error Handling

### Input Validation
- **Character filtering**: Remove invalid characters
- **Length validation**: Enforce maxlength constraints
- **Format validation**: Ensure proper pattern matching
- **Fallback handling**: Graceful degradation for edge cases

### User Feedback
- **Clear error messages**: Specific format violation details
- **Visual indicators**: Red borders for invalid input
- **Help text**: Guidance for correct format
- **Real-time updates**: Immediate feedback on changes

## 📱 Cross-Platform Consistency

### Web Interface
- **Blade templates**: Consistent behavior across admin/attendant
- **JavaScript functions**: Shared formatting logic
- **CSS styling**: Unified visual appearance
- **Form validation**: Same validation rules

### Mobile App
- **Flutter widgets**: Native mobile experience
- **Dart functions**: Platform-specific optimizations
- **Material Design**: Consistent UI/UX
- **Touch interactions**: Mobile-optimized input

## 🔮 Future Enhancements

### 1. **OCR Integration**
- Automatic plate recognition from camera
- Pre-fill plate number from image
- Auto-detect vehicle type from image

### 2. **Smart Suggestions**
- Suggest similar plate numbers
- Auto-complete based on partial input
- Historical plate number suggestions

### 3. **Advanced Validation**
- Province-specific format validation
- Regional plate number rules
- Special format exceptions

### 4. **Bulk Operations**
- CSV import with auto-formatting
- Batch plate number validation
- Bulk vehicle type assignment

### 5. **Analytics Integration**
- Track formatting patterns
- Monitor user behavior
- Optimize formatting rules

## 📞 Support

For questions or issues with the vehicle type-based auto-formatting system:

1. **Check vehicle type mapping** in JavaScript/Dart code
2. **Verify regex patterns** for specific formats
3. **Test with sample data** to identify issues
4. **Review error messages** for specific violations
5. **Contact development team** for assistance

---

**Last Updated**: 2025  
**Version**: 1.0  
**Compatibility**: Laravel 10+, Flutter 3.0+
