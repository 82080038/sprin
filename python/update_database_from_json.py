#!/usr/bin/env python3
"""
Script untuk update database personil dari JSON dengan mapping
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

def create_mappings(conn):
    """Create mappings for jabatan, pangkat, and bagian"""
    mappings = {}
    
    try:
        cursor = conn.cursor(dictionary=True)
        
        # Jabatan mapping
        cursor.execute("SELECT id, nama_jabatan FROM jabatan ORDER BY nama_jabatan")
        jabatan_data = cursor.fetchall()
        mappings['jabatan'] = {jabatan['nama_jabatan']: jabatan['id'] for jabatan in jabatan_data}
        
        # Pangkat mapping
        cursor.execute("SELECT id, nama_pangkat, singkatan FROM pangkat ORDER BY nama_pangkat")
        pangkat_data = cursor.fetchall()
        mappings['pangkat'] = {}
        for pangkat in pangkat_data:
            mappings['pangkat'][pangkat['nama_pangkat']] = pangkat['id']
            if pangkat['singkatan']:
                mappings['pangkat'][pangkat['singkatan']] = pangkat['id']
        
        # Bagian mapping
        cursor.execute("SELECT id, nama_bagian FROM bagian ORDER BY nama_bagian")
        bagian_data = cursor.fetchall()
        mappings['bagian'] = {bagian['nama_bagian']: bagian['id'] for bagian in bagian_data}
        
        # Unsur mapping
        cursor.execute("SELECT id, nama_unsur FROM unsur ORDER BY nama_unsur")
        unsur_data = cursor.fetchall()
        mappings['unsur'] = {unsur['nama_unsur']: unsur['id'] for unsur in unsur_data}
        
        cursor.close()
        
        print(f"✅ Loaded mappings:")
        print(f"  - Jabatan: {len(mappings['jabatan'])}")
        print(f"  - Pangkat: {len(mappings['pangkat'])}")
        print(f"  - Bagian: {len(mappings['bagian'])}")
        print(f"  - Unsur: {len(mappings['unsur'])}")
        
        return mappings
        
    except Exception as e:
        print(f"❌ Failed to create mappings: {e}")
        return {}

def clear_personil_table(conn):
    """Clear personil table"""
    try:
        cursor = conn.cursor()
        
        # Get count before clearing
        cursor.execute("SELECT COUNT(*) FROM personil")
        count_before = cursor.fetchone()[0]
        
        # Clear table
        cursor.execute("SET FOREIGN_KEY_CHECKS = 0")
        cursor.execute("DELETE FROM personil")
        cursor.execute("ALTER TABLE personil AUTO_INCREMENT = 1")
        cursor.execute("SET FOREIGN_KEY_CHECKS = 1")
        
        cursor.close()
        
        print(f"✅ Personil table cleared: {count_before} → 0 records")
        return True
        
    except Exception as e:
        print(f"❌ Failed to clear personil table: {e}")
        return False

def insert_personil_from_json(conn, json_data, mappings):
    """Insert personil data from JSON with mapping"""
    try:
        cursor = conn.cursor()
        
        personil_data = json_data['personil_data']
        inserted_count = 0
        skipped_count = 0
        
        # Start transaction
        cursor.execute('START TRANSACTION')
        
        for personil in personil_data:
            # Map jabatan
            jabatan_nama = personil['jabatan']
            jabatan_id = mappings['jabatan'].get(jabatan_nama)
            
            if not jabatan_id:
                print(f"⚠️  Skipping {personil['nama']} - Jabatan not found: {jabatan_nama}")
                skipped_count += 1
                continue
            
            # Map pangkat
            pangkat_nama = personil['pangkat']
            if pangkat_nama == '-' or pangkat_nama == '':
                pangkat_id = None
            else:
                pangkat_id = mappings['pangkat'].get(pangkat_nama)
            
            # Map bagian (unit)
            unit_nama = personil['unit']
            bagian_id = mappings['bagian'].get(unit_nama)
            
            if not bagian_id:
                print(f"⚠️  Skipping {personil['nama']} - Unit not found: {unit_nama}")
                skipped_count += 1
                continue
            
            # Get unsur_id from jabatan
            unsur_id = None
            if jabatan_id:
                cursor.execute("SELECT id_unsur FROM jabatan WHERE id = %s", (jabatan_id,))
                result = cursor.fetchone()
                if result:
                    unsur_id = result[0]
            
            # Prepare insert data
            insert_data = (
                personil['nrp'],
                personil['nama'],
                pangkat_id,
                jabatan_id,
                bagian_id,
                unsur_id,
                personil['keterangan'] or '',
                personil['is_active'],
                personil['is_deleted'],
                'system',
                'system',
                personil['created_at'],
                personil['updated_at']
            )
            
            # Insert record
            cursor.execute("""
                INSERT INTO personil 
                (nrp, nama, id_pangkat, id_jabatan, id_bagian, id_unsur, status_ket, is_active, is_deleted, created_by, updated_by, created_at, updated_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, insert_data)
            
            inserted_count += 1
            
            # Progress indicator
            if inserted_count % 50 == 0:
                print(f"📝 Inserted {inserted_count} records...")
        
        # Commit transaction
        cursor.execute('COMMIT')
        
        cursor.close()
        
        print(f"✅ Insert completed: {inserted_count} records inserted, {skipped_count} skipped")
        return inserted_count, skipped_count
        
    except Exception as e:
        # Rollback on error
        if 'cursor' in locals():
            cursor.execute('ROLLBACK')
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
    print("=== DATABASE UPDATE FROM JSON ===")
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
        # Create mappings
        print("\n📋 Creating mappings...")
        mappings = create_mappings(conn)
        if not mappings:
            return False
        
        # Clear personil table
        print(f"\n🗑️  Clearing personil table...")
        if not clear_personil_table(conn):
            return False
        
        # Insert data from JSON
        print(f"\n📝 Inserting personil data from JSON...")
        inserted, skipped = insert_personil_from_json(conn, json_data, mappings)
        
        if inserted > 0:
            # Verify import
            print(f"\n🔍 Verifying import...")
            total_count = verify_import(conn)
            
            print(f"\n✅ DATABASE UPDATE COMPLETED!")
            print(f"📊 Summary:")
            print(f"  - JSON records: {len(personil_data)}")
            print(f"  - Inserted: {inserted}")
            print(f"  - Skipped: {skipped}")
            print(f"  - Total in DB: {total_count}")
            
            if total_count > 0:
                print(f"✅ Update successful!")
            else:
                print(f"⚠️  Update may have issues")
        
        conn.close()
        return True
        
    except Exception as e:
        print(f"❌ Process failed: {e}")
        if conn:
            conn.close()
        return False

if __name__ == "__main__":
    main()
