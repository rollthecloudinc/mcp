<ul>
	<li><strong>Name:</strong>&nbsp;<?php echo $this->out($values['name']); ?></li>
	<li><strong>Company:</strong>&nbsp;<?php echo $this->out(strlen($values['company'])==0?$values['company']:'--'); ?></li>
	<li><strong>Email:</strong>&nbsp;<?php echo $this->out($values['email']); ?></li>
	<li><strong>Phone:</strong>&nbsp;<?php echo $this->out(strlen($values['phone'])==0?$values['phone']:'--'); ?></li>
	<li><strong>Website:</strong>&nbsp;<?php echo $this->out(strlen($values['website'])==0?$values['website']:'--'); ?></li>
</ul>
<div><?php echo wordwrap($values['message'],70); ?></div>