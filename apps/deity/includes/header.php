<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<base href="http://<?php echo BASE_DOMAIN; ?>">
	<title><?php echo (isset($config['pageTitle']) ? $config['pageTitle'] : 'Skysteed'); ?></title>
	
	<!-- Meta Data -->
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<meta charset="UTF-8" />
	
	<link rel="icon" type="image/gif" href="/favicon.gif">
	<link rel="canonical" href="<?php echo (isset($config['pageTitle']) ? $config['pageTitle'] : '/' . $url_relative); ?>" />
	
	<!-- JQuery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	
	<!-- Primary Stylesheet -->
	<link rel="stylesheet" href="<?php echo CDN; ?>/assets/css/phpTesla.css" />
	<link rel="stylesheet" href="/assets/css/icomoon.css" />
	
	<?php /*
	<script src="<?php echo CDN; ?>/js/modernizr.js"></script>
	<link rel="stylesheet" href="<?php echo CDN; ?>/assets/css/flexslider.css" type="text/css" media="screen" />
	*/ ?>
	
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	
	<?php echo Metadata::header() ?>
	
</head>
<body>

<div id="header-bar">
	<div class="container-wrap"><div class="container-inner">
		<div class="left">
			<ul class="menu-nav">
				<!-- <li><a href="/">Single Slot</a></li> -->
				<li>
					<span class="menu-nav-top">Main</span>
					<ul>
						<li><a href="/tutorial">Intro &amp; Tutorial</a></li>
						<li><a href="/mechanics">Core Mechanics</a></li>
						<li><a href="/classes">Classes</a></li>
						<li><a href="/assets/files/character-sheet.pdf">Character Sheet</a></li>
						<!-- <li><a href="/game-master">GM Book &amp; Tools</a></li> -->
						<!-- <li><a href="/blog">Blog</a></li> -->
					</ul>
				</li>
				<li>
					<span class="menu-nav-top">Setting</span>
					<ul>
						<li><a href="/setting/lore">Lore</a></li>
						<li><a href="/setting/races">Races &amp; Monsters</a></li>
						<li><a href="/setting/territories">Nations &amp; Cities</a></li>
						<li><a href="/setting/organizations">Organizations</a></li>
					</ul>
				</li>
				<li>
					<span class="menu-nav-top">References</span>
					<ul>
						<li><a href="/references/resources">Assets &amp; Resources</a></li>
						<li><a href="/references/beasts">Beasts</a></li>
						<li><a href="/references/magical-assets">Magical Assets</a></li>
						<li><a href="/references/occult-skills">Occult Skills</a></li>
						<li><a href="/references/sorcery-powers">Sorcery Powers</a></li>
						<li><a href="/references/spells">Spells</a></li>
						<!-- <li><a href="/references/golemancy">Golemancy</a></li> -->
					</ul>
				</li>
			</ul>
		</div>
		<div class="right">
			<div class="menu-logo"><a href="#">Home</a></div>
		</div>
	</div></div>
</div>
<div id="header-push"></div>