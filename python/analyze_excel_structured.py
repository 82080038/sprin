#!/usr/bin/env python3
"""
Script untuk menganalisis file Excel DATA PERS FEBRUARI 2026 NEW.xlsx
dengan struktur hierarkis (bagian/sub-bagian)
"""

import pandas as pd
import json
import os
from datetime import datetime

def analyze_structured_excel(excel_path, output_dir):
    """
    Menganalisis file Excel dengan struktur hierarkis bagian
    """
    print("🔍 Menganalisis file Excel dengan struktur bagian...")
    
    try:
        # Baca Sheet1
        df = pd.read_excel(excel_path, sheet_name='Sheet1')
        
        print(f"📊 Total baris di Sheet1: {len(df)}")
        
        # Analisis struktur
        personil_records = []
        current_section = None
        current_subsection = None
        
        for idx, row in df.iterrows():
            no = row['NO']
            nama = row['N A M A ']
            pangkat = row['PANGKAT']
            nrp = row['N R P']
            jabatan = row['JABATAN']
            ket = row['K E T']
            
            # Check jika ini adalah section header (hanya 1 kolom terisi)
            filled_columns = sum(1 for col in [no, nama, pangkat, nrp, jabatan, ket] 
                              if pd.notna(col) and str(col).strip() != '')
            
            if filled_columns == 1 and pd.notna(no):
                # Ini adalah section header
                section_name = str(no).strip().upper()
                print(f"📁 Section: {section_name}")
                
                if section_name in ['PIMPINAN', 'BAG OPS', 'BAG REN', 'BAG SDM', 'BAG LOG']:
                    current_section = section_name
                    current_subsection = None
                else:
                    current_subsection = section_name
                    
            elif filled_columns > 1 and pd.notna(nama):
                # Ini adalah data personil
                if pd.notna(nrp) and str(nrp).isdigit():
                    personil_record = {
                        "id": len(personil_records) + 1,
                        "nrp": int(nrp),
                        "nama": str(nama).strip(),
                        "pangkat": str(pangkat).strip() if pd.notna(pangkat) else None,
                        "jabatan": str(jabatan).strip() if pd.notna(jabatan) else None,
                        "keterangan": str(ket).strip() if pd.notna(ket) else None,
                        "section": current_section,
                        "subsection": current_subsection,
                        "is_active": True,
                        "is_deleted": False,
                        "created_at": datetime.now().isoformat(),
                        "updated_at": datetime.now().isoformat()
                    }
                    personil_records.append(personil_record)
        
        print(f"✅ Total personil records: {len(personil_records)}")
        
        # Analisis distribusi per section
        section_stats = {}
        for personil in personil_records:
            section = personil['section'] or 'UNASSIGNED'
            if section not in section_stats:
                section_stats[section] = {
                    'total': 0,
                    'subsection': {},
                    'jabatan': {}
                }
            section_stats[section]['total'] += 1
            
            # Track subsections
            subsection = personil['subsection']
            if subsection:
                if subsection not in section_stats[section]['subsection']:
                    section_stats[section]['subsection'][subsection] = 0
                section_stats[section]['subsection'][subsection] += 1
            
            # Track jabatan
            jabatan = personil['jabatan']
            if jabatan:
                if jabatan not in section_stats[section]['jabatan']:
                    section_stats[section]['jabatan'][jabatan] = 0
                section_stats[section]['jabatan'][jabatan] += 1
        
        # Jabatan mapping berdasarkan section
        jabatan_mapping = {
            # PIMPINAN
            'KAPOLRES SAMOSIR': 1,
            'WAKAPOLRES': 2,
            
            # BAG OPS
            'KABAG OPS': 3,
            'PS. PAUR SUBBAGBINOPS': 4,
            'BA MIN BAG OPS': 5,
            'ASN BAG OPS': 6,
            
            # BAG REN
            'PAURSUBBAGPROGAR': 14,
            'BA MIN BAG REN': 15,
            'Plt. KASUBBAGBEKPAL': 22,
            
            # BAG SDM
            'PS. KABAG SDM': 16,
            'BA MIN BAG SDM': 18,
            
            # BAG LOG
            'BA MIN BAG LOG': 23,
            'BA POLRES SAMOSIR': 19,
            'ADC KAPOLRES': 20,
            
            # SATUAN LAINNYA (perlu mapping manual)
            'KA SPKT': 7,
            'PAMAPTA 1': 8,
            'PAMAPTA 2': 9,
            'PAMAPTA 3': 10,
            'BAMIN PAMAPTA 1': 11,
            'BAMIN PAMAPTA 2': 12,
            'BAMIN PAMAPTA 3': 13,
            'BINTARA SIUM': 25,
            'PS. KASIUM': 24,
            'BINTARA SIKEU': 27,
            'PS. KASIKEU': 26,
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
            'KAURBINOPS': 61,
            'BINTARA SAT BINMAS': 83,
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
            'BANIT RESKRIM NAINGGOLAN': 106,
            'KANIT RESKRIM HARIAN BOHO': 107,
            'BINTARA POLSEK ONAN RUNGGU': 108,
            'KAPOLSEK NAINGGOLAN': 109,
        }
        
        # Update jabatan_id untuk setiap personil
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
                "filename": os.path.basename(excel_path),
                "analysis_date": datetime.now().isoformat(),
                "analysis_type": "structured_hierarchy",
                "total_personil": len(personil_records),
                "total_sections": len(section_stats)
            },
            "section_statistics": section_stats,
            "personil_data": personil_records,
            "unmatched_jabatan": list(set(unmatched_jabatan)),
            "jabatan_mapping_summary": {
                "total_mapped": len([p for p in personil_records if p['jabatan_id'] is not None]),
                "total_unmapped": len([p for p in personil_records if p['jabatan_id'] is None]),
                "unique_jabatan": len(set(p['jabatan'] for p in personil_records if p['jabatan']))
            }
        }
        
        # Simpan hasil
        output_file = os.path.join(output_dir, "personil_structured_analysis.json")
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(analysis_result, f, indent=2, ensure_ascii=False)
        
        print(f"💾 Analisis struktur disimpan ke: {output_file}")
        
        # Simun summary
        summary_file = os.path.join(output_dir, "personil_structured_summary.json")
        summary_data = {
            "metadata": analysis_result["metadata"],
            "section_overview": {},
            "key_statistics": analysis_result["jabatan_mapping_summary"],
            "unmatched_count": len(analysis_result["unmatched_jabatan"])
        }
        
        for section, stats in section_stats.items():
            summary_data["section_overview"][section] = {
                "total_personil": stats['total'],
                "subsections": len(stats['subsection']),
                "unique_jabatan": len(stats['jabatan'])
            }
        
        with open(summary_file, 'w', encoding='utf-8') as f:
            json.dump(summary_data, f, indent=2, ensure_ascii=False)
        
        print(f"📄 Summary disimpan ke: {summary_file}")
        
        return analysis_result
        
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return None

def main():
    excel_file = "/opt/lampp/htdocs/sprin/file/DATA PERS FEBRUARI 2026 NEW.xlsx"
    output_dir = "/opt/lampp/htdocs/sprin/file"
    
    if not os.path.exists(excel_file):
        print(f"❌ File tidak ditemukan: {excel_file}")
        return
    
    result = analyze_structured_excel(excel_file, output_dir)
    
    if result:
        print("\n🎉 Analisis struktur selesai!")
        print(f"👥 Total personil: {result['metadata']['total_personil']}")
        print(f"📁 Total sections: {result['metadata']['total_sections']}")
        print(f"🎯 Jabatan mapped: {result['jabatan_mapping_summary']['total_mapped']}")
        print(f"⚠️  Jabatan unmapped: {result['jabatan_mapping_summary']['total_unmapped']}")
        
        print("\n📊 Distribusi per Section:")
        for section, stats in result['section_statistics'].items():
            print(f"   {section}: {stats['total']} personil")

if __name__ == "__main__":
    main()
