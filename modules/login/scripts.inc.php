<script type="text/wbapp">
'use script'
$(document).on('wb-verify-false',function(e,input){
    $(input).addClass('is-invalid').removeClass('is-valid');
})

$(document).on('wb-verify-true',function(e,input){
    $(input).addClass('is-valid').removeClass('is-invalid');
})
</script>