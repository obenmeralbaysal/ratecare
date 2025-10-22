# Base Layout System Implementation

## 📋 Genel Bakış
Tüm view'ları tek bir base layout üzerinden extend edilecek şekilde düzenlemek.

---

## 🎯 PHASE 1: Layout System Geliştirme (3 saat)

### 1.1 View Class'a Extend Özelliği Ekle
- [ ] `@extends('layout.name')` directive ekle
- [ ] `@section('name')` directive ekle
- [ ] `@yield('name')` directive ekle
- [ ] `@parent` directive ekle (optional)
- [ ] Section stack sistemi

**Örnek Kullanım:**
```php
// Layout: resources/views/layouts/admin.php
<!DOCTYPE html>
<html>
<head>
    @yield('head')
</head>
<body>
    @include('partials.navbar')
    @yield('content')
</body>
</html>

// View: resources/views/admin/dashboard.php
@extends('layouts.admin')

@section('content')
    <h1>Dashboard</h1>
@endsection
```

### 1.2 Base Layout Dosyaları Oluştur
- [ ] `resources/views/layouts/admin.php` - Admin layout
- [ ] `resources/views/layouts/customer.php` - Customer layout
- [ ] `resources/views/layouts/guest.php` - Login/Register layout
- [ ] `resources/views/partials/navbar.php` - Navbar component
- [ ] `resources/views/partials/menu.php` - Menu component
- [ ] `resources/views/partials/footer.php` - Footer component
- [ ] `resources/views/partials/loader.php` - Page loader component

---

## 🎯 PHASE 2: Partials Oluşturma (2 saat)

### 2.1 Admin Partials
- [ ] `partials/admin/navbar.php`
  - Logo
  - Hamburger menu
  - Settings icon
  - Logout button
  
- [ ] `partials/admin/menu.php`
  - Dashboard
  - Users (dropdown)
  - Hotels (dropdown)
  - Cache (dropdown)
  - Settings
  - Logs

- [ ] `partials/admin/head.php`
  - Meta tags
  - CSS links
  - Favicon

- [ ] `partials/admin/scripts.php`
  - jQuery
  - Bootstrap
  - Common scripts

### 2.2 Customer Partials
- [ ] `partials/customer/navbar.php`
- [ ] `partials/customer/menu.php`

### 2.3 Common Partials
- [ ] `partials/loader.php` - Page loader
- [ ] `partials/alerts.php` - Flash messages
- [ ] `partials/breadcrumb.php` - Breadcrumb navigation

---

## 🎯 PHASE 3: Admin Layout Implementation (4 saat)

### 3.1 Admin Base Layout
**File:** `resources/views/layouts/admin.php`

```php
<!doctype html>
<html class="no-js" lang="en">
<head>
    @include('partials.admin.head')
    @yield('styles')
</head>
<body class="theme-black">
    @include('partials.loader')
    @include('partials.admin.navbar')
    @include('partials.admin.menu')
    
    <section class="content home">
        <div class="container">
            @include('partials.alerts')
            @yield('content')
        </div>
    </section>
    
    @include('partials.admin.scripts')
    @yield('scripts')
</body>
</html>
```

### 3.2 Convert Admin Views
- [ ] `admin/dashboard/index.php` → use admin layout
- [ ] `admin/users/index.php` → use admin layout
- [ ] `admin/users/create.php` → use admin layout
- [ ] `admin/users/edit.php` → use admin layout
- [ ] `admin/users/invite.php` → use admin layout
- [ ] `admin/hotels/index.php` → use admin layout
- [ ] `admin/hotels/edit.php` → use admin layout
- [ ] `admin/logs/index.php` → use admin layout
- [ ] `admin/cache/statistics.php` → use admin layout
- [ ] `admin/settings/index.php` → use admin layout

---

## 🎯 PHASE 4: Customer Layout Implementation (2 saat)

### 4.1 Customer Base Layout
**File:** `resources/views/layouts/customer.php`

### 4.2 Convert Customer Views
- [ ] `customer/dashboard.php` → use customer layout
- [ ] `customer/widgets.php` → use customer layout
- [ ] `customer/hotels.php` → use customer layout

---

## 🎯 PHASE 5: Guest Layout Implementation (2 saat)

### 5.1 Guest Base Layout
**File:** `resources/views/layouts/guest.php`
- Minimal layout for login/register
- No navbar/menu
- Centered content

### 5.2 Convert Guest Views
- [ ] `auth/login.php` → use guest layout
- [ ] `auth/register.php` → use guest layout
- [ ] `auth/forgot-password.php` → use guest layout
- [ ] `auth/reset-password.php` → use guest layout
- [ ] `auth/invite.php` → use guest layout

---

## 🎯 PHASE 6: Dynamic Content & Features (3 saat)

### 6.1 Dynamic Page Titles
- [ ] `@yield('title', 'Default Title')`
- [ ] Set title in each view

### 6.2 Dynamic Breadcrumbs
- [ ] `@section('breadcrumb')` in views
- [ ] Show in layout

### 6.3 Dynamic Active Menu
- [ ] Highlight active menu item based on URL
- [ ] Helper function: `isActive($route)`

### 6.4 Flash Messages
- [ ] Success messages
- [ ] Error messages
- [ ] Warning messages
- [ ] Info messages

