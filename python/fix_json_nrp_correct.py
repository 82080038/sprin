#!/usr/bin/env python3
"""
Corrected script to fix NRP in personil_complete_data.json using data from CSV
This script will properly update NRP with less than 8 digits using correct NRP from CSV
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

def backup_json_file():
    """Create backup of original JSON file"""
    print("💾 Creating backup of JSON file...")
    
    import shutil
    original_file = 'file/personil_complete_data.json'
    backup_file = f'file/personil_complete_data_backup_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
    
    shutil.copy2(original_file, backup_file)
    print(f"✅ Backup created: {backup_file}")
    return backup_file

def fix_nrp_in_json(json_data, csv_data):
    """Fix NRP in JSON with correct values from CSV"""
    print("🔄 Fixing NRP in JSON...")
    
    updated_count = 0
    not_found_count = 0
    invalid_csv_count = 0
    
    # Process each personil record
    for i, personil in enumerate(json_data['personil_data']):
        nrp = str(personil.get('nrp', '')).strip()
        
        # Only process records with NRP < 8 digits
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
                    
                    # Update JSON - THIS IS THE CORRECT WAY
                    json_data['personil_data'][i]['nrp'] = int(csv_nrp)
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
    
    print(f"📊 Fix Summary:")
    print(f"  ✅ Updated: {updated_count} records")
    print(f"  ❌ Not Found: {not_found_count} records")
    print(f"  ⚠️  Invalid CSV: {invalid_csv_count} records")
    
    return updated_count, not_found_count, invalid_csv_count

def save_updated_json(json_data):
    """Save updated JSON data"""
    print("💾 Saving updated JSON...")
    
    output_file = 'file/personil_complete_data_fixed_correct.json'
    
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(json_data, f, indent=2, ensure_ascii=False)
    
    print(f"✅ Updated JSON saved: {output_file}")
    return output_file

def verify_updates(json_data):
    """Verify the updates were successful"""
    print("🔍 Verifying updates...")
    
    # Check NRP lengths
    nrp_lengths = {}
    eight_digit_count = 0
    short_count = 0
    
    for personil in json_data['personil_data']:
        nrp = str(personil.get('nrp', '')).strip()
        if nrp and nrp != '':
            length = len(nrp)
            nrp_lengths[length] = nrp_lengths.get(length, 0) + 1
            
            if length == 8:
                eight_digit_count += 1
            elif length < 8:
                short_count += 1
    
    print(f"📊 Verification Results:")
    print(f"  8-digit NRP: {eight_digit_count}")
    print(f"  Short NRP: {short_count}")
    print(f"  Total records: {len(json_data['personil_data'])}")
    
    # Show NRP distribution
    print(f"  NRP Length Distribution:")
    for length in sorted(nrp_lengths.keys()):
        print(f"    {length} digits: {nrp_lengths[length]} records")
    
    return eight_digit_count, short_count

def main():
    """Main function"""
    print("🚀 STARTING CORRECTED NRP FIX FROM CSV")
    print("=" * 60)
    
    # Create backup
    backup_file = backup_json_file()
    
    # Load data
    json_data = load_json_data()
    csv_data = load_csv_data()
    
    # Fix NRP records
    updated_count, not_found_count, invalid_csv_count = fix_nrp_in_json(json_data, csv_data)
    
    # Save updated JSON
    updated_file = save_updated_json(json_data)
    
    # Verify updates
    eight_digit_count, short_count = verify_updates(json_data)
    
    print()
    print("🎉 NRP FIX COMPLETED!")
    print("=" * 60)
    print(f"📊 FINAL RESULTS:")
    print(f"  ✅ Backup created: {backup_file}")
    print(f"  ✅ Updated file: {updated_file}")
    print(f"  📈 Updated Records: {updated_count}")
    print(f"  ❌ Not Found: {not_found_count}")
    print(f"  ⚠️  Invalid CSV: {invalid_csv_count}")
    print(f"  🎯 8-digit NRP Records: {eight_digit_count}")
    print(f"  📊 Short NRP Records: {short_count}")
    print()
    
    success_rate = (eight_digit_count / len(json_data['personil_data'])) * 100
    print(f"  📈 Success Rate: {success_rate:.1f}%")
    
    if short_count == 0:
        print("🎉 ALL SHORT NRP RECORDS FIXED!")
    else:
        print(f"⚠️  {short_count} records still have short NRP")
    
    print("🚀 personil_complete_data_fixed_correct.json siap digunakan!")

if __name__ == "__main__":
    main()
