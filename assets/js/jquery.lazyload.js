(function($){
	run_WP_Lazy_Load();
	$( 'body' ).bind( 'post-load', run_WP_Lazy_Load ); // Infinite scroll
	function run_WP_Lazy_Load(){
		$("img[data-src]").bind("scrollin" , { distance: 200 }, function(){

			var img = this, $img = $(img);
			$img.unbind("scrollin");
			img.src = $img.attr( "data-src" );
		
		});
	}
})(jQuery);