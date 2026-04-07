#!/usr/bin/env python3
"""
Script to find missing personnel from database
"""

import json
import mysql.connector

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
        query = "SELECT nrp FROM personil"
        cursor.execute(query)
        
        nrps = [row[0] for row in cursor.fetchall()]
        
        cursor.close()
        conn.close()
        return nrps
        
    except Exception as e:
        print(f"Error connecting to database: {e}")
        return []

def find_missing_personnel():
    """Find personnel missing from database"""
    json_personnel = load_json_data()
    db_nrps = get_database_personnel()
    
    db_by_nrp = set(db_nrps)
    
    missing = []
    for person in json_personnel:
        if person['nrp'] not in db_by_nrp:
            missing.append(person)
    
    print(f"Total personnel in JSON: {len(json_personnel)}")
    print(f"Total personnel in database: {len(db_nrps)}")
    print(f"Missing from database: {len(missing)}")
    print()
    
    if missing:
        print("First 20 missing personnel:")
        print(f"{'ID':<5} {'NRP':<15} {'Nama':<35} {'Pangkat':<15} {'Jabatan':<25}")
        print("-" * 95)
        
        for i, person in enumerate(missing[:20]):
            print(f"{person['id']:<5} {person['nrp']:<15} {person['nama'][:35]:<35} {person['pangkat']:<15} {person['jabatan']:<25}")
        
        if len(missing) > 20:
            print(f"... and {len(missing) - 20} more")
    
    return missing

if __name__ == "__main__":
    missing = find_missing_personnel()
