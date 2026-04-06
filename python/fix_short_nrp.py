#!/usr/bin/env python3
"""
Fix NRP with less than 8 digits using data from CSV file
This script will update personil records with correct 8-digit NRP from CSV
"""

import csv
import mysql.connector
from datetime import datetime
import sys

def load_csv_data():
    """Load and parse CSV data"""
    print("🔍 Loading CSV data...")
    
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

def get_short_nrp_records(cursor):
    """Get personil records with NRP < 8 digits"""
    print("🔍 Finding records with short NRP...")
    
    cursor.execute('''
        SELECT id, nama, nrp, jabatan_struktural 
        FROM personil 
        WHERE is_deleted = 0 
        AND nrp IS NOT NULL 
        AND nrp != '' 
        AND LENGTH(nrp) < 8
        ORDER BY LENGTH(nrp), nama
    ''')
    
    short_nrp_records = cursor.fetchall()
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

def update_nrp_records(cursor, conn, short_nrp_records, csv_data):
    """Update NRP records with correct values from CSV"""
    print("🔄 Updating NRP records...")
    
    updated_count = 0
    not_found_count = 0
    
    for personil in short_nrp_records:
        personil_id = personil[0]
        personil_name = personil[1]
        current_nrp = personil[2]
        jabatan = personil[3]
        
        # Find matching CSV record
        csv_record = find_matching_csv_record(csv_data, personil_name)
        
        if csv_record:
            csv_nrp = csv_record['nrp']
            
            # Verify CSV NRP is 8 digits
            if len(csv_nrp) == 8 and csv_nrp.isdigit():
                print(f"  ✅ {personil_name}")
                print(f"     Current NRP: {current_nrp} (Length: {len(str(current_nrp))})")
                print(f"     CSV NRP: {csv_nrp} (Length: {len(csv_nrp)})")
                
                # Update database
                cursor.execute('''
                    UPDATE personil 
                    SET nrp = %s, updated_at = %s 
                    WHERE id = %s
                ''', (csv_nrp, datetime.now().strftime('%Y-%m-%d %H:%M:%S'), personil_id))
                
                updated_count += 1
            else:
                print(f"  ⚠️  {personil_name}")
                print(f"     CSV NRP invalid: {csv_nrp} (Length: {len(csv_nrp)})")
                not_found_count += 1
        else:
            print(f"  ❌ {personil_name}")
            print(f"     Not found in CSV")
            not_found_count += 1
        
        print()
    
    print(f"📊 Update Summary:")
    print(f"  ✅ Updated: {updated_count} records")
    print(f"  ❌ Not Found/Invalid: {not_found_count} records")
    
    return updated_count, not_found_count

def verify_updates(cursor):
    """Verify the updates were successful"""
    print("🔍 Verifying updates...")
    
    # Check remaining short NRP records
    cursor.execute('''
        SELECT COUNT(*) FROM personil 
        WHERE is_deleted = 0 
        AND nrp IS NOT NULL 
        AND nrp != '' 
        AND LENGTH(nrp) < 8
    ''')
    
    remaining_short = cursor.fetchone()[0]
    
    # Check 8-digit NRP records
    cursor.execute('''
        SELECT COUNT(*) FROM personil 
        WHERE is_deleted = 0 
        AND nrp IS NOT NULL 
        AND nrp != '' 
        AND LENGTH(nrp) = 8
    ''')
    
    eight_digit_count = cursor.fetchone()[0]
    
    print(f"📊 Verification Results:")
    print(f"  Remaining short NRP: {remaining_short}")
    print(f"  8-digit NRP records: {eight_digit_count}")
    
    return remaining_short, eight_digit_count

def main():
    """Main function"""
    print("🚀 STARTING NRP FIX PROCESS")
    print("=" * 50)
    
    # Load CSV data
    csv_data = load_csv_data()
    if not csv_data:
        print("❌ Failed to load CSV data. Exiting.")
        sys.exit(1)
    
    # Connect to database
    try:
        conn = mysql.connector.connect(
            unix_socket='/opt/lampp/var/mysql/mysql.sock',
            user='root',
            password='root',
            database='bagops'
        )
        cursor = conn.cursor()
    except Exception as e:
        print(f"❌ Database connection error: {e}")
        sys.exit(1)
    
    try:
        # Get short NRP records
        short_nrp_records = get_short_nrp_records(cursor)
        
        if not short_nrp_records:
            print("✅ No records with short NRP found!")
            return
        
        # Update records
        updated_count, not_found_count = update_nrp_records(cursor, conn, short_nrp_records, csv_data)
        
        # Commit changes
        conn.commit()
        
        # Verify updates
        remaining_short, eight_digit_count = verify_updates(cursor)
        
        print()
        print("🎉 NRP FIX COMPLETED!")
        print("=" * 50)
        print(f"📊 FINAL RESULTS:")
        print(f"  ✅ Updated Records: {updated_count}")
        print(f"  ❌ Not Found/Invalid: {not_found_count}")
        print(f"  📈 Remaining Short NRP: {remaining_short}")
        print(f"  🎯 8-digit NRP Records: {eight_digit_count}")
        print()
        
        if remaining_short == 0:
            print("🎉 ALL SHORT NRP RECORDS FIXED!")
        else:
            print(f"⚠️  {remaining_short} records still have short NRP")
        
    except Exception as e:
        print(f"❌ Error during update: {e}")
        conn.rollback()
        sys.exit(1)
    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    main()
