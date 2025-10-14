<div class="hotel-widget widget-rates" data-widget-id="<?= $widget['id'] ?>">
    <div class="widget-header">
        <h3><?= htmlspecialchars($widget['name']) ?></h3>
        <?php if ($hotel): ?>
            <div class="hotel-info">
                <h4><?= htmlspecialchars($hotel['name']) ?></h4>
                <p class="hotel-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($hotel['city'] . ', ' . $hotel['country']) ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="widget-content">
        <?php if (!empty($rates)): ?>
            <div class="rates-list">
                <?php foreach ($rates as $rate): ?>
                    <div class="rate-item" data-rate-id="<?= $rate['id'] ?>">
                        <div class="rate-info">
                            <div class="room-type">
                                <strong><?= htmlspecialchars($rate['room_type']) ?></strong>
                                <?php if ($rate['room_details']): ?>
                                    <p class="room-details"><?= htmlspecialchars($rate['room_details']) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="rate-features">
                                <?php if ($rate['breakfast_included']): ?>
                                    <span class="feature breakfast">
                                        <i class="fas fa-utensils"></i> Breakfast Included
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($rate['free_cancellation']): ?>
                                    <span class="feature cancellation">
                                        <i class="fas fa-times-circle"></i> Free Cancellation
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="rate-pricing">
                            <div class="rate-price">
                                <?= htmlspecialchars($rate['currency']) ?> 
                                <span class="price-amount"><?= number_format($rate['price'], 2) ?></span>
                            </div>
                            
                            <div class="rate-dates">
                                <?= date('M j', strtotime($rate['check_in'])) ?> - 
                                <?= date('M j', strtotime($rate['check_out'])) ?>
                            </div>
                            
                            <?php if ($rate['source']): ?>
                                <div class="rate-source">
                                    via <?= htmlspecialchars($rate['source']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="rate-actions">
                            <?php if ($rate['booking_url']): ?>
                                <a href="<?= htmlspecialchars($rate['booking_url']) ?>" 
                                   class="booking-button" 
                                   target="_blank"
                                   rel="noopener">
                                    <i class="fas fa-external-link-alt"></i>
                                    Book Now
                                </a>
                            <?php else: ?>
                                <button class="booking-button" onclick="bookRate(<?= $rate['id'] ?>)">
                                    <i class="fas fa-calendar-check"></i>
                                    Select
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="widget-footer">
                <p class="rates-info">
                    Showing <?= count($rates) ?> rate<?= count($rates) != 1 ? 's' : '' ?>
                    <?php if ($hotel): ?>
                        for <?= htmlspecialchars($hotel['name']) ?>
                    <?php endif; ?>
                </p>
                
                <div class="widget-actions">
                    <button class="refresh-rates" onclick="refreshRates(<?= $widget['id'] ?>)">
                        <i class="fas fa-sync-alt"></i>
                        Refresh Rates
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="no-rates">
                <div class="no-rates-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h4>No Rates Available</h4>
                <p>No rates found for the selected dates. Please try different dates or check back later.</p>
                
                <button class="search-other-dates" onclick="showDatePicker(<?= $widget['id'] ?>)">
                    <i class="fas fa-calendar-alt"></i>
                    Try Other Dates
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function bookRate(rateId) {
    // Track booking click
    fetch('/api/v1/widgets/<?= $widget['id'] ?>/track', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            event: 'booking_click',
            rate_id: rateId,
            timestamp: Date.now()
        })
    });
    
    // Redirect to booking page or show booking form
    window.open('/booking?rate_id=' + rateId, '_blank');
}

function refreshRates(widgetId) {
    const refreshButton = document.querySelector('.refresh-rates');
    const originalText = refreshButton.innerHTML;
    
    refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    refreshButton.disabled = true;
    
    // Reload widget content
    fetch('/api/v1/widgets/' + widgetId + '/render')
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                document.querySelector('[data-widget-id="' + widgetId + '"]').outerHTML = data.html;
            }
        })
        .catch(error => {
            console.error('Error refreshing rates:', error);
        })
        .finally(() => {
            refreshButton.innerHTML = originalText;
            refreshButton.disabled = false;
        });
}

function showDatePicker(widgetId) {
    // Show date picker modal or redirect to search page
    const searchUrl = '/search?hotel_id=<?= $hotel['id'] ?? '' ?>';
    window.open(searchUrl, '_blank');
}
</script>
