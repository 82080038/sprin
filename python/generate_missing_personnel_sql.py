#!/usr/bin/env python3
"""
Generate SQL INSERT statements for missing personnel
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

def get_pangkat_mapping():
    """Get pangkat mapping from database"""
    try:
        conn = mysql.connector.connect(
            host='localhost',
            user='root',
            password='root',
            database='bagops',
            unix_socket='/opt/lampp/var/mysql/mysql.sock'
        )
        
        cursor = conn.cursor()
        query = "SELECT id, singkatan, nama_pangkat FROM pangkat"
        cursor.execute(query)
        
        mapping = {}
        for row in cursor.fetchall():
            mapping[row[1]] = row[0]  # singkatan -> id
            # Also add some common variations
            if 'BRIGPOL' in row[2]:
                mapping['BRIGPOL'] = row[0]
            elif 'BRIPDA' in row[2]:
                mapping['BRIPDA'] = row[0]
            elif 'BRIPTU' in row[2]:
                mapping['BRIPTU'] = row[0]
        
        cursor.close()
        conn.close()
        return mapping
        
    except Exception as e:
        print(f"Error getting pangkat mapping: {e}")
        return {}

def get_jabatan_mapping():
    """Get jabatan mapping from database"""
    try:
        conn = mysql.connector.connect(
            host='localhost',
            user='root',
            password='root',
            database='bagops',
            unix_socket='/opt/lampp/var/mysql/mysql.sock'
        )
        
        cursor = conn.cursor()
        query = "SELECT id, nama_jabatan FROM jabatan"
        cursor.execute(query)
        
        mapping = {}
        for row in cursor.fetchall():
            mapping[row[1]] = row[0]  # nama_jabatan -> id
        
        cursor.close()
        conn.close()
        return mapping
        
    except Exception as e:
        print(f"Error getting jabatan mapping: {e}")
        return {}

def generate_sql():
    """Generate SQL INSERT statements for missing personnel"""
    json_personnel = load_json_data()
    db_nrps = get_database_personnel()
    pangkat_map = get_pangkat_mapping()
    jabatan_map = get_jabatan_mapping()
    
    db_by_nrp = set(db_nrps)
    
    missing = []
    for person in json_personnel:
        if person['nrp'] not in db_by_nrp:
            missing.append(person)
    
    print("-- SQL INSERT Statements for Missing Personnel")
    print("-- Generated automatically from personil_complete_data.json")
    print(f"-- Total missing personnel: {len(missing)}")
    print("--")
    print("SET FOREIGN_KEY_CHECKS = 0;")
    print()
    
    for person in missing:
        nrp = person['nrp']
        nama = person['nama'].replace("'", "\\'")
        pangkat = person['pangkat']
        jabatan = person['jabatan']
        
        # Get IDs
        id_pangkat = pangkat_map.get(pangkat, 'NULL')
        id_jabatan = jabatan_map.get(jabatan, 'NULL')
        
        if id_pangkat == 'NULL':
            print(f"-- WARNING: Pangkat '{pangkat}' not found for {nama}")
        if id_jabatan == 'NULL':
            print(f"-- WARNING: Jabatan '{jabatan}' not found for {nama}")
        
        print(f"INSERT INTO personil (nama, nrp, id_pangkat, id_jabatan, is_active, created_at, updated_at) VALUES ('{nama}', '{nrp}', {id_pangkat}, {id_jabatan}, 1, NOW(), NOW());")
        print()
    
    print("SET FOREIGN_KEY_CHECKS = 1;")
    print()
    print(f"-- {len(missing)} personnel records to insert")

if __name__ == "__main__":
    generate_sql()
