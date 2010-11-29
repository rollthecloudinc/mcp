<script type="text/javascript" src="/lib/mcp/pkg/core/mcp.js"></script>
<script type="text/javascript" src="/lib/codemirror/pkg/js/codemirror.js"></script>
<script type="text/javascript" src="/lib/jquery/pkg/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/lib/yui/pkg/build/yui/yui-min.js"></script>
<script type="text/javascript">
var mcp = {
	hash:[]
};

window.onload = function(e) {
	for(var i=0;i<mcp.hash.length;i++) {
		mcp.hash[i]();
	}
};
</script>