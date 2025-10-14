# Hotel DigiLab API Documentation

## Overview

The Hotel DigiLab API provides RESTful endpoints for managing hotels, widgets, rates, and user accounts. All API endpoints are prefixed with `/api/v1/`.

## Authentication

The API uses Bearer token authentication. Include the token in the Authorization header:

```
Authorization: Bearer YOUR_API_TOKEN
```

## Rate Limiting

- **Default**: 60 requests per minute per IP
- **Authenticated**: 100 requests per minute per user
- **Admin**: 200 requests per minute

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Request limit
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Reset timestamp

## Response Format

All responses are in JSON format:

```json
{
  "data": {},
  "message": "Success message",
  "status": "success"
}
```

Error responses:
```json
{
  "error": "Error message",
  "code": 400,
  "details": {}
}
```

## Endpoints

### Authentication

#### POST /api/v1/auth/login
Login with email and password.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "name": "John Doe",
      "role": "customer"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

#### POST /api/v1/auth/logout
Logout current user (requires authentication).

#### GET /api/v1/auth/user
Get current authenticated user information.

### Widgets

#### GET /api/v1/widgets
Get all widgets for authenticated user.

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)
- `type`: Filter by widget type (search, rates, booking, comparison)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Hotel Search Widget",
      "type": "search",
      "hotel_id": 1,
      "hotel_name": "Grand Hotel",
      "is_active": 1,
      "created_at": "2024-01-01 12:00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 25,
    "last_page": 2
  }
}
```

#### GET /api/v1/widgets/{id}
Get specific widget details.

#### POST /api/v1/widgets
Create new widget.

**Request:**
```json
{
  "name": "My Widget",
  "type": "search",
  "hotel_id": 1,
  "settings": {
    "theme": "default",
    "language": "en"
  }
}
```

#### PUT /api/v1/widgets/{id}
Update existing widget.

#### DELETE /api/v1/widgets/{id}
Delete widget.

#### GET /api/v1/widgets/{id}/render
Get widget HTML and CSS for embedding.

**Response:**
```json
{
  "html": "<div class=\"hotel-widget\">...</div>",
  "css": ".hotel-widget { ... }",
  "widget": {}
}
```

#### GET /api/v1/widgets/{id}/embed
Get widget embed code.

**Response:**
```json
{
  "embed_code": "<iframe src=\"...\"></iframe>",
  "iframe_url": "https://example.com/widgets/1/embed"
}
```

#### POST /api/v1/widgets/{id}/track
Track widget events (views, clicks).

**Request:**
```json
{
  "event": "view",
  "timestamp": 1640995200000,
  "url": "https://example.com/page",
  "referrer": "https://google.com"
}
```

#### GET /api/v1/widgets/{id}/statistics
Get widget statistics (requires authentication).

**Query Parameters:**
- `date_from`: Start date (YYYY-MM-DD)
- `date_to`: End date (YYYY-MM-DD)

### Hotels

#### GET /api/v1/hotels
Get all hotels for authenticated user.

#### GET /api/v1/hotels/{id}
Get specific hotel details.

#### POST /api/v1/hotels
Create new hotel.

**Request:**
```json
{
  "name": "Grand Hotel",
  "city": "New York",
  "country": "USA",
  "address": "123 Main St",
  "phone": "+1-555-0123",
  "email": "info@grandhotel.com",
  "website": "https://grandhotel.com",
  "star_rating": 5,
  "description": "Luxury hotel in downtown"
}
```

#### PUT /api/v1/hotels/{id}
Update existing hotel.

#### DELETE /api/v1/hotels/{id}
Delete hotel.

#### GET /api/v1/hotels/{id}/rates
Get rates for specific hotel.

**Query Parameters:**
- `check_in`: Check-in date (YYYY-MM-DD)
- `check_out`: Check-out date (YYYY-MM-DD)
- `adults`: Number of adults (default: 2)
- `children`: Number of children (default: 0)

### Rates

#### GET /api/v1/rates
Get all rates.

**Query Parameters:**
- `hotel_id`: Filter by hotel ID
- `check_in`: Check-in date
- `check_out`: Check-out date
- `min_price`: Minimum price
- `max_price`: Maximum price

#### POST /api/v1/rates
Create new rate.

#### GET /api/v1/rates/search
Search rates across multiple hotels.

#### GET /api/v1/rates/compare
Compare rates from different sources.

### Statistics

#### GET /api/v1/statistics/dashboard
Get dashboard statistics (requires authentication).

**Response:**
```json
{
  "data": {
    "total_widgets": 15,
    "total_views": 1250,
    "total_clicks": 89,
    "conversion_rate": 7.12,
    "top_performing_widgets": []
  }
}
```

### Admin Endpoints

#### GET /api/v1/admin/statistics
Get system-wide statistics (admin only).

#### GET /api/v1/admin/health
Get system health status.

**Response:**
```json
{
  "overall_status": "healthy",
  "database": {
    "status": "healthy",
    "response_time": 15
  },
  "application": {
    "status": "healthy",
    "memory_usage": "45%"
  }
}
```

#### POST /api/v1/admin/cache/clear
Clear system cache.

#### GET /api/v1/admin/cache/stats
Get cache statistics.

## Error Codes

- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

## Validation Errors

Validation errors return a 422 status with details:

```json
{
  "error": "Validation failed",
  "errors": {
    "email": ["The email field is required"],
    "password": ["The password must be at least 8 characters"]
  }
}
```

## Webhooks

Configure webhooks to receive real-time notifications:

### Widget Events
- `widget.view` - Widget was viewed
- `widget.click` - Widget was clicked
- `widget.booking` - Booking was made through widget

### Rate Events
- `rate.updated` - Hotel rates were updated
- `rate.comparison` - Rate comparison was performed

## SDKs and Examples

### JavaScript
```javascript
const api = new HotelDigiLabAPI('YOUR_API_TOKEN');

// Get widgets
const widgets = await api.widgets.list();

// Create widget
const widget = await api.widgets.create({
  name: 'My Widget',
  type: 'search',
  hotel_id: 1
});
```

### PHP
```php
$api = new HotelDigiLabAPI('YOUR_API_TOKEN');

// Get widgets
$widgets = $api->widgets()->list();

// Create widget
$widget = $api->widgets()->create([
    'name' => 'My Widget',
    'type' => 'search',
    'hotel_id' => 1
]);
```

### cURL
```bash
# Get widgets
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://api.hoteldigilab.com/api/v1/widgets

# Create widget
curl -X POST \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"name":"My Widget","type":"search","hotel_id":1}' \
     https://api.hoteldigilab.com/api/v1/widgets
```

## Postman Collection

Import our Postman collection for easy API testing:
[Download Postman Collection](./postman/HotelDigiLab-API.postman_collection.json)

## Support

For API support, contact:
- Email: api-support@hoteldigilab.com
- Documentation: https://docs.hoteldigilab.com
- Status Page: https://status.hoteldigilab.com
