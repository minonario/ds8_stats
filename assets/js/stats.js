/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Other/javascript.js to edit this template
 */


jQuery( function ( $ ) {
  
    /*lightGallery(document.getElementById('main'), {
        plugins: [lgZoom],
        selector: '.custom-lightbox',
        controls: false,
        enableDrag: false,
        pager: false
    });*/
  
  document.getElementById('listofoptions').onchange = function(){
              
    //jQuery('.entry-content').find('.visible').removeClass('oculto');

    const params = new URLSearchParams(window.location.search);
    const url = window.location.href.split('?')[0];

    console.log('destination='+url);

    if( this.value !== '-1' ){
      window.location=url+'?pyear='+this.value
    }else{
      window.location=url
    }
  };
  
  $(document).on('click','.btn-estadistica', function(event){
    event.stopPropagation();
    event.preventDefault();
    var $this = $(event.target);
    console.log('href:'+$this.data("link"));
    document.location.href = $this.data("link");
  });
  
});
