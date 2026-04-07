#!/usr/bin/env python3
"""
Script to identify and remove duplicate personnel records
"""

import mysql.connector

def get_duplicate_personnel():
    """Get all duplicate personnel records"""
    try:
        conn = mysql.connector.connect(
            host='localhost',
            user='root',
            password='root',
            database='bagops',
            unix_socket='/opt/lampp/var/mysql/mysql.sock'
        )
        
        cursor = conn.cursor()
        
        # Get duplicates by nama
        query = """
        SELECT id, nama, nrp, created_at 
        FROM personil 
        WHERE nama IN (
            SELECT nama FROM personil 
            GROUP BY nama 
            HAVING COUNT(*) > 1
        ) 
        ORDER BY nama, created_at
        """
        cursor.execute(query)
        
        duplicates = cursor.fetchall()
        
        cursor.close()
        conn.close()
        return duplicates
        
    except Exception as e:
        print(f"Error: {e}")
        return []

def analyze_duplicates():
    """Analyze and generate cleanup SQL"""
    duplicates = get_duplicate_personnel()
    
    print("=== DUPLICATE PERSONNEL ANALYSIS ===")
    print(f"Total duplicate records found: {len(duplicates)}")
    print()
    
    # Group by nama
    by_nama = {}
    for record in duplicates:
        nama = record[1]
        if nama not in by_nama:
            by_nama[nama] = []
        by_nama[nama].append(record)
    
    print("Duplicates by name:")
    print(f"{'Nama':<40} {'Count':<5} {'Records':<20}")
    print("-" * 65)
    
    for nama, records in by_nama.items():
        print(f"{nama[:40]:<40} {len(records):<5} {[r[0] for r in records]}")
    
    print()
    print("=== CLEANUP STRATEGY ===")
    print("Keep the earliest record (by created_at), delete duplicates")
    print()
    
    # Generate cleanup SQL
    print("-- SQL to remove duplicates (keep earliest record)")
    print("SET FOREIGN_KEY_CHECKS = 0;")
    print()
    
    deleted_count = 0
    for nama, records in by_nama.items():
        if len(records) > 1:
            # Sort by created_at to keep the earliest
            records.sort(key=lambda x: x[3] if x[3] else '9999-12-31')
            
            # Keep first record, delete rest
            to_keep = records[0]
            to_delete = records[1:]
            
            print(f"-- {nama}")
            print(f"-- Keeping: ID {to_keep[0]} (created: {to_keep[3]})")
            
            for record in to_delete:
                print(f"DELETE FROM personil WHERE id = {record[0]};")
                deleted_count += 1
            
            print()
    
    print("SET FOREIGN_KEY_CHECKS = 1;")
    print()
    print(f"Total records to delete: {deleted_count}")
    print(f"Records remaining: {len(duplicates) - deleted_count}")
    
    return deleted_count

if __name__ == "__main__":
    analyze_duplicates()
