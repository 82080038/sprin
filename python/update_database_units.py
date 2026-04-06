#!/usr/bin/env python3
"""
Script untuk update database dengan unit assignments dari JSON
"""

import json
import mysql.connector
from datetime import datetime
import os

def get_database_connection():
    """Connect to database"""
    try:
        conn = mysql.connector.connect(
            unix_socket='/opt/lampp/var/mysql/mysql.sock',
            user='root',
            password='root',
            database='bagops'
        )
        return conn
    except Exception as e:
        print(f"❌ Database connection failed: {e}")
        return None

def load_json_data():
    """Load personil data from JSON"""
    try:
        with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'r') as f:
            data = json.load(f)
        return data
    except Exception as e:
        print(f"❌ Failed to load JSON data: {e}")
        return None

def get_unit_mapping(conn):
    """Get mapping from unit names to bagian IDs"""
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, nama_bagian FROM bagian ORDER BY nama_bagian")
        bagian_data = cursor.fetchall()
        
        # Create unit to bagian_id mapping
        unit_mapping = {}
        for bagian in bagian_data:
            unit_mapping[bagian['nama_bagian']] = bagian['id']
        
        # Add mappings for units that might not exist in bagian table
        # Based on JSON data analysis
        additional_mappings = {
            'SAT RESKRIM': 7,  # SAT RESKRIM
            'SAT LANTAS': 9,   # SAT LANTAS
            'SAT INTELKAM': 6, # SAT INTELKAM
            'SAT SAMAPTA': 10, # SAT SAMAPTA
            'SATPAMOBVIT': 11, # SAT PAMOBVIT
            'SAT RESNARKOBA': 8, # SAT RESNARKOBA
            'SAT POLAIRUD': 13, # SAT POLAIRUD
            'SAT TAHTI': 14,   # SAT TAHTI
            'SAT BINMAS': 12,  # SAT BINMAS
            'SPKT': 20,        # SPKT
            'SIUM': 21,        # SIUM
            'SIKEU': 22,       # SIKEU
            'SIDOKKES': 23,    # SIDOKKES
            'SIWAS': 24,       # SIWAS
            'SITIK': 25,       # SITIK
            'SIKUM': 26,       # SIKUM
            'SIPROPAM': 27,    # SIPROPAM
            'SIHUMAS': 28,     # SIHUMAS
            'POLSEK SIMANINDO': 16, # POLSEK SIMANINDO
            'POLSEK PALIPI': 17,    # POLSEK PALIPI
            'POLSEK ONANRUNGGU': 18, # POLSEK ONANRUNGGU
            'POLSEK PANGURURAN': 19, # POLSEK PANGURURAN
            'POLSEK HARIAN': 15,     # POLSEK HARIAN
            'POLSEK NAINGGOLAN': 18, # POLSEK NAINGGOLAN (same as ONANRUNGGU)
            'BKO': 29,               # BKO
            'PERS MUTASI': 30,       # PERS MUTASI
        }
        
        # Merge mappings
        unit_mapping.update(additional_mappings)
        
        cursor.close()
        return unit_mapping
        
    except Exception as e:
        print(f"❌ Failed to get unit mapping: {e}")
        return {}

