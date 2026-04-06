#!/usr/bin/env python3
"""
Update database with complete personil data from personil_complete_data.json
This script will update the personil table with the latest complete data
"""

import json
import mysql.connector
from datetime import datetime
import sys

def load_complete_data():
    """Load complete personil data from JSON file"""
    try:
        with open('file/personil_complete_data.json', 'r', encoding='utf-8') as f:
            data = json.load(f)
        return data
    except Exception as e:
        print(f"❌ Error loading complete data: {e}")
        return None

def connect_database():
    """Connect to MySQL database"""
    try:
        conn = mysql.connector.connect(
            unix_socket='/opt/lampp/var/mysql/mysql.sock',
            user='root',
            password='root',
            database='bagops'
        )
        return conn
    except Exception as e:
        print(f"❌ Database connection error: {e}")
        return None

def get_or_create_mappings(cursor, conn):
    """Get or create mappings for units, jabatan, and pangkat"""
    print("🔍 Checking mappings...")
    
    # Get existing data
    cursor.execute("SELECT id, nama_bagian FROM bagian ORDER BY id")
    existing_bagian = {row[1]: row[0] for row in cursor.fetchall()}
    
    cursor.execute("SELECT id, nama_jabatan FROM jabatan ORDER BY id")
    existing_jabatan = {row[1]: row[0] for row in cursor.fetchall()}
    
    cursor.execute("SELECT id, singkatan FROM pangkat ORDER BY id")
    existing_pangkat = {row[1]: row[0] for row in cursor.fetchall()}
    
    print(f"  Existing bagian: {len(existing_bagian)}")
    print(f"  Existing jabatan: {len(existing_jabatan)}")
    print(f"  Existing pangkat: {len(existing_pangkat)}")
    
    return existing_bagian, existing_jabatan, existing_pangkat

def backup_current_data(cursor):
    """Backup current personil data"""
    print("💾 Creating backup of current personil data...")
    
    backup_table = """
    CREATE TABLE IF NOT EXISTS personil_backup_{} LIKE personil
    """.format(datetime.now().strftime('%Y%m%d_%H%M%S'))
    
    cursor.execute(backup_table)
    
    copy_data = """
    INSERT INTO personil_backup_{} 
    SELECT * FROM personil
    """.format(datetime.now().strftime('%Y%m%d_%H%M%S'))
    
    cursor.execute(copy_data)
    print("✅ Backup created successfully")

