<form action="<?php echo $action; ?>" method="<?php echo $method; ?>" name="<?php echo $name; ?>"> 
    <fieldset>
        <legend><?php echo $legend; ?></legend>
    
        <?php echo $add; ?>

        <div>
            <?php echo $read; ?>
            <?php echo $read_own; ?>
        </div>

        <div>
            <?php echo $read; ?>
            <?php echo $read_own; ?>
        </div>

        <div>
            <?php echo $edit; ?>
            <?php echo $edit_own; ?>
        </div>

         <div>
            <?php echo $delete; ?>
            <?php echo $delete_own; ?>
        </div>  

        <?php echo $submit; ?>
    
    </fieldset>
</form>