def update_database_units():
    """Update database with unit assignments from JSON"""
    print("🚀 Starting database unit update...")
    
    # Load JSON data
    json_data = load_json_data()
    if not json_data:
        return False
    
    # Connect to database
    conn = get_database_connection()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor()
        
        # Get unit mapping
        unit_mapping = get_unit_mapping(conn)
        print(f"📋 Loaded {len(unit_mapping)} unit mappings")
        
        # Get current database state
        cursor.execute("""
            SELECT p.id, p.nama, p.nrp, p.id_jabatan, p.id_bagian, j.nama_jabatan, b.nama_bagian
            FROM personil p
            LEFT JOIN jabatan j ON p.id_jabatan = j.id
            LEFT JOIN bagian b ON p.id_bagian = b.id
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
        """)
        db_personil = cursor.fetchall()
        
        print(f"📊 Current database: {len(db_personil)} personil")
        
        # Count NULL units
        null_units = [p for p in db_personil if p[5] is None]  # id_bagian is at index 5
        print(f"⚠️  Personil with NULL unit: {len(null_units)}")
        
        # Prepare updates
        updates = []
        for personil in json_data['personil_data']:
            # Find matching personil in database by NRP
            db_match = next((p for p in db_personil if p[2] == personil['nrp']), None)
            
            if db_match:
                db_id, db_nama, db_nrp, db_id_jabatan, db_id_bagian, db_jabatan_nama, db_bagian_nama = db_match
                
                # Get unit mapping
                unit_name = personil['unit']
                bagian_id = unit_mapping.get(unit_name)
                
                if bagian_id and db_id_bagian != bagian_id:
                    updates.append({
                        'personil_id': db_id,
                        'nama': db_nama,
                        'nrp': db_nrp,
                        'old_bagian_id': db_id_bagian,
                        'new_bagian_id': bagian_id,
                        'old_bagian_nama': db_bagian_nama,
                        'new_bagian_nama': unit_name
                    })
        
        print(f"🔄 Found {len(updates)} personil to update")
        
        if updates:
            print("\n📋 Sample updates:")
            for i, update in enumerate(updates[:5], 1):
                print(f"{i}. {update['nama']} ({update['nrp']})")
                print(f"   {update['old_bagian_nama'] or 'NULL'} → {update['new_bagian_nama']}")
            
            if len(updates) > 5:
                print(f"... and {len(updates) - 5} more")
            
            # Confirm update
            print(f"\n⚠️  This will update {len(updates)} personil records.")
            print("📝 Creating update script...")
            
            # Create update script
            update_script = "-- Database Unit Update Script\n"
            update_script += f"-- Generated: {datetime.now().isoformat()}\n"
            update_script += f"-- Total updates: {len(updates)}\n\n"
            
            update_script += "START TRANSACTION;\n\n"
            
            for update in updates:
                update_script += f"-- Update {update['nama']} ({update['nrp']})\n"
                update_script += f"-- {update['old_bagian_nama'] or 'NULL'} → {update['new_bagian_nama']}\n"
                update_script += f"UPDATE personil SET id_bagian = {update['new_bagian_id']}, updated_at = NOW() WHERE id = {update['personil_id']};\n\n"
            
            update_script += "COMMIT;\n"
            
            # Save update script
            script_file = '/opt/lampp/htdocs/sprin/file/database_update_script.sql'
            with open(script_file, 'w') as f:
                f.write(update_script)
            
            print(f"💾 Update script saved to: {script_file}")
            
            # Execute updates
            try:
                cursor.execute("START TRANSACTION")
                
                updated_count = 0
                for update in updates:
                    cursor.execute(
                        "UPDATE personil SET id_bagian = %s, updated_at = NOW() WHERE id = %s",
                        (update['new_bagian_id'], update['personil_id'])
                    )
                    updated_count += 1
                
                cursor.execute("COMMIT")
                print(f"✅ Successfully updated {updated_count} personil records!")
                
                # Verify results
                cursor.execute("""
                    SELECT COUNT(*) as total, 
                           SUM(CASE WHEN id_bagian IS NULL THEN 1 ELSE 0 END) as null_units
                    FROM personil 
                    WHERE is_deleted = FALSE AND is_active = TRUE
                """)
                result = cursor.fetchone()
                
                print(f"📊 Updated database state:")
                print(f"   Total personil: {result[0]}")
                print(f"   NULL units: {result[1]} (was {len(null_units)})")
                print(f"   Fixed units: {len(null_units) - result[1]}")
                
            except Exception as e:
                cursor.execute("ROLLBACK")
                print(f"❌ Update failed: {e}")
                return False
                
        else:
            print("✅ No updates needed - database already matches JSON data")
        
        cursor.close()
        conn.close()
        
        return True
        
    except Exception as e:
        print(f"❌ Database update failed: {e}")
        if conn:
            conn.close()
        return False

def verify_data_consistency():
    """Verify data consistency between JSON and database"""
    print("\n🔍 Verifying data consistency...")
    
    # Load JSON data
    json_data = load_json_data()
    if not json_data:
        return False
    
    # Connect to database
    conn = get_database_connection()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor(dictionary=True)
        
        # Get database jabatan counts
        cursor.execute("""
            SELECT j.nama_jabatan, COUNT(p.id) as db_count
            FROM personil p
            JOIN jabatan j ON p.id_jabatan = j.id
            WHERE p.is_deleted = FALSE AND p.is_active = TRUE
            GROUP BY j.nama_jabatan
            ORDER BY db_count DESC
        """)
        db_jabatan_counts = {row['nama_jabatan']: row['db_count'] for row in cursor.fetchall()}
        
        # Get JSON jabatan counts
        json_jabatan_counts = json_data['statistics']['jabatan_distribution']
        
        # Compare top 10
        print("📊 Top 10 Jabatan Comparison:")
        print("Rank | Jabatan                | JSON | DB | Diff")
        print("-" * 55)
        
        # Get top 10 from JSON
        top_json = sorted(json_jabatan_counts.items(), key=lambda x: x[1], reverse=True)[:10]
        
        for i, (jabatan, json_count) in enumerate(top_json, 1):
            db_count = db_jabatan_counts.get(jabatan, 0)
            diff = json_count - db_count
            status = "✅" if diff == 0 else "⚠️"
            print(f"{i:4d} | {jabatan[:20]:20} | {json_count:4d} | {db_count:3d} | {diff:+4d} {status}")
        
        cursor.close()
        conn.close()
        
        return True
        
    except Exception as e:
        print(f"❌ Verification failed: {e}")
        if conn:
            conn.close()
        return False

def main():
    print("=== DATABASE UNIT UPDATE PROCESS ===\n")
    
    # Step 1: Update database units
    if update_database_units():
        print("\n✅ Database update completed successfully!")
        
        # Step 2: Verify consistency
        if verify_data_consistency():
            print("\n✅ Data consistency verified!")
        else:
            print("\n⚠️  Data consistency verification failed!")
    else:
        print("\n❌ Database update failed!")

if __name__ == "__main__":
    main()
