#!/usr/bin/env python3
"""
Script untuk menganalisis file Excel DATA PERS FEBRUARI 2026 NEW.xlsx
dan mengkonversinya ke format JSON
"""

import pandas as pd
import json
import os
from datetime import datetime

def analyze_excel_file(excel_path, output_dir):
    """
    Menganalisis file Excel dan menyimpan hasilnya ke JSON
    """
    print(f"🔍 Menganalisis file: {excel_path}")
    
    try:
        # Baca semua sheet dari Excel
        excel_file = pd.ExcelFile(excel_path)
        print(f"📊 Sheet yang ditemukan: {excel_file.sheet_names}")
        
        # Dictionary untuk menyimpan semua data
        analysis_result = {
            "metadata": {
                "filename": os.path.basename(excel_path),
                "analysis_date": datetime.now().isoformat(),
                "total_sheets": len(excel_file.sheet_names),
                "sheet_names": excel_file.sheet_names
            },
            "sheets": {}
        }
        
        # Proses setiap sheet
        for sheet_name in excel_file.sheet_names:
            print(f"\n📋 Memproses sheet: {sheet_name}")
            
            # Baca sheet
            df = pd.read_excel(excel_path, sheet_name=sheet_name)
            
            # Analisis dasar
            sheet_info = {
                "basic_info": {
                    "total_rows": len(df),
                    "total_columns": len(df.columns),
                    "column_names": df.columns.tolist(),
                    "data_types": df.dtypes.to_dict()
                },
                "data_preview": {},
                "data_analysis": {},
                "cleaned_data": None
            }
            
            # Preview data (5 baris pertama)
            preview_data = df.head().to_dict('records')
            sheet_info["data_preview"] = preview_data
            
            # Analisis data per kolom
            for col in df.columns:
                col_analysis = {
                    "non_null_count": df[col].count(),
                    "null_count": df[col].isnull().sum(),
                    "unique_values": df[col].nunique(),
                    "data_type": str(df[col].dtype)
                }
                
                # Jika kolom numerik, tambahkan statistik
                if df[col].dtype in ['int64', 'float64']:
                    col_analysis.update({
                        "min": df[col].min(),
                        "max": df[col].max(),
                        "mean": df[col].mean(),
                        "median": df[col].median()
                    })
                
                # Jika kolom string/object, tambahkan sample values
                elif df[col].dtype == 'object':
                    unique_vals = df[col].dropna().unique()
                    col_analysis["sample_values"] = unique_vals[:10].tolist()
                
                sheet_info["data_analysis"][col] = col_analysis
            
            # Clean data untuk JSON
            cleaned_df = df.copy()
            
            # Konversi NaN ke None untuk JSON compatibility
            cleaned_df = cleaned_df.where(pd.notnull(cleaned_df), None)
            
            # Konversi datetime ke string
            for col in cleaned_df.columns:
                if cleaned_df[col].dtype == 'datetime64[ns]':
                    cleaned_df[col] = cleaned_df[col].dt.strftime('%Y-%m-%d %H:%M:%S')
            
            # Convert ke records
            sheet_info["cleaned_data"] = cleaned_df.to_dict('records')
            
            analysis_result["sheets"][sheet_name] = sheet_info
            
            print(f"✅ Sheet {sheet_name}: {len(df)} baris, {len(df.columns)} kolom")
        
        # Simpan hasil ke JSON
        output_file = os.path.join(output_dir, "DATA_PERS_FEBRUARI_2026_analysis.json")
        
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(analysis_result, f, indent=2, ensure_ascii=False, default=str)
        
        print(f"\n💾 Hasil analisis disimpan ke: {output_file}")
        
        # Buat summary file
        summary_file = os.path.join(output_dir, "DATA_PERS_summary.json")
        summary_data = {
            "file_info": analysis_result["metadata"],
            "sheets_summary": {}
        }
        
        for sheet_name, sheet_data in analysis_result["sheets"].items():
            summary_data["sheets_summary"][sheet_name] = {
                "rows": sheet_data["basic_info"]["total_rows"],
                "columns": sheet_data["basic_info"]["total_columns"],
                "column_names": sheet_data["basic_info"]["column_names"]
            }
        
        with open(summary_file, 'w', encoding='utf-8') as f:
            json.dump(summary_data, f, indent=2, ensure_ascii=False)
        
        print(f"📄 Summary disimpan ke: {summary_file}")
        
        return analysis_result
        
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return None

def main():
    # Path file Excel
    excel_file = "/opt/lampp/htdocs/sprin/file/DATA PERS FEBRUARI 2026 NEW.xlsx"
    output_dir = "/opt/lampp/htdocs/sprin/file"
    
    # Check jika file ada
    if not os.path.exists(excel_file):
        print(f"❌ File tidak ditemukan: {excel_file}")
        return
    
    # Analisis file
    result = analyze_excel_file(excel_file, output_dir)
    
    if result:
        print("\n🎉 Analisis selesai!")
        print(f"📊 Total sheet: {result['metadata']['total_sheets']}")
        print(f"📋 Sheet names: {', '.join(result['metadata']['sheet_names'])}")
        
        # Tampilkan summary per sheet
        for sheet_name, sheet_data in result['sheets'].items():
            info = sheet_data['basic_info']
            print(f"\n📈 {sheet_name}:")
            print(f"   - Baris: {info['total_rows']}")
            print(f"   - Kolom: {info['total_columns']}")
            print(f"   - Kolom: {', '.join(info['column_names'])}")

if __name__ == "__main__":
    main()
