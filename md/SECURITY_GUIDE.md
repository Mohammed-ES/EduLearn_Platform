# 🔐 Guide de Sécurité API - EduLearn

## ⚠️ IMPORTANT : Protection de votre API Key

### 🚨 Votre API Key Gemini est PRIVÉE !

Votre clé API est stockée dans le fichier `.env` qui est **automatiquement exclu** de Git.

### 📁 Fichiers de Configuration :

```
📄 .env              ← PRIVÉ (votre vraie clé API)
📄 .env.example      ← PUBLIC (template sans clé)
📄 .gitignore        ← Protège .env automatiquement
```

### 🔧 Comment configurer votre API Key :

1. **Obtenez votre clé** : https://aistudio.google.com/
2. **Modifiez le fichier .env** :
   ```env
   GEMINI_API_KEY=VOTRE_VRAIE_CLE_API_ICI
   ```
3. **Sauvegardez** - C'est tout !

### ✅ Vérifications de Sécurité :

- [ ] Le fichier `.env` existe avec votre vraie clé
- [ ] Le fichier `.gitignore` contient `.env`
- [ ] La clé fonctionne dans votre application
- [ ] Aucun fichier avec votre vraie clé n'est committé

### 🚀 Test de Configuration :

Visitez : `http://localhost/EduLearn/student/quiz.php`
- Si l'API fonctionne → ✅ Configuration correcte
- Si erreur → Vérifiez votre clé dans `.env`

### 🔒 Règles de Sécurité :

1. **JAMAIS** partager votre vraie clé API
2. **JAMAIS** committer le fichier `.env`
3. **TOUJOURS** utiliser `.env.example` pour les exemples
4. **TOUJOURS** vérifier le `.gitignore`

### 🆘 En cas de fuite :

Si votre clé API est exposée accidentellement :
1. **Révoquez** immédiatement la clé sur Google AI Studio
2. **Générez** une nouvelle clé
3. **Mettez à jour** votre fichier `.env`
4. **Nettoyez** l'historique Git si nécessaire

### 📞 Support :

Si vous avez des problèmes de configuration :
- Vérifiez que XAMPP/Apache est démarré
- Vérifiez les permissions du fichier `.env`
- Testez la clé directement sur Google AI Studio

---

**🛡️ Votre sécurité est notre priorité !**
