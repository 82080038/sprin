#!/usr/bin/env python3
"""
Script untuk mengekstrak data personil dari Excel dan mengkonversi ke format JSON yang siap diimport ke database
"""

import pandas as pd
import json
import os
from datetime import datetime

def clean_personil_data(df):
    """
    Membersihkan data personil dari Excel
    """
    # Remove baris kosong dan header
    df = df.dropna(subset=['N A M A '])
    
    # Remove baris yang bukan data personil (seperti "PIMPINAN")
    df = df[df['NO'].apply(lambda x: str(x).isdigit())]
    
    # Clean kolom nama
    df['N A M A '] = df['N A M A '].str.strip()
    
    # Clean kolom NRP - pastikan numeric
    df['N R P'] = pd.to_numeric(df['N R P'], errors='coerce')
    
    # Clean kolom pangkat
    df['PANGKAT'] = df['PANGKAT'].str.strip()
    
    # Clean kolom jabatan
    df['JABATAN'] = df['JABATAN'].str.strip()
    
    # Hapus baris dengan NRP kosong
    df = df.dropna(subset=['N R P'])
    
    return df

def extract_personil_to_json(excel_path, output_dir):
    """
    Mengekstrak data personil dari Excel dan menyimpan ke JSON
    """
    print("🔍 Mengekstrak data personil dari Excel...")
    
    try:
        # Baca Sheet1 (data personil)
        df = pd.read_excel(excel_path, sheet_name='Sheet1')
        
        print(f"📊 Total baris di Sheet1: {len(df)}")
        
        # Clean data
        cleaned_df = clean_personil_data(df)
        
        print(f"✅ Data personil valid: {len(cleaned_df)} baris")
        
        # Mapping jabatan ke jabatan_id (perlu disesuaikan dengan database)
        jabatan_mapping = {
            'KAPOLRES SAMOSIR': 1,
            'WAKAPOLRES': 2,
            'KABAG OPS': 3,
            'PS. PAUR SUBBAGBINOPS': 4,
            'BA MIN BAG OPS': 5,
            'ASN BAG OPS': 6,
            'KABAG REN': None,  # perlu mapping
            'PAURSUBBAGPROGAR': 14,
            'BA MIN BAG REN': 15,
            'PS. KABAG SDM': 16,
            'PAURSUBBAGBINKAR': 17,
            'BA MIN BAG SDM': 18,
            'BA POLRES SAMOSIR': 19,
            'ADC KAPOLRES': 20,
            'BINTARA SATLANTAS': 21,
            'KASAT RESKRIM': None,  # perlu mapping
            'PS.KANIT IDIK 1': 58,
            'BINTARA SATRESNARKOBA': 59,
            'KASAT SAMAPTA': None,  # perlu mapping
            'PS. KAURBINOPS': 61,
            'PS. KANIT DALMAS 2': 62,
            'PS. KANIT TURJAWALI': 63,
            'BINTARA SAT SAMAPTA': 64,
            'KASAT PAMOBVIT': None,  # perlu mapping
            'PS. KANITPAMWASTER': 66,
            'PS. KANITPAMWISATA': 67,
            'PS. PANIT PAMWASTER': 68,
            'BINTARA SAT PAMOBVIT': 69,
            'KASAT LANTAS': None,  # perlu mapping
            'PS. KANITGAKKUM': 72,
            'PS. KANITTURJAWALI': 73,
            'PS. KANITKAMSEL': 74,
            'BINTARA SAT LANTAS': 75,
            'KASAT POLAIRUD': None,  # perlu mapping
            'PS. KANITPATROLI': 77,
            'BINTARA SATPOLAIRUD': 78,
            'PS. KASAT TAHTI': 79,
            'BINTARA SAT TAHTI': 80,
            'PS. KAPOLSEK HARIAN BOHO': 81,
            'PS. KANIT INTELKAM': 82,
            'PS. KANIT BINMAS': 83,
            'PS. KANIT RESKRIM': 84,
            'PS.KANIT SAMAPTA': 85,
            'BINTARA POLSEK': 86,
            'KAPOLSEK PALIPI': 87,
            'PS. KA SPKT 1': 88,
            'PS. KANIT SAMAPTA': 89,
            'PS. KA SPKT 2': 90,
            'BINTARA POLSEK': 91,
            'PS. KAPOLSEK SIMANINDO': 92,
            'KANIT RESKRIM': 93,
            'PS. KANITPROPAM': 94,
            'PS. KA SPKT 3': 95,
            'KASIHUMAS': 96,
            'KAPOLSEK PANGURURAN': 97,
            'BINTARA POLSEK PALIPI': 98,
            'BINTARA POLSEK PANGURURAN': 99,
            'BINTARA POLSEK SIMANINDO': 100,
            'BINTARA POLSEK NAINGGOLAN': 101,
            'BINTARA POLSEK HARIAN BOHO': 102,
            'KANIT RESKRIM PALIPI': 103,
            'KANIT RESKRIM PANGURURAN': 104,
            'KANIT RESKRIM SIMANINDO': 105,
            'BINTARA POLSEK NAINGGOLAN': 101,
            'KANIT RESKRIM NAINGGOLAN': 106,
            'KANIT RESKRIM HARIAN BOHO': 107,
            'BINTARA POLSEK ONAN RUNGGU': 108,
            'KAPOLSEK NAINGGOLAN': 109,
        }
        
        # Konversi ke format JSON untuk database
        personil_list = []
        
        for idx, row in cleaned_df.iterrows():
            jabatan_nama = row['JABATAN'].strip()
            jabatan_id = jabatan_mapping.get(jabatan_nama, None)
            
            personil_data = {
                "id": idx + 1,  # temporary ID
                "nrp": int(row['N R P']),
                "nama": row['N A M A '],
                "pangkat": row['PANGKAT'],
                "jabatan_nama": jabatan_nama,
                "jabatan_id": jabatan_id,
                "keterangan": row.get('K E T', None),
                "is_active": True,
                "is_deleted": False,
                "created_at": datetime.now().isoformat(),
                "updated_at": datetime.now().isoformat()
            }
            
            personil_list.append(personil_data)
        
        # Simpan ke JSON
        output_file = os.path.join(output_dir, "personil_extracted.json")
        
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(personil_list, f, indent=2, ensure_ascii=False)
        
        print(f"💾 Data personil disimpan ke: {output_file}")
        
        # Buat summary
        summary = {
            "extraction_date": datetime.now().isoformat(),
            "source_file": os.path.basename(excel_path),
            "total_personil": len(personil_list),
            "jabatan_distribution": {},
            "pangkat_distribution": {},
            "unmatched_jabatan": []
        }
        
        # Hitung distribusi jabatan
        jabatan_counts = {}
        pangkat_counts = {}
        
        for personil in personil_list:
            # Jabatan distribution
            jabatan = personil['jabatan_nama']
            jabatan_counts[jabatan] = jabatan_counts.get(jabatan, 0) + 1
            
            # Pangkat distribution
            pangkat = personil['pangkat']
            pangkat_counts[pangkat] = pangkat_counts.get(pangkat, 0) + 1
            
            # Track unmatched jabatan
            if personil['jabatan_id'] is None:
                if jabatan not in summary["unmatched_jabatan"]:
                    summary["unmatched_jabatan"].append(jabatan)
        
        summary["jabatan_distribution"] = jabatan_counts
        summary["pangkat_distribution"] = pangkat_counts
        
        # Simpan summary
        summary_file = os.path.join(output_dir, "personil_extraction_summary.json")
        with open(summary_file, 'w', encoding='utf-8') as f:
            json.dump(summary, f, indent=2, ensure_ascii=False)
        
        print(f"📄 Summary disimpan ke: {summary_file}")
        
        return personil_list, summary
        
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return None, None

def main():
    excel_file = "/opt/lampp/htdocs/sprin/file/DATA PERS FEBRUARI 2026 NEW.xlsx"
    output_dir = "/opt/lampp/htdocs/sprin/file"
    
    if not os.path.exists(excel_file):
        print(f"❌ File tidak ditemukan: {excel_file}")
        return
    
    personil_data, summary = extract_personil_to_json(excel_file, output_dir)
    
    if personil_data:
        print("\n🎉 Ekstraksi selesai!")
        print(f"👥 Total personil: {summary['total_personil']}")
        print(f"📊 Jenis pangkat: {len(summary['pangkat_distribution'])}")
        print(f"💼 Jenis jabatan: {len(summary['jabatan_distribution'])}")
        
        if summary['unmatched_jabatan']:
            print(f"⚠️  Jabatan tidak cocok: {len(summary['unmatched_jabatan'])}")
            print(f"   Daftar: {', '.join(summary['unmatched_jabatan'][:5])}")

if __name__ == "__main__":
    main()
