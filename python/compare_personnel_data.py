#!/usr/bin/env python3
"""
Script to compare personil_complete_data.json with database personil table
and find who is missing from the database.
"""

import json
import mysql.connector
from datetime import datetime

def load_json_data():
    """Load personnel data from JSON file"""
    try:
        with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'r') as f:
            data = json.load(f)
        return data['personil_data']
    except Exception as e:
        print(f"Error loading JSON: {e}")
        return []

def get_database_personnel():
    """Get personnel from database"""
    try:
        conn = mysql.connector.connect(
            host='localhost',
            user='root',
            password='root',
            database='bagops',
            unix_socket='/opt/lampp/var/mysql/mysql.sock'
        )
        
        cursor = conn.cursor()
        query = """
        SELECT p.id, p.nama, p.nrp, pg.nama_pangkat, j.nama_jabatan 
        FROM personil p 
        LEFT JOIN pangkat pg ON p.id_pangkat = pg.id 
        LEFT JOIN jabatan j ON p.id_jabatan = j.id 
        ORDER BY p.id
        """
        cursor.execute(query)
        
        personnel = []
        for row in cursor.fetchall():
            personnel.append({
                'id': row[0],
                'nama': row[1],
                'nrp': row[2],
                'pangkat': row[3],
                'jabatan': row[4]
            })
        
        cursor.close()
        conn.close()
        return personnel
        
    except Exception as e:
        print(f"Error connecting to database: {e}")
        return []

def compare_personnel():
    """Compare JSON data with database and find missing personnel"""
    print("=== COMPARING PERSONNEL DATA ===")
    print(f"Timestamp: {datetime.now()}")
    print()
    
    # Load data
    json_personnel = load_json_data()
    db_personnel = get_database_personnel()
    
    print(f"JSON personnel count: {len(json_personnel)}")
    print(f"Database personnel count: {len(db_personnel)}")
    print()
    
    # Create lookup dictionaries
    db_by_nrp = {p['nrp']: p for p in db_personnel}
    json_by_nrp = {p['nrp']: p for p in json_personnel}
    
    # Find personnel in JSON but not in database
    missing_in_db = []
    for nrp, person in json_by_nrp.items():
        if nrp not in db_by_nrp:
            missing_in_db.append(person)
    
    # Find personnel in database but not in JSON
    extra_in_db = []
    for nrp, person in db_by_nrp.items():
        if nrp not in json_by_nrp:
            extra_in_db.append(person)
    
    # Display results
    print(f"Missing from database: {len(missing_in_db)}")
    print(f"Extra in database: {len(extra_in_db)}")
    print()
    
    if missing_in_db:
        print("=== PERSONNEL MISSING FROM DATABASE ===")
        print(f"{'ID':<5} {'NRP':<15} {'Nama':<40} {'Pangkat':<20} {'Jabatan':<30}")
        print("-" * 110)
        
        for person in missing_in_db:
            print(f"{person['id']:<5} {person['nrp']:<15} {person['nama'][:40]:<40} {person['pangkat']:<20} {person['jabatan']:<30}")
        
        print()
        print("SQL INSERT statements for missing personnel:")
        print("-- Copy and execute these SQL statements to add missing personnel")
        print()
        
        for person in missing_in_db:
            # Get pangkat ID
            pangkat_query = f"SELECT id FROM pangkat WHERE singkatan = '{person['pangkat']}' OR nama_pangkat LIKE '%{person['pangkat']}%' LIMIT 1"
            jabatan_query = f"SELECT id FROM jabatan WHERE nama_jabatan = '{person['jabatan']}' LIMIT 1"
            
            print(f"-- {person['nama']}")
            print(f"-- Find pangkat_id: {pangkat_query}")
            print(f"-- Find jabatan_id: {jabatan_query}")
            print(f"INSERT INTO personil (nama, nrp, id_pangkat, id_jabatan, is_active, created_at, updated_at) ")
            print(f"VALUES ('{person['nama']}', '{person['nrp']}', [pangkat_id], [jabatan_id], 1, NOW(), NOW());")
            print()
    
    if extra_in_db:
        print("=== PERSONNEL EXTRA IN DATABASE (not in JSON) ===")
        print(f"{'ID':<5} {'NRP':<15} {'Nama':<40} {'Pangkat':<20} {'Jabatan':<30}")
        print("-" * 110)
        
        for person in extra_in_db:
            print(f"{person['id']:<5} {person['nrp']:<15} {person['nama'][:40]:<40} {str(person['pangkat']):<20} {str(person['jabatan']):<30}")
    
    return missing_in_db, extra_in_db

if __name__ == "__main__":
    missing, extra = compare_personnel()
    
    if missing:
        print(f"\n=== SUMMARY ===")
        print(f"Found {len(missing)} personnel missing from database")
        print("Use the SQL statements above to add them")
    else:
        print("\nAll personnel from JSON are present in database!")
