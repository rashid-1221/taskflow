# 📱 TaskFlow sur mobile — Guide d'installation

## Étape 1 — Générer les icônes (une seule fois)

Ouvrez dans votre navigateur :
```
http://localhost/taskflow/generate-icons.php
```
Une fois que vous voyez "✅ Icônes générées", vous pouvez supprimer `generate-icons.php`.

---

## Étape 2 — Installer Tailscale (accès privé depuis n'importe où)

Tailscale crée un réseau privé entre vos appareils. Votre téléphone voit votre PC comme s'il était sur votre WiFi — même depuis l'autre bout du monde. Gratuit, aucune donnée publique.

### Sur le PC (Windows)
1. Téléchargez sur https://tailscale.com/download/windows
2. Installez et connectez-vous (créez un compte gratuit)
3. Notez l'**IP Tailscale** de votre PC (ex : `100.64.x.x`) — visible dans l'interface Tailscale

### Sur Android
1. Installez **Tailscale** depuis le Play Store
2. Connectez-vous avec le même compte
3. Activez Tailscale

### Sur iPhone
1. Installez **Tailscale** depuis l'App Store
2. Connectez-vous avec le même compte
3. Activez Tailscale

---

## Étape 3 — Ouvrir TaskFlow sur mobile

Dans le navigateur de votre téléphone, allez sur :
```
http://100.64.X.X/taskflow/
```
(remplacez `100.64.X.X` par l'IP Tailscale de votre PC)

> **Important :** MAMP doit être lancé sur votre PC pour que la sync fonctionne.

---

## Étape 4 — Installer l'app sur l'écran d'accueil

### Android (Chrome)
1. Ouvrez l'URL ci-dessus dans Chrome
2. Menu (⋮) → **"Ajouter à l'écran d'accueil"**
3. L'app apparaît comme une vraie app 🎉

### iPhone (Safari)
1. Ouvrez l'URL dans **Safari** (pas Chrome — obligatoire pour l'installation)
2. Bouton Partager (□↑) → **"Sur l'écran d'accueil"**
3. L'app apparaît comme une vraie app 🎉

---

## Comment fonctionne la synchronisation

- Chaque modification sur PC ou téléphone est envoyée automatiquement à `api/sync.php`
- Quand vous ouvrez l'app sur un autre appareil, elle récupère la version la plus récente
- La sync est **bidirectionnelle** : le dernier à écrire gagne
- Indicateur de sync : le petit point coloré dans l'en-tête de l'app

---

## Rendre MAMP accessible via Tailscale

Par défaut MAMP écoute sur `localhost` uniquement. Pour qu'il soit accessible via l'IP Tailscale :

1. Ouvrez MAMP → **Préférences** → **Ports**
2. Vérifiez que le port Apache est **80**
3. Dans `C:\MAMP\conf\apache\httpd.conf`, cherchez la ligne :
   ```
   Listen 127.0.0.1:80
   ```
   Remplacez par :
   ```
   Listen 80
   ```
4. Redémarrez MAMP

Votre TaskFlow est maintenant accessible depuis votre téléphone via Tailscale 🚀
