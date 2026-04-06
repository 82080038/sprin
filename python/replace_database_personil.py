#!/usr/bin/env python3
"""
Script untuk mengganti seluruh data personil di database dengan data dari JSON
"""

import json
import mysql.connector
from datetime import datetime
import os

def get_database_connection():
    """Connect to database"""
    try:
        conn = mysql.connector.connect(
            unix_socket='/opt/lampp/var/mysql/mysql.sock',
            user='root',
            password='root',
            database='bagops'
        )
        return conn
    except Exception as e:
        print(f"❌ Database connection failed: {e}")
        return None

def load_json_data():
    """Load personil data from JSON"""
    try:
        with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'r') as f:
            data = json.load(f)
        return data
    except Exception as e:
        print(f"❌ Failed to load JSON data: {e}")
        return None

def get_jabatan_mapping(conn):
    """Get jabatan mapping from database"""
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, nama_jabatan FROM jabatan ORDER BY nama_jabatan")
        jabatan_data = cursor.fetchall()
        
        # Create jabatan name to ID mapping
        jabatan_mapping = {}
        for jabatan in jabatan_data:
            jabatan_mapping[jabatan['nama_jabatan']] = jabatan['id']
        
        cursor.close()
        return jabatan_mapping
        
    except Exception as e:
        print(f"❌ Failed to get jabatan mapping: {e}")
        return {}

def get_pangkat_mapping(conn):
    """Get pangkat mapping from database"""
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, nama_pangkat, singkatan FROM pangkat ORDER BY id")
        pangkat_data = cursor.fetchall()
        
        # Create pangkat name to ID mapping
        pangkat_mapping = {}
        for pangkat in pangkat_data:
            pangkat_mapping[pangkat['nama_pangkat']] = pangkat['id']
            # Also add singkatan mapping
            if pangkat['singkatan']:
                pangkat_mapping[pangkat['singkatan']] = pangkat['id']
        
        cursor.close()
        return pangkat_mapping
        
    except Exception as e:
        print(f"❌ Failed to get pangkat mapping: {e}")
        return {}

def get_bagian_mapping(conn):
    """Get bagian mapping from database"""
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, nama_bagian FROM bagian ORDER BY nama_bagian")
        bagian_data = cursor.fetchall()
        
        # Create bagian name to ID mapping
        bagian_mapping = {}
        for bagian in bagian_data:
            bagian_mapping[bagian['nama_bagian']] = bagian['id']
        
        cursor.close()
        return bagian_mapping
        
    except Exception as e:
        print(f"❌ Failed to get bagian mapping: {e}")
        return {}

def backup_current_data(conn):
    """Backup current personil data"""
    try:
        cursor = conn.cursor()
        
        # Get current timestamp
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        backup_table = f'personil_backup_{timestamp}'
        
        # Create backup table
        cursor.execute(f"""
            CREATE TABLE {backup_table} AS SELECT * FROM personil
        """)
        
        cursor.close()
        print(f"✅ Current data backed up to {backup_table}!")
        return True
        
    except Exception as e:
        print(f"❌ Backup failed: {e}")
        return False

def clear_personil_table(conn):
    """Clear all personil data"""
    try:
        cursor = conn.cursor()
        
        # Get count before clearing
        cursor.execute("SELECT COUNT(*) FROM personil")
        count_before = cursor.fetchone()[0]
        
        # Clear table
        cursor.execute("DELETE FROM personil")
        cursor.execute("ALTER TABLE personil AUTO_INCREMENT = 1")
        
        # Verify cleared
        cursor.execute("SELECT COUNT(*) FROM personil")
        count_after = cursor.fetchone()[0]
        
        cursor.close()
        
        print(f"✅ Personil table cleared: {count_before} → {count_after} records")
        return True
        
    except Exception as e:
        print(f"❌ Failed to clear personil table: {e}")
        return False

