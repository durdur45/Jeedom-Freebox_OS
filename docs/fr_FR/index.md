Description
==========
Ce plugin permet de récupérer les informations de votre freeboxOS (Serveur Freebox Revolution ou 4K).

Les informations disponibles de votre Freebox Serveur sur Jeedom sont:

 * Les informations système
 * Le nombre d'appels en absences
 * Le nombre d'appels passés
 * Le nombre d'appels reçus
 * Les débits internet
 * L'état de votre connexion
 * La place disponible dans vos disques connectés à la Freebox Serveur. 
 * L’état de chaque équipement DHCP 
 * Couper le wifi
 * Redémarrer votre Freebox


Installation et Configuration
=============================
Une fois le plugin installé et activé, nous devons procéder à un appairage du serveur Jeedom sur la Freebox.

![introduction01](../images/Freebox_OS_screenshot_configuration.jpg)

Sur la page de configuration, vous avez la possibilité de personnaliser les options de connexion, mais seules celles par défaut ont été validées.

Appairage
=========
Pour cela, il suffit de cliquer sur le bouton "Appairer" dans votre interface de configuration.
Vous allez à ce moment avoir un message comme ceci.
Ne validez surtout pas maintenant, attendez les étapes suivantes.

![introduction01](../images/MessageValidation.jpg)

Validation sur la Freebox
-------------------------
Vous avez donc demandé a votre Freebox une nouvelle connexion par l'api, et il faut l'autoriser.
Pour cela, rien de plus simple, il vous faut donc aller valider cette connexion directement sur votre Freebox en appuyant sur la flèche de droite pour répondre "oui"

![introduction01](../images/EcranFreebox.jpg)

Validation Jeedom
-----------------
Vous pouvez donc maintenant retourner sur votre pc pour valider le message laissé en attente précédement.
L'état de fonctionnement de la liaison va alors être testé.
