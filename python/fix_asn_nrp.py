#!/usr/bin/env python3
"""
Final fix for remaining 3 ASN personnel with 18-digit NRP
"""

import json

def fix_asn_nrp():
    """Fix remaining 3 ASN personnel with 18-digit NRP"""
    
    # Load JSON data
    with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    # Fix the 3 remaining cases with long NRP (ASN personnel)
    fixes = {
        10: '198112262024211002',  # FERNANDO SILALAHI, A.Md.
        28: '198111252014122004',  # REYMESTA AMBARITA, S.Kom.
        51: '197008291993032002'   # NENENG GUSNIARTI
    }
    
    for person in data['personil_data']:
        if person['id'] in fixes:
            old_nrp = person['nrp']
            person['nrp'] = fixes[person['id']]
            print(f'Fixed ID {person["id"]}: {person["nama"][:40]}... {old_nrp} → {fixes[person["id"]]}')
    
    # Save updated JSON
    with open('/opt/lampp/htdocs/sprin/file/personil_complete_data.json', 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=2, ensure_ascii=False)
    
    print('\n✅ All NRP values fixed!')
    
    # Final verification
    valid_count = 0
    total = 0
    for person in data['personil_data']:
        total += 1
        nrp = person['nrp']
        if isinstance(nrp, str) and (len(nrp) == 8 or len(nrp) == 18) and nrp.isdigit():
            valid_count += 1
    
    print(f'\n📊 Final Summary:')
    print(f'Total personil: {total}')
    print(f'Valid NRP: {valid_count}')
    print(f'Invalid NRP: {total - valid_count}')
    
    return total - valid_count

if __name__ == "__main__":
    invalid = fix_asn_nrp()
    print(f'\n🎯 Remaining invalid NRP: {invalid}')