def insert_new_personil(conn, personil_data, jabatan_mapping, bagian_mapping, pangkat_mapping):
    """Insert new personil data"""
    try:
        cursor = conn.cursor()
        
        inserted_count = 0
        skipped_count = 0
        
        for personil in personil_data:
            # Get jabatan_id
            jabatan_nama = personil['jabatan']
            jabatan_id = jabatan_mapping.get(jabatan_nama)
            
            if not jabatan_id:
                print(f"⚠️  Skipping {personil['nama']} - Jabatan not found: {jabatan_nama}")
                skipped_count += 1
                continue
            
            # Get bagian_id
            unit_nama = personil['unit']
            bagian_id = bagian_mapping.get(unit_nama)
            
            if not bagian_id:
                print(f"⚠️  Skipping {personil['nama']} - Unit not found: {unit_nama}")
                skipped_count += 1
                continue
            
            # Get pangkat_id
            pangkat_nama = personil['pangkat']
            pangkat_id = pangkat_mapping.get(pangkat_nama)
            
            if not pangkat_id:
                print(f"⚠️  Skipping {personil['nama']} - Pangkat not found: {pangkat_nama}")
                skipped_count += 1
                continue
            
            # Prepare insert data
            insert_data = (
                personil['nama'],
                personil['nrp'],
                pangkat_id,
                jabatan_id,
                bagian_id,
                personil['keterangan'] or '',  # Use status_ket field
                personil['is_active'],
                personil['is_deleted'],
                'system',  # created_by
                'system',  # updated_by
                personil['created_at'],
                personil['updated_at']
            )
            
            # Insert record
            cursor.execute("""
                INSERT INTO personil 
                (nama, nrp, id_pangkat, id_jabatan, id_bagian, status_ket, is_active, is_deleted, created_by, updated_by, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, insert_data)
            
            inserted_count += 1
            
            # Progress indicator
            if inserted_count % 50 == 0:
                print(f"📝 Inserted {inserted_count} records...")
        
        cursor.close()
        
        print(f"✅ Insert completed: {inserted_count} records inserted, {skipped_count} skipped")
        return inserted_count, skipped_count
        
    except Exception as e:
        print(f"❌ Insert failed: {e}")
        return 0, 0

def verify_import(conn):
    """Verify import results"""
    try:
        cursor = conn.cursor()
        
        # Get total count
        cursor.execute("SELECT COUNT(*) FROM personil WHERE is_deleted = FALSE")
        total_count = cursor.fetchone()[0]
        
        # Get jabatan distribution
        cursor.execute("""
            SELECT j.nama_jabatan, COUNT(*) as count
            FROM personil p
            JOIN jabatan j ON p.id_jabatan = j.id
            WHERE p.is_deleted = FALSE
            GROUP BY j.nama_jabatan
            ORDER BY count DESC
            LIMIT 10
        """)
        jabatan_dist = cursor.fetchall()
        
        # Get bagian distribution
        cursor.execute("""
            SELECT b.nama_bagian, COUNT(*) as count
            FROM personil p
            LEFT JOIN bagian b ON p.id_bagian = b.id
            WHERE p.is_deleted = FALSE
            GROUP BY b.nama_bagian
            ORDER BY count DESC
            LIMIT 10
        """)
        bagian_dist = cursor.fetchall()
        
        cursor.close()
        
        print(f"\n📊 Import Verification:")
        print(f"Total personil: {total_count}")
        
        print(f"\n🏢 Top 10 Jabatan:")
        for jabatan, count in jabatan_dist:
            print(f"  {jabatan}: {count}")
        
        print(f"\n🏢 Top 10 Units:")
        for unit, count in bagian_dist:
            unit_name = unit or "NULL"
            print(f"  {unit_name}: {count}")
        
        return total_count
        
    except Exception as e:
        print(f"❌ Verification failed: {e}")
        return 0

def main():
    print("=== DATABASE PERSONIL REPLACEMENT PROCESS ===")
    print()
    
    # Load JSON data
    print("📁 Loading JSON data...")
    json_data = load_json_data()
    if not json_data:
        return False
    
    personil_data = json_data['personil_data']
    print(f"✅ Loaded {len(personil_data)} personil records from JSON")
    
    # Connect to database
    print("\n🔌 Connecting to database...")
    conn = get_database_connection()
    if not conn:
        return False
    
    try:
        # Get mappings
        print("📋 Loading database mappings...")
        jabatan_mapping = get_jabatan_mapping(conn)
        bagian_mapping = get_bagian_mapping(conn)
        pangkat_mapping = get_pangkat_mapping(conn)
        
        print(f"✅ Loaded {len(jabatan_mapping)} jabatan mappings")
        print(f"✅ Loaded {len(bagian_mapping)} bagian mappings")
        print(f"✅ Loaded {len(pangkat_mapping)} pangkat mappings")
        
        # Check for missing mappings
        missing_jabatan = []
        missing_bagian = []
        
        for personil in personil_data:
            if personil['jabatan'] not in jabatan_mapping:
                missing_jabatan.append(personil['jabatan'])
            if personil['unit'] not in bagian_mapping:
                missing_bagian.append(personil['unit'])
        
        if missing_jabatan:
            print(f"\n⚠️  Missing jabatan in database:")
            for jabatan in sorted(set(missing_jabatan))[:10]:
                print(f"  - {jabatan}")
            if len(set(missing_jabatan)) > 10:
                print(f"  ... and {len(set(missing_jabatan)) - 10} more")
        
        if missing_bagian:
            print(f"\n⚠️  Missing units in database:")
            for unit in sorted(set(missing_bagian))[:10]:
                print(f"  - {unit}")
            if len(set(missing_bagian)) > 10:
                print(f"  ... and {len(set(missing_bagian)) - 10} more")
        
        # Confirm operation
        print(f"\n⚠️  WARNING: This will replace ALL personil data in the database!")
        print(f"📊 Current database will be backed up")
        print(f"📝 {len(personil_data)} new records will be inserted")
        
        # Backup current data
        print(f"\n💾 Creating backup...")
        if not backup_current_data(conn):
            return False
        
        # Clear personil table
        print(f"\n🗑️  Clearing personil table...")
        if not clear_personil_table(conn):
            return False
        
        # Insert new data
        print(f"\n📝 Inserting new personil data...")
        inserted, skipped = insert_new_personil(conn, personil_data, jabatan_mapping, bagian_mapping, pangkat_mapping)
        
        if inserted > 0:
            # Verify import
            print(f"\n🔍 Verifying import...")
            total_count = verify_import(conn)
            
            print(f"\n✅ PERSONIL REPLACEMENT COMPLETED!")
            print(f"📊 Summary:")
            print(f"  - JSON records: {len(personil_data)}")
            print(f"  - Inserted: {inserted}")
            print(f"  - Skipped: {skipped}")
            print(f"  - Total in DB: {total_count}")
            
            if total_count == len(personil_data) - skipped:
                print(f"✅ Import successful!")
            else:
                print(f"⚠️  Import may have issues")
        
        conn.close()
        return True
        
    except Exception as e:
        print(f"❌ Process failed: {e}")
        if conn:
            conn.close()
        return False

if __name__ == "__main__":
    main()
