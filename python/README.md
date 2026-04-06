# Python Integration for SPRIN Application

## Overview
This folder contains Python scripts and utilities for the SPRIN (Sistem Personil Polres Samosir) application.

## Current Status
- **No Python components currently in the main application**
- This folder is prepared for future Python integration
- Main application is PHP-based

## Recent Python Integration

### 📊 Excel Analysis Tools (April 2026)

#### 1. **Excel Data Analyzer** (`analyze_excel.py`)
- **Purpose**: Menganalisis file Excel DATA PERS FEBRUARI 2026 NEW.xlsx
- **Features**:
  - Multi-sheet analysis
  - Data type detection
  - Statistical analysis
  - JSON conversion
- **Output**: 
  - `DATA_PERS_FEBRUARI_2026_analysis.json` (75KB)
  - `DATA_PERS_summary.json` (762B)

#### 2. **Personil Data Extractor** (`extract_personil.py`)
- **Purpose**: Mengekstrak data personil dari Excel ke JSON siap import
- **Features**:
  - Data cleaning and validation
  - Jabatan mapping to database IDs
  - Personil statistics
  - Unmatched jabatan tracking
- **Output**:
  - `personil_extracted.json` (85KB - 255 personil)
  - `personil_extraction_summary.json` (4KB)

### 📈 Analysis Results

#### **Data Overview**:
- **Total Sheets**: 3 (Sheet1, Sheet2, Sheet3)
- **Main Data**: Sheet1 dengan 286 baris, 6 kolom
- **Valid Personil**: 255 records
- **Jabatan Types**: 96 different positions
- **Pangkat Types**: 13 different ranks

#### **Top Statistics**:
- **Most Common**: BINTARA SAT RESKRIM (27 personil)
- **Pangkat Distribution**: BRIPDA (83 personil), BRIGPOL (41 personil)
- **Unmatched Jabatan**: 49 positions need mapping

#### **Data Quality**:
- ✅ Complete NRP and NAMA fields
- ✅ Valid pangkat assignments
- ⚠️ 49 jabatan need database mapping
- ✅ Ready for database import

## Potential Python Use Cases
1. **Data Analysis & Reporting**
   - Personnel statistics analysis
   - Export data processing
   - Advanced reporting

2. **Database Utilities**
   - Data migration scripts
   - Backup automation
   - Data validation

3. **API Integration**
   - External service connections
   - Data synchronization
   - Web scraping utilities

4. **Machine Learning**
   - Personnel assignment optimization
   - Predictive analytics
   - Pattern recognition

## Setup

### Virtual Environment
```bash
# Create virtual environment
python3 -m venv venv

# Activate
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt
```

### Dependencies
Create `requirements.txt`:
```
mysql-connector-python==8.0.33
pandas==2.0.3
numpy==1.24.3
matplotlib==3.7.1
seaborn==0.12.2
flask==2.3.2
requests==2.31.0
python-dotenv==1.0.0
```

## Database Connection
```python
import mysql.connector

config = {
    'host': 'localhost',
    'user': 'root',
    'password': 'root',
    'database': 'bagops'
}

conn = mysql.connector.connect(**config)
```

## Security Notes
- Never commit database credentials to version control
- Use environment variables for sensitive data
- Validate all inputs and sanitize database queries
- Follow secure coding practices

## Integration with PHP Application
Python scripts can be called from PHP using:
```php
<?php
$output = shell_exec('python3 /path/to/script.py');
echo $output;
?>
```

## Development Guidelines
1. Keep Python scripts modular and reusable
2. Add comprehensive error handling
3. Include logging for debugging
4. Write unit tests for critical functions
5. Document all functions and classes

---

**Note**: This is a preparation folder. No Python components are currently integrated into the main SPRIN application.
