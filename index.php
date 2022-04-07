<!--
CELERIER Louis p1803338
El besri adam p1810928
-->
<?php
	include 'php/header.php';
	$_SESSION['debut'] = true;
	$_SESSION['suivant'] = false;
?>
		<div>
			<form method="post" action="jeu.php"> <!-- Formulaire du jeu -->
				<p>
					<label>Nom</label> : <input type="text" name="nom" autofocus required/>
					<br/><br/>
					<label>Mail</label> : <input type="email" name="mail" required/>
					<br/>
					<label>Nombres d'alliés :</label>
					<br/>
					<select name="bot" required>
						<option value=3>3</option>
						<option value=4>4</option>
						<option value=5>5</option>
					</select>
					<input type="submit" value="Jouer"/>
				</p>
			</form>
		</div>
<?php include 'php/footer.php'; ?>

