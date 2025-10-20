<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RateCare API Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .api-response {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .platform-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            background: #fff;
        }
        
        .platform-card.success {
            border-left: 4px solid #28a745;
        }
        
        .platform-card.failed {
            border-left: 4px solid #dc3545;
        }
        
        .price {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
        
        .loading {
            display: none;
        }
        
        .error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">RateCare API Test</h1>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Rate Comparison API Test</h5>
                    </div>
                    <div class="card-body">
                        <form id="apiTestForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Widget Code</label>
                                        <input type="text" class="form-control" id="widgetCode" value="ad3szsw78mf" placeholder="Enter widget code">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label">Currency</label>
                                        <select class="form-control" id="currency">
                                            <option value="TRY">TRY</option>
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label">Check-in</label>
                                        <input type="date" class="form-control" id="checkin" value="2025-10-20">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label">Check-out</label>
                                        <input type="date" class="form-control" id="checkout" value="2025-10-21">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label">Adults</label>
                                        <input type="number" class="form-control" id="adult" value="2" min="1">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label">Children</label>
                                        <input type="number" class="form-control" id="child" value="0" min="0">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label">Infants</label>
                                        <input type="number" class="form-control" id="infant" value="0" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Test API</button>
                                <button type="button" class="btn btn-secondary" onclick="createTestWidget()">Create Test Widget</button>
                                <div class="loading spinner-border spinner-border-sm ms-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div id="results" class="mt-4" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5>API Response</h5>
                        </div>
                        <div class="card-body">
                            <div id="platformResults"></div>
                            <div class="mt-3">
                                <h6>Raw JSON Response:</h6>
                                <div id="rawResponse" class="api-response"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('apiTestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            testAPI();
        });
        
        function testAPI() {
            const widgetCode = document.getElementById('widgetCode').value;
            const currency = document.getElementById('currency').value;
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            const adult = document.getElementById('adult').value;
            const child = document.getElementById('child').value;
            const infant = document.getElementById('infant').value;
            
            if (!widgetCode) {
                alert('Please enter a widget code');
                return;
            }
            
            const loading = document.querySelector('.loading');
            const results = document.getElementById('results');
            
            loading.style.display = 'inline-block';
            results.style.display = 'none';
            
            const url = `/api/${widgetCode}?currency=${currency}&checkin=${checkin}&checkout=${checkout}&adult=${adult}&child=${child}&infant=${infant}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    displayResults(data);
                })
                .catch(error => {
                    loading.style.display = 'none';
                    console.error('Error:', error);
                    displayError('API request failed: ' + error.message);
                });
        }
        
        function displayResults(data) {
            const results = document.getElementById('results');
            const platformResults = document.getElementById('platformResults');
            const rawResponse = document.getElementById('rawResponse');
            
            results.style.display = 'block';
            rawResponse.textContent = JSON.stringify(data, null, 2);
            
            if (data.status === 'success' && data.data && data.data.platforms) {
                let html = '<div class="row">';
                
                if (data.data.request_info) {
                    html += `
                        <div class="col-12 mb-3">
                            <div class="alert alert-info">
                                <strong>Request Info:</strong><br>
                                Hotel: ${data.data.request_info.hotel_name}<br>
                                Dates: ${data.data.request_info.checkin} to ${data.data.request_info.checkout}<br>
                                Guests: ${data.data.request_info.adult} adults, ${data.data.request_info.child} children<br>
                                Currency: ${data.data.request_info.currency}
                            </div>
                        </div>
                    `;
                }
                
                data.data.platforms.forEach(platform => {
                    html += `
                        <div class="col-md-4">
                            <div class="platform-card ${platform.status}">
                                <h6>${platform.displayName}</h6>
                                <div class="price">${platform.price} ${data.data.request_info.currency}</div>
                                <div class="mt-2">
                                    <small class="text-muted">Status: ${platform.status}</small>
                                    ${platform.url ? `<br><a href="${platform.url}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View Offer</a>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                platformResults.innerHTML = html;
            } else {
                platformResults.innerHTML = `<div class="alert alert-danger">Error: ${data.message || 'Unknown error'}</div>`;
            }
        }
        
        function displayError(message) {
            const results = document.getElementById('results');
            const platformResults = document.getElementById('platformResults');
            
            results.style.display = 'block';
            platformResults.innerHTML = `<div class="alert alert-danger">${message}</div>`;
        }
        
        function createTestWidget() {
            // This would create a test widget in the database
            alert('Test widget creation would be implemented here');
        }
        
        // Set default dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            document.getElementById('checkin').value = today.toISOString().split('T')[0];
            document.getElementById('checkout').value = tomorrow.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
