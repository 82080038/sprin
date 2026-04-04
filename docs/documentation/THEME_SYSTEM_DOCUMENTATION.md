# 🎨 SPRIN Theme System Documentation

## 📋 Overview

The SPRIN application features a comprehensive theme system that provides automatic adaptation to user preferences, enhanced accessibility, and consistent visual design across all pages.

## ✨ Key Features

### 🌓 Automatic Theme Detection
- Detects browser's preferred color scheme (light/dark mode)
- Automatically switches themes based on system settings
- Supports manual theme switching with localStorage persistence

### ♿ Accessibility Support
- **High Contrast Mode**: Enhanced visibility for users with visual impairments
- **Reduced Motion**: Respects user's motion preferences to prevent vestibular disorders
- **WCAG 2.1 Compliance**: Proper contrast ratios and semantic HTML
- **Screen Reader Support**: ARIA labels and proper element structure

### 📱 Responsive Design System
- Mobile-first approach with breakpoints for all screen sizes
- Touch-friendly interface elements
- Optimized layouts for different device categories

## 🎯 Theme Variables

### Light Theme (Default)
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

### Dark Theme
```css
@media (prefers-color-scheme: dark) {
    :root {
        --primary-color: #3949ab;
        --secondary-color: #5e35b1;
        --accent-color: #ffd700;
        --text-primary: #ffffff;
        --text-secondary: #b3b3b3;
        --text-light: #ffffff;
        --bg-primary: #1a1a1a;
        --bg-secondary: #2d2d2d;
        --bg-tertiary: #404040;
        --border-color: #404040;
        --shadow-color: rgba(0, 0, 0, 0.3);
        --hover-bg: rgba(255, 255, 255, 0.1);
    }
}
```

### High Contrast Mode
```css
@media (prefers-contrast: high) {
    :root {
        --primary-color: #0000ff;
        --secondary-color: #000080;
        --accent-color: #ffff00;
        --text-primary: #000000;
        --text-secondary: #333333;
        --border-color: #000000;
        --shadow-color: rgba(0, 0, 0, 0.5);
    }
}
```

## 🔧 Implementation

### Core Files
- **`includes/components/header.php`**: Main theme variables, JavaScript theme management
- **`public/assets/css/responsive.css`**: Responsive design system and breakpoints
- **Page-specific PHP files**: Theme integration for individual pages

### JavaScript Theme Management
```javascript
// Theme detection and switching
window.switchTheme = function(theme) {
    applyTheme(theme);
};

window.getCurrentTheme = function() {
    return document.documentElement.getAttribute('data-theme') || 'light';
};

// Automatic system preference detection
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
        applyTheme(e.matches ? 'dark' : 'light');
    }
});
```

## 📱 Responsive Breakpoints

| Device | Width Range | Features |
|--------|-------------|----------|
| Mobile | 320px - 575px | Touch-optimized, simplified navigation |
| Tablet | 576px - 991px | Balanced layout, enhanced features |
| Desktop | 992px - 1199px | Full feature set, optimal spacing |
| Large Desktop | 1200px+ | Enhanced experience, maximum content |

## 🎨 Component Styling

### Navigation
- Consistent navbar styling across themes
- Dropdown menus with proper contrast
- Mobile-responsive hamburger menu

### Cards & Tables
- Theme-aware backgrounds and borders
- Proper text contrast in all themes
- Hover states with theme-appropriate colors

### Forms & Inputs
- Consistent styling across all themes
- Focus states with proper visibility
- Error states with appropriate colors

### Buttons
- Primary, secondary, and outline variants
- Theme-appropriate hover states
- Accessibility-compliant contrast ratios

## 🔄 Theme Switching

### Automatic Detection
1. Checks browser's `prefers-color-scheme` media query
2. Applies appropriate theme on page load
3. Updates meta theme-color for mobile browsers

### Manual Switching
1. User can manually select theme (if implemented)
2. Preference saved in localStorage
3. Overrides system preference until reset

### System Preference Changes
1. Listens for system theme changes
2. Automatically updates if no manual preference set
3. Provides seamless experience across OS changes

## ♿ Accessibility Features

### Contrast Ratios
- All text elements meet WCAG AA standards (4.5:1 minimum)
- Large text meets WCAG AAA standards (7:1 minimum)
- Interactive elements have enhanced contrast

### Focus Management
- Visible focus indicators in all themes
- Logical tab order through interactive elements
- Skip links for keyboard navigation

### Screen Reader Support
- Semantic HTML5 elements
- ARIA labels where appropriate
- Alternative text for images
- Proper heading hierarchy

## 🛠️ Customization

### Adding New Themes
1. Define new CSS variables in `header.php`
2. Add media query for automatic detection
3. Update JavaScript theme switching logic
4. Test across all components

### Modifying Colors
1. Update CSS variables in `header.php`
2. Test contrast ratios with accessibility tools
3. Verify appearance across all themes
4. Update documentation

### Adding Breakpoints
1. Define new media queries in `responsive.css`
2. Add appropriate styling adjustments
3. Test on actual devices
4. Update documentation

## 🧪 Testing

### Theme Testing Checklist
- [ ] Light mode displays correctly
- [ ] Dark mode displays correctly
- [ ] High contrast mode works
- [ ] Reduced motion is respected
- [ ] All pages use theme variables
- [ ] No hardcoded colors remain
- [ ] Contrast ratios meet WCAG standards
- [ ] Responsive design works across breakpoints

### Browser Testing
- Chrome, Firefox, Safari, Edge
- Mobile browsers (iOS Safari, Chrome Mobile)
- System preference detection
- Theme switching functionality

### Accessibility Testing
- Screen reader compatibility
- Keyboard navigation
- Contrast validation tools
- Focus management

## 📚 References

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [CSS Custom Properties](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)
- [Media Queries for Prefers](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-color-scheme)
- [Bootstrap 5 Theming](https://getbootstrap.com/docs/5.3/customize/overview/)

## 📈 Future Enhancements

### Planned Features
- [ ] Custom theme selection UI
- [ ] Theme preview functionality
- [ ] Export/import theme preferences
- [ ] Seasonal themes
- [ ] Brand color customization

### Improvements
- [ ] Performance optimization
- [ ] Enhanced animation system
- [ ] More granular control options
- [ ] Theme sharing capabilities

---

**Last Updated**: April 2, 2026  
**Version**: v1.2.0  
**Maintainer**: SPRIN Development Team
