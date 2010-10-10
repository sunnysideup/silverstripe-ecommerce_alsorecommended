<hr /><hr />recommended products<hr /><hr />
<% if EcommerceRecommendedProducts %>
<div id="EcommerceRecommendedProducts">
	<h3>You may also be interested in the following products</h3>
	<ul id="EcommerceRecommendedProducts">
		<% control EcommerceRecommendedProducts %><li class="$OddEven $FirstLast"><% include ProductGroupItem %></li><% end_control %>
	</ul>
</div>
<% end_if %>
<hr /><hr />