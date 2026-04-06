#!/usr/bin/env python3
"""
Script untuk menganalisis file CSV DATA PERS FEBRUARI 2026 NEW.csv
dengan struktur yang benar (semicolon separated)
"""

import pandas as pd
import json
import os
from datetime import datetime

def analyze_csv_correct(excel_path, output_dir):
    """
    Menganalisis file CSV dengan format yang benar
    """
    print("🔍 Menganalisis file CSV dengan format semicolon...")
    
    try:
        # Baca file CSV dengan separator semicolon
        df = pd.read_csv(excel_path, sep=';', encoding='utf-8')
        
        print(f"📊 Total baris di CSV: {len(df)}")
        print(f"📋 Kolom: {list(df.columns)}")
        
        # Analisis struktur
        personil_records = []
        current_section = None
        current_subsection = None
        
        for idx, row in df.iterrows():
            # Get first column (usually NO or section header)
            first_col = str(row.iloc[0]).strip()
            
            # Check jika ini adalah section header (hanya kolom pertama yang terisi)
            non_empty_cols = [i for i, val in enumerate(row) if pd.notna(val) and str(val).strip() != '' and str(val).strip() != 'nan']
            
            if len(non_empty_cols) == 1 and non_empty_cols[0] == 0:
                # Ini adalah section header
                section_name = first_col.upper()
                print(f"📁 Section: {section_name}")
                
                if section_name in ['PIMPINAN', 'BAG OPS', 'BAG REN', 'BAG SDM', 'BAG LOG']:
                    current_section = section_name
                    current_subsection = None
                elif section_name in ['SPKT', 'SIUM', 'SIKEU', 'SIDOKKES', 'SIWAS', 'SITIK', 'SIKUM', 'SIPROPAM', 'SIHUMAS']:
                    # Ini adalah subsection dari BAG LOG
                    current_subsection = section_name
                    if current_section != 'BAG LOG':
                        current_section = 'BAG LOG'
                elif section_name in ['SAT BINMAS', 'SAT INTELKAM', 'SAT RESKRIM', 'SAT RESNARKOBA', 'SAT SAMAPTA', 'SATPAMOBVIT', 'SAT LANTAS', 'SATPOLAIRUD', 'SAT TAHTI']:
                    # Ini adalah subsection dari BAG OPS atau BAG LOG
                    current_subsection = section_name
                    if section_name in ['SAT INTELKAM', 'SAT RESKRIM', 'SAT RESNARKOBA']:
                        current_section = 'BAG OPS'
                    else:
                        current_section = 'BAG LOG'
                elif section_name in ['POLSEK', 'HARIAN BOHO']:
                    # Subsection dari BAG OPS
                    current_subsection = section_name
                    current_section = 'BAG OPS'
                else:
                    # Other sections
                    current_subsection = section_name
                    if current_section is None:
                        current_section = 'OTHER'
                    
            elif len(non_empty_cols) > 1:
                # Ini adalah data personil
                # Extract data dari kolom yang relevan
                no = row.iloc[0] if len(row) > 0 else None
                nama = row.iloc[1] if len(row) > 1 else None
                pangkat = row.iloc[2] if len(row) > 2 else None
                nrp = row.iloc[3] if len(row) > 3 else None
                jabatan = row.iloc[4] if len(row) > 4 else None
                ket = row.iloc[5] if len(row) > 5 else None
                
                # Validasi personil (harus ada nama dan nrp)
                if pd.notna(nama) and pd.notna(nrp) and str(nrp).strip() != '':
                    try:
                        nrp_clean = str(nrp).strip()
                        # Handle NRP dengan format khusus
                        if '.' in nrp_clean:
                            nrp_clean = nrp_clean.replace('.', '')
                        
                        nrp_int = int(nrp_clean) if nrp_clean.isdigit() else None
                        
                        if nrp_int:
                            personil_record = {
                                "id": len(personil_records) + 1,
                                "no": int(no) if str(no).isdigit() else None,
                                "nrp": nrp_int,
                                "nama": str(nama).strip(),
                                "pangkat": str(pangkat).strip() if pd.notna(pangkat) else None,
                                "jabatan": str(jabatan).strip() if pd.notna(jabatan) else None,
                                "keterangan": str(ket).strip() if pd.notna(ket) and str(ket).strip() != '' else None,
                                "section": current_section,
                                "subsection": current_subsection,
                                "is_active": True,
                                "is_deleted": False,
                                "created_at": datetime.now().isoformat(),
                                "updated_at": datetime.now().isoformat()
                            }
                            personil_records.append(personil_record)
                    except (ValueError, TypeError):
                        # Skip invalid NRP
                        continue
        
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
        
        # Jabatan mapping yang lebih akurat
        jabatan_mapping = {
            # PIMPINAN
            'KAPOLRES SAMOSIR ': 1,
            'WAKAPOLRES': 2,
            
            # BAG OPS
            'KABAG OPS': 3,
            'PS. PAUR SUBBAGBINOPS': 4,
            'BA MIN BAG OPS': 5,
            'ASN BAG OPS': 6,
            
            # SPKT (BAG LOG)
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
            
            # BAG SDM
            'PS. KABAG SDM': 16,
            'PAURSUBBAGBINKAR': 17,
            'BA MIN BAG SDM': 18,
            
            # BAG LOG
            'BA POLRES SAMOSIR': 19,
            'ADC KAPOLRES': 20,
            'BINTARA SATLANTAS': 21,
            'Plt. KASUBBAGBEKPAL': 22,
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
                "analysis_type": "csv_semicolon_structured",
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
        output_file = os.path.join(output_dir, "personil_csv_analysis.json")
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(analysis_result, f, indent=2, ensure_ascii=False)
        
        print(f"💾 Analisis CSV disimpan ke: {output_file}")
        
        # Simpan summary
        summary_file = os.path.join(output_dir, "personil_csv_summary.json")
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
    csv_file = "/opt/lampp/htdocs/sprin/file/DATA PERS FEBRUARI 2026 NEW.csv"
    output_dir = "/opt/lampp/htdocs/sprin/file"
    
    if not os.path.exists(csv_file):
        print(f"❌ File tidak ditemukan: {csv_file}")
        return
    
    result = analyze_csv_correct(csv_file, output_dir)
    
    if result:
        print("\n🎉 Analisis CSV selesai!")
        print(f"👥 Total personil: {result['metadata']['total_personil']}")
        print(f"📁 Total sections: {result['metadata']['total_sections']}")
        print(f"🎯 Jabatan mapped: {result['jabatan_mapping_summary']['total_mapped']}")
        print(f"⚠️  Jabatan unmapped: {result['jabatan_mapping_summary']['total_unmapped']}")
        
        print("\n📊 Distribusi per Section:")
        for section, stats in result['section_statistics'].items():
            print(f"   {section}: {stats['total']} personil")

if __name__ == "__main__":
    main()
