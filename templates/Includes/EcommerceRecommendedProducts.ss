<% if EcommerceRecommendedProducts %>
<div id="EcommerceRecommendedProducts">
	<h3><% _t("YOUMAYALSO", "You may also be interested in the following products") %></h3>
	<ul class="productList">
		<% loop EcommerceRecommendedProducts %><% include ProductGroupItem %><% end_loop %>
	</ul>
</div>
<% end_if %>
