

var $ = jQuery.noConflict();
$(document).on('click', 'button.ajax', function(){
    that = $(this);
    var event_id = that.data("event_id");
    var url = URL_FOR_ORDER_TICKETS;
    that.hide();
    $('#ajax-results'+event_id).show();
    $.ajax({
       url:url,
       type: "POST",
       data: {
           "event_id": event_id
       },
       success: function (data)
       {

           $('#ajax-results'+event_id).html(data);

          $('#toggle_'+event_id).show();
       }
   });

    return false;

});
$(document).on('click', 'button.toggle', function(){
  that = $(this);
  var event_id = that.data("event_id");
  $('#ajax-results'+event_id).toggle();
  return false;
});

function sumAmount(input){

  var event_id = input.data("event_id");
  var ticket_id = input.data("ticket_id");
  var price = input.data("price");
  var amount_anterior = input.data("amount");
  var total_anterior = input.data("total");
  var total =   price*input.val();
  if(input.val() >= 0){
    $('#ticket_total_'+event_id+'_'+ticket_id).html('Total = '+total+'€');
    var dif_amount = amount_anterior - input.val();
    var dif_total = total_anterior - total;
    input.data("amount",input.val());
    input.data("total",total);

    var total_event = $('#total_event_'+event_id).data("total");
    var amount_event = $('#total_event_'+event_id).data("amount");
    total_event = total_event - dif_total;
    amount_event = amount_event - dif_amount;
    $('#total_event_'+event_id).html(amount_event+" tickets, total: "+total_event+'€');
    $('#total_event_'+event_id).data("total",total_event);
    $('#total_event_'+event_id).data("amount",amount_event);

    var total_order = $('#order').data("total");
    var amount_order = $('#order').data("amount");
    total_order = total_order - dif_total;
    amount_order = amount_order - dif_amount;
    $('#order').html(amount_order+" tickets<br> total: "+total_order+'€');
    $('#order').data("total",total_order);
    $('#order').data("amount",amount_order);

  }else{
    $('#ticket_total_'+event_id+'_'+ticket_id).html('Precio '+price+'€');
    input.val(0);
  }


}
