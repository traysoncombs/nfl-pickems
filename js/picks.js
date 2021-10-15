function init_submit_button(){
  $("#submit_button").click(function(){
    var entries = []
    var return_all = false;
    $(".entry:not(.locked)").each(function(){
      if (!$(this).find(".team_entry").attr("winner")){
        alert("Make sure all teams have been selected.");
        return_all = true;
        return false;
      } else if ( $(this).hasClass('locked') ) { // dont add locked entries, need server side check for this
        return;
      }
      entries.push(entry = {
        event_id: $(this).attr("data-event"),
        winner_id: $(this).find(".team_entry").attr("winner"),
        confidence: $(this).find('.confidence').text()
      });
    });
    if (return_all) {
      return false;
    }
    let urlParams = new URLSearchParams(window.location.search);
    $.ajax({
      url: '/picks',
      method: 'POST',
      data: {
        "entries": entries,
        "week": urlParams.get('week'),
        "username": urlParams.get('username')
      },
      statusCode: {
        400: function(){
          swal({
            title: "Error",
            icon: "error",
            text: "Picks failed to submit, this has been been logged and will hopefully be resolved",
            timer: 2000
          });
        },
        200: function(){
           swal({
             title: "Success",
             icon: "success",
             text: "Picks successfully submitted!",
             timer: 2000
           });
        }
      }}
    );
  });
}

function init_selectors() {
  $('.team-selector:not(.locked)').click(function(clicked){
    $(this).parent().find('.team-selector').each(function(){
      if($(this)[0] == clicked.currentTarget && !$(this).attr('selected')) {
        $(this).attr('selected', '1');
        $(this).css('background-color', '#'+$(this).attr('data-team-color'));
        $(this).css('color', 'White');
        var team_id = $(this).attr('data-team');
        $(this).parent().attr('winner', team_id);
      } else if ($(this)[0] != clicked.currentTarget && $(this).attr('selected')) {
        $(this).removeAttr('selected');
        $(this).css('background-color', '');
        $(this).css('color', '');
      }
    });
  });
}

function make_pretty(){
  $(".entry .team-selector").each(function(){
    if ($(this).attr('selected')){
      $(this).css('background-color', '#'+$(this).attr('data-team-color'));
      $(this).css('color', 'White');
    }
  });
}

function create_sortable(swap, events){
  var el = document.getElementById('picks');
  var getIndex = function(index) {
    return ( ( index + 1 ) * events ) % ( events + 1 );
  }
  var options = {
    scroll: true,
    forceFallback: true,
    swapClass: "highlight",
    filter: ".locked",
    handle: ".handle",
    scrollSensitivity: 100,
    scrollSpeed: 30,
    animation: 150,
  }
  if (swap) {
    options.swap = true;
    options.onMove = function (evt) { // prevents swapping of disabled elements
      return evt.related.className.indexOf('locked') === -1;
    };
    options.onEnd = function (evt){
      evt.item.children[2].children[0].innerText = getIndex(evt.newIndex);
      evt.swapItem.children[2].children[0].innerText = getIndex(evt.oldIndex);
    };
  }
  else {
    options.swap = false;
    options.onChange = function(evt) {
      $("#picks").children('.entry:not(.sortable-drag)').each(function(counter){
        $(this).find('.confidence').text(getIndex(counter))
      });
      //$("#picks").find('.sortable-chosen').find('.confidence').text(((evt.newIndex+1)*16)%17);
    };
    options.ghostClass = "ghost";
  }
  Sortable.create(el, options);
}
