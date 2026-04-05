---
description: Theme system development and management
---

# Theme System Development Skill

## Overview

Skill ini digunakan untuk mengembangkan dan mengelola theme system di aplikasi SPRIN v1.2.0 dengan fitur automatic theme detection dan CSS variables.

## Current Theme System (v1.2.0)

### Features
- **Automatic Theme Detection**: Mendeteksi preferensi browser (light/dark)
- **CSS Variables System**: Pengelolaan warna terpusat
- **High Contrast Mode**: Aksesibilitas compliance
- **Reduced Motion Support**: Respek user preferences
- **Cross-Platform Consistency**: Theme konsisten di semua halaman

### Theme Variables
```css
:root {
    --primary-color: #1a237e;
    --secondary-color: #3949ab;
    --accent-color: #ffd700;
    --text-primary: #212529;
    --text-secondary: #6c757d;
    --text-light: #ffffff;
    --bg-primary: #ffffff;
    --bg-secondary: #f8f9fa;
    --bg-tertiary: #e9ecef;
    --border-color: #dee2e6;
    --shadow-color: rgba(0, 0, 0, 0.1);
    --hover-bg: rgba(0, 0, 0, 0.05);
}
```

## Development Tasks

### 1. Adding New Theme Colors

#### Step 1: Update CSS Variables
```css
/* Di public/assets/css/responsive.css */
:root {
    /* Existing colors */
    --primary-color: #1a237e;
    
    /* New colors */
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
}
```

#### Step 2: Update Dark Mode
```css
@media (prefers-color-scheme: dark) {
    :root {
        --success-color: #34ce57;
        --warning-color: #ffdd57;
        --danger-color: #e74c3c;
        --info-color: #3498db;
    }
}
```

#### Step 3: Apply to Components
```css
.btn-success {
    background-color: var(--success-color);
    border-color: var(--success-color);
}

.alert-warning {
    background-color: var(--warning-color);
    color: var(--text-primary);
}
```

### 2. Creating Theme Variants

#### Light Theme Enhancement
```css
[data-theme="light"] {
    --primary-color: #1976d2;
    --bg-primary: #ffffff;
    --text-primary: #212529;
}
```

#### Dark Theme Enhancement
```css
[data-theme="dark"] {
    --primary-color: #90caf9;
    --bg-primary: #121212;
    --text-primary: #ffffff;
}
```

#### High Contrast Theme
```css
@media (prefers-contrast: high) {
    :root {
        --border-color: #000000;
        --text-primary: #000000;
        --bg-primary: #ffffff;
    }
}
```

### 3. Theme Switching Implementation

#### JavaScript Theme Controller
```javascript
// Di public/assets/js/theme-controller.js
class ThemeController {
    constructor() {
        this.currentTheme = this.detectTheme();
        this.init();
    }
    
    detectTheme() {
        if (localStorage.getItem('theme')) {
            return localStorage.getItem('theme');
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    
    setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        this.currentTheme = theme;
        this.updateThemeIcon(theme);
    }
    
    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
    }
    
    updateThemeIcon(theme) {
        const icon = document.querySelector('.theme-toggle i');
        if (icon) {
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    }
    
    init() {
        this.setTheme(this.currentTheme);
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }
}

// Initialize theme controller
document.addEventListener('DOMContentLoaded', () => {
    window.themeController = new ThemeController();
});
```

#### Theme Toggle Button
```html
<!-- Di header component -->
<button class="btn btn-outline-secondary theme-toggle" onclick="themeController.toggleTheme()">
    <i class="fas fa-moon"></i>
</button>
```

### 4. Responsive Theme Updates

#### Mobile-First Theme
```css
/* Base theme (mobile) */
:root {
    --font-size-base: 14px;
    --spacing-unit: 0.5rem;
    --border-radius: 0.25rem;
}

/* Tablet theme */
@media (min-width: 576px) {
    :root {
        --font-size-base: 16px;
        --spacing-unit: 0.75rem;
        --border-radius: 0.375rem;
    }
}

/* Desktop theme */
@media (min-width: 992px) {
    :root {
        --font-size-base: 18px;
        --spacing-unit: 1rem;
        --border-radius: 0.5rem;
    }
}
```

