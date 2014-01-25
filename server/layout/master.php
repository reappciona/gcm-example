<?php
/**
 * @package		appkr/gcm-example
 * @file		/layout/master.php
 * @author		Juwon Kim <juwonkim@me.com>
 * @brief		master layout
 */
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="description" content="reference implementation of Google Cloud Messaging - 3rd application server in php and Android client in java" />
		<meta name="author" content="juwonkim@me.com" />

		<title><?php echo $doc_title;?></title>

		<link href="res/css/bootstrap.min.css" rel="stylesheet">
		<link href="res/css/style.css" rel="stylesheet">

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="res/js/html5shiv.js"></script>
		  <script src="res/js/respond.min.js"></script>
		<![endif]-->

	</head>

	<body>

<?php echo $content;?>

		<div class="container breathing">
			<footer>
				<p class="pull-right"><a href="#">Back to top</a></p>
				<p><a href="https://github.com/appkr/gcm-example" target="_top"><?php echo $project;?></a></p>
			</footer>
		</div>

		<!-- Bootstrap core JavaScript -->
		<script src="res/js/jquery.js"></script>
		<script src="res/js/bootstrap.min.js"></script>
<?php if (stristr($_SERVER['PHP_SELF'], 'apidoc') === false):?>
		<script src="res/js/form_manipulation.js"></script>
<?php endif;?>

	</body>
</html>
