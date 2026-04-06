#!/usr/bin/env python3
"""
Fix NRP in personil_complete_data.json using data from CSV
This script will update NRP with less than 8 digits using correct NRP from CSV
"""

import json
import csv
from datetime import datetime

def load_json_data():
    """Load personil_complete_data.json"""
    print("📖 Loading JSON data...")
    
    with open('file/personil_complete_data.json', 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    print(f"✅ Loaded {len(data['personil_data'])} records from JSON")
    return data

def load_csv_data():
    """Load CSV data"""
    print("📖 Loading CSV data...")
    
    csv_data = []
    with open('file/DATA PERS FEBRUARI 2026 NEW.csv', 'r', encoding='utf-8') as f:
        reader = csv.reader(f, delimiter=';')
        
        # Skip header
        next(reader)
        
        for row in reader:
            if len(row) >= 4 and row[0].strip():
                try:
                    record_id = row[0].strip()
                    name = row[1].strip()
                    pangkat = row[2].strip()
                    nrp = row[3].strip()
                    jabatan = row[4].strip() if len(row) > 4 else ''
                    
                    if record_id and name and nrp:
                        csv_data.append({
                            'id': record_id,
                            'nama': name,
                            'pangkat': pangkat,
                            'nrp': nrp,
                            'jabatan': jabatan
                        })
                except Exception as e:
                    continue
    
    print(f"✅ Loaded {len(csv_data)} records from CSV")
    return csv_data

def find_short_nrp_records(json_data):
    """Find records with NRP < 8 digits"""
    print("🔍 Finding records with short NRP...")
    
    short_nrp_records = []
    for personil in json_data['personil_data']:
        nrp = str(personil.get('nrp', '')).strip()
        if nrp and nrp != '' and len(nrp) < 8:
            short_nrp_records.append(personil)
    
    print(f"📊 Found {len(short_nrp_records)} records with NRP < 8 digits")
    return short_nrp_records

def find_matching_csv_record(csv_data, personil_name):
    """Find matching record in CSV data"""
    # Try exact match first
    for record in csv_data:
        if record['nama'].strip().lower() == personil_name.strip().lower():
            return record
    
    # Try partial match (handle name variations)
    personil_words = personil_name.lower().split()
    for record in csv_data:
        csv_words = record['nama'].lower().split()
        
        # Check if most words match
        match_count = 0
        for word in personil_words:
            if word in csv_words:
                match_count += 1
        
        # If at least 2 words match, consider it a match
        if match_count >= 2 and len(personil_words) >= 2:
            return record
    
    return None

def update_nrp_in_json(json_data, csv_data):
    """Update NRP in JSON with correct values from CSV"""
    print("🔄 Updating NRP in JSON...")
    
    updated_count = 0
    not_found_count = 0
    invalid_csv_count = 0
    
    # Find all records with short NRP
    for personil in json_data['personil_data']:
        nrp = str(personil.get('nrp', '')).strip()
        if nrp and nrp != '' and len(nrp) < 8:
            # Find matching CSV record
            csv_record = find_matching_csv_record(csv_data, personil['nama'])
            
            if csv_record:
                csv_nrp = csv_record['nrp']
                
                # Verify CSV NRP is 8 digits
                if len(csv_nrp) == 8 and csv_nrp.isdigit():
                    print(f"  ✅ {personil['nama']}")
                    print(f"     JSON NRP: {nrp} (Length: {len(nrp)})")
                    print(f"     CSV NRP: {csv_nrp} (Length: {len(csv_nrp)})")
                    
                    # Update JSON
                    personil['nrp'] = int(csv_nrp)
                    updated_count += 1
                else:
                    print(f"  ⚠️  {personil['nama']}")
                    print(f"     CSV NRP invalid: {csv_nrp} (Length: {len(csv_nrp)})")
                    invalid_csv_count += 1
            else:
                print(f"  ❌ {personil['nama']}")
                print(f"     Not found in CSV")
                not_found_count += 1
            print()
    
    print(f"📊 Update Summary:")
    print(f"  ✅ Updated: {updated_count} records")
    print(f"  ❌ Not Found: {not_found_count} records")
    print(f"  ⚠️  Invalid CSV: {invalid_csv_count} records")
    
    return updated_count, not_found_count, invalid_csv_count

def backup_json_file():
    """Create backup of original JSON file"""
    print("💾 Creating backup of JSON file...")
    
    import shutil
    original_file = 'file/personil_complete_data.json'
    backup_file = f'file/personil_complete_data_backup_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
    
    shutil.copy2(original_file, backup_file)
    print(f"✅ Backup created: {backup_file}")
    return backup_file

def save_updated_json(json_data):
    """Save updated JSON data"""
    print("💾 Saving updated JSON...")
    
    output_file = 'file/personil_complete_data_fixed.json'
    
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(json_data, f, indent=2, ensure_ascii=False)
    
    print(f"✅ Updated JSON saved: {output_file}")
    return output_file

def verify_updates(json_data):
    """Verify the updates were successful"""
    print("🔍 Verifying updates...")
    
    # Check remaining short NRP records
    remaining_short = 0
    eight_digit_count = 0
    
    for personil in json_data['personil_data']:
        nrp = str(personil.get('nrp', '')).strip()
        if nrp and nrp != '':
            if len(nrp) < 8:
                remaining_short += 1
            elif len(nrp) == 8:
                eight_digit_count += 1
    
    print(f"📊 Verification Results:")
    print(f"  Remaining short NRP: {remaining_short}")
    print(f"  8-digit NRP records: {eight_digit_count}")
    print(f"  Total records: {len(json_data['personil_data'])}")
    
    return remaining_short, eight_digit_count

def main():
    """Main function"""
    print("🚀 STARTING NRP FIX FROM CSV")
    print("=" * 60)
    
    # Create backup
    backup_file = backup_json_file()
    
    # Load data
    json_data = load_json_data()
    csv_data = load_csv_data()
    
    # Find short NRP records
    short_nrp_records = find_short_nrp_records(json_data)
    
    if not short_nrp_records:
        print("✅ No records with short NRP found!")
        return
    
    # Update records
    updated_count, not_found_count, invalid_csv_count = update_nrp_in_json(json_data, csv_data)
    
    # Save updated JSON
    updated_file = save_updated_json(json_data)
    
    # Verify updates
    remaining_short, eight_digit_count = verify_updates(json_data)
    
    print()
    print("🎉 NRP FIX COMPLETED!")
    print("=" * 60)
    print(f"📊 FINAL RESULTS:")
    print(f"  ✅ Backup created: {backup_file}")
    print(f"  ✅ Updated file: {updated_file}")
    print(f"  📈 Updated Records: {updated_count}")
    print(f"  ❌ Not Found: {not_found_count}")
    print(f"  ⚠️  Invalid CSV: {invalid_csv_count}")
    print(f"  📊 Remaining Short NRP: {remaining_short}")
    print(f"  🎯 8-digit NRP Records: {eight_digit_count}")
    print()
    
    if remaining_short == 0:
        print("🎉 ALL SHORT NRP RECORDS FIXED!")
    else:
        print(f"⚠️  {remaining_short} records still have short NRP")
    
    print("🚀 personil_complete_data_fixed.json siap digunakan!")

if __name__ == "__main__":
    main()
