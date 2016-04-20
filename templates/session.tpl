{extends file="subpage.tpl"}

{block name="head-scripts"}
	<script>
		var id = '{$id|default:''}';
	</script>
	
{/block}

{block name="subcontent"}
	
	<div class="container">
		<div id="carousel"></div>
		<div id="hero" class="embed-responsive embed-responsive-4by3"></div>
	</div>	
	
{/block}

{block name="post-bootstrap-scripts"}
	
	<script src="https://static.opentok.com/v2/js/opentok.min.js"></script>
	<script src="js/session.js.php"></script>
	
{/block}
