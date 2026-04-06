#!/usr/bin/env python3
"""
Final working script to fix NRP in personil_complete_data.json using data from CSV
This script will properly update all NRP with less than 8 digits using correct NRP from CSV
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
    """Load CSV data and create lookup map"""
    print("📖 Loading CSV data...")
    
    csv_lookup = {}
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
                        # Create direct lookup map
                        csv_lookup[name] = nrp
                except Exception as e:
                    continue
    
    print(f"✅ Created lookup map with {len(csv_lookup)} records from CSV")
    return csv_lookup

def backup_json_file():
    """Create backup of original JSON file"""
    print("💾 Creating backup of JSON file...")
    
    import shutil
    original_file = 'file/personil_complete_data.json'
    backup_file = f'file/personil_complete_data_backup_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
    
    shutil.copy2(original_file, backup_file)
    print(f"✅ Backup created: {backup_file}")
    return backup_file

def fix_all_nrp_in_json(json_data, csv_lookup):
    """Fix all NRP in JSON with correct values from CSV"""
    print("🔄 Fixing ALL NRP in JSON...")
    
    updated_count = 0
    not_found_count = 0
    invalid_csv_count = 0
    already_8_digit = 0
    
    # Process each personil record
    for i, personil in enumerate(json_data['personil_data']):
        nrp = str(personil.get('nrp', '')).strip()
        
        # Check if NRP is exactly 8 digits (already correct)
        if len(nrp) == 8 and nrp.isdigit():
            already_8_digit += 1
            continue
        
        # Check if NRP is empty or invalid
        if not nrp or nrp == '':
            continue
        
        # Try to find in CSV lookup
        if personil['nama'] in csv_lookup:
            csv_nrp = csv_lookup[personil['nama']]
            
            # Verify CSV NRP is 8 digits
            if len(csv_nrp) == 8 and csv_nrp.isdigit():
                print(f"  ✅ {personil['nama']}")
                print(f"     JSON NRP: {nrp} (Length: {len(nrp)})")
                print(f"     CSV NRP: {csv_nrp} (Length: {len(csv_nrp)})")
                
                # Update JSON
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
    
    print(f"📊 Fix Summary:")
    print(f"  ✅ Updated: {updated_count} records")
    print(f"  ❌ Not Found: {not_found_count} records")
    print(f"  ⚠️  Invalid CSV: {invalid_csv_count} records")
    print(f"  📋 Already 8-digit: {already_8_digit} records")
    
    return updated_count, not_found_count, invalid_csv_count, already_8_digit

def save_updated_json(json_data):
    """Save updated JSON data"""
    print("💾 Saving updated JSON...")
    
    output_file = 'file/personil_complete_data_final_fixed.json'
    
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(json_data, f, indent=2, ensure_ascii=False)
    
    print(f"✅ Updated JSON saved: {output_file}")
    return output_file

def verify_final_updates(json_data):
    """Verify the final updates were successful"""
    print("🔍 Verifying final updates...")
    
    # Check NRP lengths
    nrp_lengths = {}
    eight_digit_count = 0
    short_count = 0
    long_count = 0
    empty_count = 0
    
    for personil in json_data['personil_data']:
        nrp = str(personil.get('nrp', '')).strip()
        if nrp == '':
            empty_count += 1
        else:
            length = len(nrp)
            nrp_lengths[length] = nrp_lengths.get(length, 0) + 1
            
            if length == 8:
                eight_digit_count += 1
            elif length < 8:
                short_count += 1
            else:
                long_count += 1
    
    print(f"📊 Final Verification Results:")
    print(f"  🎯 8-digit NRP: {eight_digit_count}")
    print(f"  ⚠️  Short NRP: {short_count}")
    print(f"  📏 Long NRP: {long_count}")
    print(f"  📝 Empty NRP: {empty_count}")
    print(f"  📊 Total records: {len(json_data['personil_data'])}")
    
    # Show NRP distribution
    print(f"  📈 NRP Length Distribution:")
    for length in sorted(nrp_lengths.keys()):
        print(f"    {length} digits: {nrp_lengths[length]} records")
    
    # Calculate success rate
    success_rate = (eight_digit_count / len(json_data['personil_data'])) * 100
    print(f"  📈 Success Rate: {success_rate:.1f}%")
    
    return eight_digit_count, short_count, success_rate

def main():
    """Main function"""
    print("🚀 STARTING FINAL NRP FIX FROM CSV")
    print("=" * 60)
    
    # Create backup
    backup_file = backup_json_file()
    
    # Load data
    json_data = load_json_data()
    csv_lookup = load_csv_data()
    
    # Fix all NRP records
    updated_count, not_found_count, invalid_csv_count, already_8_digit = fix_all_nrp_in_json(json_data, csv_lookup)
    
    # Save updated JSON
    updated_file = save_updated_json(json_data)
    
    # Verify final updates
    eight_digit_count, short_count, success_rate = verify_final_updates(json_data)
    
    print()
    print("🎉 FINAL NRP FIX COMPLETED!")
    print("=" * 60)
    print(f"📊 FINAL RESULTS:")
    print(f"  ✅ Backup created: {backup_file}")
    print(f"  ✅ Updated file: {updated_file}")
    print(f"  📈 Updated Records: {updated_count}")
    print(f"  ❌ Not Found: {not_found_count}")
    print(f"  ⚠️  Invalid CSV: {invalid_csv_count}")
    print(f"  📋 Already 8-digit: {already_8_digit}")
    print(f"  🎯 Final 8-digit NRP: {eight_digit_count}")
    print(f"  ⚠️  Remaining Short NRP: {short_count}")
    print(f"  📈 Success Rate: {success_rate:.1f}%")
    print()
    
    if short_count == 0:
        print("🎉 ALL SHORT NRP RECORDS FIXED!")
        print("🚀 JSON siap untuk mapping ke database!")
    else:
        print(f"⚠️  {short_count} records still have short NRP")
        print("🔧 Perlu investigasi lebih lanjut untuk records yang tersisa")
    
    print(f"🚀 File final: personil_complete_data_final_fixed.json")

if __name__ == "__main__":
    main()
