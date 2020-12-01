$('.btn-install')
.removeAttr('disabled')
.click(function(e){
    $('#errors ol').html('')
    $('#errors').addClass('d-none');
    if ($('#setup').verify() == false) {
        return false;
    }
   
})

$('#setup').on('wb-verify-false',function(e,el, err){
    $(el).addClass('border-danger');
    $('#errors').removeClass('d-none');
    $('#errors ol').append('<li>'+err+'</li>');
    setTimeout(function(){
        $(el).removeClass('border-danger');
    },1000)
})