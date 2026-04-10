#!/usr/bin/env python3
"""
Simple HTML Structure Validator
Checks for common HTML structure issues
"""

import requests
import re
from pathlib import Path

BASE_URL = "http://localhost/sprin/pages/jabatan.php"

def check_html_structure():
    """Check HTML structure of jabatan page"""
    print("=" * 70)
    print("HTML STRUCTURE VALIDATOR")
    print("=" * 70)
    
    try:
        # Fetch the page
        print(f"\nFetching: {BASE_URL}")
        response = requests.get(BASE_URL, timeout=10)
        
        if response.status_code != 200:
            print(f"❌ Failed to fetch page: HTTP {response.status_code}")
            return False
        
        html = response.text
        
        # Check for unclosed tags (basic check)
        print("\n[1] Checking tag balance...")
        
        # Count opening and closing divs
        opening_divs = len(re.findall(r'<div[^>]*>', html, re.IGNORECASE))
        closing_divs = len(re.findall(r'</div>', html, re.IGNORECASE))
        
        print(f"   Opening <div>: {opening_divs}")
        print(f"   Closing </div>: {closing_divs}")
        
        if opening_divs != closing_divs:
            print(f"   ⚠️  MISMATCH: {opening_divs - closing_divs} unclosed div(s)")
        else:
            print(f"   ✅ Balanced")
        
        # Check for jabatan-card structure
        print("\n[2] Checking jabatan-card structure...")
        card_count = len(re.findall(r'jabatan-card', html))
        print(f"   Found {card_count} jabatan-card elements")
        
        # Check for row/col structure
        print("\n[3] Checking grid structure...")
        row_count = len(re.findall(r'class="row"', html))
        col_md_6_count = len(re.findall(r'col-md-6', html))
        print(f"   Rows: {row_count}")
        print(f"   col-md-6 columns: {col_md_6_count}")
        
        # Check for sortable containers
        print("\n[4] Checking sortable containers...")
        sortable_count = len(re.findall(r'sortable-container', html))
        print(f"   Sortable containers: {sortable_count}")
        
        # Check for drag handles
        drag_handle_count = len(re.findall(r'drag-handle', html))
        print(f"   Drag handles: {drag_handle_count}")
        
        # Check HTML size
        print("\n[5] Page statistics...")
        print(f"   HTML size: {len(html)} bytes")
        print(f"   Lines: {len(html.splitlines())}")
        
        # Check for common errors
        print("\n[6] Checking for common errors...")
        
        errors = []
        
        # Check for nested forms (can cause issues)
        if re.search(r'<form[^>]*>.*<form', html, re.DOTALL):
            errors.append("Nested forms found")
        
        # Check for duplicate IDs
        ids = re.findall(r'id="([^"]+)"', html)
        duplicate_ids = [id for id in set(ids) if ids.count(id) > 1]
        if duplicate_ids:
            errors.append(f"Duplicate IDs: {duplicate_ids[:5]}")
        
        # Check for broken tags
        broken_tags = re.findall(r'<[a-z]+[^>]*[^/]>', html)
        if len(broken_tags) > 100:  # Arbitrary threshold
            errors.append(f"Many unclosed tags ({len(broken_tags)})")
        
        if errors:
            for error in errors:
                print(f"   ⚠️  {error}")
        else:
            print(f"   ✅ No common errors found")
        
        print("\n" + "=" * 70)
        print("VALIDATION COMPLETE")
        print("=" * 70)
        
        return len(errors) == 0 and opening_divs == closing_divs
        
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

if __name__ == '__main__':
    success = check_html_structure()
    exit(0 if success else 1)
