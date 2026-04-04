<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | POLRES Samosir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 50%, #ffd700 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }
        
        .error-icon {
            font-size: 5rem;
            color: #1a237e;
            margin-bottom: 20px;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #1a237e;
            line-height: 1;
            margin-bottom: 10px;
        }
        
        .error-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 15px;
        }
        
        .error-message {
            color: #666;
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn-home {
            background: linear-gradient(135deg, #1a237e, #3949ab);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(26, 35, 126, 0.3);
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 35, 126, 0.4);
            color: white;
        }
        
        .error-details {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 0.85rem;
            color: #999;
        }
        
        @media (max-width: 576px) {
            .error-container {
                padding: 40px 25px;
            }
            
            .error-code {
                font-size: 4rem;
            }
            
            .error-icon {
                font-size: 3.5rem;
            }
            
            .error-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fa-solid fa-compass"></i>
        </div>
        <div class="error-code">404</div>
        <h1 class="error-title">Halaman Tidak Ditemukan</h1>
        <p class="error-message">
            Maaf, halaman yang Anda cari tidak dapat ditemukan.<br>
            Mungkin telah dipindahkan atau dihapus.
        </p>
        <a href="/sprint/pages/main.php" class="btn-home">
            <i class="fa-solid fa-house me-2"></i>Kembali ke Dashboard
        </a>
        <div class="error-details">
            <i class="fa-solid fa-shield-halved me-1"></i>
            POLRES Samosir - Sistem Manajemen Personil
        </div>
    </div>
</body>
</html>
