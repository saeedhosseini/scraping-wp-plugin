 jQuery('body').on('click', '.confirm', function () {
	var data = jQuery(this).data('param');
	//var data = jQuery(this).data('param').split('|');
	var Url = jQuery('script[src*=action-script]').attr('src'); 
	Url = Url.replace('js/action-script.js?ver=1.0.0', 'action.php'); 
	 var subCat=jQuery('#sub_cat').val();
	 console.log(subCat,"fdfdfdfdfdf");
	jQuery.ajax({
    type: "POST",
    url: Url,
    data: { prodcutId: data,categoryId:subCat }
  }).done(function( msg ) {
		if(msg){
			console.log(msg);
			alert("woocommerce is update");
			
					jQuery("#table").empty();
						jQuery.ajax({
							url:"/turk/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=sub_cat_action&sub_catid=' + subCat,
							success:function(results)
								 {
									 if(results)
				                       jQuery("#table").append(results);
									 else
									   jQuery("#table").append("No Data");
								 }
								   });
		// window.location.reload();	
		}
	     
  });
});


jQuery('#main_cat').change(function(){
					var mainCat=jQuery('#main_cat').val(); 
					  jQuery("#sub_cat").empty();
					  jQuery("#table").empty();
						jQuery.ajax({
							url:"/turk/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=my_special_action&main_catid=' + mainCat,
							success:function(results)
								 {
									 if(results){
										jQuery("#sub_cat").removeAttr("disabled");
										jQuery("#sub_cat").append(results); 
									 }
								  else
									  jQuery("#sub_cat").attr("disabled", true);
								}
								});
						  }	);


jQuery('#sub_cat').change(function(){
					var subCat=jQuery('#sub_cat').val();
					jQuery("#table").empty();
						jQuery.ajax({
							url:"/turk/wp-admin/admin-ajax.php",
							type:'POST',
							data:'action=sub_cat_action&sub_catid=' + subCat,
							success:function(results)
								 {
									 if(results)
				                       jQuery("#table").append(results);
									 else
									   jQuery("#table").append("No Data");
								 }
								   });
						  }	);


 