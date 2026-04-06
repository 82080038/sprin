#!/usr/bin/env python3
"""
Script to fix NRP values in personil_complete_data.json
Updates NRP values that have less than 8 digits with correct values from CSV
"""

import json
import csv
import re

def load_json_data(filepath):
    """Load JSON data from file"""
    with open(filepath, 'r', encoding='utf-8') as f:
        return json.load(f)

def load_csv_data(filepath):
    """Load CSV data and create name-to-nrp mapping"""
    name_to_nrp = {}
    with open(filepath, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            # Clean up name for matching
            name = row['nama'].strip('"').strip()
            nrp = row['nrp'].strip()
            name_to_nrp[name] = nrp
    return name_to_nrp

def find_closest_match(target_name, name_list):
    """Find closest matching name"""
    target_clean = target_name.lower().replace('"', '').strip()
    
    for name in name_list:
        name_clean = name.lower().replace('"', '').strip()
        # Remove extra spaces and compare
        if ' '.join(target_clean.split()) == ' '.join(name_clean.split()):
            return name
    return None

def fix_nrp_values():
    """Main function to fix NRP values"""
    # Load data
    json_data = load_json_data('/opt/lampp/htdocs/sprin/file/personil_complete_data.json')
    csv_mapping = load_csv_data('/opt/lampp/htdocs/sprin/file/data_personel_lengkap.csv')
    
    # Track changes
    changes_made = []
    personil_list = json_data['personil_data']
    
    # Process each person
    for person in personil_list:
        current_nrp = str(person['nrp'])
        
        # Check if NRP has less than 8 digits and is not empty/null
        if current_nrp and current_nrp != 'null' and len(current_nrp.replace('0', '')) < 8:
            # Try to find matching name in CSV
            csv_name = find_closest_match(person['nama'], csv_mapping.keys())
            
            if csv_name:
                correct_nrp = csv_mapping[csv_name]
                if len(correct_nrp) == 8:  # Ensure correct format
                    person['nrp'] = int(correct_nrp) if correct_nrp.isdigit() else correct_nrp
                    changes_made.append({
                        'id': person['id'],
                        'nama': person['nama'],
                        'old_nrp': current_nrp,
                        'new_nrp': correct_nrp
                    })
    
    # Save updated JSON
    with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'w', encoding='utf-8') as f:
        json.dump(json_data, f, indent=2, ensure_ascii=False)
    
    # Print summary
    print(f"✅ Updated {len(changes_made)} NRP values:")
    for change in changes_made:
        print(f"  ID {change['id']}: {change['nama'][:30]}... {change['old_nrp']} → {change['new_nrp']}")
    
    return len(changes_made)

if __name__ == "__main__":
    changes = fix_nrp_values()
    print(f"\n🎯 Total NRP values fixed: {changes}")
