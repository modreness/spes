<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nedozvoljeni pristup - SPES</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="error-page">
        <div class="error-container">
            <div class="error-icon">
                <i class="fa-solid fa-shield-exclamation"></i>
            </div>
            
            <div class="error-content">
                <h1>Nedozvoljeni pristup</h1>
                <p class="error-message">
                    Nemate dozvolu za pristup ovoj stranici ili funkcionalnosti.
                </p>
                
                <div class="error-details">
                    <div class="error-detail-item">
                        <i class="fa-solid fa-user"></i>
                        <span>Vaša uloga: <strong><?= htmlspecialchars($user['uloga'] ?? 'Nepoznata') ?></strong></span>
                    </div>
                    
                    <?php if (isset($required_permission)): ?>
                    <div class="error-detail-item">
                        <i class="fa-solid fa-key"></i>
                        <span>Potrebna dozvola: <strong><?= htmlspecialchars($required_permission) ?></strong></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="error-actions">
                    <a href="javascript:history.back()" class="error-btn error-btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i>
                        Nazad
                    </a>
                    
                    <a href="/dashboard" class="error-btn error-btn-primary">
                        <i class="fa-solid fa-home"></i>
                        Dashboard
                    </a>
                    
                    <?php if (in_array($user['uloga'] ?? '', ['admin', 'recepcioner'])): ?>
                    <a href="/admin/dozvole" class="error-btn error-btn-info">
                        <i class="fa-solid fa-shield-halved"></i>
                        Upravljanje dozvolama
                    </a>
                    <?php endif; ?>
                </div>
                
                <div class="error-help">
                    <h3>Potrebna pomoć?</h3>
                    <p>
                        Ako mislite da trebate pristup ovoj funkcionalnosti, 
                        kontaktirajte administratora sistema.
                    </p>
                    
                    <div class="error-contact">
                        <div class="error-contact-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span>admin@spes.ba</span>
                        </div>
                        <div class="error-contact-item">
                            <i class="fa-solid fa-phone"></i>
                            <span>063/123-456</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .error-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        font-family: 'Montserrat', sans-serif;
    }

    .error-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 40px;
        text-align: center;
        max-width: 600px;
        width: 100%;
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .error-icon {
        font-size: 5rem;
        color: #e74c3c;
        margin-bottom: 20px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    .error-content h1 {
        color: #2c3e50;
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .error-message {
        color: #7f8c8d;
        font-size: 1.2rem;
        margin-bottom: 30px;
        line-height: 1.5;
    }

    .error-details {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
        text-align: left;
    }

    .error-detail-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
        font-size: 1rem;
    }

    .error-detail-item:last-child {
        margin-bottom: 0;
    }

    .error-detail-item i {
        color: #3498db;
        width: 20px;
        text-align: center;
    }

    .error-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 40px;
    }

    .error-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-family: 'Montserrat', sans-serif;
    }

    .error-btn-primary {
        background: #3498db;
        color: white;
    }

    .error-btn-primary:hover {
        background: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .error-btn-secondary {
        background: #95a5a6;
        color: white;
    }

    .error-btn-secondary:hover {
        background: #7f8c8d;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
    }

    .error-btn-info {
        background: #e67e22;
        color: white;
    }

    .error-btn-info:hover {
        background: #d35400;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);
    }

    .error-help {
        border-top: 1px solid #ecf0f1;
        padding-top: 30px;
        text-align: center;
    }

    .error-help h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        font-size: 1.3rem;
    }

    .error-help p {
        color: #7f8c8d;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .error-contact {
        display: flex;
        gap: 30px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .error-contact-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #3498db;
        font-weight: 500;
    }

    .error-contact-item i {
        color: #e67e22;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .error-container {
            padding: 30px 20px;
        }
        
        .error-content h1 {
            font-size: 2rem;
        }
        
        .error-message {
            font-size: 1rem;
        }
        
        .error-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .error-btn {
            width: 100%;
            justify-content: center;
            max-width: 250px;
        }
        
        .error-contact {
            flex-direction: column;
            gap: 15px;
        }
    }
    </style>
</body>
</html>