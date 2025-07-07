<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration API Key - EduLearn</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #007BFF 0%, #0F4C75 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #007BFF;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .status {
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            font-weight: 500;
        }
        
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #007BFF;
        }
        
        .btn {
            background: #007BFF;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .instructions h3 {
            color: #007BFF;
            margin-bottom: 10px;
        }
        
        .instructions ol {
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        .security-note {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #007BFF;
            margin: 20px 0;
        }
        
        .test-button {
            background: #28a745;
            margin-left: 10px;
        }
        
        .test-button:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Configuration API Key</h1>
            <p>Configurez votre clé API Gemini de manière sécurisée</p>
        </div>

        <?php
        $envFile = '.env';
        $configStatus = '';
        $isConfigured = false;

        // Inclure la configuration API
        if (file_exists('config/api_config.php')) {
            require_once 'config/api_config.php';
            $isConfigured = isApiConfigured();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_key'])) {
            $apiKey = trim($_POST['api_key']);
            
            if (!empty($apiKey) && $apiKey !== 'YOUR_ACTUAL_GEMINI_API_KEY_HERE') {
                // Lire le fichier .env existant
                $envContent = '';
                if (file_exists($envFile)) {
                    $envContent = file_get_contents($envFile);
                }
                
                // Mettre à jour ou ajouter la clé API
                if (strpos($envContent, 'GEMINI_API_KEY=') !== false) {
                    $envContent = preg_replace('/GEMINI_API_KEY=.*/', 'GEMINI_API_KEY=' . $apiKey, $envContent);
                } else {
                    $envContent .= "\nGEMINI_API_KEY=" . $apiKey;
                }
                
                // Sauvegarder
                if (file_put_contents($envFile, $envContent)) {
                    $configStatus = '<div class="status success">✅ Clé API configurée avec succès et sauvegardée de manière sécurisée !</div>';
                    $isConfigured = true;
                } else {
                    $configStatus = '<div class="status error">❌ Erreur lors de la sauvegarde. Vérifiez les permissions.</div>';
                }
            } else {
                $configStatus = '<div class="status error">❌ Veuillez entrer une clé API valide.</div>';
            }
        }

        echo $configStatus;

        if ($isConfigured) {
            echo '<div class="status success">
                    <strong>🎉 Configuration terminée !</strong><br>
                    Votre clé API est active et sécurisée dans le fichier .env
                  </div>';
        } else {
            echo '<div class="status warning">
                    <strong>⚠️ API non configurée</strong><br>
                    Veuillez configurer votre clé API Gemini ci-dessous
                  </div>';
        }
        ?>

        <div class="instructions">
            <h3>📋 Instructions :</h3>
            <ol>
                <li>Visitez <a href="https://aistudio.google.com/" target="_blank">Google AI Studio</a></li>
                <li>Connectez-vous avec votre compte Google</li>
                <li>Cliquez sur "Get API Key" dans le menu</li>
                <li>Créez un nouveau projet ou sélectionnez un existant</li>
                <li>Générez une nouvelle clé API</li>
                <li>Copiez la clé et collez-la ci-dessous</li>
            </ol>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="api_key">🔑 Clé API Gemini :</label>
                <input 
                    type="password" 
                    id="api_key" 
                    name="api_key" 
                    placeholder="Collez votre clé API ici..." 
                    required
                    <?php echo $isConfigured ? 'value="[CONFIGURÉE]" disabled' : ''; ?>
                >
            </div>
            
            <button type="submit" class="btn" <?php echo $isConfigured ? 'disabled' : ''; ?>>
                <?php echo $isConfigured ? '✅ Déjà Configurée' : '💾 Sauvegarder'; ?>
            </button>
            
            <?php if ($isConfigured): ?>
            <a href="student/quiz.php" class="btn test-button">🧠 Tester les Quiz IA</a>
            <?php endif; ?>
        </form>

        <div class="security-note">
            <strong>🛡️ Note de Sécurité :</strong> Votre clé API est stockée dans le fichier <code>.env</code> qui est automatiquement exclu de Git. Elle ne sera jamais partagée publiquement.
        </div>

        <?php if ($isConfigured): ?>
        <div style="margin-top: 20px; text-align: center;">
            <p><strong>🎯 Prochaines étapes :</strong></p>
            <p>✅ Testez la génération de quiz IA<br>
               ✅ Explorez l'assistant IA<br>
               ✅ Votre projet est prêt pour GitHub !</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Afficher/masquer la clé API
        const input = document.getElementById('api_key');
        input.addEventListener('dblclick', function() {
            this.type = this.type === 'password' ? 'text' : 'password';
        });
    </script>
</body>
</html>
