<% if EcommerceRecommendedProducts %>
<div id="EcommerceRecommendedProducts">
	<h3><% _t("YOUMAYALSO", "You may also be interested in the following products") %></h3>
	<ul class="productList">
		<% control EcommerceRecommendedProducts %><% include ProductGroupItem %><% end_control %>
	</ul>
</div>
<% end_if %>

<% require themedCSS(ProductGroup) %>
