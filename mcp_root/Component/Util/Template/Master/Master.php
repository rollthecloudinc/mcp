<?php $this->doctype(); ?>
<html lang="en">
<head>
	<?php $this->meta(); ?>
	<?php $this->css(); ?>
	<?php $this->js(); ?>
</head>
<body>

<div id="container">

<?php $this->admin(); ?>
<?php $this->header(); ?>
<?php $this->messages(); ?>

<div id="view"><?php $this->content(); ?></div>

<?php $this->footer(); ?>

</div>

</body>
</html>