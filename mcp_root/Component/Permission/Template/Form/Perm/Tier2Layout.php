<form action="<?php echo $action; ?>" method="<?php echo $method; ?>" name="<?php echo $name; ?>">
    <fieldset>
        <legend><?php echo $legend; ?></legend>
        
        <div>
            
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
            
        </div>
        
        <?php echo $add; ?>
        
        <div>
            
            <div>
                <?php echo $read_child; ?>
                <?php echo $read_own_child; ?>
            </div>
            
            <div>
                <?php echo $edit_child; ?>
                <?php echo $edit_own_child; ?>
            </div>
            
            <div>
                <?php echo $delete_child; ?>
                <?php echo $delete_own_child; ?>
            </div>
            
        </div>

        <?php echo $submit; ?>
    
    </fieldset>
</form>