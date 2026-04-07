#!/usr/bin/env python3
"""
Isi database dengan nama dan NRP personil dari JSON file
Termasuk personil yang tidak memiliki NRP
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

def insert_personil_basic():
    """Insert personil data (nama dan NRP) ke database"""
    print("=== Insert Personil Nama dan NRP ===")
    
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
        
        # Check if table is empty
        cursor.execute("SELECT COUNT(*) FROM personil")
        existing_count = cursor.fetchone()[0]
        print(f"Existing personil: {existing_count}")
        
        if existing_count > 0:
            print("Table not empty, clearing first...")
            cursor.execute("DELETE FROM personil")
        
        inserted_count = 0
        error_count = 0
        
        print("Inserting personil data...")
        
        for person in data['personil_data']:
            try:
                # Handle NRP (some might be empty or null)
                nrp = person.get('nrp', '').strip()
                if not nrp:
                    nrp = None  # Set to NULL for empty NRP
                
                nama = person.get('nama', '').strip()
                if not nama:
                    print(f"  Skipping - empty nama")
                    error_count += 1
                    continue
                
                # Insert basic personil data
                sql = """
                INSERT INTO personil (
                    nrp, nama, status_ket, is_active, is_deleted, 
                    created_at, updated_at
                ) VALUES (%s, %s, %s, %s, %s, %s, %s)
                """
                
                values = (
                    nrp,
                    nama,
                    'aktif',
                    1 if person.get('is_active', True) else 0,
                    1 if person.get('is_deleted', False) else 0,
                    person.get('created_at', datetime.now()),
                    datetime.now()
                )
                
                cursor.execute(sql, values)
                inserted_count += 1
                
                if nrp:
                    print(f"  Inserted: {nama} (NRP: {nrp})")
                else:
                    print(f"  Inserted: {nama} (No NRP)")
                
            except Exception as e:
                print(f"  Error inserting {person.get('nama', 'Unknown')}: {e}")
                error_count += 1
        
        conn.commit()
        
        print(f"\n=== Insert Complete ===")
        print(f"Inserted: {inserted_count} records")
        print(f"Errors: {error_count} records")
        print(f"Total processed: {inserted_count + error_count} records")
        
        # Verify data
        cursor.execute("SELECT COUNT(*) FROM personil")
        total = cursor.fetchone()[0]
        print(f"Total records in database: {total}")
        
        # Show sample data
        cursor.execute("SELECT id, nrp, nama FROM personil LIMIT 5")
        sample = cursor.fetchall()
        print(f"\nSample inserted data:")
        for row in sample:
            nrp_display = row[1] if row[1] else "No NRP"
            print(f"  ID: {row[0]}, NRP: {nrp_display}, Nama: {row[2]}")
        
        # Show records without NRP
        cursor.execute("SELECT COUNT(*) FROM personil WHERE nrp IS NULL")
        no_nrp_count = cursor.fetchone()[0]
        if no_nrp_count > 0:
            print(f"\nRecords without NRP: {no_nrp_count}")
            cursor.execute("SELECT id, nama FROM personil WHERE nrp IS NULL")
            no_nrp_records = cursor.fetchall()
            for record in no_nrp_records:
                print(f"  ID: {record[0]}, Nama: {record[1]}")
        
        return True
        
    except Exception as e:
        print(f"Insert error: {e}")
        conn.rollback()
        return False
        
    finally:
        conn.close()

if __name__ == "__main__":
    success = insert_personil_basic()
    sys.exit(0 if success else 1)
