#!/usr/bin/env python3
"""
Script untuk menganalisis file data_personel_lengkap.csv
format CSV yang sudah clean dan terstruktur
"""

import pandas as pd
import json
import os
from datetime import datetime

def analyze_data_personel(csv_path, output_dir):
    """
    Menganalisis file CSV data personel yang sudah clean
    """
    print("🔍 Menganalisis data_personel_lengkap.csv...")
    
    try:
        # Baca file CSV
        df = pd.read_csv(csv_path, encoding='utf-8')
        
        print(f"📊 Total baris: {len(df)}")
        print(f"📋 Kolom: {list(df.columns)}")
        
        # Clean data
        personil_records = []
        
        for idx, row in df.iterrows():
            # Skip header row
            if idx == 0 and str(row.get('nama', '')).lower() == 'nama':
                continue
                
            # Extract data
            nama = str(row.get('nama', '')).strip()
            pangkat = str(row.get('pangkat', '')).strip()
            nrp = str(row.get('nrp', '')).strip()
            jabatan = str(row.get('jabatan', '')).strip()
            ket = str(row.get('ket', '')).strip()
            unit = str(row.get('unit', '')).strip()
            
            # Skip empty rows
            if not nama or nama.lower() == 'nan':
                continue
                
            # Clean NRP
            try:
                nrp_clean = nrp.replace('.', '') if '.' in nrp else nrp
                nrp_int = int(nrp_clean) if nrp_clean.isdigit() else None
                
                if nrp_int:
                    personil_record = {
                        "id": len(personil_records) + 1,
                        "nama": nama,
                        "pangkat": pangkat if pangkat and pangkat.lower() != 'nan' else None,
                        "nrp": nrp_int,
                        "jabatan": jabatan if jabatan and jabatan.lower() != 'nan' else None,
                        "keterangan": ket if ket and ket.lower() != 'nan' and ket != '' else None,
                        "unit": unit if unit and unit.lower() != 'nan' and unit != '' else None,
                        "is_active": True,
                        "is_deleted": False,
                        "created_at": datetime.now().isoformat(),
                        "updated_at": datetime.now().isoformat()
                    }
                    personil_records.append(personil_record)
            except (ValueError, TypeError):
                continue
        
        print(f"✅ Total personil records: {len(personil_records)}")
        
        # Analisis distribusi per unit
        unit_stats = {}
        pangkat_stats = {}
        jabatan_stats = {}
        
        for personil in personil_records:
            # Unit statistics
            unit = personil['unit'] or 'UNASSIGNED'
            if unit not in unit_stats:
                unit_stats[unit] = 0
            unit_stats[unit] += 1
            
            # Pangkat statistics
            pangkat = personil['pangkat'] or 'UNKNOWN'
            if pangkat not in pangkat_stats:
                pangkat_stats[pangkat] = 0
            pangkat_stats[pangkat] += 1
            
            # Jabatan statistics
            jabatan = personil['jabatan'] or 'UNKNOWN'
            if jabatan not in jabatan_stats:
                jabatan_stats[jabatan] = 0
            jabatan_stats[jabatan] += 1
        
        # Jabatan mapping
        jabatan_mapping = {
            # PIMPINAN
            'KAPOLRES SAMOSIR': 1,
            'WAKAPOLRES': 2,
            
            # BAG OPS
            'KABAG OPS': 3,
            'PS. PAUR SUBBAGBINOPS': 4,
            'BA MIN BAG OPS': 5,
            'ASN BAG OPS': 6,
            
            # SPKT
            'KA SPKT': 7,
            'PAMAPTA 1': 8,
            'PAMAPTA 2': 9,
            'PAMAPTA 3': 10,
            'BAMIN PAMAPTA 1': 11,
            'BAMIN PAMAPTA 2': 12,
            'BAMIN PAMAPTA 3': 13,
            
            # BAG REN
            'PAURSUBBAGPROGAR': 14,
            'BA MIN BAG REN': 15,
            'Plt. KASUBBAGBEKPAL': 22,
            
            # BAG SDM
            'PS. KABAG SDM': 16,
            'PAURSUBBAGBINKAR': 17,
            'BA MIN BAG SDM': 18,
            
            # BAG LOG
            'BA POLRES SAMOSIR': 19,
            'ADC KAPOLRES': 20,
            'BINTARA SATLANTAS': 21,
            'BA MIN BAG LOG': 23,
            'PS. KASIUM': 24,
            'BINTARA SIUM': 25,
            'PS. KASIKEU': 26,
            'BINTARA SIKEU': 27,
            'KASIDOKKES': 29,
            'BA SIDOKKES': 28,
            'Plt. KASIWAS': 30,
            'BINTARA SIWAS': 31,
            'BINTARA SITIK': 32,
            'KASUBSIBANKUM': 33,
            'BINTARA SIKUM': 34,
            'PS. KASIPROPAM': 35,
            'PS. KANIT PROPOS': 36,
            'PS. KANIT PAMINAL': 37,
            'BINTARA SIPROPAM': 38,
            'BINTARA SIHUMAS': 39,
            
            # SATUAN (BAG OPS)
            'PS. KASAT INTELKAM': 48,
            'PS. KAURMINTU': 43,
            'PS. KANIT 1': 58,
            'PS. KANIT 2': 59,
            'PS. KANIT 3': 60,
            'BINTARA SAT INTELKAM': 48,
            'BINTARA SATINTELKAM': 48,
            'KASAT RESKRIM': None,
            'KANITIDIK 1': 58,
            'KANITIDIK 2': 58,
            'KANITIDIK 3': 58,
            'KANITIDIK 4': 58,
            'KANITIDIK 5': 58,
            'PS. KANITIDIK 2': 58,
            'PS. KANIT IDENTIFIKASI': 48,
            'BINTARA SAT RESKRIM': 56,
            'KASATRESNARKOBA': None,
            'PS.KANIT IDIK 1': 58,
            'BINTARA SATRESNARKOBA': 59,
            'KASAT SAMAPTA': None,
            'PS. KAURBINOPS': 61,
            'PS. KANIT DALMAS 2': 62,
            'PS. KANIT TURJAWALI': 63,
            'BINTARA SAT SAMAPTA': 64,
            'KASAT PAMOBVIT': None,
            'PS. KANITPAMWASTER': 66,
            'PS. KANITPAMWISATA': 67,
            'PS. PANIT PAMWASTER': 68,
            'BINTARA SAT PAMOBVIT': 69,
            'KASAT LANTAS': None,
            'KANITREGIDENT LANTAS': 75,
            'PS. KANITGAKKUM': 72,
            'PS. KANITTURJAWALI': 63,
            'PS. KANITKAMSEL': 74,
            'BINTARA SAT LANTAS': 75,
            'KASAT POLAIRUD': None,
            'PS. KANITPATROLI': 77,
            'BINTARA SATPOLAIRUD': 78,
            'PS. KASAT TAHTI': 79,
            'BINTARA SAT TAHTI': 80,
            
            # POLSEK (BAG OPS)
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
            'BINTARA  POLSEK': 86,
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
            'KANIT RESKRIM NAINGGOLAN': 106,
            'KANIT RESKRIM HARIAN BOHO': 107,
            'BINTARA POLSEK ONAN RUNGGU': 108,
            'KAPOLSEK NAINGGOLAN': 109,
        }
        
        # Update jabatan_id
        unmatched_jabatan = []
        for personil in personil_records:
            jabatan = personil['jabatan']
            jabatan_id = jabatan_mapping.get(jabatan, None)
            personil['jabatan_id'] = jabatan_id
            
            if jabatan_id is None and jabatan:
                unmatched_jabatan.append(jabatan)
        
        # Buat hasil analisis
        analysis_result = {
            "metadata": {
                "filename": os.path.basename(csv_path),
                "analysis_date": datetime.now().isoformat(),
                "analysis_type": "clean_csv_structured",
                "total_personil": len(personil_records),
                "total_units": len(unit_stats)
            },
            "unit_statistics": unit_stats,
            "pangkat_statistics": pangkat_stats,
            "jabatan_statistics": jabatan_stats,
            "personil_data": personil_records,
            "unmatched_jabatan": list(set(unmatched_jabatan)),
            "jabatan_mapping_summary": {
                "total_mapped": len([p for p in personil_records if p['jabatan_id'] is not None]),
                "total_unmapped": len([p for p in personil_records if p['jabatan_id'] is None]),
                "unique_jabatan": len(set(p['jabatan'] for p in personil_records if p['jabatan']))
            }
        }
        
        # Simpan hasil
        output_file = os.path.join(output_dir, "data_personel_analysis.json")
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(analysis_result, f, indent=2, ensure_ascii=False)
        
        print(f"💾 Analisis disimpan ke: {output_file}")
        
        # Simpan summary
        summary_file = os.path.join(output_dir, "data_personel_summary.json")
        summary_data = {
            "metadata": analysis_result["metadata"],
            "top_units": dict(sorted(unit_stats.items(), key=lambda x: x[1], reverse=True)[:10]),
            "top_pangkat": dict(sorted(pangkat_stats.items(), key=lambda x: x[1], reverse=True)[:10]),
            "key_statistics": analysis_result["jabatan_mapping_summary"],
            "unassigned_count": unit_stats.get('UNASSIGNED', 0)
        }
        
        with open(summary_file, 'w', encoding='utf-8') as f:
            json.dump(summary_data, f, indent=2, ensure_ascii=False)
        
        print(f"📄 Summary disimpan ke: {summary_file}")
        
        return analysis_result
        
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return None

def main():
    csv_file = "/opt/lampp/htdocs/sprin/file/data_personel_lengkap.csv"
    output_dir = "/opt/lampp/htdocs/sprin/file"
    
    if not os.path.exists(csv_file):
        print(f"❌ File tidak ditemukan: {csv_file}")
        return
    
    result = analyze_data_personel(csv_file, output_dir)
    
    if result:
        print("\n🎉 Analisis data_personel_lengkap.csv selesai!")
        print(f"👥 Total personil: {result['metadata']['total_personil']}")
        print(f"📁 Total units: {result['metadata']['total_units']}")
        print(f"🎯 Jabatan mapped: {result['jabatan_mapping_summary']['total_mapped']}")
        print(f"⚠️  Jabatan unmapped: {result['jabatan_mapping_summary']['total_unmapped']}")
        print(f"🔍 Unassigned personil: {result['unit_statistics'].get('UNASSIGNED', 0)}")

if __name__ == "__main__":
    main()
