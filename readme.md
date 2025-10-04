# Prédiction de Paris Sportifs avec Machine Learning

## Description

Ce script PHP permet de prédire les résultats de matchs de football en utilisant un modèle de machine learning. Il récupère les données de matchs passés via une API, calcule la forme des équipes et utilise un modèle K-Nearest Neighbors (KNN) pour estimer l’issue des rencontres. Il propose également un indicateur simple pour identifier les « value bets ».

Le script est interactif et fournit une interface web permettant de sélectionner les équipes et de générer une prédiction instantanément.

Les principaux objectifs de ce script sont :

- Analyser les performances passées des équipes.
- Estimer la probabilité de victoire, match nul ou défaite pour un match.
- Aider les utilisateurs à identifier des opportunités de pari potentiellement intéressantes.

---

## Fonctionnalités

1. Récupération automatique des données de matchs via l’API Football-Data.org.
2. Calcul de la forme récente des équipes (basé sur les 5 derniers matchs).
3. Préparation des données pour le modèle de machine learning.
4. Entraînement d’un modèle K-Nearest Neighbors pour prédire le résultat des matchs.
5. Interface web simple pour sélectionner les équipes et afficher la prédiction.
6. Indication d’un « value bet » basé sur un calcul simplifié des cotes et de la forme des équipes.

---

## Prérequis

Pour utiliser ce script, vous devez disposer de :

- PHP 8 ou supérieur.
- Composer pour installer les dépendances.
- Une clé API Football-Data.org.
- Accès à un serveur web (Apache, Nginx, ou serveur local comme XAMPP, WAMP, MAMP).

---

## Installation

1. Clonez ou téléchargez le dépôt.
2. Installez les dépendances avec Composer :

```
composer install
```

3. Remplacez la clé API dans le fichier `index.php` : `$apiKey = 'VOTRE_CLE_API_ICI';`
4. Placez les fichiers du projet dans le dossier de votre serveur web.
5. Accédez à l’URL correspondante depuis votre navigateur pour utiliser l’interface.

---

## Utilisation

1. Ouvrez le script dans votre navigateur.
2. Sélectionnez l’équipe à domicile et l’équipe à l’extérieur dans le formulaire.
3. Cliquez sur le bouton « Prédire ».

Le script affichera :
- Le résultat prédit (victoire domicile, match nul ou victoire extérieur).
- La précision actuelle du modèle.
- Une indication sur le potentiel « value bet ».

---

## Structure du code

Le script est organisé en plusieurs sections principales :

- **Récupération des matchs** : utilisation de cURL pour interroger l’API et récupérer les résultats des matchs.
- **Calcul de la forme des équipes** : analyse des 5 derniers matchs pour déterminer un score de performance.
- **Préparation des données pour le ML** : création de `samples` et `labels` pour entraîner le modèle.
- **Entraînement du modèle KNN** : utilisation de la librairie PHP-ML pour entraîner le classificateur.
- **Évaluation du modèle** : calcul de la précision (accuracy) des prédictions sur les matchs connus.
- **Interface web** : formulaire pour sélectionner les équipes et afficher les résultats.

---

## Dépendances

Le script utilise la librairie suivante :
- **PHP-ML** : librairie PHP pour le machine learning.

Pour l’installer via Composer :
```
composer require php-ai/php-ml
```

---

## Limitations

- Les prédictions ne prennent en compte que la forme récente des équipes.
- Les facteurs comme les blessures, les suspensions ou les conditions météo ne sont pas inclus.
- Le calcul du « value bet » est simplifié et ne remplace pas une analyse complète des cotes des bookmakers.
- La précision du modèle peut varier selon la quantité et la qualité des données disponibles.

---

## Améliorations possibles

- Intégrer des statistiques supplémentaires comme les tirs cadrés, la possession ou les confrontations directes.
- Ajouter une vraie récupération des cotes des bookmakers via une API.
- Tester différents modèles de machine learning ou ajuster les paramètres du KNN.
- Ajouter une interface graphique plus interactive avec des graphiques et des historiques.
- Mettre en place une validation croisée pour évaluer le modèle de manière plus réaliste.

---

## Conseils d’utilisation

- Mettez régulièrement à jour les données pour que le modèle soit toujours entraîné sur les matchs les plus récents.
- Considérez les résultats comme une aide à la décision et non comme une garantie de gain.
- Combinez les prédictions du script avec votre analyse personnelle pour augmenter vos chances de succès.
- N’investissez jamais plus que ce que vous êtes prêt à perdre.

---

## Licence

Ce projet est libre et peut être utilisé à des fins personnelles ou éducatives. Il est fourni sans garantie de performance ou de résultats dans le cadre des paris sportifs.

---

## Remerciements

Merci à Football-Data.org pour la fourniture de l’API de données footballistiques, et à PHP-ML pour les outils de machine learning en PHP.