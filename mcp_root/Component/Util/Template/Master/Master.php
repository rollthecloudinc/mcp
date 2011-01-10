<?php $this->doctype(); ?>
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title><?php $this->title(); ?></title>
	<?php $this->meta(); ?>
	<?php $this->css(); ?>
	<?php $this->js(); ?>
</head>
<body>

<div id="container">

<ul>
	<li><a href="/index.php/<?php $this->_objMCP->getBaseUrl().'/' ?>Admin/Settings">Settings</a></li>
	<li><a href="/index.php/<?php $this->_objMCP->getBaseUrl().'/' ?>Admin/Content">Content</a></li>
	<li><a href="/index.php/<?php $this->_objMCP->getBaseUrl().'/' ?>Admin/Vocabulary">Vocabularies</a></li>
	<!--  <li><a href="/index.php/<?php $this->_objMCP->getBaseUrl().'/' ?>Admin/VD">Displays</a></li> -->
	<li><a href="/index.php/<?php $this->_objMCP->getBaseUrl().'/' ?>Admin/Navigation">Navigation</a></li>
	<li><a href="/index.php/<?php $this->_objMCP->getBaseUrl().'/' ?>Admin/Users">Users</a></li>
	<li><a href="/index.php/<?php $this->_objMCP->getBaseUrl().'/' ?>Admin/Sites">Sites</a></li>
</ul>

<?php $this->header(); ?>

<div id="view"><?php $this->content(); ?></div>

<?php $this->footer(); ?>

</div>

</body>
</html>