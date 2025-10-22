# Etstur API Integration Fix

## Problem
Etstur API'den fiyat geldiği halde "Price not available" hatası alınıyordu.

## Root Cause
Etstur API response formatı beklenenden farklıydı:

**Actual Response:**
```json
{
  "id": "PAPABU",
  "rateType": "Bed & Breakfast",
  "totalRate": 13800.00,
  "baseRate": 13800.00,
  "currency": "TRY",
  "deeplink": "https://...",
  "restrictions": ["non_refundable"]
}
```

**What Code Was Looking For:**
- `result['price']`
- `result['minPrice']`
- `result['data']['price']`

## Solution
Updated `getEtsturPriceReal()` method to check for Etstur-specific fields:

1. **Primary Check:** `totalRate` (recommended by Etstur)
2. **Secondary Check:** `baseRate` (fallback)
3. **Legacy Checks:** Still supports other formats for compatibility

## Changes Made

### File: `/app/Controllers/Api/ApiController.php`

#### Line ~1757-1762:
```php
// Check for price in Etstur response structure (totalRate, baseRate)
if (isset($result['totalRate'])) {
    $price = $result['totalRate'];
    $this->logMessage("Etstur API: Using totalRate - " . $price, 'DEBUG');
} elseif (isset($result['baseRate'])) {
    $price = $result['baseRate'];
    $this->logMessage("Etstur API: Using baseRate - " . $price, 'DEBUG');
}
```

#### Line ~1775-1783 (Rooms array):
```php
if (isset($room['totalRate'])) {
    $prices[] = $room['totalRate'];
} elseif (isset($room['baseRate'])) {
    $prices[] = $room['baseRate'];
}
```

#### Line ~1792-1794 (Availability array):
```php
if (isset($avail['totalRate'])) {
    $prices[] = $avail['totalRate'];
}
```

## Testing

### Test Case 1: Single Room Response
**Input:**
```bash
Hotel ID: PAPABU
Currency: TRY
Check-in: 2025-10-29
Check-out: 2025-10-31
```

**Expected Output:**
```json
{
  "status": "success",
  "name": "etstur",
  "displayName": "ETSTur",
  "price": 13800,
  "currency": "TRY"
}
```

### Test Case 2: EUR Currency Request
**Input:**
```bash
Currency: EUR
```

**Expected Output:**
```json
{
  "price": 400.00,  // 13800 / 34.50 (EUR rate)
  "currency": "EUR"
}
```

## Response Fields Priority

1. ✅ `totalRate` (Primary - includes all charges)
2. ✅ `baseRate` (Secondary - base room rate)
3. ✅ `price` (Legacy support)
4. ✅ `minPrice` (Legacy support)

## Logging

New debug logs added:
- "Etstur API: Response structure - {full_json}"
- "Etstur API: Using totalRate - {price}"
- "Etstur API: Using baseRate - {price}"

## Verification Steps

1. Check logs: `/app/storage/logs/app.log`
2. Look for: "ETSTur: Successfully added to response"
3. Verify response contains price and currency
4. Test currency conversion (TRY → EUR/USD)

## Date Fixed
October 22, 2025

## Notes
- Etstur API may return different response structures based on availability
- Always check `totalRate` first as it includes all applicable fees
- `baseRate` excludes some fees but is a good fallback
- Response structure logging helps identify future API changes
