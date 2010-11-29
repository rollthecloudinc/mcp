<script type="text/javascript">
/*
* Code mirror initiation 
*/
mcp.hash.push(function() {

	var config = {
		parserfile: [
			,"parsexml.js"
			,"parsecss.js"
			,"tokenizejavascript.js"
			,"parsejavascript.js"
			,"../contrib/php/js/tokenizephp.js"
			,"../contrib/php/js/parsephp.js"
			,"../contrib/php/js/parsephphtmlmixed.js"],
		path: "/lib/codemirror/pkg/js/",
		stylesheet: [
			,"/lib/codemirror/pkg/css/xmlcolors.css"
			,"/lib/codemirror/pkg/css/jscolors.css"
			,"/lib/codemirror/pkg/css/csscolors.css"
			,"/lib/codemirror/pkg/contrib/php/css/phpcolors.css"]
		};
	
	var nodeContentEditor = CodeMirror.fromTextArea("node-content",config);
	//var blogIntroEditor =  CodeMirror.fromTextArea("node-intro-content",config);
});
</script>
<?php echo $this->ui('Common.Form.Form',array(
	'name'=>$name
	,'action'=>$action
	,'config'=>$config
	,'values'=>$values
	,'errors'=>$errors
	,'legend'=>$legend
	,'idbase'=>'node-'
)); ?>
