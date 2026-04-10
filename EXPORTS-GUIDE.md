# 📊 GUIDE D'UTILISATION - EXPORTS PDF/EXCEL

> Guide complet d'utilisation du système d'export de données SmartMenu SaaS
> Version 2.5 | Dernière mise à jour : 2026-01-28

---

## 📋 TABLE DES MATIÈRES

1. [Présentation](#présentation)
2. [Accès aux Exports](#accès-aux-exports)
3. [Types d'Exports](#types-dexports)
4. [Guides par Export](#guides-par-export)
5. [Formats Disponibles](#formats-disponibles)
6. [Cas d'Usage](#cas-dusage)
7. [FAQ](#faq)
8. [Dépannage](#dépannage)

---

## 🎯 PRÉSENTATION

Le système d'export SmartMenu permet aux administrateurs d'extraire leurs données dans différents formats pour :
- **Archivage** : Sauvegarder les données comptables
- **Analyse** : Analyser les tendances dans Excel
- **Impression** : Imprimer des rapports professionnels
- **Partage** : Communiquer avec partenaires/investisseurs

### Formats Supportés

| Format | Extension | Usage Principal |
|--------|-----------|-----------------|
| **PDF** | `.pdf` | Archivage, impression, partage |
| **Excel** | `.xlsx` | Analyse, calculs, graphiques |
| **CSV** | `.csv` | Import dans autres logiciels |

---

## 🔐 ACCÈS AUX EXPORTS

### Prérequis
- Rôle : **ADMIN** ou **SUPER_ADMIN**
- Permissions : Accès au dashboard administrateur
- Tenant : Actif et configuré

### Accéder à la Page Rapports

1. Connectez-vous avec un compte administrateur
2. Allez dans le menu administrateur
3. Cliquez sur **"Rapports et Exports"**
4. Route : `/admin/{tenantSlug}/reports`

---

## 📦 TYPES D'EXPORTS

### 1. Export des Commandes

Exporte la liste complète des commandes avec détails.

**Données incluses :**
- N° de commande
- Date et heure
- Table
- Statut
- Nombre d'articles
- Total (FCFA)
- Notes

**Formats disponibles :** CSV, PDF, Excel

### 2. Export des Statistiques

Rapport complet avec KPIs et analyses.

**Données incluses :**
- Chiffre d'affaires
- Nombre de commandes
- Panier moyen
- Articles vendus
- Commandes par statut
- Top 10 plats
- Distribution horaire
- Évolution quotidienne

**Formats disponibles :** PDF, Excel (multi-sheet)

### 3. Export du Menu

Carte menu imprimable pour distribution.

**Données incluses :**
- Catégories
- Plats avec descriptions
- Prix
- Variantes disponibles
- Options supplémentaires
- Allergènes

**Formats disponibles :** CSV, PDF

### 4. Autres Exports

- **Réservations** : Export CSV des réservations
- **Avis Clients** : Export CSV des reviews
- **Journaux d'Audit** : Export CSV des logs

---

## 📘 GUIDES PAR EXPORT

### 🟦 EXPORT COMMANDES

#### En PDF

**Utilisation :**
1. Sélectionnez la période (date début/fin)
2. Cliquez sur **"PDF"**
3. Le fichier se télécharge automatiquement

**Contenu du PDF :**
- **Header** : Logo tenant, nom du restaurant
- **KPIs** :
  - Nombre total de commandes
  - Chiffre d'affaires total
  - Panier moyen
- **Tableau** : Liste des commandes avec badges de statut colorés
- **Footer** : Date de génération

**Exemple de nom** : `commandes-restaurant-20260128.pdf`

#### En Excel

**Utilisation :**
1. Sélectionnez la période
2. Cliquez sur **"Excel"**
3. Ouvrez le fichier dans Excel/Google Sheets

**Structure du fichier :**
- **1 feuille** : "Commandes"
- **Colonnes** :
  - N° Commande
  - Date
  - Heure
  - Table
  - Statut
  - Nombre d'articles
  - Total (FCFA)
  - Notes
- **Styling** : Header bleu, colonnes auto-dimensionnées

**Avantages :**
- Tri et filtres Excel
- Calculs personnalisés
- Graphiques

---

### 📊 EXPORT STATISTIQUES

#### En PDF

**Utilisation :**
1. Sélectionnez la période (aujourd'hui, semaine, mois, année)
2. Cliquez sur **"PDF"** dans la carte Statistiques
3. Le rapport complet se télécharge

**Contenu du PDF :**

**Page 1 - Vue d'ensemble :**
- 4 KPI Cards colorés
- Répartition commandes par statut
- Top 10 plats avec ranking
- Distribution horaire (graphique en barres)
- Évolution quotidienne du CA

**Design professionnel :**
- Palette de couleurs : Vert (#10b981)
- Layout responsive
- Footer avec confidentialité

**Exemple de nom** : `statistiques-restaurant-janvier-2026.pdf`

#### En Excel (Multi-sheet)

**Utilisation :**
1. Sélectionnez la période (dates début/fin)
2. Cliquez sur **"Excel"** dans la carte Statistiques
3. Ouvrez le fichier pour analyse

**Structure du fichier (3 feuilles) :**

**Feuille 1 : "Vue d'ensemble"**
- Titre : RAPPORT STATISTIQUES
- Tenant et période
- Indicateurs clés (CA, commandes, panier moyen)
- Commandes par statut (tableau)

**Feuille 2 : "Top Plats"**
- Top 20 plats vendus
- Colonnes : Plat, Quantité, CA (FCFA)
- Header vert (#10b981)
- Tri par quantité décroissante

**Feuille 3 : "CA Quotidien"**
- Évolution jour par jour
- Colonnes : Date, Nb Commandes, CA
- **Formule SUM** en bas pour totaux
- Header orange (#f59e0b)

**Avantages :**
- Tableaux croisés dynamiques
- Graphiques interactifs
- Formules de calcul

---

### 📋 EXPORT MENU

#### En PDF

**Utilisation :**
1. Sélectionnez le menu (ou laissez par défaut)
2. Cliquez sur **"PDF"** dans la carte Menu
3. Obtenez une carte menu imprimable

**Contenu du PDF :**
- **Header** : Logo + nom restaurant + description menu
- **Catégories** : Titre avec fond coloré
- **Plats** :
  - Nom + Prix
  - Description
  - Variantes avec prix ajustement
  - Options supplémentaires
  - Allergènes (encadré rouge)
- **Footer** : Coordonnées restaurant, date d'impression

**Cas d'usage :**
- Impression pour tables
- Affichage vitrine
- Distribution clients
- Menu QR code backup

**Exemple de nom** : `menu-restaurant-carte-principale.pdf`

---

## 🎨 FORMATS DISPONIBLES

### Format PDF

**Avantages :**
- Archivage à long terme
- Impression professionnelle
- Partage facile (email, WhatsApp)
- Apparence identique partout
- Non modifiable (intégrité)

**Librairie utilisée :** DomPDF (barryvdh/laravel-dompdf)

**Caractéristiques :**
- Résolution : 72 DPI (écran) / 300 DPI (impression)
- Format : A4 portrait
- Police : DejaVu Sans (support UTF-8/FCFA)
- Couleurs : RGB
- Taille moyenne : 100-500 KB

### Format Excel

**Avantages :**
- Analyse approfondie
- Calculs personnalisés
- Graphiques interactifs
- Tri et filtres
- Tableaux croisés
- Import dans autres outils

**Librairie utilisée :** Maatwebsite Excel (PhpSpreadsheet)

**Caractéristiques :**
- Format : XLSX (OpenXML)
- Formules : SUM, AVERAGE, COUNT
- Styling : Couleurs, bordures, auto-size
- Multi-sheet : Jusqu'à 255 feuilles
- Taille moyenne : 50-200 KB

### Format CSV

**Avantages :**
- Léger (petit fichier)
- Compatible tous logiciels
- Import bases de données
- Édition texte brut

**Caractéristiques :**
- Encodage : UTF-8
- Séparateur : Virgule (,)
- Délimiteur : Guillemets (")
- Fin de ligne : CRLF

---

## 💡 CAS D'USAGE

### 📌 Comptabilité

**Objectif :** Déclarer le CA mensuel

**Processus :**
1. Export Statistiques Excel (mois complet)
2. Ouvrir feuille "CA Quotidien"
3. Vérifier formule SUM en bas
4. Copier dans logiciel comptable

### 📌 Analyse Performances

**Objectif :** Identifier plats rentables

**Processus :**
1. Export Statistiques Excel (trimestre)
2. Ouvrir feuille "Top Plats"
3. Créer graphique barres (quantité)
4. Créer graphique camembert (CA)
5. Analyser marges

### 📌 Rapport Investisseurs

**Objectif :** Présenter croissance

**Processus :**
1. Export Statistiques PDF (année)
2. Export Commandes PDF (année)
3. Assembler avec PowerPoint
4. Ajouter commentaires

### 📌 Impression Menus

**Objectif :** Distribuer cartes papier

**Processus :**
1. Export Menu PDF
2. Imprimer recto-verso
3. Plastifier (optionnel)
4. Distribuer aux tables

### 📌 Audit Interne

**Objectif :** Contrôler cohérence

**Processus :**
1. Export Commandes Excel (mois)
2. Export Audit Logs CSV (mois)
3. Croiser dans Excel
4. Identifier anomalies

---

## ❓ FAQ

### Q1 : Combien de temps prend un export ?

**R :**
- PDF : 2-5 secondes
- Excel : 3-8 secondes
- Dépend du volume de données

### Q2 : Quelle est la limite de données ?

**R :**
- Pas de limite technique
- Recommandé : Max 10 000 commandes par export
- Au-delà : Découper par période

### Q3 : Les exports incluent-ils les données supprimées ?

**R :**
- Non, seules les données actives (soft deletes exclus)
- Pour les données supprimées : Contact support

### Q4 : Puis-je automatiser les exports ?

**R :**
- Pas d'API publique pour le moment
- Roadmap Phase 5 : API exports
- Workaround : Script Selenium

### Q5 : Les PDF respectent-ils mon branding ?

**R :**
- Oui, logo tenant intégré automatiquement
- Couleurs : Palette tenant si définie
- Personnalisation avancée : Phase future

### Q6 : Puis-je exporter en d'autres devises ?

**R :**
- Non, uniquement FCFA actuellement
- Multi-devises : Roadmap Phase 5

### Q7 : Comment exporter un grand volume ?

**R :**
1. Découper par période (1 mois max)
2. Exporter chaque mois séparément
3. Consolider dans Excel
4. Alternative : Export CSV (plus léger)

### Q8 : Les exports sont-ils conformes RGPD ?

**R :**
- Oui, données chiffrées en transit (HTTPS)
- Audit logging activé
- Pas de données personnelles sensibles

---

## 🛠️ DÉPANNAGE

### Problème : Export ne se télécharge pas

**Solutions :**
1. Vérifier connexion internet
2. Désactiver bloqueurs popup
3. Essayer navigateur différent (Chrome recommandé)
4. Vider cache navigateur
5. Vérifier espace disque disponible

### Problème : PDF vide ou corrompu

**Solutions :**
1. Vérifier période sélectionnée (données existantes ?)
2. Essayer période plus courte
3. Vider cache Laravel : `php artisan cache:clear`
4. Vérifier logs : `storage/logs/laravel.log`

### Problème : Excel ne s'ouvre pas

**Solutions :**
1. Mettre à jour Excel/LibreOffice
2. Vérifier extension : `.xlsx`
3. Essayer Google Sheets en ligne
4. Réinstaller PhpSpreadsheet côté serveur

### Problème : Caractères mal affichés

**Solutions :**
1. Encodage CSV : Ouvrir avec UTF-8
2. PDF : Vérifier police DejaVu Sans installée
3. Excel : Utiliser "Données > Importer" avec UTF-8

### Problème : Formules Excel ne calculent pas

**Solutions :**
1. Activer calcul automatique : Formules > Options
2. F9 pour recalculer
3. Vérifier format cellules (nombre, pas texte)

---

## 📞 SUPPORT

### Besoin d'aide ?

**Documentation :**
- [ROADMAP.md](ROADMAP.md) - Fonctionnalités complètes
- [POS-MANUAL.md](POS-MANUAL.md) - Guide POS

**Contact Support :**
- Email : support@smartmenu.com
- Ticket : Dashboard Admin > Support
- Temps de réponse : < 24h

---

**📊 SmartMenu SaaS - Export PDF/Excel v2.5**
*Documentation générée le 2026-01-28*
