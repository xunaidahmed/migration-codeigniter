<script type="text/javascript">

  $('.form-group').hide();

  $.ajax({
    type: "POST",
    url: "<?php echo base_url('welcome');?>/migration",    
    success: function(data)
    {
      if(data > 0)
      {
        $('#loginmodal').modal({backdrop: 'static', keyboard: false});
        now_executes();
      }
      else
      {
        $('.form-group').show();
      }
    }
  });

  function now_executes()
  {
    var url             = "<?php echo base_url('welcome');?>/execute_migrate";
    let ajaxTime        = new Date().getTime();
    let totalTime       = 0;
    let progress        = $(".loading-progress").progressTimer({timeLimit: 10,});

    $.ajax({
      type: "POST",
      url: url,
      beforeSend: function() {
        $('.loading-progress > .progress').addClass('progress-lg');
        progress.fadeIn("slow");
        $(".alert-block").show();
        $(".alert-block").addClass('alert-info');
        $(".text-white").html('Database script is running ...');    	
        $(".alert-block p").html('Database script is running ...');
        $('#loginmodal').modal();
      },
      success: function(data)
      {
        var response = data.split(',');
        setTimeout(function()
        {
          if(response[0].replace(/\s+/g, '')=="alert-danger")
          {
            progress.progressTimer('error', {errorText:'No records found!'});
            $(".text-white").hide();
            $(".progress").hide();
            $(".alert-block").addClass(response[0]);
            $(".alert-block").html(response[1]+'. Please contact technical support / administrator for further assistance.');
            $(".modal-title").text('Error Occur During Database Updation Process');
            $(".alert-block").css("display", "block");
          }
          else{
            $("#loginmodal").modal('toggle');
            $('.modal-backdrop').remove();
            $('#loginmodal').modal('hide');
            $('.form-group').show();
          }
        },3000);
        $('input[name=<?php echo $this->security->get_csrf_token_name(); ?>]').val(getCookie('csrf_cookie_name'));
      }

    });
  }
</script>