### 5. Component Theme Integration

#### Card Component Theming
```css
.card {
    background-color: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-color);
}

.card-header {
    background-color: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
}

.card-body {
    color: var(--text-primary);
}
```

#### Modal Component Theming
```css
.modal-content {
    background-color: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
}

.modal-header {
    background-color: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}

.modal-footer {
    background-color: var(--bg-tertiary);
    border-top: 1px solid var(--border-color);
}
```

### 6. Animation and Transitions

#### Reduced Motion Support
```css
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
```

#### Theme Transitions
```css
:root {
    --transition-fast: 0.15s ease-in-out;
    --transition-normal: 0.3s ease-in-out;
    --transition-slow: 0.5s ease-in-out;
}

.btn {
    transition: background-color var(--transition-fast),
                border-color var(--transition-fast),
                color var(--transition-fast);
}

.card {
    transition: box-shadow var(--transition-normal),
                transform var(--transition-normal);
}
```

## Testing Theme System

### 1. Manual Testing
```bash
# Test theme detection
curl -s "http://localhost/sprint/" | grep -i "prefers-color-scheme"

# Test theme variables
curl -s "http://localhost/sprint/" | grep -i "css variables"
```

### 2. Browser Testing
- Chrome DevTools: Device Mode + Dark Mode simulation
- Firefox: Responsive Design Mode + Theme testing
- Safari: Develop menu + Theme testing

### 3. Accessibility Testing
```bash
# Check contrast ratios
# Use browser extension or online tool
# Target ratios: 4.5:1 (normal), 3:1 (large text)
```

## Theme Maintenance

### 1. Regular Updates
- Review color palette quarterly
- Test new browser theme features
- Update accessibility compliance
- Optimize CSS performance

### 2. Documentation Updates
- Update theme documentation in header.php
- Maintain CSS variable reference
- Document new theme features
- Update development guidelines

### 3. Performance Monitoring
```bash
# Check CSS file size
du -sh /opt/lampp/htdocs/sprint/public/assets/css/responsive.css

# Test CSS loading performance
curl -w "@curl-format.txt" -o /dev/null -s "http://localhost/sprint/public/assets/css/responsive.css"
```

## Troubleshooting

### Common Issues

#### Theme Not Applying
```bash
# Check CSS loading
curl -I "http://localhost/sprint/public/assets/css/responsive.css"

# Check CSS variables
grep -n "primary-color" /opt/lampp/htdocs/sprint/public/assets/css/responsive.css
```

#### Dark Mode Issues
```bash
# Check media queries
grep -n "prefers-color-scheme" /opt/lampp/htdocs/sprint/public/assets/css/responsive.css

# Test dark mode simulation
# Use browser dev tools to simulate dark mode
```

#### Performance Issues
```bash
# Minimize CSS (if needed)
# Use CSS optimization tools
# Check for unused CSS
```

## Best Practices

### 1. CSS Variables Usage
- Use semantic variable names
- Group related variables
- Provide fallback values
- Document variable purposes

### 2. Theme Consistency
- Apply theme variables consistently
- Test across all components
- Maintain visual hierarchy
- Ensure accessibility compliance

### 3. Performance Optimization
- Minimize CSS complexity
- Use efficient selectors
- Optimize for mobile first
- Test loading performance

## Future Enhancements

### 1. Advanced Theme Features
- Custom theme builder
- Theme import/export
- Seasonal themes
- Brand customization

### 2. Accessibility Improvements
- Enhanced contrast modes
- Screen reader optimization
- Keyboard navigation themes
- Colorblind-friendly themes

### 3. Performance Enhancements
- CSS critical path optimization
- Theme lazy loading
- Component-based theming
- Dynamic theme switching
