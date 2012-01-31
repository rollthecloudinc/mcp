<?php $this->doctype(); ?>
<html lang="en">
<head>
	<?php $this->meta(); ?>
        <?php $this->css(); ?>
</head>
<body>

<div id="container" style="padding: 0 1em;">

    <div class="topbar">
        <div class="topbar-inner">
            <div class="container">
                <p class="brand">mcp</p>
                <?php $this->admin(); ?>  
                <?php $this->login(); ?>
            </div>
        </div>
    </div>

    <div id="content">
        
        <?php $this->breadcrumbs(); ?>
    
        <?php $this->messages(); ?>
    
        <div id="main-content">
            <?php $this->content(); ?>
        </div>
    
    </div>

    <?php $this->footer(); ?>

</div>

    
    <?php $this->js(); ?>
</body>
</html>