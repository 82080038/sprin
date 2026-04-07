#!/usr/bin/env python3
"""
Update Database dari personil_complete_data.json
Berdasarkan NAMA dan NRP sebagai acuan utama (bukan ID)
"""

import json
import mysql.connector
from datetime import datetime
import sys

def load_json_data():
    """Load personil data dari JSON"""
    try:
        with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'r', encoding='utf-8') as f:
            data = json.load(f)
        return data
    except Exception as e:
        print(f"Error loading JSON: {e}")
        return None

def connect_db():
    """Connect ke database MySQL"""
    try:
        conn = mysql.connector.connect(
            unix_socket='/opt/lampp/var/mysql/mysql.sock',
            user='root',
            password='root',
            database='bagops'
        )
        return conn
    except Exception as e:
        print(f"Database connection error: {e}")
        return None

def create_mappings(cursor):
    """Create mapping dictionary untuk foreign keys"""
    mappings = {}
    
    # Mapping bagian (unit)
    cursor.execute("SELECT id, nama_bagian FROM bagian WHERE is_active = 1")
    mappings['bagian'] = {row[1]: row[0] for row in cursor.fetchall()}
    
    # Mapping pangkat (singkatan)
    cursor.execute("SELECT id, singkatan FROM pangkat")
    mappings['pangkat'] = {row[1]: row[0] for row in cursor.fetchall()}
    
    # Mapping jabatan
    cursor.execute("SELECT id, nama_jabatan FROM jabatan WHERE is_active = 1")
    mappings['jabatan'] = {row[1]: row[0] for row in cursor.fetchall()}
    
    # Mapping unsur
    cursor.execute("SELECT id, nama_unsur FROM unsur WHERE is_active = 1")
    mappings['unsur'] = {row[1]: row[0] for row in cursor.fetchall()}
    
    return mappings

def get_existing_personil(cursor):
    """Get existing personil data from database"""
    cursor.execute("""
        SELECT id, nrp, nama, id_pangkat, id_jabatan, id_bagian, id_unsur, 
               status_ket, is_active, is_deleted 
        FROM personil 
        ORDER BY id
    """)
    return cursor.fetchall()

