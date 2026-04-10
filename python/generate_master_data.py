#!/usr/bin/env python3
"""
Isi ulang master data dari personil_complete_data.json
Membuat mapping untuk unsurs, bagian, pangkat, dan jabatan
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

def extract_unique_data(data):
    """Extract unique data from JSON"""
    units = set()
    pangkats = set()
    jabatans = set()
    
    for person in data['personil_data']:
        if person.get('unit'):
            units.add(person['unit'])
        if person.get('pangkat'):
            pangkats.add(person['pangkat'])
        if person.get('jabatan'):
            jabatans.add(person['jabatan'])
    
    return sorted(units), sorted(pangkats), sorted(jabatans)

def insert_unsur_data(cursor):
    """Insert default unsur data"""
    print("Inserting unsur data...")
    
    # Check if unsur data already exists
    cursor.execute("SELECT COUNT(*) FROM unsur")
    if cursor.fetchone()[0] > 0:
        print("Unsur data already exists, skipping...")
        return
    
    unsurs = [
        ("UNS001", "Pimpinan", "Struktur pimpinan POLRES Samosir", 1),
        ("UNS002", "Pembantu Pimpinan", "Pembantu pimpinan POLRES Samosir", 2),
        ("UNS003", "Pelaksana Tugas Pokok", "Pelaksana tugas pokok POLRES Samosir", 3),
        ("UNS004", "Pelaksana Kewilayahan", "Pelaksana kewilayahan POLRES Samosir", 4),
        ("UNS005", "Pendukung", "Unit pendukung POLRES Samosir", 5),
        ("UNS006", "Lainnya", "Unit lainnya POLRES Samosir", 6)
    ]
    
    for unsur in unsurs:
        sql = "INSERT INTO unsur (kode_unsur, nama_unsur, deskripsi, urutan, is_active, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, %s, %s)"
        values = (unsur[0], unsur[1], unsur[2], unsur[3], 1, datetime.now(), datetime.now())
        cursor.execute(sql, values)
    
    print(f"Inserted {len(unsurs)} unsur records")

def insert_bagian_data(cursor, units):
    """Insert bagian data"""
    print("Inserting bagian data...")
    
    # Mapping units to unsur (using actual IDs from database)
    unit_to_unsur = {
        "PIMPINAN": 19,
        "BAG OPS": 21,
        "BAG REN": 21,
        "BAG SDM": 21,
        "BAG LOG": 21,
        "SAT INTELKAM": 21,
        "SAT RESKRIM": 21,
        "SAT RESNARKOBA": 21,
        "SAT LANTAS": 21,
        "SAT SAMAPTA": 21,
        "SAT PAMOBVIT": 21,
        "SAT POLAIRUD": 21,
        "SAT TAHTI": 21,
        "SAT BINMAS": 22,
        "SIUM": 23,
        "SIKEU": 23,
        "SIDOKKES": 23,
        "SIWAS": 23,
        "SITIK": 23,
        "SIKUM": 23,
        "SIPROPAM": 23,
        "SIHUMAS": 23,
        "SPKT": 23,
        "POLSEK SIMANINDO": 22,
        "POLSEK PANGURURAN": 22,
        "POLSEK PALIPI": 22,
        "POLSEK NAINGGOLAN": 22,
        "POLSEK HARIAN BOHO": 22,
        "BKO": 24,
        "PERS MUTASI": 24
    }
    
    for i, unit in enumerate(units, 1):
        id_unsur = unit_to_unsur.get(unit, 24)  # Default to "Lainnya" (ID 24)
        sql = "INSERT INTO bagian (kode_bagian, nama_bagian, id_unsur, urutan, is_active, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, %s, %s)"
        values = (f"BG{i:03d}", unit, id_unsur, i, 1, datetime.now(), datetime.now())
        cursor.execute(sql, values)
    
    print(f"Inserted {len(units)} bagian records")

def insert_pangkat_data(cursor, pangkats):
    """Insert pangkat data"""
    print("Inserting pangkat data...")
    
    # Level mapping untuk pangkat
    pangkat_levels = {
        "AKBP": 10,
        "KOMPOL": 9,
        "AKP": 8,
        "IPDA": 7,
        "IPTU": 8,
        "AIPDA": 6,
        "AIPTU": 5,
        "BRIPKA": 4,
        "BRIPTU": 3,
        "BRIGPOL": 2,
        "BRIPDA": 1,
        "PENATA": 5,
        "PENDA": 4,
        "-": 0
    }
    
    for i, pangkat in enumerate(pangkats, 1):
        level = pangkat_levels.get(pangkat, 0)
        sql = "INSERT INTO pangkat (nama_pangkat, singkatan, level_pangkat, created_at, updated_at) VALUES (%s, %s, %s, %s, %s)"
        values = (pangkat, pangkat, level, datetime.now(), datetime.now())
        cursor.execute(sql, values)
    
    print(f"Inserted {len(pangkats)} pangkat records")

def generate_kode_jabatan(nama_jabatan, cursor=None):
    """Generate kode_jabatan from nama_jabatan (PHP-compatible logic)"""
    import re
    # Remove non-alphanumeric and convert to uppercase
    kode = re.sub(r'[^a-zA-Z0-9]', '', nama_jabatan).upper()
    
    if cursor:
        # Check for duplicates and append number if needed
        check_sql = "SELECT COUNT(*) FROM jabatan WHERE kode_jabatan = %s"
        counter = 1
        original_kode = kode
        while True:
            cursor.execute(check_sql, (kode,))
            if cursor.fetchone()[0] == 0:
                break
            kode = f"{original_kode}{counter}"
            counter += 1
    
    return kode

def insert_jabatan_data(cursor, jabatans):
    """Insert jabatan data"""
    print("Inserting jabatan data...")
    
    for i, jabatan in enumerate(jabatans, 1):
        # Auto-generate kode_jabatan from nama_jabatan
        kode_jabatan = generate_kode_jabatan(jabatan, cursor)
        
        sql = "INSERT INTO jabatan (kode_jabatan, nama_jabatan, urutan, is_active, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, %s)"
        values = (kode_jabatan, jabatan, i, 1, datetime.now(), datetime.now())
        cursor.execute(sql, values)
    
    print(f"Inserted {len(jabatans)} jabatan records")

def create_mapping_tables(cursor):
    """Create mapping tables untuk reference"""
    print("Creating mapping references...")
    
    # Get all data for mapping
    cursor.execute("SELECT id, nama_unsur FROM unsur ORDER BY id")
    unsur_mapping = {row[1]: row[0] for row in cursor.fetchall()}
    
    cursor.execute("SELECT id, nama_bagian FROM bagian ORDER BY id")
    bagian_mapping = {row[1]: row[0] for row in cursor.fetchall()}
    
    cursor.execute("SELECT id, singkatan FROM pangkat ORDER BY id")
    pangkat_mapping = {row[1]: row[0] for row in cursor.fetchall()}
    
    cursor.execute("SELECT id, nama_jabatan FROM jabatan ORDER BY id")
    jabatan_mapping = {row[1]: row[0] for row in cursor.fetchall()}
    
    return unsur_mapping, bagian_mapping, pangkat_mapping, jabatan_mapping

def update_personil_relations(cursor, data, mappings):
    """Update personil relations dengan mapping baru"""
    print("Updating personil relations...")
    
    unsur_mapping, bagian_mapping, pangkat_mapping, jabatan_mapping = mappings
    
    updated_count = 0
    error_count = 0
    
    for person in data['personil_data']:
        try:
            # Get mapping values
            id_unsur = 1  # Default
            id_bagian = bagian_mapping.get(person.get('unit'))
            id_pangkat = pangkat_mapping.get(person.get('pangkat'))
            id_jabatan = jabatan_mapping.get(person.get('jabatan'))
            
            # Skip if mapping not found
            if not all([id_bagian, id_pangkat, id_jabatan]):
                print(f"  Skipping {person['nama']} - mapping not found")
                error_count += 1
                continue
            
            # Update personil
            sql = """
            UPDATE personil SET 
                id_unsur = %s,
                id_bagian = %s,
                id_pangkat = %s,
                id_jabatan = %s,
                updated_at = %s
            WHERE nrp = %s
            """
            
            values = (id_unsur, id_bagian, id_pangkat, id_jabatan, datetime.now(), person['nrp'])
            cursor.execute(sql, values)
            updated_count += 1
            
        except Exception as e:
            print(f"  Error updating {person['nama']}: {e}")
            error_count += 1
    
    print(f"Updated {updated_count} personil records")
    print(f"Errors: {error_count} records")

def main():
    """Main function"""
    print("=== SPRIN Master Data Generator ===")
    print("Generating master data from JSON file...")
    
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
        
        # Extract unique data
        units, pangkats, jabatans = extract_unique_data(data)
        
        print(f"Found unique data:")
        print(f"  - Units: {len(units)}")
        print(f"  - Pangkats: {len(pangkats)}")
        print(f"  - Jabatans: {len(jabatans)}")
        
        # Insert master data
        insert_unsur_data(cursor)
        insert_bagian_data(cursor, units)
        insert_pangkat_data(cursor, pangkats)
        insert_jabatan_data(cursor, jabatans)
        
        # Create mappings
        mappings = create_mapping_tables(cursor)
        
        # Update personil relations
        update_personil_relations(cursor, data, mappings)
        
        conn.commit()
        
        print(f"\n=== Master Data Generation Complete ===")
        
        # Verify data
        cursor.execute("SELECT COUNT(*) FROM unsur")
        print(f"Unsur: {cursor.fetchone()[0]} records")
        
        cursor.execute("SELECT COUNT(*) FROM bagian")
        print(f"Bagian: {cursor.fetchone()[0]} records")
        
        cursor.execute("SELECT COUNT(*) FROM pangkat")
        print(f"Pangkat: {cursor.fetchone()[0]} records")
        
        cursor.execute("SELECT COUNT(*) FROM jabatan")
        print(f"Jabatan: {cursor.fetchone()[0]} records")
        
        cursor.execute("SELECT COUNT(*) FROM personil WHERE id_bagian IS NOT NULL")
        print(f"Personil with relations: {cursor.fetchone()[0]} records")
        
        return True
        
    except Exception as e:
        print(f"Error: {e}")
        conn.rollback()
        return False
        
    finally:
        conn.close()

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
