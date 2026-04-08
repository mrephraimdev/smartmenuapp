# 💰 MANUEL D'UTILISATION - MODULE POS (POINT OF SALE)

> Guide complet du système de caisse (Point of Sale) SmartMenu SaaS
> Version 2.5 | Dernière mise à jour : 2026-01-28

---

## 📋 TABLE DES MATIÈRES

1. [Présentation](#présentation)
2. [Concepts de Base](#concepts-de-base)
3. [Ouverture de Session](#ouverture-de-session)
4. [Utilisation Quotidienne](#utilisation-quotidienne)
5. [Fermeture de Session](#fermeture-de-session)
6. [Rapports de Caisse](#rapports-de-caisse)
7. [Gestion des Écarts](#gestion-des-écarts)
8. [Bonnes Pratiques](#bonnes-pratiques)
9. [FAQ & Dépannage](#faq--dépannage)

---

## 🎯 PRÉSENTATION

### Qu'est-ce que le Module POS ?

Le module POS (Point of Sale / Point de Vente) est un système de caisse enregistreuse intégré qui permet de :
- ✅ Gérer les sessions de caisse
- ✅ Suivre les ventes en temps réel
- ✅ Contrôler les fonds de caisse
- ✅ Détecter les écarts de caisse
- ✅ Générer des rapports Z et X
- ✅ Assurer la traçabilité comptable

### Qui peut l'utiliser ?

- **Administrateurs** : Ouverture, fermeture, rapports
- **Caissiers** : Utilisation pendant sessions
- **Managers** : Consultation rapports

### Accès au POS

**URL :** `/admin/{tenantSlug}/pos`

**Prérequis :**
- Rôle : ADMIN ou SUPER_ADMIN
- Session utilisateur active
- Tenant configuré

---

## 📚 CONCEPTS DE BASE

### Session de Caisse

**Définition :** Une session = Une période d'utilisation de la caisse par un caissier.

**Caractéristiques :**
- **1 session** par caissier à la fois
- **Statuts** : OPEN (ouverte) ou CLOSED (fermée)
- **Numéro unique** : POS-20260128-T1-001
- **Timing** : Date/heure ouverture et fermeture

### Fond de Caisse (Opening Float)

**Définition :** Argent liquide mis dans la caisse au début de la session pour rendre la monnaie.

**Montant typique :** 50 000 - 100 000 FCFA

**Composition recommandée :**
- Billets de 10 000 FCFA : 3 pièces
- Billets de 5 000 FCFA : 4 pièces
- Billets de 2 000 FCFA : 5 pièces
- Billets de 1 000 FCFA : 10 pièces
- Pièces de 500 FCFA : 20 pièces
- **Total :** 80 000 FCFA

### Moyens de Paiement

Le système track 3 types de paiement :
1. **Espèces (CASH)** - Billets et pièces
2. **Carte Bancaire (CARD)** - TPE
3. **Mobile Money (MOBILE)** - Orange Money, MTN, Moov

### Rapport Z vs Rapport X

| Critère | Rapport Z | Rapport X |
|---------|-----------|-----------|
| **Type** | Clôture définitive | Intermédiaire |
| **Moment** | Fin de journée | Milieu de service |
| **Session** | Fermée | Ouverte |
| **Comptage cash** | Obligatoire | Optionnel |
| **Effet** | Remet compteurs à 0 | Aucun effet |
| **Archivage** | Légal obligatoire | Consultatif |

---

## 🔓 OUVERTURE DE SESSION

### Procédure Standard

#### Étape 1 : Préparer le Fond de Caisse

1. **Compter** l'argent liquide disponible
2. **Vérifier** les billets (authenticité)
3. **Noter** le montant exact
4. **Ranger** dans le tiroir-caisse

#### Étape 2 : Ouvrir la Session dans le Système

1. Connectez-vous à SmartMenu
2. Allez dans **"POS"** (menu admin)
3. Cliquez sur **"Ouvrir une Session"**

**Formulaire d'ouverture :**
```
┌─────────────────────────────────────┐
│  Nouvelle Session                   │
├─────────────────────────────────────┤
│ Fond de caisse initial (FCFA)       │
│ [____________50000______________]    │
│                                     │
│ Notes (optionnel)                   │
│ [________________________________]  │
│ [Session du matin - 3 caissiers]    │
│                                     │
│  [Annuler]    [Ouvrir]              │
└─────────────────────────────────────┘
```

4. **Saisissez** le montant du fond de caisse
5. **Ajoutez** des notes si nécessaire (ex: "Session matin", "Fond incomplet")
6. Cliquez sur **"Ouvrir"**

#### Étape 3 : Confirmation

Vous verrez une carte verte **"Session Active"** avec :
- Numéro de session (ex: POS-20260128-T1-001)
- Fond de caisse : 50 000 FCFA
- Durée : 0 min
- Commandes : 0
- Ventes : 0 FCFA

✅ **Votre session est ouverte, vous pouvez encaisser !**

### Que se passe-t-il en arrière-plan ?

```php
// Le système crée un enregistrement
PosSession {
    id: 1,
    tenant_id: 1,
    user_id: 5, // Votre ID
    session_number: "POS-20260128-T1-001",
    status: "OPEN",
    opened_at: "2026-01-28 08:00:00",
    opening_float: 50000.00,
    opening_notes: "Session du matin",
    // ... autres champs à 0
}
```

---

## 💻 UTILISATION QUOTIDIENNE

### Pendant la Session

#### Tableau de Bord Temps Réel

La session active affiche 4 KPIs en temps réel :

```
┌──────────────────────────────────────────────────────┐
│  SESSION ACTIVE - POS-20260128-T1-001               │
├──────────────────────────────────────────────────────┤
│  Fond de caisse    Durée        Commandes    Ventes  │
│   50 000 FCFA     2h 30min         45      850 000   │
└──────────────────────────────────────────────────────┘
```

#### Encaissement des Commandes

Les commandes encaissées pendant votre session sont **automatiquement liées** :

```php
Order {
    id: 123,
    tenant_id: 1,
    pos_session_id: 1, // ← Lien avec session
    total: 15000,
    status: "SERVI",
    created_at: "2026-01-28 10:30:00"
}
```

#### Rapport X (Contrôle Intermédiaire)

**Quand l'utiliser ?**
- Changement d'équipe (relève)
- Vérification en cours de service
- Contrôle superviseur

**Comment :**
1. Cliquez sur **"Rapport X (Intermédiaire)"**
2. Consultez les totaux actuels
3. Le rapport affiche :
   - CA depuis ouverture
   - Commandes traitées
   - Répartition par moyen de paiement
   - Espèces attendues en caisse
4. **Optionnel** : Exporter en PDF

**Important** : Le rapport X ne ferme PAS la session.

---

## 🔒 FERMETURE DE SESSION

### Procédure de Clôture

#### Étape 1 : Comptage de Caisse

**CRITIQUE** : Comptez l'argent **AVANT** de fermer dans le système.

**Méthode recommandée :**

1. **Sortez** tout l'argent du tiroir
2. **Triez** par valeur faciale
3. **Comptez** chaque pile 2 fois
4. **Notez** sur papier brouillon

**Feuille de comptage (exemple) :**
```
Billets 10 000 x 8  = 80 000
Billets  5 000 x 12 = 60 000
Billets  2 000 x 10 = 20 000
Billets  1 000 x 25 = 25 000
Pièces    500  x 30 = 15 000
                      -------
        TOTAL        200 000 FCFA
```

#### Étape 2 : Fermeture dans le Système

1. Cliquez sur **"Fermer la Session"**
2. **Saisissez** le montant compté

**Formulaire de fermeture :**
```
┌─────────────────────────────────────┐
│  Fermer la Session                  │
├─────────────────────────────────────┤
│ Montant en caisse (FCFA)            │
│ [___________200000______________]    │
│ Comptez l'argent et entrez le       │
│ montant total                       │
│                                     │
│ Notes (optionnel)                   │
│ [________________________________]  │
│ [Aucun problème]                    │
│                                     │
│  [Annuler]    [Fermer]              │
└─────────────────────────────────────┘
```

3. **Ajoutez** des notes si nécessaire
4. Confirmez : **"Êtes-vous sûr ?"**
5. Cliquez **"Fermer"**

#### Étape 3 : Rapport Z Automatique

Le système vous redirige automatiquement vers le **Rapport Z**.

---

## 📊 RAPPORTS DE CAISSE

### Rapport Z (Clôture de Journée)

**Document officiel** archivé pour la comptabilité.

#### Structure du Rapport Z

```
╔═══════════════════════════════════════════════════╗
║            RAPPORT Z - CLÔTURE DE CAISSE          ║
╠═══════════════════════════════════════════════════╣
║  [Logo Restaurant]                                ║
║  Restaurant Le Gourmet                            ║
║  123 Avenue de la Liberté                         ║
╠═══════════════════════════════════════════════════╣
║  N° Session: POS-20260128-T1-001                  ║
║  Caissier: Marie DUPONT                           ║
║  Ouverture: 28/01/2026 à 08:00                    ║
║  Fermeture: 28/01/2026 à 20:00                    ║
║  Durée: 720 minutes                               ║
╠═══════════════════════════════════════════════════╣
║            INDICATEURS CLÉS                       ║
║  ┌───────────────────────────────────────────┐   ║
║  │  Chiffre d'Affaires    850 000 FCFA      │   ║
║  │  Commandes             45                 │   ║
║  │  Panier Moyen          18 889 FCFA        │   ║
║  │  Articles Vendus       125                │   ║
║  └───────────────────────────────────────────┘   ║
╠═══════════════════════════════════════════════════╣
║  RÉPARTITION PAR MOYEN DE PAIEMENT                ║
║  ┌───────────────────────────────────────────┐   ║
║  │  Espèces          650 000 FCFA            │   ║
║  │  Carte Bancaire   150 000 FCFA            │   ║
║  │  Mobile Money      50 000 FCFA            │   ║
║  │  TOTAL            850 000 FCFA            │   ║
║  └───────────────────────────────────────────┘   ║
╠═══════════════════════════════════════════════════╣
║  BILAN DE CAISSE                                  ║
║  ┌───────────────────────────────────────────┐   ║
║  │  Fond de caisse ouverture   50 000 FCFA   │   ║
║  │  + Ventes en espèces       650 000 FCFA   │   ║
║  │  - Remboursements              0 FCFA     │   ║
║  │  = Espèces attendues       700 000 FCFA   │   ║
║  │                                            │   ║
║  │  Espèces comptées          700 000 FCFA   │   ║
║  │                                            │   ║
║  │  ÉCART:                        0 FCFA ✓   │   ║
║  └───────────────────────────────────────────┘   ║
╠═══════════════════════════════════════════════════╣
║  ✓ Caisse conforme - Aucun écart détecté         ║
╠═══════════════════════════════════════════════════╣
║  TOP 10 PLATS VENDUS                              ║
║  1. Poulet Yassa           15 ventes  225 000 F   ║
║  2. Riz Gras               12 ventes  180 000 F   ║
║  3. Thiéboudienne          10 ventes  250 000 F   ║
║  ... (7 autres)                                   ║
╠═══════════════════════════════════════════════════╣
║  SIGNATURES                                       ║
║  Caissier: _____________  Responsable: ________   ║
╚═══════════════════════════════════════════════════╝
```

#### Export et Archivage

**Boutons disponibles :**
- **Imprimer** (Ctrl+P)
- **Exporter PDF** (download)
- **Retour** (historique sessions)

**Archivage légal :**
- Conservez les PDF pendant **10 ans**
- Classement : `YYYY/MM/rapport-z-session-XXX.pdf`
- Backup : Cloud + disque dur externe

### Rapport X (Situation Intermédiaire)

**Document consultatif** (non archivé).

#### Différences avec Rapport Z

| Élément | Rapport Z | Rapport X |
|---------|-----------|-----------|
| **Badge** | Noir | Bleu |
| **Titre** | CLÔTURE DE CAISSE | SITUATION INTERMÉDIAIRE |
| **Notice** | - | "Session en cours" |
| **Cash compté** | Oui (obligatoire) | Non (calculé) |
| **Écart** | Affiché | Non applicable |
| **Signatures** | Oui | Non |

---

## ⚠️ GESTION DES ÉCARTS

### Types d'Écarts

#### Écart Positif (Excédent)

**Définition :** Vous avez + d'argent que prévu.

**Exemple :**
- Espèces attendues : 700 000 FCFA
- Espèces comptées : 702 000 FCFA
- **Écart : +2 000 FCFA** (excédent)

**Causes possibles :**
- Client a laissé pourboire
- Erreur de saisie (montant trop élevé enregistré)
- Oubli de rendre la monnaie

**Actions :**
1. Notez dans "Closing notes"
2. Informez le manager
3. Conservez l'excédent séparément
4. Tentez d'identifier la cause

#### Écart Négatif (Manque)

**Définition :** Vous avez - d'argent que prévu.

**Exemple :**
- Espèces attendues : 700 000 FCFA
- Espèces comptées : 695 000 FCFA
- **Écart : -5 000 FCFA** (manque)

**Causes possibles :**
- Erreur de rendu monnaie
- Oubli d'encaisser une commande
- Vol/perte
- Erreur de comptage

**Actions :**
1. **Recompter** immédiatement (erreur de comptage ?)
2. Vérifier **monnaie rendue** (tickets manuels)
3. Consulter **commandes du jour** (oubli d'encaissement ?)
4. Notez dans "Closing notes"
5. **Déclarer** au manager immédiatement

### Alertes Système

Le système affiche des alertes visuelles :

**Alerte Écart (rouge) :**
```
╔═══════════════════════════════════════════╗
║  ⚠ ATTENTION: Un écart de caisse de      ║
║  5 000 FCFA a été détecté (manque).      ║
╚═══════════════════════════════════════════╝
```

**Caisse Conforme (vert) :**
```
╔═══════════════════════════════════════════╗
║  ✓ Caisse conforme - Aucun écart détecté ║
╚═══════════════════════════════════════════╝
```

### Seuils de Tolérance

| Montant Session | Tolérance | Action |
|-----------------|-----------|--------|
| < 100 000 FCFA | ± 500 FCFA | Note |
| 100k - 500k | ± 1 000 FCFA | Rapport manager |
| 500k - 1M | ± 2 000 FCFA | Rapport + enquête |
| > 1M FCFA | ± 5 000 FCFA | Rapport + audit |

---

## ✅ BONNES PRATIQUES

### Ouverture de Session

✅ **À FAIRE :**
- Comptez le fond de caisse AVANT ouverture
- Vérifiez TPE (carte) et smartphone (mobile money) fonctionnent
- Ouvrez la session au DÉBUT du service (pas en avance)
- Ajoutez notes si situation particulière

❌ **À ÉVITER :**
- Ouvrir sans compter le fond
- Utiliser un fond trop important (risque vol)
- Ouvrir plusieurs sessions simultanément
- Laisser session ouverte pendant pause

### Pendant la Session

✅ **À FAIRE :**
- Demandez systématiquement le moyen de paiement
- Rendez la monnaie AVANT de ranger le billet
- Fermez le tiroir-caisse entre chaque transaction
- Générez un rapport X toutes les 4h (contrôle)

❌ **À ÉVITER :**
- Laisser tiroir ouvert
- Accepter billets sans vérification
- Mélanger votre argent personnel avec la caisse
- Sortir argent de la caisse sans trace

### Fermeture de Session

✅ **À FAIRE :**
- Comptez l'argent 2 fois minimum
- Fermez la session IMMÉDIATEMENT après comptage
- Archivez le rapport Z (PDF)
- Rangez l'argent dans coffre
- Remettez rapport signé au manager

❌ **À ÉVITER :**
- Compter devant clients
- Fermer sans compter
- Laisser argent dans tiroir la nuit
- Perdre le rapport Z

---

## ❓ FAQ & DÉPANNAGE

### Questions Fréquentes

**Q1 : Puis-je ouvrir une session pour un collègue ?**
R : Non, chaque caissier ouvre sa propre session. La traçabilité est individuelle.

**Q2 : Que faire si j'oublie de fermer ma session ?**
R : Contactez immédiatement votre manager. Il peut forcer la fermeture via SuperAdmin. Un écart sera constaté.

**Q3 : Puis-je rouvrir une session fermée ?**
R : Non, une fois fermée, la session est archivée définitivement. Ouvrez une nouvelle session.

**Q4 : Comment gérer une annulation de commande ?**
R : Le système track automatiquement les annulations. Elles apparaissent dans le rapport Z (section "Annulations et Remboursements").

**Q5 : Mon rapport Z ne correspond pas à ma compta !**
R : Vérifiez :
- Les commandes du jour sont bien liées à votre session
- Le mode de paiement est correct (espèces vs carte)
- Pas de commandes "hors système" (papier)

**Q6 : Puis-je modifier un rapport Z après fermeture ?**
R : Non, les rapports Z sont **immuables** pour garantir l'intégrité comptable. Contactez le support si erreur critique.

### Dépannage

**Problème : Impossible d'ouvrir une session**

**Solutions :**
1. Vérifiez que vous n'avez pas déjà une session ouverte
2. Déconnectez-vous et reconnectez-vous
3. Vérifiez votre rôle (ADMIN requis)
4. Videz le cache navigateur

**Problème : Rapport Z ne se génère pas**

**Solutions :**
1. Vérifiez que la session est bien CLOSED
2. Attendez 30 secondes (génération PDF)
3. Essayez de recharger la page
4. Contactez support avec N° session

**Problème : Écart de caisse important**

**Solutions :**
1. **Recomptez** l'argent immédiatement
2. Vérifiez les **commandes non encaissées**
3. Consultez le **rapport X intermédiaire** précédent
4. Vérifiez transactions **carte** et **mobile money**
5. Déclarez au manager (procédure interne)

---

## 📞 SUPPORT & CONTACT

### Besoin d'aide ?

**Documentation :**
- [ROADMAP.md](ROADMAP.md) - Vue d'ensemble projet
- [EXPORTS-GUIDE.md](EXPORTS-GUIDE.md) - Guide exports

**Support Technique :**
- Email : support@smartmenu.com
- Ticket : Dashboard > Support
- Téléphone : +221 XX XXX XX XX
- Temps de réponse : < 2h (horaires bureau)

### Signaler un Bug

**Informations à fournir :**
1. N° de session
2. Action effectuée
3. Message d'erreur (screenshot)
4. Navigateur utilisé
5. Heure de l'incident

---

## 📝 CHECKLIST QUOTIDIENNE

### Matin (Ouverture)

- [ ] Compter fond de caisse (2x)
- [ ] Vérifier billets (authenticité)
- [ ] Tester TPE carte bancaire
- [ ] Vérifier smartphone (mobile money)
- [ ] Ouvrir session dans SmartMenu
- [ ] Vérifier N° session généré
- [ ] Ranger argent dans tiroir
- [ ] Fermer tiroir à clé

### Midi (Contrôle)

- [ ] Générer rapport X intermédiaire
- [ ] Vérifier cohérence (CA / commandes)
- [ ] Comptage rapide espèces (optionnel)
- [ ] Signaler anomalies immédiates

### Soir (Fermeture)

- [ ] Terminer dernières commandes
- [ ] Retirer argent du tiroir
- [ ] Compter 2x (feuille de comptage)
- [ ] Fermer session dans SmartMenu
- [ ] Consulter rapport Z
- [ ] Vérifier écarts
- [ ] Exporter PDF rapport Z
- [ ] Ranger argent dans coffre
- [ ] Remettre rapport signé au manager

---

**💰 SmartMenu SaaS - Module POS v2.5**
*Manuel généré le 2026-01-28*
*Ce manuel est confidentiel et destiné à un usage interne uniquement.*
