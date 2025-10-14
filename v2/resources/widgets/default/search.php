<div class="hotel-widget widget-search" data-widget-id="<?= $widget['id'] ?>">
    <div class="widget-header">
        <h3><?= htmlspecialchars($widget['name']) ?></h3>
        <?php if ($hotel): ?>
            <div class="hotel-info">
                <h4><?= htmlspecialchars($hotel['name']) ?></h4>
                <p class="hotel-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($hotel['city'] . ', ' . $hotel['country']) ?>
                </p>
                <?php if ($hotel['star_rating']): ?>
                    <div class="hotel-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= $hotel['star_rating'] ? 'active' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="widget-content">
        <form class="search-form" action="<?= url('/search') ?>" method="GET">
            <input type="hidden" name="hotel_id" value="<?= $hotel['id'] ?? '' ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="checkin_<?= $widget['id'] ?>">Check-in</label>
                    <input type="date" 
                           id="checkin_<?= $widget['id'] ?>" 
                           name="checkin" 
                           value="<?= $settings['check_in'] ?? date('Y-m-d', strtotime('+1 day')) ?>"
                           min="<?= date('Y-m-d') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="checkout_<?= $widget['id'] ?>">Check-out</label>
                    <input type="date" 
                           id="checkout_<?= $widget['id'] ?>" 
                           name="checkout" 
                           value="<?= $settings['check_out'] ?? date('Y-m-d', strtotime('+2 days')) ?>"
                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="adults_<?= $widget['id'] ?>">Adults</label>
                    <select id="adults_<?= $widget['id'] ?>" name="adults">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>" <?= ($settings['adults'] ?? 2) == $i ? 'selected' : '' ?>>
                                <?= $i ?> Adult<?= $i > 1 ? 's' : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="children_<?= $widget['id'] ?>">Children</label>
                    <select id="children_<?= $widget['id'] ?>" name="children">
                        <?php for ($i = 0; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>" <?= ($settings['children'] ?? 0) == $i ? 'selected' : '' ?>>
                                <?= $i ?> Child<?= $i != 1 ? 'ren' : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i>
                    Search Rates
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkinInput = document.getElementById('checkin_<?= $widget['id'] ?>');
    const checkoutInput = document.getElementById('checkout_<?= $widget['id'] ?>');
    
    // Update checkout min date when checkin changes
    checkinInput.addEventListener('change', function() {
        const checkinDate = new Date(this.value);
        checkinDate.setDate(checkinDate.getDate() + 1);
        checkoutInput.min = checkinDate.toISOString().split('T')[0];
        
        // Update checkout if it's before new minimum
        if (new Date(checkoutInput.value) <= new Date(this.value)) {
            checkoutInput.value = checkinDate.toISOString().split('T')[0];
        }
    });
});
</script>
