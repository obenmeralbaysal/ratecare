@extends('layouts.admin-new')

@section('title', 'System Settings')

@section('menu-settings', 'active')

@section('content')
<div class="block-header">
    <div class="row clearfix">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <h2 class="float-left">System Settings</h2>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 text-right">
            <button type="button" class="btn btn-warning" onclick="clearCache()">
                <i class="zmdi zmdi-delete"></i> Clear Cache
            </button>
        </div>
    </div>
</div>

<?php if(flash('error')): ?>
    <div class="alert alert-danger"><?php echo flash('error'); ?></div>
<?php endif; ?>

<?php if(flash('success')): ?>
    <div class="alert alert-success"><?php echo flash('success'); ?></div>
<?php endif; ?>

<div class="row clearfix">
    <div class="col-lg-12">
        <div class="card p-4">
            <div class="body">
                <!-- Settings Groups Tabs -->
                <?php if(isset($groups) && !empty($groups)): ?>
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <?php foreach($groups as $group): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $group === ($current_group ?? 'general') ? 'active' : ''; ?>" 
                                   href="<?php echo url('/admin/settings?group=' . $group); ?>">
                                    <?php echo ucfirst($group); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <!-- Settings Form -->
                <form method="POST" action="<?php echo url('/admin/settings/update'); ?>" id="settingsForm" onsubmit="return handleSettingsUpdate(event)">
                    <?php echo csrfField(); ?>
                    
                    <?php if(isset($grouped_settings) && !empty($grouped_settings)): ?>
                        <?php 
                        $currentGroup = $current_group ?? 'general';
                        $settings = $grouped_settings[$currentGroup] ?? [];
                        ?>
                        
                        <?php if(!empty($settings)): ?>
                            <?php foreach($settings as $setting): ?>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">
                                        <?php echo ucfirst(str_replace('_', ' ', $setting['key'])); ?>
                                        <?php if(!empty($setting['description'])): ?>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($setting['description']); ?></small>
                                        <?php endif; ?>
                                    </label>
                                    <div class="col-sm-9">
                                        <?php 
                                        $type = $setting['type'] ?? 'string';
                                        $value = htmlspecialchars($setting['value'] ?? '');
                                        ?>
                                        
                                        <?php if($type === 'boolean'): ?>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" 
                                                       id="setting_<?php echo $setting['key']; ?>" 
                                                       name="settings[<?php echo $setting['key']; ?>]" 
                                                       value="1" <?php echo $value == '1' ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="setting_<?php echo $setting['key']; ?>">Enable</label>
                                            </div>
                                        <?php elseif($type === 'text'): ?>
                                            <textarea class="form-control" 
                                                      name="settings[<?php echo $setting['key']; ?>]" 
                                                      rows="3"><?php echo $value; ?></textarea>
                                        <?php else: ?>
                                            <input type="text" class="form-control" 
                                                   name="settings[<?php echo $setting['key']; ?>]" 
                                                   value="<?php echo $value; ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="form-group row">
                                <div class="col-sm-9 offset-sm-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="zmdi zmdi-save"></i> Save Settings
                                    </button>
                                    <a href="<?php echo url('/admin/settings/reset?group=' . $currentGroup); ?>" 
                                       class="btn btn-warning" 
                                       onclick="return confirm('Reset all settings in this group to default values?')">
                                        <i class="zmdi zmdi-refresh"></i> Reset to Default
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="zmdi zmdi-info"></i> No settings found in this group.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="zmdi zmdi-info"></i> No settings configured yet.
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function handleSettingsUpdate(event) {
    event.preventDefault();
    
    const form = document.getElementById('settingsForm');
    const formData = new FormData(form);
    
    $.ajax({
        url: $(form).attr('action'),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message || 'Settings updated successfully!');
            } else {
                toastr.error(response.message || 'Failed to update settings');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.errors) {
                Object.values(response.errors).forEach(function(error) {
                    toastr.error(error[0]);
                });
            } else if (response && response.message) {
                toastr.error(response.message);
            } else {
                toastr.error('An error occurred. Please try again.');
            }
        }
    });
    
    return false;
}

function clearCache() {
    if (!confirm('Are you sure you want to clear all cache?')) {
        return;
    }
    
    $.ajax({
        url: '<?php echo url("/admin/cache/clear"); ?>',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message || 'Cache cleared successfully!');
            } else {
                toastr.error(response.message || 'Failed to clear cache');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            toastr.error(response && response.message ? response.message : 'An error occurred.');
        }
    });
}
</script>
@endsection

@section('styles')
<style>
    .nav-tabs .nav-link {
        color: #495057;
        border: 1px solid transparent;
    }
    .nav-tabs .nav-link:hover {
        border-color: #e9ecef #e9ecef #dee2e6;
    }
    .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }
    .custom-control-label {
        cursor: pointer;
    }
</style>
@endsection