### 6.5 Page-Specific Styles/Scripts
- [ ] `@section('styles')` in head
- [ ] `@section('scripts')` before </body>

---

## 🎯 PHASE 7: View Class Enhancement (2 saat)

### 7.1 Extend Method Implementation
```php
// core/View.php
private $layout = null;
private $sections = [];
private $currentSection = null;

public function compileDirectives($content) {
    // @extends
    $content = preg_replace('/@extends\([\'"](.+?)[\'"]\)/', '<?php $this->layout = "$1"; ?>', $content);
    
    // @section
    $content = preg_replace('/@section\([\'"](.+?)[\'"]\)/', '<?php $this->startSection("$1"); ?>', $content);
    
    // @endsection
    $content = preg_replace('/@endsection/', '<?php $this->endSection(); ?>', $content);
    
    // @yield
    $content = preg_replace('/@yield\([\'"](.+?)[\'"]\)/', '<?php echo $this->yieldSection("$1"); ?>', $content);
    
    // @include
    $content = preg_replace('/@include\([\'"](.+?)[\'"]\)/', '<?php echo $this->renderPartial("$1"); ?>', $content);
    
    return $content;
}
```

### 7.2 Section Management Methods
- [ ] `startSection($name)`
- [ ] `endSection()`
- [ ] `yieldSection($name, $default = '')`
- [ ] `renderPartial($template)`

---

## 🎯 PHASE 8: Testing & Cleanup (2 saat)

### 8.1 Test All Pages
- [ ] Admin Dashboard
- [ ] All User pages
- [ ] All Hotel pages
- [ ] Cache Statistics
- [ ] Settings
- [ ] Logs
- [ ] Customer Dashboard
- [ ] Login/Register

### 8.2 Cleanup Old Files
- [ ] Remove old `_partials/layout.php` if not used
- [ ] Remove duplicate styles
- [ ] Remove duplicate scripts

### 8.3 Documentation
- [ ] Update README with layout usage
- [ ] Add example views
- [ ] Document available sections
- [ ] Document available partials

---

## 📅 Timeline Summary

| Phase | Task | Time | Status |
|-------|------|------|--------|
| **1** | Layout System Development | 3h | ⏺️ Pending |
| **2** | Partials Creation | 2h | ⏺️ Pending |
| **3** | Admin Views Conversion | 4h | ⏺️ Pending |
| **4** | Customer Views Conversion | 2h | ⏺️ Pending |
| **5** | Guest Views Conversion | 2h | ⏺️ Pending |
| **6** | Dynamic Features | 3h | ⏺️ Pending |
| **7** | View Class Enhancement | 2h | ⏺️ Pending |
| **8** | Testing & Cleanup | 2h | ⏺️ Pending |
| **TOTAL** | | **20 hours** | |

---

## 📊 Benefits

### Before (Current State):
```php
// Every view repeats:
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="bootstrap.css">
    ...
</head>
<body>
    <nav>...</nav>
    <menu>...</menu>
    
    <!-- Actual content -->
    <h1>Page Title</h1>
    
    <script src="jquery.js"></script>
</body>
</html>
```
❌ ~400 lines per view
❌ Duplicate code
❌ Hard to maintain
❌ Menu changes = 20+ files

### After (Layout System):
```php
@extends('layouts.admin')

@section('content')
    <h1>Page Title</h1>
    <p>Actual content here</p>
@endsection
```
✅ ~10-20 lines per view
✅ DRY (Don't Repeat Yourself)
✅ Easy to maintain
✅ Menu changes = 1 file

---

## 🎯 Implementation Priority

### High Priority (Start with these):
1. ✅ View Class Enhancement (PHASE 7) - Must do first
2. ✅ Admin Base Layout (PHASE 3.1)
3. ✅ Admin Partials (PHASE 2.1)
4. ✅ Convert 2-3 admin views as POC

### Medium Priority:
5. Convert remaining admin views
6. Customer layout & views
7. Guest layout & views

### Low Priority:
8. Dynamic features
9. Advanced features
10. Full cleanup

---

## 🚀 Quick Start Guide

### Step 1: Enhance View Class
```bash
# Add directive support to core/View.php
@extends, @section, @yield, @include
```

### Step 2: Create Base Layout
```bash
# Create resources/views/layouts/admin.php
# Move navbar, menu, scripts to partials
```

### Step 3: Convert One View as POC
```bash
# Convert admin/dashboard/index.php
# Test thoroughly
# If works, proceed with others
```

### Step 4: Rollout
```bash
# Convert all admin views (10 files)
# Convert customer views (3 files)
# Convert guest views (5 files)
```

---

## 📝 Notes

- **Backward Compatibility:** Keep old views working during transition
- **Testing:** Test each converted view immediately
- **Incremental:** Convert views one by one, not all at once
- **Rollback Plan:** Git commit after each successful conversion

---

## ✅ Success Criteria

- [ ] All views use base layout
- [ ] No duplicate HTML/CSS/JS
- [ ] Menu change updates all pages
- [ ] Page loads 30% faster (less HTML)
- [ ] Code reduced by 70%
- [ ] Easy to add new pages

---

**Status:** 📋 PLANNED
**Total Effort:** ~20 hours (2.5 days)
**ROI:** High - Much easier maintenance long-term
