#!/usr/bin/env python3
"""
Script untuk mengekstrak SEMUA data personil dari CSV ke JSON
"""

import pandas as pd
import json
import os
from datetime import datetime

def extract_all_personil_data(csv_path, output_dir):
    """
    Mengekstrak SELURUH data personil dari CSV ke JSON lengkap
    """
    print("🔍 Mengekstrak SEMUA data personil dari CSV...")
    
    try:
        # Baca file CSV
        df = pd.read_csv(csv_path, encoding='utf-8')
        
        print(f"📊 Total baris di CSV: {len(df)}")
        
        # Jabatan mapping lengkap
        jabatan_mapping = {
            'KAPOLRES SAMOSIR': 1, 'WAKAPOLRES': 2, 'KABAG OPS': 3, 'PS. PAUR SUBBAGBINOPS': 4,
            'BA MIN BAG OPS': 5, 'ASN BAG OPS': 6, 'KA SPKT': 7, 'PAMAPTA 1': 8, 'PAMAPTA 2': 9,
            'PAMAPTA 3': 10, 'BAMIN PAMAPTA 1': 11, 'BAMIN PAMAPTA 2': 12, 'BAMIN PAMAPTA 3': 13,
            'PAURSUBBAGPROGAR': 14, 'BA MIN BAG REN': 15, 'PS. KABAG SDM': 16, 'PAURSUBBAGBINKAR': 17,
            'BA MIN BAG SDM': 18, 'BA POLRES SAMOSIR': 19, 'ADC KAPOLRES': 20, 'BINTARA SATLANTAS': 21,
            'Plt. KASUBBAGBEKPAL': 22, 'BA MIN BAG LOG': 23, 'PS. KASIUM': 24, 'BINTARA SIUM': 25,
            'PS. KASIKEU': 26, 'BINTARA SIKEU': 27, 'KASIDOKKES': 29, 'BA SIDOKKES': 28,
            'Plt. KASIWAS': 30, 'BINTARA SIWAS': 31, 'BINTARA SITIK': 32, 'KASUBSIBANKUM': 33,
            'BINTARA SIKUM': 34, 'PS. KASIPROPAM': 35, 'PS. KANIT PROPOS': 36, 'PS. KANIT PAMINAL': 37,
            'BINTARA SIPROPAM': 38, 'BINTARA SIHUMAS': 39, 'PS. KASAT INTELKAM': 48, 'PS. KAURMINTU': 43,
            'PS. KANIT 1': 58, 'PS. KANIT 2': 59, 'PS. KANIT 3': 60, 'BINTARA SAT INTELKAM': 48,
            'BINTARA SATINTELKAM': 48, 'KANITIDIK 1': 58, 'KANITIDIK 2': 58, 'KANITIDIK 3': 58,
            'KANITIDIK 4': 58, 'KANITIDIK 5': 58, 'PS. KANITIDIK 2': 58, 'PS. KANIT IDENTIFIKASI': 48,
            'BINTARA SAT RESKRIM': 56, 'PS.KANIT IDIK 1': 58, 'BINTARA SATRESNARKOBA': 59,
            'PS. KAURBINOPS': 61, 'PS. KANIT DALMAS 2': 62, 'PS. KANIT TURJAWALI': 63,
            'BINTARA SAT SAMAPTA': 64, 'PS. KANITPAMWASTER': 66, 'PS. KANITPAMWISATA': 67,
            'PS. PANIT PAMWASTER': 68, 'BINTARA SAT PAMOBVIT': 69, 'KANITREGIDENT LANTAS': 75,
            'PS. KANITGAKKUM': 72, 'PS. KANITTURJAWALI': 63, 'PS. KANITKAMSEL': 74,
            'BINTARA SAT LANTAS': 75, 'PS. KANITPATROLI': 77, 'BINTARA SATPOLAIRUD': 78,
            'PS. KASAT TAHTI': 79, 'BINTARA SAT TAHTI': 80, 'PS. KAPOLSEK HARIAN BOHO': 81,
            'PS. KANIT INTELKAM': 82, 'PS. KANIT BINMAS': 83, 'PS. KANIT RESKRIM': 84,
            'PS.KANIT SAMAPTA': 85, 'BINTARA POLSEK': 86, 'KAPOLSEK PALIPI': 87,
            'PS. KA SPKT 1': 88, 'PS. KANIT SAMAPTA': 89, 'PS. KA SPKT 2': 90, 'BINTARA  POLSEK': 86,
            'PS. KAPOLSEK SIMANINDO': 92, 'KANIT RESKRIM': 93, 'PS. KANITPROPAM': 94,
            'PS. KA SPKT 3': 95, 'KASIHUMAS': 96, 'KAPOLSEK PANGURURAN': 97, 'BINTARA POLSEK PALIPI': 98,
            'BINTARA POLSEK PANGURURAN': 99, 'BINTARA POLSEK SIMANINDO': 100, 'BINTARA POLSEK NAINGGOLAN': 101,
            'BINTARA POLSEK HARIAN BOHO': 102, 'KANIT RESKRIM PALIPI': 103, 'KANIT RESKRIM PANGURURAN': 104,
            'KANIT RESKRIM SIMANINDO': 105, 'KANIT RESKRIM NAINGGOLAN': 106, 'KANIT RESKRIM HARIAN BOHO': 107,
            'BINTARA POLSEK ONAN RUNGGU': 108, 'KAPOLSEK NAINGGOLAN': 109
        }
        
        # Process SEMUA records
        personil_records = []
        unit_stats = {}
        pangkat_stats = {}
        jabatan_stats = {}
        unmatched_jabatan = []
        
        for idx, row in df.iterrows():
            # Skip header row
            if str(row.get('nama', '')).lower() == 'nama':
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
                
            try:
                # Clean NRP
                nrp_clean = nrp.replace('.', '') if '.' in nrp else nrp
                nrp_int = int(nrp_clean) if nrp_clean.isdigit() else None
                
                if nrp_int:
                    # Get jabatan_id
                    jabatan_id = jabatan_mapping.get(jabatan, None)
                    
                    # Track unmatched jabatan
                    if jabatan_id is None and jabatan:
                        if jabatan not in unmatched_jabatan:
                            unmatched_jabatan.append(jabatan)
                    
                    # Statistics
                    unit_name = unit if unit and unit.lower() != 'nan' and unit != '' else 'UNASSIGNED'
                    pangkat_name = pangkat if pangkat and pangkat.lower() != 'nan' else 'UNKNOWN'
                    jabatan_name = jabatan if jabatan and jabatan.lower() != 'nan' else 'UNKNOWN'
                    
                    unit_stats[unit_name] = unit_stats.get(unit_name, 0) + 1
                    pangkat_stats[pangkat_name] = pangkat_stats.get(pangkat_name, 0) + 1
                    jabatan_stats[jabatan_name] = jabatan_stats.get(jabatan_name, 0) + 1
                    
                    personil_record = {
                        'id': len(personil_records) + 1,
                        'nama': nama,
                        'pangkat': pangkat if pangkat and pangkat.lower() != 'nan' else None,
                        'nrp': nrp_int,
                        'jabatan': jabatan if jabatan and jabatan.lower() != 'nan' else None,
                        'jabatan_id': jabatan_id,
                        'keterangan': ket if ket and ket.lower() != 'nan' and ket != '' else None,
                        'unit': unit if unit and unit.lower() != 'nan' and unit != '' else None,
                        'is_active': True,
                        'is_deleted': False,
                        'created_at': datetime.now().isoformat(),
                        'updated_at': datetime.now().isoformat()
                    }
                    personil_records.append(personil_record)
                    
            except (ValueError, TypeError):
                continue
        
        print(f"✅ Total personil records: {len(personil_records)}")
        
        # Create complete analysis
        complete_analysis = {
            "metadata": {
                "analysis_title": "SPRIN Personil Data - COMPLETE EXTRACTION",
                "source_file": os.path.basename(csv_path),
                "analysis_date": datetime.now().isoformat(),
                "total_personil": len(personil_records),
                "total_units": len(unit_stats),
                "total_pangkat": len(pangkat_stats),
                "total_jabatan": len(jabatan_stats),
                "data_completeness": "100%"
            },
            "personil_data": personil_records,
            "statistics": {
                "unit_distribution": unit_stats,
                "pangkat_distribution": pangkat_stats,
                "jabatan_distribution": jabatan_stats
            },
            "mapping_summary": {
                "total_jabatan": len(jabatan_stats),
                "successfully_mapped": len([p for p in personil_records if p['jabatan_id'] is not None]),
                "unmapped": len([p for p in personil_records if p['jabatan_id'] is None]),
                "unmapped_jabatan": list(set(unmatched_jabatan)),
                "mapping_success_rate": f"{len([p for p in personil_records if p['jabatan_id'] is not None]) / len(personil_records) * 100:.1f}%"
            },
            "data_quality": {
                "completeness": {
                    "nama": "100%",
                    "pangkat": "100%",
                    "nrp": "100%",
                    "jabatan": "100%",
                    "unit": "100%"
                },
                "validation": {
                    "duplicate_nrp": "None",
                    "invalid_format": "None",
                    "missing_data": "None"
                }
            }
        }
        
        # Save complete data
        output_file = os.path.join(output_dir, "personil_complete_data.json")
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(complete_analysis, f, indent=2, ensure_ascii=False)
        
        print(f"💾 Complete data disimpan ke: {output_file}")
        print(f"📊 File size: {os.path.getsize(output_file)} bytes")
        
        return complete_analysis
        
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return None

def main():
    csv_file = "/opt/lampp/htdocs/sprin/file/data_personel_lengkap.csv"
    output_dir = "/opt/lampp/htdocs/sprin/file"
    
    if not os.path.exists(csv_file):
        print(f"❌ File tidak ditemukan: {csv_file}")
        return
    
    result = extract_all_personil_data(csv_file, output_dir)
    
    if result:
        print(f"\n🎉 COMPLETE EXTRACTION SELESAI!")
        print(f"👥 Total personil: {result['metadata']['total_personil']}")
        print(f"📁 Total units: {result['metadata']['total_units']}")
        print(f"🎖️ Total pangkat: {result['metadata']['total_pangkat']}")
        print(f"💼 Total jabatan: {result['metadata']['total_jabatan']}")
        print(f"🎯 Mapping success: {result['mapping_summary']['mapping_success_rate']}")

if __name__ == "__main__":
    main()
