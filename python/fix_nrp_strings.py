#!/usr/bin/env python3
"""
Final fix: Store NRP as strings to preserve leading zeros
"""

import json

def fix_nrp_as_strings():
    """Convert all NRP values to strings with proper 8-digit format"""
    
    # Load JSON data
    with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'r', encoding='utf-8') as f:
        json_data = json.load(f)
    
    # Load CSV for reference
    csv_mapping = {}
    import csv
    with open('/opt/lampp/htdocs/sprin/file/data_personel_lengkap.csv', 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            name = row['nama'].strip('"').strip()
            nrp = row['nrp'].strip()
            csv_mapping[name] = nrp
    
    changes_made = []
    
    # Process each person
    for person in json_data['personil_data']:
        current_nrp = str(person['nrp'])
        
        # Find matching name in CSV
        csv_name = None
        for name in csv_mapping.keys():
            name_clean = name.lower().replace('"', '').strip()
            person_name_clean = person['nama'].lower().replace('"', '').strip()
            if ' '.join(name_clean.split()) == ' '.join(person_name_clean.split()):
                csv_name = name
                break
        
        if csv_name:
            correct_nrp = csv_mapping[csv_name]
            if len(correct_nrp) == 8:
                old_nrp = str(person['nrp'])
                person['nrp'] = correct_nrp  # Store as string
                if old_nrp != correct_nrp:
                    changes_made.append({
                        'id': person['id'],
                        'nama': person['nama'],
                        'old_nrp': old_nrp,
                        'new_nrp': correct_nrp
                    })
    
    # Save updated JSON
    with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'w', encoding='utf-8') as f:
        json.dump(json_data, f, indent=2, ensure_ascii=False)
    
    # Print summary
    print(f"✅ Final fix: Updated {len(changes_made)} NRP values to strings:")
    for change in changes_made[:10]:  # Show first 10
        print(f"  ID {change['id']}: {change['nama'][:30]}... {change['old_nrp']} → {change['new_nrp']}")
    if len(changes_made) > 10:
        print(f"  ... and {len(changes_made) - 10} more")
    
    return len(changes_made)

if __name__ == "__main__":
    changes = fix_nrp_as_strings()
    print(f"\n🎯 Total NRP values converted to strings: {changes}")
