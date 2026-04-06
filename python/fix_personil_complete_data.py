#!/usr/bin/env python3
"""
Fix personil_complete_data.json with correct naming conventions
This script will correct the JSON data according to the proper naming conventions
"""

import json
import os
import shutil
from datetime import datetime

def backup_json_file():
    """Create backup of original JSON file"""
    print("💾 Creating backup of JSON file...")
    
    original_file = 'file/personil_complete_data.json'
    backup_file = f'file/personil_complete_data_backup_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
    
    shutil.copy2(original_file, backup_file)
    print(f"✅ Backup created: {backup_file}")
    return backup_file

def read_json_data():
    """Read JSON data"""
    print("📖 Reading JSON data...")
    
    with open('file/personil_complete_data.json', 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    print(f"✅ Loaded {len(data['personil_data'])} records from JSON")
    return data

def apply_corrections(json_data):
    """Apply corrections to JSON data"""
    print("🔧 Applying corrections to JSON...")
    
    corrections_made = 0
    corrections_log = []
    
    for personil in json_data['personil_data']:
        changes = []
        
        # Correction 1: SATPAMOBVIT → SAT PAMOBVIT
        if 'unit' in personil and personil['unit'] == 'SATPAMOBVIT':
            personil['unit'] = 'SAT PAMOBVIT'
            changes.append('Unit: SATPAMOBVIT → SAT PAMOBVIT')
            corrections_made += 1
        
        if 'jabatan' in personil and 'SATPAMOBVIT' in personil['jabatan']:
            personil['jabatan'] = personil['jabatan'].replace('SATPAMOBVIT', 'SAT PAMOBVIT')
            changes.append('Jabatan: SATPAMOBVIT → SAT PAMOBVIT')
            corrections_made += 1
        
        # Correction 2: SAT POLAIRUD (ensure consistency)
        if 'unit' in personil and personil['unit'] == 'SAT POLAIRUD':
            # This is already correct, but ensure it's exactly "SAT POLAIRUD"
            if personil['unit'] != 'SAT POLAIRUD':
                personil['unit'] = 'SAT POLAIRUD'
                changes.append('Unit: Standardized to SAT POLAIRUD')
                corrections_made += 1
        
        if 'jabatan' in personil and 'SAT POLAIRUD' in personil['jabatan']:
            if personil['jabatan'] != 'SAT POLAIRUD':
                personil['jabatan'] = 'SAT POLAIRUD'
                changes.append('Jabatan: Standardized to SAT POLAIRUD')
                corrections_made += 1
        
        # Correction 3: POLSEK HARIAN → POLSEK HARIAN BOHO
        if 'unit' in personil and personil['unit'] == 'POLSEK HARIAN':
            personil['unit'] = 'POLSEK HARIAN BOHO'
            changes.append('Unit: POLSEK HARIAN → POLSEK HARIAN BOHO')
            corrections_made += 1
        
        if 'jabatan' in personil and 'POLSEK HARIAN' in personil['jabatan'] and 'BOHO' not in personil['jabatan']:
            personil['jabatan'] = personil['jabatan'].replace('POLSEK HARIAN', 'POLSEK HARIAN BOHO')
            changes.append('Jabatan: POLSEK HARIAN → POLSEK HARIAN BOHO')
            corrections_made += 1
        
        # Log changes for debugging
        if changes:
            corrections_log.append({
                'nama': personil['nama'],
                'changes': changes
            })
            print(f"  📝 {personil['nama']}: {', '.join(changes)}")
    
    print(f"✅ Applied {corrections_made} corrections to {len(corrections_log)} records")
    return json_data, corrections_log

def write_corrected_json(json_data):
    """Write corrected JSON data"""
    print("💾 Writing corrected JSON...")
    
    output_file = 'file/personil_complete_data_corrected.json'
    
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(json_data, f, indent=2, ensure_ascii=False)
    
    print(f"✅ Corrected JSON written: {output_file}")
    return output_file

def verify_corrections():
    """Verify corrections were applied"""
    print("🔍 Verifying corrections...")
    
    # Read corrected JSON
    with open('file/personil_complete_data_corrected.json', 'r', encoding='utf-8') as f:
        corrected_data = json.load(f)
    
    # Check for corrected terms
    units = [p['unit'] for p in corrected_data['personil_data'] if 'unit' in p]
    jabatans = [p['jabatan'] for p in corrected_data['personil_data'] if 'jabatan' in p]
    
    sat_pamobvit_units = units.count('SAT PAMOBVIT')
    sat_polairud_units = units.count('SAT POLAIRUD')
    polsek_harian_boho_units = units.count('POLSEK HARIAN BOHO')
    
    sat_pamobvit_jabatans = sum(1 for j in jabatans if 'SAT PAMOBVIT' in j)
    sat_polairud_jabatans = sum(1 for j in jabatans if 'SAT POLAIRUD' in j)
    polsek_harian_boho_jabatans = sum(1 for j in jabatans if 'POLSEK HARIAN BOHO' in j)
    
    print(f"📊 Verification Results:")
    print(f"  Units:")
    print(f"    SAT PAMOBVIT: {sat_pamobvit_units} records")
    print(f"    SAT POLAIRUD: {sat_polairud_units} records")
    print(f"    POLSEK HARIAN BOHO: {polsek_harian_boho_units} records")
    print(f"  Jabatans:")
    print(f"    SAT PAMOBVIT: {sat_pamobvit_jabatans} records")
    print(f"    SAT POLAIRUD: {sat_polairud_jabatans} records")
    print(f"    POLSEK HARIAN BOHO: {polsek_harian_boho_jabatans} records")
    
    return sat_pamobvit_units, sat_polairud_units, polsek_harian_boho_units

def check_mapping_compatibility():
    """Check if corrected data is compatible with database"""
    print("🔍 Checking mapping compatibility...")
    
    # Read corrected JSON
    with open('file/personil_complete_data_corrected.json', 'r', encoding='utf-8') as f:
        corrected_data = json.load(f)
    
    # Get unique units and jabatans from JSON
    json_units = set()
    json_jabatans = set()
    
    for personil in corrected_data['personil_data']:
        if 'unit' in personil and personil['unit']:
            json_units.add(personil['unit'])
        if 'jabatan' in personil and personil['jabatan']:
            json_jabatans.add(personil['jabatan'])
    
    # Connect to database
    import mysql.connector
    conn = mysql.connector.connect(
        unix_socket='/opt/lampp/var/mysql/mysql.sock',
        user='root',
        password='root',
        database='bagops'
    )
    cursor = conn.cursor()
    
    # Get database data
    cursor.execute('SELECT nama_bagian FROM bagian')
    db_bagian = {row[0] for row in cursor.fetchall()}
    
    cursor.execute('SELECT nama_jabatan FROM jabatan')
    db_jabatan = {row[0] for row in cursor.fetchall()}
    
    cursor.close()
    conn.close()
    
    # Check compatibility
    missing_bagian = json_units - db_bagian
    missing_jabatan = json_jabatans - db_jabatan
    
    print(f"📊 Mapping Compatibility:")
    print(f"  JSON Units: {len(json_units)}")
    print(f"  Database Bagian: {len(db_bagian)}")
    print(f"  Missing Bagian: {len(missing_bagian)}")
    
    if missing_bagian:
        print(f"    Missing: {', '.join(list(missing_bagian))}")
    
    print(f"  JSON Jabatans: {len(json_jabatans)}")
    print(f"  Database Jabatan: {len(db_jabatan)}")
    print(f"  Missing Jabatan: {len(missing_jabatan)}")
    
    if missing_jabatan:
        print(f"    Missing: {', '.join(list(missing_jabatan)[:5])}...")
        if len(missing_jabatan) > 5:
            print(f"    ... and {len(missing_jabatan) - 5} more")
    
    return len(missing_bagian), len(missing_jabatan)

def main():
    """Main function"""
    print("🚀 STARTING PERSONIL_COMPLETE_DATA.JSON CORRECTION")
    print("=" * 60)
    
    # Create backup
    backup_file = backup_json_file()
    
    # Read JSON data
    json_data = read_json_data()
    
    # Apply corrections
    corrected_data, corrections_log = apply_corrections(json_data)
    
    # Write corrected JSON
    corrected_file = write_corrected_json(corrected_data)
    
    # Verify corrections
    sat_pamobvit, sat_polairud, polsek_harian_boho = verify_corrections()
    
    # Check mapping compatibility
    missing_bagian, missing_jabatan = check_mapping_compatibility()
    
    print()
    print("🎉 JSON CORRECTION COMPLETED!")
    print("=" * 60)
    print(f"📊 RESULTS:")
    print(f"  ✅ Backup created: {backup_file}")
    print(f"  ✅ Corrected file: {corrected_file}")
    print(f"  📈 Records corrected: {len(corrections_log)}")
    print(f"  📊 SAT PAMOBVIT units: {sat_pamobvit}")
    print(f"  📊 SAT POLAIRUD units: {sat_polairud}")
    print(f"  📊 POLSEK HARIAN BOHO units: {polsek_harian_boho}")
    print(f"  🎯 Missing bagian: {missing_bagian}")
    print(f"  🎯 Missing jabatan: {missing_jabatan}")
    print()
    
    if missing_bagian == 0 and missing_jabatan == 0:
        print("🎉 PERFECT! All data is now compatible with database!")
    else:
        print(f"⚠️  Still have {missing_bagian} bagian and {missing_jabatan} jabatan missing")
    
    print("🚀 Ready to update database with corrected JSON!")

if __name__ == "__main__":
    main()
