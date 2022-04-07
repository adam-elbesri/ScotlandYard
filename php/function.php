<?php

session_start(); //Ouverture de la session

try {
	$bdd = new PDO('mysql:host=localhost;dbname=p1803338;charset=utf8', 'p1803338', 'c1c7a6', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} //Essaie d'ouverture de la base de donnée avec gestion des erreurs

catch(Exception $error)
{
        die('Erreur : '.$error->getMessage());
} //En cas d'echec d'ouverture de la bdd

function Site() { echo "La chasse à Fantômas"; }

function Statistique() { // Affichage de toutes les routes
	global $bdd;
	$ville = $bdd->query('SELECT id_q, nom_q, nom_com FROM Quartier q INNER JOIN Commune c ON q.id_q = c.id_com');
	$transport = $bdd->query('SELECT q.id_q, nom_q, moyen_t, id_quartier_arrivee FROM Quartier q INNER JOIN route r ON q.id_q = r.id_quartier_depart');
	$value_t = $transport->fetch();
	while ($value_v = $ville->fetch()) {
	?>
		<div class ='stat'>
			<p><?php
				echo ('<p>' . $value_v['id_q'] . '<br/>' . $value_v['nom_q'] . '<br/>' . $value_v['nom_com'] . '<br/>' . $value_t['id_quartier_arrivee'] . ' ' . $value_t['moyen_t']);
				while ($value_t = $transport->fetch() and $value_t['id_q'] == $value_v['id_q']) {
					echo ('<br/>' . $value_t['id_quartier_arrivee'] . ' ' . $value_t['moyen_t']);
				}
			?>
		</p></div>
	<?php
	}
}

function P_depart($x = false) { //Positionnement aleatoire des joueur
	global $bdd;
	if(!$x)	$nombre_dentree = $bdd->query('SELECT COUNT(id_q) q FROM Quartier WHERE pointdepart');
	else $nombre_dentree = $bdd->query('SELECT COUNT(id_q) q FROM Quartier');
	$nb = $nombre_dentree->fetch();
	if(!$x) $depart = $bdd->query('SELECT id_q FROM Quartier WHERE pointdepart');
	else  $depart = $bdd->query('SELECT id_q FROM Quartier');
	$p_alea = rand(1, $nb['q']);
	$depart_i = 0;
	for($i=0; $i<$p_alea; $i++) $depart_i = $depart->fetch();
	return $depart_i['id_q'];
}

function NewP() { //Initialisation de la partie
	global $bdd;
	$_SESSION['pos_j'] = P_depart();
	$pos[0] = $_SESSION['pos_j'];
	$_SESSION['nom'] = htmlspecialchars($_POST['nom']);
	$_SESSION['mail'] = htmlspecialchars($_POST['mail']);
	$_SESSION['nb_bot'] = $_POST['bot'];
	for($i=0; $i<$_POST['bot']; $i++) {
		$_SESSION['bot'][$i] = P_depart();
		$pos[$i+1] = $_SESSION['pos_j'];
	}
	$_SESSION['MisterX'] = P_depart(true);
	for($i=0; $i<count($pos); $i++) {
			if($_SESSION['MisterX'] == $pos[$i]) {
				$_SESSION['MisterX'] = P_depart(true);
				$i = 0;
			}
	}
	$present = $bdd->query('SELECT email_j e FROM joueur');
	while ($joueur = $present->fetch()) if($joueur['e'] == $_SESSION['mail']) break;
	if($joueur['e'] ==  $_SESSION['mail']) {
		$req = $bdd->prepare('UPDATE joueur SET nom_j = ?, position = ? WHERE email_j = ?');
		$req->execute(array($_SESSION['nom'], $_SESSION['pos_j'], $_SESSION['mail']));
		$req = $bdd->prepare('UPDATE MisterX SET num_tour = 0, position_x = ? WHERE email_j = ?');
		$req->execute(array($_SESSION['MisterX'], $_SESSION['mail']));
	}
	else {
		$req = $bdd->prepare('INSERT INTO joueur(nom_j, email_j, position) VALUES (?, ?, ?)');
		$req->execute(array($_SESSION['nom'],$_SESSION['mail'], $_SESSION['pos_j']));
		$req = $bdd->prepare('INSERT INTO MisterX(email_j, num_tour, position_x) VALUES (?, 0, ?)');
		$req->execute(array($_SESSION['mail'], $_SESSION['MisterX']));
	}
	$req = $bdd->prepare('INSERT INTO Partie(datedebut, nb_d, email_j, nom_conf) VALUES (?, ?, ?, 1)');
	$req->execute(array(date("Y-m-d"), htmlspecialchars($_POST['bot']), htmlspecialchars($_SESSION['mail'])));
	$_SESSION['debut'] = false;
}

function Position($pos, $pers, $bot=false) { //Affichage de la position d'un personnage
	global $bdd;
	$ville = $bdd->prepare('SELECT id_q, nom_q, nom_com FROM Quartier q INNER JOIN Commune c ON q.id_q = c.id_com AND q.id_q = ?');
	$ville->execute(array($pos));
	$transport = $bdd->prepare('SELECT nom_q, moyen_t, id_quartier_arrivee FROM Quartier q INNER JOIN route r ON q.id_q = r.id_quartier_depart AND id_q = ?');
	$transport->execute(array($pos));
	while ($value_v = $ville->fetch()) {
	?>
		<div class ='stat'>
			<p><?php
				echo ('<p>' . $pers . '<br/>' . $value_v['id_q'] . '<br/>' . $value_v['nom_q'] . '<br/>' . $value_v['nom_com']);
				while ($value_t = $transport->fetch()) {
					if($bot) echo ('<br/>' . $value_t['id_quartier_arrivee'] . ' ' . $value_t['moyen_t']);
					else {
						echo ('<br/><form method="post" action="jeu.php">' . ' <input type="submit" value="' . $value_t['id_quartier_arrivee'] . '" name="N_pos"/>' . ' ' . $value_t['moyen_t']);
					}
				}
			?>
		</p></div>
	<?php
	}
}

function Posi() { //Affichage de la position de tout les personnages
	global $bdd;
	echo ("<div id='position'>");
	Position($_SESSION['pos_j'], $_SESSION['nom']);
	for($i=0; $i<$_SESSION['nb_bot']; $i++) Position($_SESSION['bot'][$i], 'Détective ' . ($i+1), true);
	echo ("<br/>");
	$req = $bdd->prepare('SELECT num_tour t FROM MisterX WHERE email_j = ?');
	$req->execute(array($_SESSION['mail']));
	$tour = $req->fetch();
	if($tour['t'] == 3) Position($_SESSION['MisterX'], 'Fantômas', true);
	if($tour['t'] == 8) Position($_SESSION['MisterX'], 'Fantômas', true);
	if($tour['t'] == 13) Position($_SESSION['MisterX'], 'Fantômas', true);
	if($tour['t'] == 18) Position($_SESSION['MisterX'], 'Fantômas', true);
	echo ("</div>");
}

function Choix($pos, $x = false) { //Positionnement aleatoire pour le tour suivant
	global $bdd;
	$req = $bdd->prepare('SELECT COUNT(id_quartier_arrivee) q FROM Quartier q INNER JOIN route r ON q.id_q = r.id_quartier_depart AND id_q = ?');
	$req->execute(array($pos));
	$nb = $req->fetch();
	$req = $bdd->prepare('SELECT id_quartier_arrivee n FROM Quartier q INNER JOIN route r ON q.id_q = r.id_quartier_depart AND id_q = ?');
	$req->execute(array($pos));
	if(!$x) {
		for($i=0; $i<$nb['q']; $i++) {
			$new_p = $req->fetch();
			if($new_p['n'] == $_SESSION['MisterX']) return $new_p['n'];
		}
		$req = $bdd->prepare('SELECT id_quartier_arrivee n FROM Quartier q INNER JOIN route r ON q.id_q = r.id_quartier_depart AND id_q = ?');
		$req->execute(array($pos));
		$p_alea = rand(1, $nb['q']);
		$new_p = 0;
		for($i=0; $i<$p_alea; $i++) $new_p = $req->fetch();
	}
	else {
		do {
			$p_alea = rand(1, $nb['q']);
			$new_p = 0;
			for($i=0; $i<$p_alea; $i++) $new_p = $req->fetch();
			if($pos == $_SESSION['pos_j']) {$p_pris = true; continue;}
			else $p_pris = false;
			for($i=0; $i<$_SESSION['nb_bot']; $i++) {
				if($pos == $_SESSION['bot'][$i]) {$p_pris = true; continue;}
				else $p_pris = false;
			}
		} while($p_pris);
	}
	return $new_p['n'];
}

function P_suivant() { //Fonction du tour suivant
	global $bdd;
	$_SESSION['pos_j'] = $_POST['N_pos'];
	$req = $bdd->prepare('UPDATE joueur SET position = ? WHERE email_j = ?');
	$req->execute(array($_SESSION['pos_j'], $_SESSION['mail']));
	for($i=0; $i<$_SESSION['nb_bot']; $i++) {
		$_SESSION['bot'][$i] = Choix($_SESSION['bot'][$i]);
		$pos[$i+1] = $_SESSION['bot'][$i];
	}
	$_SESSION['MisterX'] = Choix($_SESSION['MisterX'], true);
	$req = $bdd->prepare('SELECT num_tour t FROM MisterX WHERE email_j = ?');
	$req->execute(array($_SESSION['mail']));
	$tour = $req->fetch();
	$req = $bdd->prepare('UPDATE MisterX SET num_tour = ?, position_x = ? WHERE email_j = ?');
	$req->execute(array(($tour['t']+1), $_SESSION['MisterX'], $_SESSION['mail']));
	echo ('<p>Il vous reste : ' . (20-$tour['t']-1) . ' tour(s).');
}

function Gagne() { // cas de victoire
	global $bdd;
	$req = $bdd->prepare('SELECT COUNT(id_quartier_arrivee) q FROM Quartier q INNER JOIN route r ON q.id_q = r.id_quartier_depart AND id_q = ?');
	$req->execute(array($_SESSION['MisterX']));
	$nb = $req->fetch();
	$req = $bdd->prepare('SELECT id_quartier_arrivee n FROM Quartier q INNER JOIN route r ON q.id_q = r.id_quartier_depart AND id_q = ?');
	$req->execute(array($_SESSION['MisterX']));
	for($i=0; $i<$nb['q']; $i++) $possible[$i] = false;
	for($i=0; $i<$nb['q']; $i++) {
		$mx = $req->fetch();
		if($mx['n'] == $_SESSION['pos_j']) $possible[$i] = true;
		for($j=0; $j<$_SESSION['nb_bot']; $j++) if($mx['n'] == $_SESSION['bot'][$j]) $possible[$i] = true;
	}
	if($_SESSION['MisterX'] == $_SESSION['pos_j']) return true;
	for($i=0; $i<$_SESSION['nb_bot']; $i++) if($_SESSION['MisterX'] == $_SESSION['bot'][$i]) return true;
	for($i=0; $i<$nb['q']; $i++) if($possible[$i] == false) return false;
	return true;
}

function Perdu() { // cas de defaite
	global $bdd;
	if(Gagne()) return false;
	$req = $bdd->prepare('SELECT num_tour t FROM MisterX WHERE email_j = ?');
	$req->execute(array($_SESSION['mail']));
	$tour = $req->fetch();
	return ($tour['t'] == 20);
}

function Victoire() { // Ajout de la victoire
	global $bdd;
	$req = $bdd->prepare('SELECT nbvictoire_j v FROM joueur WHERE email_j = ?');
	$req->execute(array($_SESSION['mail']));
	$nb = $req->fetch();
	if($nb['v'] == NULL) $victoire = 0;
	$victoire = $nb['v'] + 1; 
	$req = $bdd->prepare('UPDATE joueur SET nbvictoire_j = ? WHERE email_j = ?');
	$req->execute(array($victoire, $_SESSION['mail']));
}

?>
