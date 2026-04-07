#!/usr/bin/env python3
"""
Extract distinct jabatan dari JSON file dan insert ke master jabatan database
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

def extract_distinct_jabatan(data):
    """Extract distinct jabatan dari JSON data"""
    jabatan_set = set()
    
    for person in data['personil_data']:
        jabatan = person.get('jabatan', '').strip()
        if jabatan:
            jabatan_set.add(jabatan)
    
    return sorted(jabatan_set)

def insert_master_jabatan():
    """Insert distinct jabatan ke master jabatan table"""
    print("=== Insert Master Jabatan ===")
    
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
        
        # Extract distinct jabatan
        distinct_jabatan = extract_distinct_jabatan(data)
        print(f"Found {len(distinct_jabatan)} distinct jabatan")
        
        # Check existing data
        cursor.execute("SELECT COUNT(*) FROM jabatan")
        existing_count = cursor.fetchone()[0]
        print(f"Existing jabatan: {existing_count}")
        
        if existing_count > 0:
            print("Table not empty, clearing first...")
            cursor.execute("DELETE FROM jabatan")
        
        inserted_count = 0
        error_count = 0
        
        print("Inserting jabatan data...")
        
        for i, jabatan in enumerate(distinct_jabatan, 1):
            try:
                # Generate kode jabatan
                kode_jabatan = f"JB{i:03d}"
                
                # Insert jabatan
                sql = """
                INSERT INTO jabatan (
                    kode_jabatan, nama_jabatan, urutan, is_active, 
                    created_at, updated_at
                ) VALUES (%s, %s, %s, %s, %s, %s)
                """
                
                values = (
                    kode_jabatan,
                    jabatan,
                    i,
                    1,
                    datetime.now(),
                    datetime.now()
                )
                
                cursor.execute(sql, values)
                inserted_count += 1
                
                print(f"  {i:3d}. {kode_jabatan} - {jabatan}")
                
            except Exception as e:
                print(f"  Error inserting {jabatan}: {e}")
                error_count += 1
        
        conn.commit()
        
        print(f"\n=== Insert Complete ===")
        print(f"Inserted: {inserted_count} records")
        print(f"Errors: {error_count} records")
        print(f"Total processed: {inserted_count + error_count} records")
        
        # Verify data
        cursor.execute("SELECT COUNT(*) FROM jabatan")
        total = cursor.fetchone()[0]
        print(f"Total records in database: {total}")
        
        # Show sample data
        cursor.execute("SELECT id, kode_jabatan, nama_jabatan FROM jabatan LIMIT 10")
        sample = cursor.fetchall()
        print(f"\nSample inserted data:")
        for row in sample:
            print(f"  ID: {row[0]}, Kode: {row[1]}, Jabatan: {row[2]}")
        
        # Show jabatan categories
        cursor.execute("SELECT COUNT(*) FROM jabatan WHERE nama_jabatan LIKE '%KAPOLRES%'")
        kapolres_count = cursor.fetchone()[0]
        
        cursor.execute("SELECT COUNT(*) FROM jabatan WHERE nama_jabatan LIKE '%ASN%'")
        asn_count = cursor.fetchone()[0]
        
        cursor.execute("SELECT COUNT(*) FROM jabatan WHERE nama_jabatan LIKE '%BA MIN%'")
        ba_min_count = cursor.fetchone()[0]
        
        cursor.execute("SELECT COUNT(*) FROM jabatan WHERE nama_jabatan LIKE '%BINTARA%'")
        bintara_count = cursor.fetchone()[0]
        
        print(f"\nJabatan Categories:")
        print(f"  KAPOLRES level: {kapolres_count}")
        print(f"  ASN: {asn_count}")
        print(f"  BA MIN: {ba_min_count}")
        print(f"  BINTARA: {bintara_count}")
        
        return True
        
    except Exception as e:
        print(f"Insert error: {e}")
        conn.rollback()
        return False
        
    finally:
        conn.close()

if __name__ == "__main__":
    success = insert_master_jabatan()
    sys.exit(0 if success else 1)
