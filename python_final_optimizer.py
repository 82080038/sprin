#!/usr/bin/env python3
"""
Final Python Optimizer for SPRIN Application
Complete optimization and performance enhancement
"""

import os
import re
import json
import subprocess
from pathlib import Path
from typing import Dict, List, Tuple

class SPRINFinalOptimizer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.optimizations = []
        
    def optimize_responsive_design(self):
        """Optimize responsive design for better mobile experience"""
        css_file = self.base_path / 'public' / 'assets' / 'css' / 'responsive.css'
        
        if not css_file.exists():
            print(f"CSS file not found: {css_file}")
            return False
        
        try:
            with open(css_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Add comprehensive responsive improvements
            responsive_optimizations = """
/* Python Final Optimizer - Responsive Design Improvements */

/* Enhanced Mobile Experience */
@media (max-width: 768px) {
    .container {
        max-width: 95%;
        padding: 10px;
    }
    
    .login-container {
        flex-direction: column;
        max-width: 95%;
        margin: 10px auto;
        border-radius: 15px;
    }
    
    .login-left, .login-right {
        padding: 20px;
        text-align: center;
    }
    
    .form-control {
        font-size: 16px !important; /* Prevent zoom on iOS */
        padding: 12px;
        margin: 8px 0;
    }
    
    .btn {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
    }
    
    .logo {
        font-size: 2rem;
        margin-bottom: 20px;
    }
    
    .card {
        margin: 10px 0;
        border-radius: 10px;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}

/* Ultra Mobile */
@media (max-width: 480px) {
    .login-container {
        margin: 5px;
        padding: 15px;
    }
    
    .logo {
        font-size: 1.5rem;
    }
    
    h1 {
        font-size: 1.5rem;
    }
    
    h2 {
        font-size: 1.3rem;
    }
    
    .form-control {
        font-size: 14px;
        padding: 10px;
    }
    
    .btn {
        padding: 10px;
        font-size: 14px;
    }
}

/* Tablet Optimization */
@media (min-width: 769px) and (max-width: 1024px) {
    .container {
        max-width: 90%;
    }
    
    .login-container {
        max-width: 80%;
    }
    
    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

/* Desktop Enhancements */
@media (min-width: 1025px) {
    .container {
        max-width: 1200px;
    }
    
    .login-container {
        max-width: 800px;
    }
}

/* Form Input Improvements */
input[type="text"],
input[type="password"],
input[type="email"],
input[type="search"],
textarea,
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

input[type="text"]:focus,
input[type="password"]:focus,
input[type="email"]:focus,
input[type="search"]:focus,
textarea:focus,
select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
    outline: none;
}

/* Button Improvements */
.btn {
    border-radius: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
    font-weight: 500;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn:active {
    transform: translateY(0);
}

/* Card Improvements */
.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

/* Navigation Improvements */
.navbar {
    border-radius: 0 0 12px 12px;
}

.nav-link {
    border-radius: 6px;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background-color: var(--hover-bg);
}

/* Table Improvements */
.table {
    border-radius: 8px;
    overflow: hidden;
}

.table th {
    background-color: var(--bg-secondary);
    font-weight: 600;
    border-bottom: 2px solid var(--border-color);
}

.table td {
    border-bottom: 1px solid var(--border-color);
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.spinner {
    border: 3px solid var(--border-color);
    border-top: 3px solid var(--primary-color);
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Accessibility Improvements */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Focus Improvements */
:focus-visible {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Print Styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        font-size: 12pt;
        line-height: 1.4;
    }
    
    .container {
        max-width: 100%;
        margin: 0;
        padding: 0;
    }
}
"""
            
            content += responsive_optimizations
            
            with open(css_file, 'w', encoding='utf-8') as f:
                f.write(content)
            
            self.optimizations.append({
                'type': 'responsive_design',
                'file': str(css_file),
                'improvements': 'Added comprehensive responsive design rules'
            })
            
            print(f"✅ Optimized responsive design in {css_file}")
            return True
            
        except Exception as e:
            print(f"Error optimizing responsive design: {e}")
            return False

    def optimize_calendar_page(self):
        """Fix calendar page issues"""
        calendar_file = self.base_path / 'pages' / 'calendar_dashboard.php'
        
        if not calendar_file.exists():
            print(f"Calendar file not found: {calendar_file}")
            return False
        
        try:
            with open(calendar_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Fix common calendar issues
            fixes_applied = []
            
            # Fix 1: Ensure proper authentication
            if 'AuthHelper::validateSession()' not in content:
                auth_fix = '''<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Start session using SessionManager
SessionManager::start();

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}
?>'''
                
                if content.startswith('<?php'):
                    content = auth_fix + content[content.find('?>')+2:]
                    fixes_applied.append('Added proper authentication')
            
            # Fix 2: Add proper database connection
            if 'DB_HOST' not in content and 'new PDO' in content:
                db_fix = '''// Initialize database connection
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';unix_socket=' . DB_SOCKET,
        DB_USER, 
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo '<div class="alert alert-danger">Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}
'''
                
                content = db_fix + content
                fixes_applied.append('Fixed database connection')
            
            # Write back if changed
            if fixes_applied:
                with open(calendar_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                
                self.optimizations.append({
                    'type': 'calendar_fix',
                    'file': str(calendar_file),
                    'fixes': fixes_applied
                })
                
                print(f"✅ Fixed calendar page: {', '.join(fixes_applied)}")
                return True
        
        except Exception as e:
            print(f"Error fixing calendar page: {e}")
            return False

    def optimize_performance(self):
        """Add performance optimizations"""
        # Create performance optimization file
        perf_file = self.base_path / 'public' / 'assets' / 'js' / 'performance.js'
        
        performance_js = '''
// Performance Optimizations for SPRIN Application
document.addEventListener('DOMContentLoaded', function() {
    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    
    // Debounce function for search
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Optimized search functionality
    const searchInputs = document.querySelectorAll('input[type="search"], input[name*="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function(e) {
            // Perform search
            const query = e.target.value;
            if (query.length > 2) {
                // Trigger search AJAX
                performSearch(query);
            }
        }, 300));
    });
    
    // Form validation optimization
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    });
    
    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Loading states for buttons
    const buttons = document.querySelectorAll('.btn[type="submit"]');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            this.classList.add('loading');
            this.disabled = true;
            
            // Re-enable after 5 seconds (fallback)
            setTimeout(() => {
                this.classList.remove('loading');
                this.disabled = false;
            }, 5000);
        });
    });
});

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Search function
function performSearch(query) {
    // Implementation depends on specific search requirements
    console.log('Searching for:', query);
}

// Export for global use
window.SPRINPerformance = {
    showNotification,
    debounce
};
'''
        
        try:
            perf_file.parent.mkdir(parents=True, exist_ok=True)
            with open(perf_file, 'w', encoding='utf-8') as f:
                f.write(performance_js)
            
            self.optimizations.append({
                'type': 'performance',
                'file': str(perf_file),
                'improvements': 'Added performance optimizations'
            })
            
            print(f"✅ Created performance optimization file: {perf_file}")
            return True
            
        except Exception as e:
            print(f"Error creating performance file: {e}")
            return False

    def run_final_optimization(self):
        """Run all optimizations"""
        print("🚀 Starting Final Python Optimization for SPRIN Application...")
        
        optimizations = [
            self.optimize_responsive_design(),
            self.optimize_calendar_page(),
            self.optimize_performance()
        ]
        
        successful_optimizations = sum(1 for opt in optimizations if opt)
        
        # Generate report
        report = {
            'timestamp': subprocess.check_output(['date'], text=True).strip(),
            'optimizations_applied': successful_optimizations,
            'total_optimizations': len(optimizations),
            'details': self.optimizations
        }
        
        report_file = self.base_path / 'python_final_optimization_report.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, default=str)
        
        print(f"\n📊 Final Optimization Summary:")
        print(f"Optimizations Applied: {successful_optimizations}/{len(optimizations)}")
        print(f"Report saved to: {report_file}")
        
        return successful_optimizations > 0

def main():
    """Main execution"""
    optimizer = SPRINFinalOptimizer()
    success = optimizer.run_final_optimization()
    
    if success:
        print("\n🎉 Final optimization completed successfully!")
    else:
        print("\n❌ Some optimizations failed")
    
    return success

if __name__ == "__main__":
    main()
