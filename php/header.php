<?php require('php/function.php'); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset=UTF-8 />
		<title><?php Site() ?></title>
		<link rel="icon" type="image/png" href="img/icon.png" />
		<link rel="stylesheet" href="css/styles.css" />
	</head>
	<body>
		<header>
		<div>
			<a href="index.php">
				<img src="img/icon.png" width="200px" height="200px">
			</a>
		</div>
		<h1><?php site() ?></h1>
		</header>
		<nav>
			<a href="index.php">Accueil</a>
			<br/>
			<br/>
			<a href="pdf/regles-scotland-yard.pdf">Règle</a>
		</nav>
		<div id='conteneur'>
		<?php Statistique(); ?>
		</div>
