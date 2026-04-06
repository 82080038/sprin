#!/usr/bin/env python3
"""
Fix CSV data corrections based on user feedback
This script will correct the CSV data according to the proper naming conventions
"""

import csv
import os
import shutil
from datetime import datetime

def backup_csv_file():
    """Create backup of original CSV file"""
    print("💾 Creating backup of CSV file...")
    
    original_file = 'file/DATA PERS FEBRUARI 2026 NEW.csv'
    backup_file = f'file/DATA PERS FEBRUARI 2026 NEW_backup_{datetime.now().strftime("%Y%m%d_%H%M%S")}.csv'
    
    shutil.copy2(original_file, backup_file)
    print(f"✅ Backup created: {backup_file}")
    return backup_file

def read_csv_data():
    """Read CSV data"""
    print("📖 Reading CSV data...")
    
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
                            'jabatan': jabatan,
                            'raw_row': row  # Keep original row for reference
                        })
                except Exception as e:
                    continue
    
    print(f"✅ Loaded {len(csv_data)} records from CSV")
    return csv_data

def apply_corrections(csv_data):
    """Apply corrections to CSV data"""
    print("🔧 Applying corrections...")
    
    corrections_made = 0
    
    for record in csv_data:
        changes = []
        
        # Correction 1: SATPAMOBVIT → SAT PAMOBVIT
        if 'SATPAMOBVIT' in record['jabatan']:
            record['jabatan'] = record['jabatan'].replace('SATPAMOBVIT', 'SAT PAMOBVIT')
            changes.append('SATPAMOBVIT → SAT PAMOBVIT')
            corrections_made += 1
        
        # Correction 2: SAT POLAIRUD → SAT POLAIRUD (already correct, but ensure consistency)
        if 'SAT POLAIRUD' in record['jabatan']:
            # This is already correct, but let's ensure it's exactly "SAT POLAIRUD"
            if record['jabatan'] != 'SAT POLAIRUD':
                record['jabatan'] = 'SAT POLAIRUD'
                changes.append('Standardized to SAT POLAIRUD')
                corrections_made += 1
        
        # Correction 3: POLSEK HARIAN → POLSEK HARIAN BOHO
        if 'POLSEK HARIAN' in record['jabatan'] and 'BOHO' not in record['jabatan']:
            record['jabatan'] = record['jabatan'].replace('POLSEK HARIAN', 'POLSEK HARIAN BOHO')
            changes.append('POLSEK HARIAN → POLSEK HARIAN BOHO')
            corrections_made += 1
        
        # Log changes for debugging
        if changes:
            print(f"  📝 {record['nama']}: {', '.join(changes)}")
    
    print(f"✅ Applied {corrections_made} corrections")
    return csv_data

def write_corrected_csv(csv_data):
    """Write corrected CSV data"""
    print("💾 Writing corrected CSV...")
    
    output_file = 'file/DATA PERS FEBRUARI 2026 NEW_corrected.csv'
    
    with open(output_file, 'w', encoding='utf-8', newline='') as f:
        writer = csv.writer(f, delimiter=';')
        
        # Write header
        writer.writerow(['PIMPINAN;;;;;;;;;;;'])
        
        # Write data
        for record in csv_data:
            # Reconstruct row in original format
            row = [
                record['id'],
                record['nama'],
                record['pangkat'],
                record['nrp'],
                record['jabatan']
            ]
            
            # Add empty columns to match original format (10 more columns)
            row.extend([''] * 10)
            writer.writerow(row)
    
    print(f"✅ Corrected CSV written: {output_file}")
    return output_file

def verify_corrections():
    """Verify corrections were applied"""
    print("🔍 Verifying corrections...")
    
    # Read corrected CSV
    corrected_data = []
    with open('file/DATA PERS FEBRUARI 2026 NEW_corrected.csv', 'r', encoding='utf-8') as f:
        reader = csv.reader(f, delimiter=';')
        next(reader)  # Skip header
        
        for row in reader:
            if len(row) >= 4 and row[0].strip():
                jabatan = row[4].strip() if len(row) > 4 else ''
                if jabatan:
                    corrected_data.append(jabatan)
    
    # Check for corrected terms
    sat_pamobvit_count = corrected_data.count('SAT PAMOBVIT')
    sat_polairud_count = corrected_data.count('SAT POLAIRUD')
    polsek_harian_boho_count = sum(1 for j in corrected_data if 'POLSEK HARIAN BOHO' in j)
    
    print(f"📊 Verification Results:")
    print(f"  SAT PAMOBVIT: {sat_pamobvit_count} records")
    print(f"  SAT POLAIRUD: {sat_polairud_count} records")
    print(f"  POLSEK HARIAN BOHO: {polsek_harian_boho_count} records")
    
    return sat_pamobvit_count, sat_polairud_count, polsek_harian_boho_count

def main():
    """Main function"""
    print("🚀 STARTING CSV DATA CORRECTION")
    print("=" * 50)
    
    # Create backup
    backup_file = backup_csv_file()
    
    # Read CSV data
    csv_data = read_csv_data()
    
    # Apply corrections
    corrected_data = apply_corrections(csv_data)
    
    # Write corrected CSV
    corrected_file = write_corrected_csv(corrected_data)
    
    # Verify corrections
    sat_pamobvit, sat_polairud, polsek_harian_boho = verify_corrections()
    
    print()
    print("🎉 CSV CORRECTION COMPLETED!")
    print("=" * 50)
    print(f"📊 RESULTS:")
    print(f"  ✅ Backup created: {backup_file}")
    print(f"  ✅ Corrected file: {corrected_file}")
    print(f"  📈 SAT PAMOBVIT records: {sat_pamobvit}")
    print(f"  📈 SAT POLAIRUD records: {sat_polairud}")
    print(f"  📈 POLSEK HARIAN BOHO records: {polsek_harian_boho}")
    print()
    print("🔧 Ready to update database with PERS MUTASI!")

if __name__ == "__main__":
    main()
