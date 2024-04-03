jQuery(document).ready(function(){
	jQuery('.addFav').click(function(e) {
		e.preventDefault();
		fav(jQuery(this).data('game'));
	});
                                                    
	jQuery('.removeFav').click(function(e) {
		e.preventDefault();
		unfav(jQuery(this).data('game'), jQuery(this).data('reload'));
	});

	jQuery('.moveUp').click(function(e) {
		e.preventDefault();
	    var game1 = $(this).data('game');
	    var game2 =  $(this).parent().parent().parent().prev().data('game');
	    updateListOrder(game1, game2);
  	});

  	jQuery('.moveDown').click(function(e) {
  		e.preventDefault();
	    var game1 = $(this).data('game');
	    var game2 =  $(this).parent().parent().parent().next().data('game');
	    updateListOrder(game1, game2);
  	});

  	jQuery('#sube-partida.new-game .form-control.timepicker').val(null);
});

function fav (game) {
	jQuery(".button-container button").prop("disabled",true);
	jQuery.ajax({
        url: '/games/'+game+'/fav',
        type: 'GET',
        success: function (data) {
        	jQuery(".button-container button").prop("disabled",false);
        	jQuery('.button-container[data-game='+game+']').html('<button class="removeFav" data-game="'+game+'" onclick="unfav('+game+')"><i class="fa-solid fa-heart"></i> Eliminar de favoritas</button>');
        },
        error:function(request){
        	jQuery(".button-container button").prop("disabled",false);
        }
    });
}

function unfav (game, reload=false) {
	jQuery(".button-container button").prop("disabled",true);
	jQuery.ajax({
        url: '/games/'+game+'/unfav',
        type: 'GET',
        success: function (data) {
        	if (reload){
        		location.reload();
        	} else {
        		jQuery(".button-container button").prop("disabled", false);
        		jQuery('.button-container[data-game='+game+']').html('<button class="addFav" data-game="'+game+'" onclick="fav('+game+')"><i class="fa-regular fa-heart"></i> Añadir a favoritas</button>');	
        	}
        	
        },
        error:function(request){
        	jQuery(".button-container button").prop("disabled", false);
        }
    });
}

function updateListOrder(game1, game2) {
	$.ajax({
	  type: 'GET',
	  url: '/games/'+game1+'/'+game2+'/exchange',
	  data: {},
	  success: function(response) {
	  	console.log(response);
	  	location.reload();
	  },
	  error: function(xhr, status, error) {
	  	console.log(error);
		}
	});
}