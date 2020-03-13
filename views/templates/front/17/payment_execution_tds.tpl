{extends file='page.tpl'}

{block name="page_content"}
<script>
	if (window.jQuery === undefined) {
		var s = document.createElement('script');
		s.src = "//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.js";
		document.head.appendChild(s);
	}
</script>
{$tdsform nofilter}
{/block}