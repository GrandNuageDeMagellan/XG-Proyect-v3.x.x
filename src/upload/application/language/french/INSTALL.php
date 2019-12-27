<?php
// SOME MESSAGES
$lang['404_error'] = 'La page demandée n\'existe pas';
$lang['ins_no_server_requirements'] = 'Votre serveur / hébergement ne répond pas aux exigences minimales requises pour exécuter XG Proyect.<br /><br />Exigences: <br />- PHP 5.5.x<br />- MySQL 5.5.x';
$lang['ins_not_writable'] = 'Vous devez fournir une autorisation d\'écriture (chmod 777) au répertoire application / config pour continuer l\'installation.';
$lang['ins_already_installed'] = 'XG Proyect est déjà installé. Sélectionnez une option: <br /><br /> - <a href="../admin.php?page=update">Update</a> <br /> - <a href="../admin.php?page=migrate">Migration</a> <br /> - <a href="../">Retour au jeu</a> <br /><br />Si vous ne souhaitez prendre aucune mesure, pour des raisons de sécurité, nous vous recommandons <span style="color:red;text-decoration:underline;">SUPPRIMER</span> le répertoire d\'installation.';

// SOME ERROR HEADERS
$lang['ins_error_title'] = 'Alerte!';
$lang['ins_warning_title'] = 'Attention!';
$lang['ins_ok_title'] = 'Ok!';

// TOP MENU
$lang['ins_overview'] = 'Aperçu';
$lang['ins_license'] = 'Licence';
$lang['ins_install'] = 'Installer';
$lang['ins_language_select'] = 'Choisir la langue';


// OVERVIEW PAGE
$lang['ins_install_title'] = 'Installation';
$lang['ins_title'] = 'Introduction';
$lang['ins_welcome'] = 'Bienvenue sur XG Project!';
$lang['ins_welcome_first_line'] = 'XG Proyect est le meilleur des clones OGame. XG Proyect 3 est le package le plus récent et le plus stable jamais développé auparavant. Comme toute autre version, XG Proyect reçoit le soutien de l\'équipe connue sous le nom de Xtreme-gameZ, en veillant toujours à obtenir la meilleure qualité de soins et la stabilité de la version. XG Proyect 3 regarde vers l\'avenir et recherche la croissance, la stabilité, la flexibilité, le dynamisme, la qualité et la confiance des utilisateurs. Nous nous attendons toujours à ce que XG Proyect soit meilleur que vos attentes.';
$lang['ins_welcome_second_line'] = 'Le système d\'installation vous guidera à travers l\'installation ou la mise à niveau d\'une version précédente vers la dernière. Pour les doutes, problèmes ou requêtes, n\'hésitez pas à consulter notre <a href="http://www.xgproyect.org/"><em>communauté de soutien et de développement</em></a>.';
$lang['ins_welcome_third_line'] = 'XG Proyect est un projet OpenSource, pour voir les spécifications de licence, cliquez sur licence dans le menu principal. Pour démarrer l\'installation, cliquez sur le bouton d\'installation, pour mettre à jour ou migrer le journal dans le CP ADMIN.';
$lang['ins_install_license'] = 'Licence';

// INSTALL PAGE
// GENERAL
$lang['ins_steps'] = 'Etapes';
$lang['ins_step1'] = 'Connection data';
$lang['ins_step2'] = 'Vérifier la connexion';
$lang['ins_step3'] = 'Fichier de configuration';
$lang['ins_step4'] = 'Insérer des données';
$lang['ins_step5'] = 'Créer un administrateur';
$lang['ins_continue'] = 'Continuer';

// STEP1
$lang['ins_connection_data_title'] = 'Données pour se connecter à la base de données';
$lang['ins_server_title'] = 'Serveur SQL:';
$lang['ins_db_title'] = 'Base de données:';
$lang['ins_user_title'] = 'Utilisateur:';
$lang['ins_password_title'] = 'Mot de passe:';
$lang['ins_prefix_title'] = 'Préfixe des tables:';
$lang['ins_ex_tag'] = 'Ex:';
$lang['ins_install_go'] = 'Intaller';

// ERRORS
$lang['ins_not_connected_error'] = 'Impossible de se connecter à la base de données avec les données entrées.';
$lang['ins_db_not_exists'] = 'Impossible d\'accéder à la base de données avec le nom fourni.';
$lang['ins_empty_fields_error'] = 'Tous les champs sont requis';
$lang['ins_write_config_error'] = 'Erreur lors de l\'écriture du fichier config.php, assurez-vous qu\'il s\'agit du 777 CHMOD (autorisations d\'écriture) ou que le fichier existe';
$lang['ins_insert_tables_error'] = 'Impossible d\'insérer des données dans la base de données, vérifiez la base de données ou que le serveur est actif.';

// STEP2
$lang['ins_done_config'] = 'Le fichier config.php a été configuré avec succès.';
$lang['ins_done_connected'] = 'Connexion établie avec succès.';
$lang['ins_done_insert'] = 'Les données de base ont été insérées avec succès.';

// STEP3
$lang['ins_admin_create_title'] = 'Nouveau compte administrateur';
$lang['ins_admin_create_user'] = 'Utilisateur:';
$lang['ins_admin_create_pass'] = 'Mot de passe:';
$lang['ins_admin_create_email'] = 'Adresse Email:';
$lang['ins_admin_create_create'] = 'Créer';

// ERRORS
$lang['ins_adm_empty_fields_error'] = 'Tous les champs sont requis';
$lang['ins_adm_invalid_email_address'] = 'Veuillez spécifier une adresse e-mail valide';

// STEP 4
$lang['ins_completed'] = 'INSTALLATION COMPLÈTE!';
$lang['ins_admin_account_created'] = 'Administrateur créé avec succès!';
$lang['ins_delete_install'] = 'Vous devez supprimer le<i>installer</i>répertoire pour éviter les risques de sécurité!';
$lang['ins_end'] = 'Finaliser';
/* end of INSTALL.php */
