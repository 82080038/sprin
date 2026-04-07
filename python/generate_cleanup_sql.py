#!/usr/bin/env python3
"""
Generate clean SQL for removing duplicates
"""

import mysql.connector

def generate_clean_sql():
    """Generate only SQL statements"""
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
        
        # Group by nama
        by_nama = {}
        for record in duplicates:
            nama = record[1]
            if nama not in by_nama:
                by_nama[nama] = []
            by_nama[nama].append(record)
        
        # Generate clean SQL
        sql_lines = [
            "-- Remove duplicate personnel records",
            "-- Keep earliest record by created_at",
            "SET FOREIGN_KEY_CHECKS = 0;",
            ""
        ]
        
        deleted_count = 0
        for nama, records in by_nama.items():
            if len(records) > 1:
                # Sort by created_at to keep the earliest
                records.sort(key=lambda x: x[3] if x[3] else '9999-12-31')
                
                # Keep first record, delete rest
                to_keep = records[0]
                to_delete = records[1:]
                
                sql_lines.append(f"-- {nama}")
                sql_lines.append(f"-- Keeping: ID {to_keep[0]}")
                
                for record in to_delete:
                    sql_lines.append(f"DELETE FROM personil WHERE id = {record[0]};")
                    deleted_count += 1
                
                sql_lines.append("")
        
        sql_lines.append("SET FOREIGN_KEY_CHECKS = 1;")
        sql_lines.append("")
        sql_lines.append(f"-- Total records deleted: {deleted_count}")
        
        cursor.close()
        conn.close()
        
        return '\n'.join(sql_lines)
        
    except Exception as e:
        print(f"Error: {e}")
        return ""

if __name__ == "__main__":
    sql = generate_clean_sql()
    print(sql)
