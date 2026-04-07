#!/usr/bin/env python3
"""
Mapping jabatan dari JSON file ke tabel jabatan dan update personil.id_jabatan
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

def create_jabatan_mapping(cursor):
    """Create mapping dictionary untuk jabatan"""
    cursor.execute("SELECT id, nama_jabatan FROM jabatan ORDER BY id")
    jabatan_data = cursor.fetchall()
    return {jabatan[1]: jabatan[0] for jabatan in jabatan_data}

def update_personil_jabatan():
    """Update personil.id_jabatan berdasarkan jabatan dari JSON"""
    print("=== Update Personil Jabatan Mapping ===")
    
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
        
        # Create jabatan mapping
        jabatan_mapping = create_jabatan_mapping(cursor)
        print(f"Loaded {len(jabatan_mapping)} jabatan mappings")
        
        # Check existing personil
        cursor.execute("SELECT COUNT(*) FROM personil")
        total_personil = cursor.fetchone()[0]
        print(f"Total personil: {total_personil}")
        
        # Check current jabatan mapping
        cursor.execute("SELECT COUNT(*) FROM personil WHERE id_jabatan IS NOT NULL")
        current_mapped = cursor.fetchone()[0]
        print(f"Currently mapped: {current_mapped}")
        
        updated_count = 0
        error_count = 0
        not_found_count = 0
        
        print("Updating personil jabatan mapping...")
        
        for person in data['personil_data']:
            try:
                # Get data from JSON
                nrp = person.get('nrp', '').strip()
                jabatan_nama = person.get('jabatan', '').strip()
                person_nama = person.get('nama', '').strip()
                
                if not nrp or not jabatan_nama:
                    print(f"  Skipping {person_nama} - missing NRP or jabatan")
                    error_count += 1
                    continue
                
                # Find jabatan ID
                id_jabatan = jabatan_mapping.get(jabatan_nama)
                if not id_jabatan:
                    print(f"  Jabatan not found: {jabatan_nama} for {person_nama}")
                    not_found_count += 1
                    continue
                
                # Update personil
                sql = "UPDATE personil SET id_jabatan = %s, updated_at = %s WHERE nrp = %s"
                values = (id_jabatan, datetime.now(), nrp)
                
                cursor.execute(sql, values)
                updated_count += 1
                
                print(f"  Updated: {person_nama} -> {jabatan_nama} (ID: {id_jabatan})")
                
            except Exception as e:
                print(f"  Error updating {person.get('nama', 'Unknown')}: {e}")
                error_count += 1
        
        conn.commit()
        
        print(f"\n=== Update Complete ===")
        print(f"Updated: {updated_count} records")
        print(f"Jabatan not found: {not_found_count} records")
        print(f"Errors: {error_count} records")
        print(f"Total processed: {updated_count + not_found_count + error_count} records")
        
        # Verify results
        cursor.execute("SELECT COUNT(*) FROM personil WHERE id_jabatan IS NOT NULL")
        mapped_count = cursor.fetchone()[0]
        print(f"Total mapped in database: {mapped_count}")
        
        # Show sample results
        cursor.execute("""
            SELECT p.id, p.nrp, p.nama, j.nama_jabatan 
            FROM personil p 
            JOIN jabatan j ON p.id_jabatan = j.id 
            ORDER BY p.id 
            LIMIT 10
        """)
        sample = cursor.fetchall()
        print(f"\nSample mapped data:")
        for row in sample:
            print(f"  ID: {row[0]}, NRP: {row[1]}, Nama: {row[2]}, Jabatan: {row[3]}")
        
        # Show unmapped records
        cursor.execute("SELECT COUNT(*) FROM personil WHERE id_jabatan IS NULL")
        unmapped_count = cursor.fetchone()[0]
        if unmapped_count > 0:
            print(f"\nUnmapped records: {unmapped_count}")
            cursor.execute("""
                SELECT id, nrp, nama 
                FROM personil 
                WHERE id_jabatan IS NULL 
                LIMIT 5
            """)
            unmapped = cursor.fetchall()
            for row in unmapped:
                print(f"  ID: {row[0]}, NRP: {row[1]}, Nama: {row[2]}")
        
        # Show jabatan distribution
        cursor.execute("""
            SELECT j.nama_jabatan, COUNT(p.id) as personil_count
            FROM jabatan j
            LEFT JOIN personil p ON j.id = p.id_jabatan
            WHERE p.id IS NOT NULL
            GROUP BY j.id, j.nama_jabatan
            ORDER BY personil_count DESC
            LIMIT 10
        """)
        distribution = cursor.fetchall()
        print(f"\nTop 10 Jabatan Distribution:")
        for row in distribution:
            print(f"  {row[0]}: {row[1]} personil")
        
        return True
        
    except Exception as e:
        print(f"Update error: {e}")
        conn.rollback()
        return False
        
    finally:
        conn.close()

if __name__ == "__main__":
    success = update_personil_jabatan()
    sys.exit(0 if success else 1)