def update_personil_by_nama_nrp():
    """Main update function based on NAMA and NRP"""
    print("=== SPRIN Database Update Tool ===")
    print("Updating personil data based on NAMA and NRP...")
    
    # Load data
    data = load_json_data()
    if not data:
        return False
    
    # Connect database
    conn = connect_db()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor()
        
        # Create mappings
        mappings = create_mappings(cursor)
        print(f"Loaded mappings:")
        print(f"  - Bagian: {len(mappings['bagian'])}")
        print(f"  - Pangkat: {len(mappings['pangkat'])}")
        print(f"  - Jabatan: {len(mappings['jabatan'])}")
        print(f"  - Unsur: {len(mappings['unsur'])}")
        
        # Get existing data
        existing_personil = get_existing_personil(cursor)
        print(f"Existing personil in database: {len(existing_personil)}")
        
        # Create lookup for existing data
        existing_by_nrp = {row[1]: row for row in existing_personil}  # by NRP
        existing_by_nama = {row[2].strip().upper(): row for row in existing_personil}  # by NAMA
        
        # Backup existing data
        print("Creating backup...")
        cursor.execute("CREATE TABLE IF NOT EXISTS personil_backup_" + datetime.now().strftime("%Y%m%d_%H%M%S") + " AS SELECT * FROM personil")
        
        # Track updates
        updated_count = 0
        inserted_count = 0
        error_count = 0
        skipped_count = 0
        
        print("\nProcessing personil data...")
        
        for person in data['personil_data']:
            try:
                # Clean data
                nama_clean = person['nama'].strip().upper()
                nrp_clean = person['nrp'].strip()
                
                # Map data
                id_bagian = mappings['bagian'].get(person['unit'])
                id_pangkat = mappings['pangkat'].get(person['pangkat'])
                id_jabatan = mappings['jabatan'].get(person['jabatan'])
                
                # Skip if mapping not found
                if not all([id_bagian, id_pangkat, id_jabatan]):
                    print(f"  Skipping {person['nama']} - mapping not found")
                    skipped_count += 1
                    continue
                
                # Check if person exists by NRP (primary key)
                if nrp_clean in existing_by_nrp:
                    # Update existing record
                    existing_id = existing_by_nrp[nrp_clean][0]
                    
                    sql = """
                    UPDATE personil SET 
                        nama = %s,
                        id_pangkat = %s,
                        id_jabatan = %s,
                        id_bagian = %s,
                        status_ket = %s,
                        is_active = %s,
                        is_deleted = %s,
                        updated_at = %s
                    WHERE id = %s
                    """
                    
                    values = (
                        person['nama'],
                        id_pangkat,
                        id_jabatan,
                        id_bagian,
                        'aktif' if person.get('is_active', True) else 'tidak aktif',
                        1 if person.get('is_active', True) else 0,
                        1 if person.get('is_deleted', False) else 0,
                        datetime.now(),
                        existing_id
                    )
                    
                    cursor.execute(sql, values)
                    updated_count += 1
                    print(f"  Updated: {person['nama']} (ID: {existing_id})")
                    
                # Check if person exists by NAMA (secondary key)
                elif nama_clean in existing_by_nama:
                    # Update existing record by nama
                    existing_id = existing_by_nama[nama_clean][0]
                    
                    sql = """
                    UPDATE personil SET 
                        nrp = %s,
                        nama = %s,
                        id_pangkat = %s,
                        id_jabatan = %s,
                        id_bagian = %s,
                        status_ket = %s,
                        is_active = %s,
                        is_deleted = %s,
                        updated_at = %s
                    WHERE id = %s
                    """
                    
                    values = (
                        person['nrp'],
                        person['nama'],
                        id_pangkat,
                        id_jabatan,
                        id_bagian,
                        'aktif' if person.get('is_active', True) else 'tidak aktif',
                        1 if person.get('is_active', True) else 0,
                        1 if person.get('is_deleted', False) else 0,
                        datetime.now(),
                        existing_id
                    )
                    
                    cursor.execute(sql, values)
                    updated_count += 1
                    print(f"  Updated by NAMA: {person['nama']} (ID: {existing_id})")
                    
                else:
                    # Insert new record
                    sql = """
                    INSERT INTO personil (
                        nrp, nama, id_pangkat, id_jabatan, id_bagian, id_unsur,
                        status_ket, is_active, is_deleted, created_at, updated_at
                    ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                    """
                    
                    # Default values
                    id_unsur = 1  # Default to first unsur
                    
                    values = (
                        person['nrp'],
                        person['nama'],
                        id_pangkat,
                        id_jabatan,
                        id_bagian,
                        id_unsur,
                        'aktif' if person.get('is_active', True) else 'tidak aktif',
                        1 if person.get('is_active', True) else 0,
                        1 if person.get('is_deleted', False) else 0,
                        person.get('created_at', datetime.now()),
                        datetime.now()
                    )
                    
                    cursor.execute(sql, values)
                    inserted_count += 1
                    print(f"  Inserted: {person['nama']}")
                
            except Exception as e:
                print(f"  Error processing {person['nama']}: {e}")
                error_count += 1
        
        conn.commit()
        
        print(f"\n=== Update Complete ===")
        print(f"Updated: {updated_count} records")
        print(f"Inserted: {inserted_count} records")
        print(f"Skipped: {skipped_count} records")
        print(f"Errors: {error_count} records")
        print(f"Total processed: {updated_count + inserted_count + skipped_count + error_count} records")
        
        # Verify data
        cursor.execute("SELECT COUNT(*) FROM personil")
        total = cursor.fetchone()[0]
        print(f"Total records in database: {total}")
        
        # Show sample updated data
        cursor.execute("SELECT id, nrp, nama, id_pangkat, id_jabatan, id_bagian FROM personil LIMIT 5")
        sample = cursor.fetchall()
        print(f"\nSample updated data:")
        for row in sample:
            print(f"  ID: {row[0]}, NRP: {row[1]}, Nama: {row[2]}, Pangkat_ID: {row[3]}, Jabatan_ID: {row[4]}, Bagian_ID: {row[5]}")
        
        return True
        
    except Exception as e:
        print(f"Update error: {e}")
        conn.rollback()
        return False
        
    finally:
        conn.close()

if __name__ == "__main__":
    success = update_personil_by_nama_nrp()
    sys.exit(0 if success else 1)