def update_personil_data(cursor, conn, complete_data, existing_bagian, existing_jabatan, existing_pangkat):
    """Update personil table with complete data"""
    print("🔄 Updating personil data...")
    
    # Clear current personil data
    print("  🗑️  Clearing current personil data...")
    cursor.execute("DELETE FROM personil")
    
    # Prepare insert statement
    insert_stmt = """
    INSERT INTO personil (
        nama, nrp, id_pangkat, jabatan_struktural, id_jabatan, id_bagian, id_unsur,
        is_active, is_deleted, created_at, updated_at
    ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    
    updated_count = 0
    skipped_count = 0
    
    for personil in complete_data['personil_data']:
        try:
            # Map unit to bagian_id
            unit_name = personil['unit']
            bagian_id = existing_bagian.get(unit_name, None)
            
            # Map jabatan to jabatan_id
            jabatan_name = personil['jabatan']
            jabatan_id = existing_jabatan.get(jabatan_name, None)
            
            # Map pangkat to pangkat_id
            pangkat_name = personil['pangkat']
            pangkat_id = existing_pangkat.get(pangkat_name, None)
            
            # Get unsur_id from bagian
            unsur_id = None
            if bagian_id:
                cursor.execute("SELECT id_unsur FROM bagian WHERE id = %s", (bagian_id,))
                result = cursor.fetchone()
                if result:
                    unsur_id = result[0]
            
            # Skip if critical mappings are missing
            if not bagian_id and unit_name != 'PIMPINAN':
                skipped_count += 1
                continue
                
            if not jabatan_id:
                skipped_count += 1
                continue
            
            # Prepare data
            nama = personil['nama']
            nrp = personil['nrp']
            jabatan_struktural = personil['jabatan']
            is_active = personil.get('is_active', True)
            is_deleted = personil.get('is_deleted', False)
            created_at = personil.get('created_at', datetime.now().strftime('%Y-%m-%d %H:%M:%S'))
            updated_at = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            
            # Insert data
            cursor.execute(insert_stmt, (
                nama, nrp, pangkat_id, jabatan_struktural, jabatan_id, bagian_id, unsur_id,
                is_active, is_deleted, created_at, updated_at
            ))
            
            updated_count += 1
            
        except Exception as e:
            print(f"  ⚠️  Error processing {personil['nama']}: {e}")
            skipped_count += 1
            continue
    
    print(f"  ✅ Updated: {updated_count} personil")
    print(f"  ⚠️  Skipped: {skipped_count} personil")
    
    return updated_count, skipped_count

def verify_update(cursor):
    """Verify the update was successful"""
    print("🔍 Verifying update...")
    
    cursor.execute("SELECT COUNT(*) FROM personil WHERE is_deleted = 0")
    total_personil = cursor.fetchone()[0]
    
    cursor.execute("SELECT COUNT(DISTINCT id_bagian) FROM personil WHERE is_deleted = 0 AND id_bagian IS NOT NULL")
    distinct_bagian = cursor.fetchone()[0]
    
    cursor.execute("SELECT COUNT(DISTINCT id_jabatan) FROM personil WHERE is_deleted = 0 AND id_jabatan IS NOT NULL")
    distinct_jabatan = cursor.fetchone()[0]
    
    cursor.execute("SELECT COUNT(DISTINCT id_pangkat) FROM personil WHERE is_deleted = 0 AND id_pangkat IS NOT NULL")
    distinct_pangkat = cursor.fetchone()[0]
    
    print(f"  📊 Verification Results:")
    print(f"    Total Personil: {total_personil}")
    print(f"    Distinct Bagian: {distinct_bagian}")
    print(f"    Distinct Jabatan: {distinct_jabatan}")
    print(f"    Distinct Pangkat: {distinct_pangkat}")
    
    return total_personil, distinct_bagian, distinct_jabatan, distinct_pangkat

def main():
    """Main function"""
    print("🚀 STARTING DATABASE UPDATE FROM COMPLETE DATA")
    print("=" * 50)
    
    # Load complete data
    complete_data = load_complete_data()
    if not complete_data:
        print("❌ Failed to load complete data. Exiting.")
        sys.exit(1)
    
    print(f"📊 Complete Data Summary:")
    print(f"  Total Personil: {complete_data['metadata']['total_personil']}")
    print(f"  Total Units: {complete_data['metadata']['total_units']}")
    print(f"  Total Jabatan: {complete_data['metadata']['total_jabatan']}")
    print(f"  Total Pangkat: {complete_data['metadata']['total_pangkat']}")
    print()
    
    # Connect to database
    conn = connect_database()
    if not conn:
        print("❌ Failed to connect to database. Exiting.")
        sys.exit(1)
    
    try:
        cursor = conn.cursor()
        
        # Backup current data
        backup_current_data(cursor)
        
        # Get mappings
        existing_bagian, existing_jabatan, existing_pangkat = get_or_create_mappings(cursor, conn)
        
        # Update personil data
        updated_count, skipped_count = update_personil_data(
            cursor, conn, complete_data, existing_bagian, existing_jabatan, existing_pangkat
        )
        
        # Commit changes
        conn.commit()
        
        # Verify update
        total_personil, distinct_bagian, distinct_jabatan, distinct_pangkat = verify_update(cursor)
        
        print()
        print("🎉 UPDATE COMPLETED SUCCESSFULLY!")
        print("=" * 50)
        print(f"📊 FINAL RESULTS:")
        print(f"  ✅ Updated Personil: {updated_count}")
        print(f"  ⚠️  Skipped Personil: {skipped_count}")
        print(f"  📈 Total Personil: {total_personil}")
        print(f"  🏢 Distinct Bagian: {distinct_bagian}")
        print(f"  💼 Distinct Jabatan: {distinct_jabatan}")
        print(f"  🎖️  Distinct Pangkat: {distinct_pangkat}")
        print()
        print("🔗 Database updated successfully!")
        
    except Exception as e:
        print(f"❌ Error during update: {e}")
        conn.rollback()
        sys.exit(1)
    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    main()
