<?php
	include 'php/header.php';
	if(Gagne()) { // Si on gagne
		echo ("<p>FELICITATION ! VOUS AVEZ GAGNÉ !</p>");
		Victoire();
		Position($_SESSION['MisterX'], 'Fantômas', true);
	}
	if(Perdu()) { // Si on perds
		echo ("<p>Vous avez perdu...</p>");
		Position($_SESSION['MisterX'], 'Fantômas', true);
	}
	if(isset($_POST['N_pos']) and !Gagne() and !Perdu()) P_suivant(); //tour suivant si la partie n'est pas fini
	if(isset($_SESSION['debut']) and $_SESSION['debut']) NewP(); //Initialisation si c'est le debut de la partie
	Posi();
	include 'php/footer.php';
?>
