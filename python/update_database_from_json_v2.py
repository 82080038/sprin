#!/usr/bin/env python3
"""
Update Database dari personil_complete_data.json
Dengan mapping yang tepat untuk struktur database SPRIN
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

def update_personil_data():
    """Main update function"""
    print("=== SPRIN Database Update Tool ===")
    print("Updating personil data from JSON file...")
    
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
        
        # Backup existing data
        print("Creating backup...")
        cursor.execute("CREATE TABLE IF NOT EXISTS personil_backup AS SELECT * FROM personil")
        
        # Clear existing data
        print("Clearing existing personil data...")
        cursor.execute("DELETE FROM personil")
        
        # Insert new data
        print("Inserting new personil data...")
        success_count = 0
        error_count = 0
        
        for person in data['personil_data']:
            try:
                # Map data
                id_bagian = mappings['bagian'].get(person['unit'])
                id_pangkat = mappings['pangkat'].get(person['pangkat'])
                id_jabatan = mappings['jabatan'].get(person['jabatan'])
                
                # Skip if mapping not found
                if not all([id_bagian, id_pangkat, id_jabatan]):
                    print(f"  Skipping {person['nama']} - mapping not found")
                    error_count += 1
                    continue
                
                # Insert data
                sql = """
                INSERT INTO personil (
                    nrp, nama, id_pangkat, id_jabatan, id_bagian, id_unsur,
                    status_ket, is_active, is_deleted, created_at, updated_at
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                """
                
                # Default values
                id_unsur = 1  # Default to first unsur
                status_ket = 'aktif' if person.get('is_active', True) else 'tidak aktif'
                
                values = (
                    person['nrp'],
                    person['nama'],
                    id_pangkat,
                    id_jabatan,
                    id_bagian,
                    id_unsur,
                    status_ket,
                    1 if person.get('is_active', True) else 0,
                    1 if person.get('is_deleted', False) else 0,
                    person.get('created_at', datetime.now()),
                    person.get('updated_at', datetime.now())
                )
                
                cursor.execute(sql, values)
                success_count += 1
                
            except Exception as e:
                print(f"  Error inserting {person['nama']}: {e}")
                error_count += 1
        
        conn.commit()
        
        print(f"\n=== Update Complete ===")
        print(f"Success: {success_count} records")
        print(f"Errors: {error_count} records")
        print(f"Total processed: {success_count + error_count} records")
        
        # Verify data
        cursor.execute("SELECT COUNT(*) FROM personil")
        total = cursor.fetchone()[0]
        print(f"Total records in database: {total}")
        
        return True
        
    except Exception as e:
        print(f"Update error: {e}")
        conn.rollback()
        return False
        
    finally:
        conn.close()

if __name__ == "__main__":
    success = update_personil_data()
    sys.exit(0 if success else 1)
