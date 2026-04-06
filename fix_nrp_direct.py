#!/usr/bin/env python3
"""
SOLUSI LANGSUNG - Update JSON file in place
Berdasarkan solusi dari Stack Overflow
"""

import json
import csv

def update_json_inplace():
    """Update JSON file langsung dengan mode r+"""
    
    print("🔧 UPDATE LANGSUNG personil_complete_data.json")
    
    # Load CSV data
    csv_lookup = {}
    with open('file/DATA PERS FEBRUARI 2026 NEW.csv', 'r', encoding='utf-8') as f:
        reader = csv.reader(f, delimiter=';')
        next(reader)  # Skip header
        
        for row in reader:
            if len(row) >= 4 and row[0].strip():
                name = row[1].strip()
                nrp = row[3].strip()
                if name and nrp and len(nrp) == 8:
                    csv_lookup[name] = nrp
    
    print(f"✅ Loaded {len(csv_lookup)} records from CSV")
    
    # Update JSON file in place
    with open('file/personil_complete_data.json', 'r+', encoding='utf-8') as f:
        # Load existing data
        f.seek(0)
        data = json.load(f)
        
        # Update data
        updated_count = 0
        for personil in data['personil_data']:
            nrp = str(personil.get('nrp', '')).strip()
            
            if nrp and len(nrp) < 8 and personil['nama'] in csv_lookup:
                old_nrp = nrp
                new_nrp = csv_lookup[personil['nama']]
                
                print(f"  {personil['nama']}: {old_nrp} → {new_nrp}")
                personil['nrp'] = int(new_nrp)
                updated_count += 1
        
        # Write back to file
        f.seek(0)
        json.dump(data, f, indent=2, ensure_ascii=False)
        f.truncate()
        
        print(f"✅ Updated {updated_count} records")
        print("✅ File saved in place")

if __name__ == "__main__":
    update_json_inplace()
