-- Check which hotels have Etstur configured
SELECT 
    h.id,
    h.name,
    h.is_etstur_active,
    h.etstur_hotel_id,
    w.code as widget_code
FROM hotels h
LEFT JOIN widgets w ON w.hotel_id = h.id AND w.type = 'main'
WHERE h.is_etstur_active = 1 
  AND h.etstur_hotel_id IS NOT NULL 
  AND h.etstur_hotel_id != '';

-- Check all hotels regardless of Etstur status
SELECT 
    h.id,
    h.name,
    h.is_etstur_active,
    h.etstur_hotel_id,
    h.booking_is_active,
    h.sabee_is_active,
    w.code as widget_code
FROM hotels h
LEFT JOIN widgets w ON w.hotel_id = h.id AND w.type = 'main'
ORDER BY h.id;
