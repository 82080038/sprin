# SPRIN Database Schema Documentation

## Overview
The SPRIN application uses MySQL database to store personnel, units, and organizational data.

## Tables

### personil
Stores personnel information.

| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| nama | VARCHAR(255) | Personnel name |
| nrp | VARCHAR(20) | Police registration number |
| pangkat | VARCHAR(100) | Rank/grade |
| bagian_id | INT | Foreign key to bagian table |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

### bagian
Stores organizational units.

| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| nama | VARCHAR(255) | Unit name |
| deskripsi | TEXT | Unit description |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

### unsur
Stores organizational elements.

| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| nama | VARCHAR(255) | Element name |
| deskripsi | TEXT | Element description |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

### users
Stores user accounts.

| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| username | VARCHAR(50) | Username |
| password | VARCHAR(255) | Hashed password |
| email | VARCHAR(255) | Email address |
| role | VARCHAR(50) | User role |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

## Relationships

- personil.bagian_id → bagian.id (Many-to-One)
- bagian has many personil (One-to-Many)

## Indexes

### personil table
- PRIMARY KEY (id)
- INDEX (nrp)
- INDEX (bagian_id)
- INDEX (pangkat)

### bagian table
- PRIMARY KEY (id)
- INDEX (nama)

### unsur table
- PRIMARY KEY (id)
- INDEX (nama)

### users table
- PRIMARY KEY (id)
- UNIQUE KEY (username)
- INDEX (email)

## Sample Data

### personil table
```sql
INSERT INTO personil (nama, nrp, pangkat, bagian_id) VALUES
('John Doe', '123456789', 'Inspector', 1),
('Jane Smith', '987654321', 'Senior Inspector', 2);
```

### bagian table
```sql
INSERT INTO bagian (nama, deskripsi) VALUES
('Intelligence', 'Handles intelligence operations'),
('Operations', 'Handles operational tasks');
```

## Security Considerations

1. All passwords are hashed using password_hash()
2. User input is validated and sanitized
3. SQL injection is prevented with prepared statements
4. Session management is secure

## Performance Considerations

1. Indexes are properly configured
2. Queries use prepared statements
3. Connection pooling is implemented
4. Regular maintenance is scheduled

---

*This schema documentation is updated automatically with database changes.*
