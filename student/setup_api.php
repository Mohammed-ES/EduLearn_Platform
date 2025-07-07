<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration API Gemini - EduLearn</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .step h3 {
            margin-top: 0;
            color: #007bff;
        }
        .code {
            background: #f1f3f4;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Configuration API Gemini AI</h1>
        
        <div class="warning">
            <strong>⚠️ Information:</strong> L'API Gemini n'est pas encore configurée. Pour utiliser la génération automatique de quiz, suivez les étapes ci-dessous.
        </div>

        <div class="step">
            <h3>🔑 Étape 1: Obtenir une clé API</h3>
            <p>1. Rendez-vous sur <a href="https://aistudio.google.com/" target="_blank">Google AI Studio</a></p>
            <p>2. Connectez-vous avec votre compte Google</p>
            <p>3. Cliquez sur "Get API key" dans le menu de gauche</p>
            <p>4. Créez une nouvelle clé API</p>
            <p>5. Copiez la clé générée</p>
        </div>

        <div class="step">
            <h3>📁 Étape 2: Créer le fichier .env</h3>
            <p>1. Créez un fichier nommé <code>.env</code> dans le dossier racine EduLearn</p>
            <p>2. Ajoutez votre clé API dans ce fichier :</p>
            <div class="code">
GEMINI_API_KEY=VOTRE_CLE_API_ICI<br>
GEMINI_MODEL=gemini-2.0-flash
            </div>
        </div>

        <div class="step">
            <h3>⚙️ Étape 3: Alternative - Modification directe</h3>
            <p>Si vous préférez ne pas utiliser de fichier .env :</p>
            <p>1. Ouvrez le fichier <code>config/api_config.php</code></p>
            <p>2. Remplacez <code>YOUR_ACTUAL_GEMINI_API_KEY_HERE</code> par votre vraie clé API</p>
        </div>

        <div class="step">
            <h3>✅ Étape 4: Tester la configuration</h3>
            <p>Une fois configuré, le générateur de quiz IA sera automatiquement activé.</p>
            <a href="quiz.php" class="btn">Retour à la page Quiz</a>
        </div>

        <div class="success">
            <strong>📚 Note:</strong> Même sans API, vous pouvez utiliser toutes les autres fonctionnalités d'EduLearn : prise de notes, gestion des étudiants, tableaux de bord, etc.
        </div>

        <?php
        try {
            include('../config/api_config.php');
            if (isApiConfigured()) {
                echo '<div class="success"><strong>✅ Succès:</strong> API Gemini configurée et prête à utiliser !</div>';
            } else {
                echo '<div class="warning"><strong>⏳ En attente:</strong> Configuration API requise pour activer la génération de quiz IA.</div>';
            }
        } catch (Exception $e) {
            echo '<div class="warning"><strong>⚠️ Status:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
</body>
</html>
