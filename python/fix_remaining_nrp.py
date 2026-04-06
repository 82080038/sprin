#!/usr/bin/env python3
"""
Script to fix remaining NRP values with manual mapping
"""

import json

def fix_remaining_nrp():
    """Fix remaining NRP values that weren't caught by the first script"""
    
    # Load JSON data
    with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'r', encoding='utf-8') as f:
        json_data = json.load(f)
    
    # Manual mapping for remaining cases
    manual_fixes = {
        5: "02120141",    # AGUNG NUGRAHA NADAP-DAP
        6: "03010386",    # ALDI PRANATA GINTING
        7: "02040489",    # HENDRIKSON SILALAHI
        8: "02071119",    # TOHONAN SITOHANG
        9: "03101364",    # GILANG SUTOYO
        17: "00010166",   # EDY SUSANTO PARDEDE
        19: "01070820",   # GABRIEL PAULIMA NADEAK
        20: "02091526",   # ANDRE OWEN PURBA
        21: "04070159",   # EDWARD FERDINAND SIDABUTAR
        22: "03060873",   # BIMA SANTO HUTAGAOL
        23: "03121291",   # KRISTIAN M. H. NABABAN
        26: "03080202",   # GRENIEL WIARTO SIHITE
        31: "05070285",   # EFRANTA SAPUTRA SITEPU
        33: "00080579",   # REYSON YOHANNES SIMBOLON
        34: "02090891",   # ANDRE TARUNA SIMBOLON
        35: "03081525",   # YOLANDA NAULIVIA ARITONANG
        42: "00010095",   # PRIADI MAROJAHAN HUTABARAT
        43: "03070263",   # CHRIST JERICHO SAPUTRA TAMPUBOLON
        45: "04010804",   # YOGI ADE PRATAMA SITOHANG
        49: "03070010",   # HESKIEL WANDANA MELIALA
        50: "03040138",   # DANIEL RICARDO SARAGIH
        57: "00070791",   # ANDREAS D. S. SITANGGANG
        58: "01101139",   # JACKSON SIDABUTAR
        68: "00080343",   # SIMON TIGRIS SIAGIAN
        69: "01080575",   # FIRIAN JOSUA SITORUS
        75: "04020118",   # RONAL PARTOGI SITUMORANG
        85: "02030032",   # DIEN VAROSCY I. SITUMORANG
        86: "02120339",   # ARDY TRIANO MALAU
        87: "02040459",   # JUNEDI SAGALA
        88: "02101010",   # GABRIEL SEBASTIAN SIREGAR
        89: "04020209",   # RIO F. T ERENST PANJAITAN
        90: "04080118",   # AGHEO HARMANA JOUSTRA SINURAYA
        91: "04010932",   # SAMUEL RINALDI PAKPAHAN
        92: "04040520",   # RAYMONTIUS HAROMUNTE
        112: "00030346",  # RIDHOTUA F. SITANGGANG
        113: "00110362",  # NICHO FERNANDO SARAGIH
    }
    
    changes_made = []
    
    # Apply fixes
    for person in json_data['personil_data']:
        person_id = person['id']
        if person_id in manual_fixes:
            old_nrp = str(person['nrp'])
            new_nrp = manual_fixes[person_id]
            person['nrp'] = int(new_nrp) if new_nrp.isdigit() else new_nrp
            changes_made.append({
                'id': person_id,
                'nama': person['nama'],
                'old_nrp': old_nrp,
                'new_nrp': new_nrp
            })
    
    # Save updated JSON
    with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'w', encoding='utf-8') as f:
        json.dump(json_data, f, indent=2, ensure_ascii=False)
    
    # Print summary
    print(f"✅ Fixed additional {len(changes_made)} NRP values:")
    for change in changes_made:
        print(f"  ID {change['id']}: {change['nama'][:30]}... {change['old_nrp']} → {change['new_nrp']}")
    
    return len(changes_made)

if __name__ == "__main__":
    changes = fix_remaining_nrp()
    print(f"\n🎯 Additional NRP values fixed: {changes}")
